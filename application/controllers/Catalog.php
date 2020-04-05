<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require_once $_SERVER["DOCUMENT_ROOT"] . "/application/libraries/ReCaptcha.php";

class Catalog extends CI_Controller
{

	protected $user_info = [];
	protected $store_info = [];

	protected $post = [];
	protected $get = [];

	public function __construct()
	{


		parent::__construct();
		$this->user_info = ($this->mdl_users->user_data()) ? $this->mdl_users->user_data() : false;
		$this->store_info = $this->mdl_stores->allConfigs();

		$this->post = $this->security->xss_clean($_POST);
		$this->get = $this->security->xss_clean($_GET);

	}

	// Предворительная обработка системными средствами
	public function _remap($method, $params = [])
	{
		if (method_exists($this, $method)) {
			return call_user_func_array([$this, $method], $params);
		} else {
			return call_user_func_array([$this, "ExtractTree"], func_get_args());
		}
	}

	// Серверное извлечение всех алиасов из URL

	/**
	 * получение фильтра по ID
	 *
	 * @param int $filterId
	 * @return array
	 */
	protected function getFilter($filterId,$category_id=false)
	{
		$filter = $this->mdl_category->queryData([
			'return_type' => 'ARR1',
			'table_name' => 'products_filters',
			'where' => [
				'method' => 'AND',
				'set' => [
					['item' => 'id', 'value' => $filterId],
				],
			],
			'labels' => ['id', 'labels'],
			'module' => false,
		]);

		$filterData = $filter ? json_decode($filter['labels'], true) : [];

		$filterData = array_map(function ($filterItem) {
			if (isset($filterItem['data']) && is_array($filterItem['data'])) {
				$filterItem['data'] = array_filter($filterItem['data'], function ($filterDataItem) {
					return !isset($filterDataItem['hidden']) || !$filterDataItem['hidden'];
				});
			}
			return $filterItem;
		}, $filterData);

		$filterData = array_filter($filterData, function ($filterItem) {
			return !isset($filterItem['hidden']) || !$filterItem['hidden'];
		});

		if ($category_id) {
			$query_max_price = $this->db->query(" SELECT MAX(price_real) as max_price FROM `products` WHERE cat='".$category_id."' AND view>'0' AND qty>'0' AND moderate>'1'");
		} else {
			$query_max_price = $this->db->query(" SELECT MAX(price_real) as max_price FROM `products` WHERE view>'0' AND qty>'0' AND moderate>'1' ");
		}
		$max_price = $query_max_price->row_array();
		$price = $max_price['max_price']/100;
		$filterData[0]['max_price'] = ($price<50000) ? 50000 : $price;
		
		return $filterData;
	}

	/**
	 * парсинг фильтра
	 *
	 * @param array $filter
	 * @return array
	 */
	protected function parseFilter($filter)
	{
		$result = [];

		foreach ($filter as $filterItem) {
			if (!$filterItem['variabled']) {
				continue;
			}
			$dataOrType = $filterItem['type'] == 'range-values' ? $filterItem['type'] :
				(isset($filterItem['data']) && is_array($filterItem['data']) ?
					array_flip(array_map(function ($dataItem) {
						return $dataItem['variabled'];
					}, $filterItem['data'])) :
					[]
				);

			$result[$filterItem['variabled']] = $dataOrType;
		}

		return $result;
	}

	private function ExtractTree()
	{

		$argList = func_get_args();
		$arg_list = ($argList !== false) ? $argList : [];
		$item = (count($arg_list[1]) > 0) ? $arg_list[1][(count($arg_list[1]) - 1)] : $arg_list[0];
		$tree = [];
		$tree[] = $arg_list[0];
		foreach ($arg_list[1] as $v) {
			$tree[] = $v;
		}
		// Передача алиасов и получение информации и инструкций...
		$logicData = $this->getLogicData($tree, false);

		if ($logicData['error404'] !== true) {
			$variable = $logicData['method'];
			self::$variable($logicData);
		} else {
			redirect('/catalog');
		}

	}

	// Получение информации и инструкций [ сервер / json ]
	public function getLogicData($tree = [], $j = true)
	{

		$tree = (isset($this->post['tree'])) ? $this->post['tree'] : $tree;
		$r = [
			'item' => [],
			'brb' => [
				[
					'name' => 'Каталог',
					'link' => '/catalog',
				],
			],
		];
		$lastAliase = $tree[count($tree) - 1];

		if (count($tree) > 0) {

			$product = $this->mdl_product->queryData([
				'return_type' => 'ARR1',
				'where' => [
					'method' => 'AND',
					'set' => [[
						'item' => 'aliase',
						'value' => $lastAliase,
					]],
				],
				'labels' => ['id', 'aliase', 'title', 'modules'],
				'module' => true,
				'modules' => [[
					'module_name' => 'linkPath',
					'result_item' => 'linkPath',
					'option' => [],
				]],
			]);

			$linkCurrent = '/catalog/' . implode('/', $tree);
				// нас интересует категория последнего уровеня (сейчас каталог одноуровневый, но может быть и многоуровневым)
				// родительские категории добавляются в хлебные крошки
				$category = null;
				$parentCategoryId = 0;
				$link = '/catalog';
				do {
					$categoryAliase = reset($tree);
					$categoryCurrent = $this->mdl_category->queryData([
						'return_type' => 'ARR1',
						'where' => [
							'method' => 'AND',
							'set' => [
								['item' => 'aliase', 'value' => $categoryAliase],
								['item' => 'parent_id', 'value' => $parentCategoryId],
							],
						],
						'labels' => ['id', 'aliase', 'name', 'desription', 'filter_id'],
					]);
					if ($categoryCurrent) {
						array_shift($tree);
						$category = $categoryCurrent;
						$parentCategoryId = $categoryCurrent['id'];
						$link .= '/' . $categoryCurrent['aliase'];
						$r['brb'][] = [
							'name' => $categoryCurrent['name'],
							'link' => $link,
						];
					}
				} while ($categoryCurrent);
				
				
			if ($product) {
				array_pop($tree);
				$r['method'] = 'view_product';
				$r['item'] = $product;
				$r['item']['cat'] = $category;
				$r['error404'] = ($linkCurrent === $r['item']['modules']['linkPath']) ? false : true;
			} else {


				// ToDo: для главной страницы доделать!
				if ($category) {
					// удаляем текущую категорию из хлебных крошек
					array_pop($r['brb']);
				}

				if (count($tree) == 1) { // значит установлены фильтры (в последней части урла)

					// значения фильтра в урле бывают в виде val-1-i-val2-filter1_val3-i-val4-filter2
					// то есть группы фильтров разделены '_', имя фильтра в группе идет в конце через дефис
					// значения фильтров разделены '-i-', сами значения могут содержать дефис

					$filtersFromUrl = explode('_', trim(array_shift($tree)));

					if (count($filtersFromUrl)) {

						$filter = $this->getFilter($category ? $category['filter_id'] : 11, $category['id']);
						$filter = $this->parseFilter($filter);

						$filterSettings = [];

						foreach ($filtersFromUrl as $urlPart) {
							$filterParts = explode('-', $urlPart);
							$filterName = array_pop($filterParts);

							if (!isset($filter[$filterName]) || !count($filterParts)) {
								$r['error404'] = true;
								break;
							}

							$filterParts = implode('-', $filterParts);

							if ($filter[$filterName] == 'range-values') {
								preg_match('/^(?:from-(?<from>\d+))?-?(?:to-(?<to>\d+))?$/', $filterParts, $matches);

								if (!isset($matches['from']) && !isset($matches['to'])) {
									$r['error404'] = true;
									break;
								}

								$filterSettings[$filterName] = $matches['from'] . '|' . (isset($matches['to']) ? $matches['to'] : '');
							} else {
								$filterParts = explode('-i-', $filterParts);

								if (is_array($filter[$filterName])) {
									foreach ($filterParts as $filterPart) {
										if (!isset($filter[$filterName][$filterPart])) {
											$r['error404'] = true;
											break 2;
										}
									}
									$filterSettings[$filterName] = implode('|', $filterParts);
								}
							}
						}
						$this->get['f'] = isset($this->get['f']) && is_array($this->get['f']) ?
							array_merge($filterSettings, $this->get['f']) : $filterSettings;
					} else {
						$r['error404'] = true;
					}
				}
				if (!isset($r['error404']) || !$r['error404']) {
					$r['error404'] = false;
					if ($category) {
						$r['method'] = 'view_category';
						$r['item'] = $category;
					} else {
						$r['method'] = 'index';
					}
				}
			}
		}

		if (!isset($r['error404'])) {
			$r['error404'] = false;
		}

		if ($j === true) {
			$this->mdl_helper->__json($r);
		} else {
			return $r;
		}

	}

	// Вывести главную страницу каталога
	public function index()
	{
		
		$start = microtime(true);

		$page_var = 'catalog';

//		$getData = $this->getCategoryHome();
		$getData = $this->getCatData();

		$title = (!isset($getData['setFilters']['category']) ? 'Каталог ювелирных изделий ювелирной группы компаний Монарх, Настоящее золото - цены и фото на золотые украшения со скидкой в Москве' : '') . $getData['filterTitle'];
		$h1 = (!isset($getData['setFilters']['category']) ? 'Ювелирные изделия в интернет-магазине - каталог украшений' : '') . $getData['filterTitle'];
		$title = mb_strtoupper(mb_substr($title, 0, 1)) . mb_substr($title, 1);

		// Если нет товаров, ставим заглушку
		if (count($getData["products"]) < 1) {
			$descr = "<p style='font-size:18px;font-weight:bold;'>
				Не нашли, что искали? 
				Закажите <a href='#' data-toggle='modal' data-target='#modal_callback' style='color:#337ab7;'>звонок консультанта</a> или напишите в чат - возможно, на сайте идут работы.
				Приносим свои извинения за неудобства!
			</p>";
		} else $descr = "";

		$this->mdl_tpl->view('templates/doctype_catalog.html', [

			'title' => $title,
			'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'),
			'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),

			'seo' => $this->mdl_tpl->view('snipets/seo_tools.html', [
				'mk' => (!empty($this->store_info['seo_keys'])) ? $this->store_info['seo_keys'] : '',
				//'md' => (!empty($this->store_info['seo_desc'])) ? $this->store_info['seo_desc'] : '',
				'md' => 'Золотые украшения по низким ценам. Посмотрите каталог с фото и закажите бесплатную доставку. Покупка после примерки. Скидки на ювелирные изделия из золота.',
			], true),

			'navTop' => $this->mdl_tpl->view('snipets/navTop.html', [
				'store' => $this->store_info,
				'active' => 'home',
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			], true),

			'header' => $this->mdl_tpl->view('snipets/header.html', [
			
			    'filter' => 1, 
				'title' => '',
				'store' => $this->store_info,
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			], true),

			'navMenu' => $this->mdl_tpl->view('snipets/navMenu.html', [
				'store' => $this->store_info,
				'active' => 'home',
				'itemsTree' => $this->mdl_category->getTreeMenu(),
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			], true),

			'content' => $this->mdl_tpl->view('pages/catalog/home.html', [
				'blocks' => $this->mdl_tpl->view('pages/catalog/filters/items.html', [
					'items' => $getData['filter'],
					'price' => $getData['cena'],
					'weight' => $getData['weight'],
				], true),
				'textSearch' => $getData['textSearch'],
				'sort' => $getData['sort'],
				'header_title' => $h1,
				'collections' => $this->mdl_tpl->view('pages/catalog/category_view_collections.html', [
					'items' => $getData['collections'],
				], true),
				'products' => $this->mdl_tpl->view('pages/catalog/category_view_products.html', [
					'items' => $getData['products'],
				], true),
				'pagination' => $getData['products_pag'],
				'description' => $descr,
			], true),

			'footer' => $this->mdl_tpl->view('snipets/footer.html', [
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			], true),

			'load' => $this->mdl_tpl->view('snipets/load.html', [
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'utmLabels' => $this->mdl_tpl->view('snipets/utmLabels.html', $this->mdl_seo->utmLabels($this->get), true),
			], true),

			'resorses' => $this->mdl_tpl->view('resorses/catalog/cats_head.html', [
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'),
				'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path'),
			], true),

		], false);

		// echo '<p style="background: yellow none repeat scroll 0% 0%; margin: 20px 0px 0px; position: fixed; bottom: 0px;">Время выполнения скрипта: '.(microtime(true) - $start).' сек.</p>';

	}

	/*	// Получить все товары для главной страницы
		public function getCategoryHome($j = false)
		{
			$r = [
				'products' => [],
				'podcat' => [],
				'collections' => [],
			];

			$query_string = array();
			$query_string = array_merge($query_string, $this->get);
			$query_string = array_merge($query_string, $this->post);

			unset($query_string['page']);
			unset($query_string['limit']);

			$query_string = $this->mdl_helper->clear_array_0($query_string, array(
				'f', 's', 'l', 't', 'brand',
			));

			$sffix = $query_string;

			$filter = $this->mdl_category->queryData([
				'return_type' => 'ARR1',
				'table_name' => 'products_filters',
				'where' => [
					'method' => 'AND',
					'set' => [
						['item' => 'id', 'value' => 11],
					],
				],
				'labels' => ['id', 'labels'],
				'module' => false,
			]);

			$filter = ($filter) ? json_decode($filter['labels'], true) : [];

			$option = [
				'return_type' => 'ARR2+',
				'debug' => true,
				'where' => [
					'method' => 'AND',
					'set' => [[
						'item' => 'view >',
						'value' => 0,
					], [
						'item' => 'qty >',
						'value' => 0,
					], [
						'item' => 'moderate >',
						'value' => 1,
					]],
				],
				'group_by' => 'articul',
				'distinct' => true,
				'labels' => ['id', 'aliase', 'articul', 'title', 'seo_keys', 'seo_desc', 'seo_title', 'prices_empty', 'filters', 'salle_procent', 'modules'],
				'pagination' => [
					'on' => true,
					'page' => (isset($this->get['page'])) ? $this->get['page'] : 1,
					'limit' => (isset($this->get['l'])) ? $this->get['l'] : 40,
				],
				'module_queue' => [
					'price_actual',
					'limit', 'pagination',
					'prices_all', 'photos', 'reviews', 'linkPath', 'salePrice',
					'emptyPrice', 'qty_empty_status', 'paramsView',
				],
				'module' => true,
				'modules' => [[
					'module_name' => 'linkPath',
					'result_item' => 'linkPath',
					'option' => [],
				], [
					'module_name' => 'price_actual',
					'result_item' => 'price_actual',
					'option' => [
						'labels' => false,
					],
				], [
					'module_name' => 'salePrice',
					'result_item' => 'salePrice',
					'option' => [],
				], [
					'module_name' => 'photos',
					'result_item' => 'photos',
					'option' => [
						'no_images_view' => 1,
					],
				], [
					'module_name' => 'emptyPrice',
					'result_item' => 'emptyPrice',
					'option' => [
						'labels' => false,
					],
				]],
			];

			$setFilters = []; // Запомнить установки выбора
			if (isset($this->get['f']) && is_array($this->get['f'])) {

				$f = $this->get['f'];
				$fNew = [];
				foreach ($f as $k => $v) {
					foreach ($filter as $fv) {
						if ($fv['variabled'] == $k) {
							$fNew[$k] = [
								'item' => $k,
								'type' => $fv['type'],
								'values' => explode('|', $v),
							];
						}
					}
				}

				$setFilters = $fNew;

				$option['setFilters'] = $setFilters;
				$r['setFilters'] = $setFilters;
			}

			$r['brand'] = (isset($this->get['brand'])) ? $this->get['brand'] : '';

			if (isset($this->get['brand'])) {
				$b = $this->get['brand'];
				$option['where']['set'][] = [
					'item' => 'postavchik',
					'value' => $b,
				];
			}

			$r['sort'] = (isset($this->get['s'])) ? $this->get['s'] : 'pricemin';

			$sort = $r['sort'];

			if ($sort === 'pop') {
				$option['order_by'] = [
					'item' => 'view',
					'value' => 'DESC',
				];
			}

			if ($sort === 'new') {
				$option['order_by'] = [
					'item' => 'id',
					'value' => 'DESC',
				];
			}

			if ($sort === 'upsells') {
				$option['order_by'] = [
					'item' => 'salle_procent',
					'value' => 'DESC',
				];
			}

			if ($sort === 'pricemin') {
				$option['order_by'] = [
					'item' => 'price_real',
					'value' => 'ASC',
				];
			}

			if ($sort === 'pricemax') {
				$option['order_by'] = [
					'item' => 'price_real',
					'value' => 'DESC',
				];
			}

			$r['textSearch'] = '';
			if (isset($this->get['t'])) {
				$t = $r['textSearch'] = $this->get['t'];
				$option['like'] = [
					'math' => 'both', // '%before', 'after%' и '%both%' - опциональность поиска
					'method' => 'AND', // AND (и) / OR(или) / NOT(за исключением и..) / OR_NOT(за исключением или)
					'set' => [[
						'item' => 'title',
						'value' => $t,
					]]  // [ 'item' => '', 'value' => '' ],[...]
				];
			}

			$option['modules'][] = [
				'module_name' => 'pagination',
				'result_item' => 'pagination',
				'option' => [
					'path' => $_SERVER['REDIRECT_URL'],
					'option_paginates' => [
						'uri_segment' => 1,
						'num_links' => 3,
						'suffix' => $sffix,
					],
				],
			];

			$_r = $this->mdl_product->queryData($option);

			$r['products'] = $_r['result'];
			$r['products_pag'] = $_r['option']['pag'];

			$filterTitle = '';
	//		$r['cena'] = [0, 90000];
			$r['cena'] = [];
			if (isset($this->get['f']['price'])) {
				$prices = explode('|', $this->get['f']['price']);
				$r['cena'] = $prices;
				list($price_from, $price_to) = $prices;
				$price_from = (int)$price_from;
				$price_to = (int)$price_to;
				if ($price_from || $price_to) {
					$filterTitle .= 'ценой ' . ($price_from ? 'от ' . $price_from : '') . ($price_to ? 'до ' . $price_to : '');
				}
			}

			$r['weight'] = [];
			if (isset($this->get['f']['weight'])) {
				$weights = explode('|', $this->get['f']['weight']);
				$r['weight'] = $weights;
				list($weight_from, $weight_to) = $weights;
				$weight_from = (float)$weight_from;
				$weight_to = (float)$weight_to;
				if ($weight_from || $weight_to) {
					$filterTitle .= ' весом ' . ($weight_from ? 'от ' . $weight_from : '') . ($weight_to ? 'до ' . $weight_to : '');
				}
			}

			foreach ($filter as $k => $v) {
				foreach ($setFilters as $sfv) {
					foreach ($v['data'] as $kData => $vData) {
						$filter[$k]['data'][$kData]['check'] = 'off';
					}
				}
			}

			foreach ($filter as $k => $v) {
				$filterTitles = [];
				foreach ($setFilters as $sfv) {
					if ($v['variabled'] === $sfv['item']) {

						foreach ($v['data'] as $kData => $vData) {
							if (in_array($vData['variabled'], $sfv['values'])) {
								$filter[$k]['data'][$kData]['check'] = 'on';
								$filterTitles[] = isset($vData['metaTitle']) && $vData['metaTitle'] ? $vData['metaTitle'] : mb_strtolower($vData['title']);
							} else {
								$filter[$k]['data'][$kData]['check'] = 'off';
							}
						}

					}
				}
				if (count($filterTitles)) {
					$filterTitle .= isset($v['metaTitle']) && $v['metaTitle'] ? ' ' . $v['metaTitle'] . ' ' : ' ';
					$filterTitle .= implode(' и ', $filterTitles);
				}
			};
			$filterTitle = trim($filterTitle);

			$r['filter'] = $filter;
			$r['filterTitle'] = $filterTitle;

			if ($j === true) {
				$this->mdl_helper->__json($r);
			} else {
				return $r;
			}

		}*/

	// Вывести категорию или разделы и товары в ней
	public function view_category($data)
	{

		$start = microtime(true);

		$getData = $this->getCatData($data['item']['id']);
 
		// Если нет товаров, ставим заглушку
		if (count($getData["products"]) < 1) {
			$descr = "<p style='font-size:18px;font-weight:bold;'>
				Не нашли, что искали? 
				Закажите <a href='#' data-toggle='modal' data-target='#modal_callback' style='color:#337ab7;'>звонок консультанта</a> или напишите в чат - возможно, на сайте идут работы. 
				Приносим свои извинения за неудобства!
			</p>";
		} else $descr = "";

		$title = $data['item']['name'] . ' ' . $getData['filterTitle'];
		//$title = ( !empty( $this->store_info['seo_title'] ) ) ? $this->store_info['seo_title'] : $this->store_info['header'];
		$page_var = 'catalog';

		$this->mdl_tpl->view('templates/doctype_catalog.html', [

			'title' => $title,
			'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'),
			'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),

			'seo' => $this->mdl_tpl->view('snipets/seo_tools.html', [
				'mk' => (!empty($this->store_info['seo_keys'])) ? $this->store_info['seo_keys'] : '',
				'md' => (!empty($this->store_info['seo_desc'])) ? $this->store_info['seo_desc'] : '',
			], true),

			'navTop' => $this->mdl_tpl->view('snipets/navTop.html', [
				'store' => $this->store_info,
				'active' => 'home',
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			], true),

			'header' => $this->mdl_tpl->view('snipets/header.html', [
			
			    'filter' => 1, 
				'title' => $data['item']['name'],
				'store' => $this->store_info,
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			], true),

			'navMenu' => $this->mdl_tpl->view('snipets/navMenu.html', [
				'store' => $this->store_info,
				'active' => 'home',
				'itemsTree' => $this->mdl_category->getTreeMenu(),
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			], true),

			'breadcrumb' => $this->mdl_tpl->view('snipets/breadcrumb.html', [
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
				'title' => $data['item']['name'],
				'array' => $data['brb'],
			], true),

			'content' => $this->mdl_tpl->view('pages/catalog/home.html', [
				'blocks' => $this->mdl_tpl->view('pages/catalog/filters/items.html', [
					'items' => $getData['filter'],
					'price' => $getData['cena'],
					'weight' => $getData['weight'],
				], true),
				//'snipets' => ($getData['snipet'] !== false) ? $this->mdl_tpl->view('pages/catalog/cats_snipets/' . $data['item']['aliase'] . '.html', array(), true) : '',
				'textSearch' => $getData['textSearch'],
				'sort' => $getData['sort'],
				'header_title' => $title,
				'collections' => $this->mdl_tpl->view('pages/catalog/category_view_collections.html', [
					'items' => $getData['collections'],
				], true),
				'podcat' => $this->mdl_tpl->view('pages/catalog/category_view_podcat.html', [
					'items' => $getData['podcat'],
				], true),
				'products' => $this->mdl_tpl->view('pages/catalog/category_view_products.html', [
					'items' => $getData['products'],
				], true),
				'pagination' => $getData['products_pag'],
				'description' => $descr //$data['item']['desription'],
			], true),

			'footer' => $this->mdl_tpl->view('snipets/footer.html', [
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			], true),

			'load' => $this->mdl_tpl->view('snipets/load.html', [
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'utmLabels' => $this->mdl_tpl->view('snipets/utmLabels.html', $this->mdl_seo->utmLabels($this->get), true),
			], true),

			'resorses' => $this->mdl_tpl->view('resorses/catalog/cats_head.html', [
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'),
				'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path'),
			], true),

		], false);

		//echo '<p style="background: yellow none repeat scroll 0% 0%; margin: 20px 0px 0px; position: fixed; bottom: 0px; right: 0;">Время выполнения скрипта: '.(microtime(true) - $start).' сек.</p>';

	}

	// Получить данные о категории
	public function getCatData($catId = false, $catAliase = false, $j = false)
	{

		$catId = (isset($this->post['cat_id'])) ? $this->post['cat_id'] : $catId;
		$catAliase = (isset($this->post['catAliase'])) ? $this->post['catAliase'] : $catAliase;

		if ($catId === false && $catAliase) {
			$queryCat = $this->mdl_category->queryData([
				'return_type' => 'ARR1',
				'where' => [
					'method' => 'AND',
					'set' => [
						['item' => 'aliase', 'value' => $catAliase],
					],
				],
				'labels' => ['id'],
				'module' => false,
			]);
			$catId = $queryCat['id'];
		}

		$r = [
			'products' => [],
			'podcat' => [],
			'collections' => [],
		];

		$query_string = [];
		$query_string = array_merge($query_string, $this->get);
		$query_string = array_merge($query_string, $this->post);

		unset($query_string['page']);
		unset($query_string['limit']);

		$query_string = $this->mdl_helper->clear_array_0($query_string, [
			'f', 's', 'l', 't', 'brand'
			//'set_id', 'cat_id', 'project_id', 'user_id', 'search'
		]);

		$sffix = $query_string;

		$category = false;

		if ($catId !== false) {

			$fileSnipet = './' . $this->config->item('config_tpl_path') . '/pages/catalog/cats_snipets/' . $catAliase . '.html';

			$r['snipet'] = (file_exists($fileSnipet)) ? $fileSnipet : false;

			$r['podcat'] = $this->mdl_category->queryData([
				'return_type' => 'ARR2',
				'where' => [
					'method' => 'AND',
					'set' => [
						['item' => 'parent_id', 'value' => $catId],
					],
				],
				'labels' => ['id', 'aliase', 'name', 'modules'],
				'module' => true,
				'modules' => [[
					'module_name' => 'linkPath',
					'result_item' => 'linkPath',
//					'option' => [
//						'cat_aliase' => $catAliase,
//					],
				]],
			]);

			$r['collections'] = $this->mdl_collections->queryData([
				'return_type' => 'ARR2',
				'like' => [
					'math' => 'both',
					'method' => 'AND',
					'set' => [
						[
							'item' => 'cats',
							'value' => '"' . $catId . '"',
						],
					],
				],
				'labels' => ['id', 'aliase', 'title'],
				'module' => false,
			]);

			$category = $this->mdl_category->queryData([
				'return_type' => 'ARR1',
				'where' => [
					'method' => 'AND',
					'set' => [
						['item' => 'id', 'value' => $catId],
					],
				],
				'labels' => ['id', 'filter_id'],
				'module' => false,
			]);
		}

		$filter = $this->getFilter($category ? $category['filter_id'] : 11, $category['id']);

		$option = [
			'return_type' => 'ARR2+',
			'debug' => true,
			'where' => [
				'method' => 'AND',
				'set' => [[
					'item' => 'view >',
					'value' => 0,
				], [
					'item' => 'qty >',
					'value' => 0,
				], [
					'item' => 'moderate >',
					'value' => 1,
				]],
			],
			'group_by' => 'articul',
			'distinct' => true,
			'labels' => ['id', 'aliase', 'articul', 'title', 'seo_keys', 'seo_desc', 'seo_title', 'prices_empty', 'filters', 'salle_procent', 'modules'],
			'pagination' => [
				'on' => true,
				'page' => (isset($this->get['page'])) ? $this->get['page'] : 1,
				'limit' => (isset($this->get['l'])) ? $this->get['l'] : 40,
			],
			'module_queue' => [
				'price_actual',
				'limit', 'pagination',
				'prices_all', 'photos', /*'reviews', */
				'linkPath', 'salePrice',
				'emptyPrice', 'qty_empty_status', 'paramsView',
			],
			'module' => true,
			'modules' => [[
				'module_name' => 'linkPath',
				'result_item' => 'linkPath',
//				'option' => [
//					'cat_aliase' => $catAliase,
//				],
			], [
				'module_name' => 'price_actual',
				'result_item' => 'price_actual',
				'option' => [
					'labels' => false,
				],
			], [
				'module_name' => 'salePrice',
				'result_item' => 'salePrice',
				'option' => [],
			], [
				'module_name' => 'photos',
				'result_item' => 'photos',
				'option' => [
					'no_images_view' => 1,
				],
			], [
				'module_name' => 'emptyPrice',
				'result_item' => 'emptyPrice',
				'option' => [
					'labels' => false,
				],
			]],
		];

		$setFilters = []; // Запомнить установки выбора
		if (isset($this->get['f']) && is_array($this->get['f'])) {

			$f = $this->get['f'];
			$fNew = [];
			foreach ($f as $k => $v) {
				foreach ($filter as $fv) {
					if ($fv['variabled'] == $k) {
						$fNew[$k] = [
							'item' => $k,
							'type' => $fv['type'],
							'values' => explode('|', $v),
						];
					}
				}
			}

			$setFilters = $fNew;

			$option['setFilters'] = $setFilters;
			$r['setFilters'] = $setFilters;
		}

		$r['brand'] = (isset($this->get['brand'])) ? $this->get['brand'] : '';

		if ($catId) {
			$option['where']['set'][] = [
				'item' => 'cat',
				'value' => $catId,
			];
		}

		if (isset($this->get['brand'])) {
			$b = $this->get['brand'];
			$option['where']['set'][] = [
				'item' => 'postavchik',
				'value' => $b,
			];
		}

		//$r['sort'] = (isset($this->get['s'])) ? $this->get['s'] : 'pop';
		$r['sort'] = (isset($this->get['s'])) ? $this->get['s'] : 'pricemin';

		//if( isset( $this->get['s'] ) ){

		$sort = $r['sort'];

		if ($sort === 'pop') {
			$option['order_by'] = [
				'item' => 'view',
				'value' => 'DESC',
			];
		}

		if ($sort === 'new') {
			$option['order_by'] = [
				'item' => 'id',
				'value' => 'DESC',
			];
		}

		if ($sort === 'upsells') {
			$option['order_by'] = [
				'item' => 'salle_procent',
				'value' => 'DESC',
			];
		}

		if ($sort === 'pricemin') {
			$option['order_by'] = [
				'item' => 'price_real',
				'value' => 'ASC',
			];
		}

		if ($sort === 'pricemax') {
			$option['order_by'] = [
				'item' => 'price_real',
				'value' => 'DESC',
			];
		}

		//}

		$r['textSearch'] = '';
		if (isset($this->get['t'])) {
			$t = $r['textSearch'] = $this->get['t'];
			$option['like'] = [
				'math' => 'both', // '%before', 'after%' и '%both%' - опциональность поиска
				'method' => 'AND', // AND (и) / OR(или) / NOT(за исключением и..) / OR_NOT(за исключением или)
				'set' => [[
					'item' => 'title',
					'value' => $t,
				]]  // [ 'item' => '', 'value' => '' ],[...]
			];
		}

		$option['modules'][] = [
			'module_name' => 'pagination',
			'result_item' => 'pagination',
			'option' => [
				'path' => $_SERVER['REDIRECT_URL'],
				'option_paginates' => [
					'uri_segment' => 1,
					'num_links' => 3,
					'suffix' => $sffix,
				],
			],
		];

		$_r = $this->mdl_product->queryData($option);

		$r['products'] = $_r['result'];
		$r['products_pag'] = $_r['option']['pag'];

		$filterTitle = '';
//			$r['cena'] = [0, 90000];
		$r['cena'] = [];
		if (isset($this->get['f']['price'])) {
			$prices = explode('|', $this->get['f']['price']);
			$r['cena'] = $prices;
			list($price_from, $price_to) = $prices;
			$price_from = (int)$price_from;
			$price_to = (int)$price_to;
			if ($price_from || $price_to) {
				$filterTitle .= 'ценой' . ($price_from ? ' от ' . $price_from : '') . ($price_to ? ' до ' . $price_to : '') . ' рублей';
			}
		}

		$r['weight'] = [];
		if (isset($this->get['f']['weight'])) {
			$weights = explode('|', $this->get['f']['weight']);
			$r['weight'] = $weights;
			list($weight_from, $weight_to) = $weights;
			$weight_from = (float)$weight_from;
			$weight_to = (float)$weight_to;
			if ($weight_from || $weight_to) {
				$filterTitle .= ' весом' . ($weight_from ? ' от ' . $weight_from : '') . ($weight_to ? ' до ' . $weight_to : '') . ' граммов';
			}
		}

		foreach ($filter as $k => $v) {
			foreach ($setFilters as $sfv) {
				foreach ($v['data'] as $kData => $vData) {
					$filter[$k]['data'][$kData]['check'] = 'off';
				}
			}
		}

		foreach ($filter as $k => $v) {
			$filterTitles = [];
			foreach ($setFilters as $sfv) {
				if ($v['variabled'] === $sfv['item']) {

					foreach ($v['data'] as $kData => $vData) {
						if (in_array($vData['variabled'], $sfv['values'])) {
							$filter[$k]['data'][$kData]['check'] = 'on';
							$filterTitles[] = isset($vData['metaTitle']) && $vData['metaTitle'] ? $vData['metaTitle'] : mb_strtolower($vData['title']);
						} else {
							$filter[$k]['data'][$kData]['check'] = 'off';
						}
					}

				}
			}
			if (count($filterTitles)) {
				$filterTitle .= isset($v['metaTitle']) && $v['metaTitle'] ? ' ' . $v['metaTitle'] . ' ' : ' ';
				$filterTitle .= implode(' и ', $filterTitles);
			}
		}
		$filterTitle = trim($filterTitle);

		$r['filter'] = $filter;
		$r['filterTitle'] = $filterTitle;

		if ($j === true) {
			$this->mdl_helper->__json($r);
		} else {
			return $r;
		}

	}

	// Серверное отображение товара
	public function view_product($data)
	{

//		$start = microtime(true);

		$dataItem = $this->getProductData($data['item']['id'], $data['item']['aliase'], false);

//		$cat = $dataItem['product']['cat'];
//		$price = $dataItem['product']['modules']['salePrice']['COP']['orig'];
//		$salePrice = $dataItem['product']['modules']['salePrice']['COP']['salePrice'];
		
		//$dataItem1 = $this->getVamPonravitsa($data['item']['id'], false);

		//print_r($dataItem1);
		
		if (!empty ($dataItem)) {

			$product = $dataItem['product'];
		
			//print_r($product['modules']['paramsView']);
			//print_r($data['item']['aliase']);
			
			// 'seo_keys', 'seo_desc', 'seo_title',
			$title = (!empty($product['seo_title'])) ? $product['seo_title'] : $product['title'];
			$page_var = 'catalog';
			
			$brand = array(
				'kaborovsky' => 'от «Ювелирного Дома Кабаровских»',
				'alkor' => 'от «Ювелирной фабрики Алькор»',
				'master-brilliant' => 'от «Мастер Бриллиант»',
				'trofimova-jewellery' => 'от «TROFIMOVA jewellery»',
				'yuvelirnye-tradicii' => 'от «Ювелирные Традиции»',
				'delta' => 'от «КЮЗ Дельта»',
				'Estet' => 'от «Ювелирного дома Эстет»',
				'sokolov' => 'от «Ювелирной компании SOKOLOV»',
			);

			$this->mdl_tpl->view('templates/doctype_catalog.html', [

				'title' => $title,
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'),
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),

				'seo' => $this->mdl_tpl->view('snipets/seo_tools.html', [
					'oggMetta' => [
						'title' => $title,
						'url' => $this->mdl_helper->PROTOCOL(true) . $_SERVER['SERVER_NAME'] . $data['item']['modules']['linkPath'],
						'image' => $this->mdl_helper->PROTOCOL(true) . $_SERVER['SERVER_NAME'] . '/uploads/products/100/' . $dataItem['product']['modules']['photos'][0]['photo_name'],
						'site_name' => 'Ювелирный магазин «IVAN TOPAZOV»',
						'description' => (!empty($product['description'])) ? mb_substr($dataItem['product']['description'], 0, 250) : $title . " по разумной цене в магазине «IVAN TOPAZOV»: ✔продажа украшений в Москве с доставкой по России ✔привлекательные цены ✔выгодный в кредит ✔пожизненная гарантия. Звоните круглосуточно: ☎ +7 (4 95 ) 230 26 83",
					],
					'mk' => (!empty($dataItem['product']['seo_keys'])) ? $dataItem['product']['seo_keys'] : "",
					'md' => (!empty($dataItem['product']['seo_desc'])) ? $dataItem['product']['seo_desc'] : (!empty($dataItem['product']['description'])) ? mb_substr($dataItem['product']['description'], 0, 250) : $title . " по разумной цене в магазине «IVAN TOPAZOV»: ✔продажа украшений в Москве с доставкой по России ✔привлекательные цены ✔выгодный в кредит ✔пожизненная гарантия. Звоните круглосуточно: ☎ +7 (4 95 ) 230 26 83",
				], true),

				'navTop' => $this->mdl_tpl->view('snipets/navTop.html', [
					'store' => $this->store_info,
					'active' => 'home',
					'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
				], true),

				'header' => $this->mdl_tpl->view('snipets/header.html', [
				
					'filter' => 1, 
					'title' => $data['item']['cat']['name'], 
					'store' => $this->store_info,
					'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
				], true),

				'navMenu' => $this->mdl_tpl->view('snipets/navMenu.html', [
					'store' => $this->store_info,
					'active' => 'home',
					'itemsTree' => $this->mdl_category->getTreeMenu(),
					'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
				], true),

				'breadcrumb' => $this->mdl_tpl->view('snipets/breadcrumb.html', [
					'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
					'title' => $product['title'],
					'array' => $data['brb'],
				], true),

				'content' => $this->mdl_tpl->view('pages/catalog/product_view.html', [
					'product' => $product,
					'brand_desc' => $product['postavchik'] ? $this->mdl_tpl->view('pages/catalog/brands_descriptions/' . $product['postavchik'] . '.html', [], true) : '', 
					'brand' => $product['postavchik'] ? $brand[$product['postavchik']] : '',
					'counter' => $dataItem['timerCount'],
					'sizes' => isset($dataItem['sizes']) ? $dataItem['sizes'] : [],
					'otherSizes' => isset($dataItem['otherSizes']) ? $dataItem['otherSizes'] : [],
					'header_title' => $product['title'],
					'oneString' => rand(2000, 1000000) . '_' . rand(2000, 1000000),
					'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
					'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path'),
				], true),

				'komplect' => $this->mdl_tpl->view('snipets/komplect.html', [
					'items' => $this->getKomplect($product['id'], false),
				], true),

				'VamPonravitsa' => $this->mdl_tpl->view('snipets/VamPonravitsa.html', array(
					'items' => $this->getVamPonravitsa($product['id'], false),
				), true),

				'preimushchestva' => $this->mdl_tpl->view('snipets/preimushchestva.html', [
					'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
				], true),

				'footer' => $this->mdl_tpl->view('snipets/footer.html', [
					'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
				], true),

				'load' => $this->mdl_tpl->view('snipets/load.html', [
					'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
					'utmLabels' => $this->mdl_tpl->view('snipets/utmLabels.html', $this->mdl_seo->utmLabels($this->get), true),
				], true),

				'resorses' => $this->mdl_tpl->view('resorses/catalog/product_view_head.html', [
					'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
					'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'),
					'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path'),
				], true),

			], false);

		} else {
			redirect('/search?t=' . $data['item']['title']);
		}
		//echo '<p style="background: yellow none repeat scroll 0% 0%; margin: 20px 0px 0px; position: fixed; bottom: 0px;">Время выполнения скрипта: '.(microtime(true) - $start).' сек.</p>';
	}

	// Получить данные о товаре
	public function getProductData($productId = false, $productAliase = false, $j = true)
	{
		$Id = (isset($this->post['productId'])) ? $this->post['productId'] : $productId;
		$Aliase = (isset($this->post['productAliase'])) ? $this->post['productAliase'] : $productAliase;

		$r = [];
		if ($Id !== false) {

			$option = [
				'return_type' => 'ARR1',
				'where' => [
					'method' => 'AND',
					'set' => [[
						'item' => 'id',
						'value' => $Id,
					], [
						'item' => 'aliase',
						'value' => $Aliase,
//					], [
//						'item' => 'qty >',
//						'value' => 0,
					], [
						'item' => 'moderate >',
						'value' => 1,
					]],
				],
				'labels' => [
					'id', 'aliase', 'articul', 'title', 'description', 'cat',
					'seo_title', 'seo_keys', 'seo_desc', 'qty', 'filters',
					'price', 'modules', 'view', 'size', 'salle_procent', 'postavchik', 'drag',
				],
				'module' => true,
				'modules' => [[
					'module_name' => 'drags',
					'result_item' => 'drags',
					'option' => [],
				], [
					'module_name' => 'linkPath',
					'result_item' => 'linkPath',
					'option' => [
					],
				], [
					'module_name' => 'price_actual',
					'result_item' => 'price_actual',
					'option' => [
						'labels' => false,
					],
				], [
					'module_name' => 'salePrice',
					'result_item' => 'salePrice',
					'option' => [],
				], [
					'module_name' => 'photos',
					'result_item' => 'photos',
					'option' => [
						'no_images_view' => 1,
					],
				],/* [
					'module_name' => 'reviews',
					'result_item' => 'reviews',
					'option' => [],
				], */
					[
						'module_name' => 'emptyPrice',
						'result_item' => 'emptyPrice',
						'option' => [
							'labels' => false,
						],
					], [
						'module_name' => 'qtyEmptyStatus',
						'result_item' => 'qtyEmptyStatus',
						'option' => [
							'labels' => false,
						],
					], [
						'module_name' => 'paramsView',
						'result_item' => 'paramsView',
						'option' => [
							'labels' => false,
						],
					]],
			];

			$product = $this->mdl_product->queryData($option);

			if ($product) {

				$r['product'] = $product;

				if ($product['size']) {
					$sizes = $this->mdl_product->queryData([
						'retyrn_type' => 'ARR2',
						'where' => [
							'method' => 'AND',
							'set' => [[
								'item' => 'articul',
								'value' => $product['articul'],
							], [
								'item' => 'postavchik',
								'value' => $product['postavchik'],
							], [
								'item' => 'qty >',
								'value' => 0,
							]],
						],
						'order_by' => [
							'item' => 'size',
							'value' => 'acb',
						],
						'group_by' => 'size',
						'distinct' => true,
						'labels' => ['id', 'modules', 'size'],
						'module' => true,
						'modules' => [[
							'module_name' => 'linkPath',
							'result_item' => 'linkPath',
							'option' => [],
						]],
					]);

					$r['sizes'] = $sizes;

					if ($product['cat']) {
						$category = $this->mdl_category->queryData([
							'return_type' => 'ARR1',
							'where' => [
								'method' => 'AND',
								'set' => [
									['item' => 'id', 'value' => $product['cat']],
								],
							],
							'labels' => ['id', 'filter_id'],
							'module' => false,
						]);
					}

					if ($category['filter_id']) {
						$filter = $this->mdl_category->queryData([
							'return_type' => 'ARR1',
							'table_name' => 'products_filters',
							'where' => [
								'method' => 'AND',
								'set' => [
									['item' => 'id', 'value' => $category['filter_id']],
								],
							],
							'labels' => ['id', 'labels'],
							'module' => false,
						]);
					}

					if (!empty($filter)) {
						$filterData = json_decode($filter['labels'], true);
						if (is_array($filterData)) {
							$filterSizes = array_filter($filterData, function ($item) {
								return $item['variabled'] == 'size';
							});
							$filterSizes = reset($filterSizes)['data'];

							if (is_array($filterSizes)) {
								$allSizes = array_map(function ($item) {
									return str_replace('.0', '', str_replace(',', '.', trim($item['title'])));
								}, $filterSizes);
								$productSizes = array_map(function ($item) {
									return str_replace('.0', '', str_replace(',', '.', trim($item['size'])));
								}, $sizes);
								$otherSizes = array_diff($allSizes, $productSizes);

								if (count($otherSizes)) {
									$r['otherSizes'] = $otherSizes;
								}
							}
						}
					}
				}

				$_time = time();
				$start_day = mktime(0, 0, 0, date("m", $_time), date("d", $_time), date("y", $_time));
				$r['timerCount'] = ($start_day + 86400) - time();

				$this->mdl_db->_update_db("products", "id", $product['id'], [
					'view' => ($product['view'] + 1),
				]);
			}

		}

		if ($j === true) {
			$this->mdl_helper->__json($r);
		} else {
			return $r;
		}

	}

	// Блок вам понравится
	// ToDo: если этот блок понадобится, нужно править данные для фильтров
	public function getVamPonravitsa($prodID = false, $j = true)
	{

		$r = [];
		if ($prodID !== false) {

			$prod = $this->mdl_product->queryData([
				'return_type' => 'ARR1',
				'where' => [
					'method' => 'AND',
					'set' => [[
						'item' => 'id',
						'value' => $prodID,
					]],
				],
				'labels' => ['id', 'sex', 'cat', 'filter_zoloto', 'filter_kamen', 'modules'],
				'module' => true,
				'modules' => [[
					'module_name' => 'price_actual',
					'result_item' => 'price_actual',
					'option' => [
						'labels' => false,
					],
				]],
			]);
			$sex = $prod['sex'];
			$cat = $prod['cat'];
			$kamen = false;
			$zoloto = false;

			if (!empty($prod['filter_kamen'])) {
				$filterKamen = json_decode($prod['filter_kamen'], true);
				$kamen = $filterKamen[0];
			}

			if (!empty($prod['filter_zoloto'])) {
				$filterZoloto = json_decode($prod['filter_zoloto'], true);
				$zoloto = $filterZoloto[0];
			}

			/*if (!empty($prod['filters'])) {
				$ddd = json_decode($prod['filters'], true);
				foreach ($ddd as $_v) {
					if ($_v['item'] === 'metall') {
						$metall = (count($_v['values']) > 0) ? $_v['values'][0] : $metall;
					}
					if ($_v['item'] === 'kamen') {
						$kamen = (count($_v['values']) > 0) ? $_v['values'][0] : $kamen;
					}
				}
			}*/


			
			/*if ($metall !== false) {
				$metall_arr = array(
					'krasnZoloto' => 'krasnoe',
					'JoltZoloto' => 'zhyoltoe',
					'belZoloto' => 'beloe',
					'Zoloto' => 'Zoloto',
					'Золото' => 'Золото',
				);
				$metall = $metall_arr[$metall];
			}*/
			
			
			$prod_price = $prod['modules']['price_actual']['number'];
			$price_ot = ($prod_price < 5000) ? 0 : $prod_price - 5000;
			$price_do = $prod_price + 5000;


			$options = [
				'return_type' => 'ARR2',
				'debug' => true,
				'where' => [
					'method' => 'AND',
					'set' => [[
						'item' => 'id !=',
						'value' => $prod['id'],
					], [
						'item' => 'qty >',
						'value' => 0,
					], [
						'item' => 'sex',
						'value' => $sex,
					], [
						'item' => 'cat',
						'value' => $cat,
					], [
						'item' => 'moderate >',
						'value' => 1,
					]],
				],
				'limit' => 4,
				'order_by' => [
					'item' => 'id',
					'value' => 'RANDOM',
				],
				'group_by' => 'articul',
				'distinct' => true,
				'labels' => ['id', 'aliase', 'title', 'salle_procent', 'sex', 'modules'],
				'setFilters' => [
					[
						'item' => 'price',
						'type' => 'range-values',
						'values' => [$price_ot, $price_do],
					], 
					[
						'item' => 'kamen',
						'type' => 'checkbox-group',
						'values' => ($kamen !== false) ? [$kamen] : [],
					], [
						'item' => 'zoloto',
						'type' => 'checkbox-group',
						'values' => ($zoloto !== false) ? [$zoloto] : [],
					], 
				],
				'module_queue' => [
					'price_actual', 'limit', 'salePrice', 'linkPath', 'photos',
				],
				'module' => true,
				'modules' => [[
					'module_name' => 'price_actual',
					'result_item' => 'price_actual',
					'option' => [
						'labels' => false,
					],
				], [
					'module_name' => 'salePrice',
					'result_item' => 'salePrice',
					'option' => [],
				], [
					'module_name' => 'linkPath',
					'result_item' => 'linkPath',
					'option' => [],
				], [
					'module_name' => 'photos',
					'result_item' => 'photos',
					'option' => [
						'no_images_view' => 1,
					],
				]], 
			];

			$r = $this->mdl_product->queryData($options);

		}

		if ($j === true) {
			$this->mdl_helper->__json($r);
		} else {
			return $r;
		}

	}

	// Блок "Дополните свой образ"
	public function getKomplect($prodID = false, $j = true)
	{

		$r = [];
		if ($prodID !== false) {

			$prod = $this->mdl_product->queryData([
				'return_type' => 'ARR1',
				'where' => [
					'method' => 'AND',
					'set' => [[
						'item' => 'id',
						'value' => $prodID,
					]],
				],
				'labels' => ['id', 'optionLabel'],
				'module' => false,
			]);

			if (isset($prod['optionLabel'])) {
				$optionLabel = json_decode($prod['optionLabel'], true);

				if (isset($optionLabel['options'])) {

					$OlO = $optionLabel['options'];
					$_olo = array_map(function ($article) {
						return trim($article);
					}, explode(',', $OlO));

					if (count($_olo) > 0) {

						$r = $this->mdl_product->queryData([
							'return_type' => 'ARR2',
							'in' => [
								'method' => 'AND',
								'set' => [[
									'item' => 'articul',
									'values' => $_olo,
								]],
							],
							'limit' => 4,
							'group_by' => 'articul',
							'distinct' => true,
							'labels' => ['id', 'aliase', 'title', 'salle_procent', 'sex', 'modules'],
							'module_queue' => [
								'price_actual', 'limit', 'salePrice', 'linkPath', 'photos',
							],
							'module' => true,
							'modules' => [[
								'module_name' => 'price_actual',
								'result_item' => 'price_actual',
								'option' => [
									'labels' => false,
								],
							], [
								'module_name' => 'salePrice',
								'result_item' => 'salePrice',
								'option' => [],
							], [
								'module_name' => 'linkPath',
								'result_item' => 'linkPath',
								'option' => [],
							], [
								'module_name' => 'photos',
								'result_item' => 'photos',
								'option' => [
									'no_images_view' => 1,
								],
							]],
						]);

					}

				}
			}
		}

		if ($j === true) {
			$this->mdl_helper->__json($r);
		} else {
			return $r;
		}

	}

	// получение данных отзыва
	public function setReview($j = true)
	{
		$r = ['err' => 1, 'mess' => 'Данные введены некорректно'];

		$name = (isset($this->post['name'])) ? $this->post['name'] : false;
		$author = (isset($this->post['author'])) ? $this->post['author'] : false;
		$city = (isset($this->post['city'])) ? $this->post['city'] : false;
		$email = (isset($this->post['email'])) ? $this->post['email'] : false;
		$description = (isset($this->post['description'])) ? $this->post['description'] : false;
		$set_rating = (isset($this->post['set_rating'])) ? $this->post['set_rating'] : false;
		$product_id = (isset($this->post['product_id'])) ? $this->post['product_id'] : false;
		$codetch = (isset($this->post['codetch'])) ? $this->post['codetch'] : false;

		// Проверка капчи со стороны гугла
		$secret = "6LdaxbUUAAAAAF-z_Jut-sQIOzD2Wc7SGPFUa3nU";
		$response = null;
		$reCaptcha = new ReCaptcha($secret);
		if ($_POST["g-recaptcha-response"]) {
			$response = $reCaptcha->verifyResponse(
				$_SERVER["REMOTE_ADDR"],
				$_POST["g-recaptcha-response"]
			);
		}

		if (
			$codetch !== false &&
			$name !== false &&
			$author !== false &&
			$city !== false &&
			$email !== false &&
			$description !== false &&
			$set_rating !== false &&
			$product_id !== false &&
			$response != null &&
			$response->success
		) {

			$this->db->insert("products_reviews", [
				'product_id' => $product_id,
				'name' => $name,
				'author' => $author,
				'city' => $city,
				'email' => $email,
				'description' => $description,
				'rating' => $set_rating,
				'date_public' => time(),
				'ls_code' => $codetch,
				'moderate' => 0,
			]);

			$r = ['err' => 0, 'mess' => 'Ваш отзыв успешно добавлен', 'PID' => $this->db->insert_id(), 'code' => $codetch];

		}

		if ($j === true) {
			$this->mdl_helper->__json($r);
		} else {
			return $r;
		}

	}

}

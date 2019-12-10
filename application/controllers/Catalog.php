<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require_once $_SERVER["DOCUMENT_ROOT"] . "/application/libraries/ReCaptcha.php";

class Catalog extends CI_Controller
{

	protected $user_info = array();
	protected $store_info = array();

	protected $post = array();
	protected $get = array();

	public function __construct()
	{

		parent::__construct();
		$this->user_info = ($this->mdl_users->user_data()) ? $this->mdl_users->user_data() : false;
		$this->store_info = $this->mdl_stores->allConfigs();

		$this->post = $this->security->xss_clean($_POST);
		$this->get = $this->security->xss_clean($_GET);

	}

	// Предворительная обработка системными средствами
	public function _remap($method, $params = array())
	{
		if (method_exists($this, $method)) {
			return call_user_func_array(array($this, $method), $params);
		} else {
			return call_user_func_array(array($this, "ExtractTree"), func_get_args());
		}
	}

	// Серверное извлечение всех алиасов из URL
	private function ExtractTree()
	{

		$argList = func_get_args();
		$arg_list = ($argList !== false) ? $argList : array();
		$item = (count($arg_list[1]) > 0) ? $arg_list[1][(count($arg_list[1]) - 1)] : $arg_list[0];
		$tree = array();
		$tree[] = $arg_list[0];
		foreach ($arg_list[1] as $v) $tree[] = $v;

		// Передача алиасов и получение информации и инструкций...
		$logicData = $this->getLogicData($tree, false);

		if ($logicData['error404'] !== true) {
			if (!empty($logicData['item'])) {
				$variable = 'view_' . $logicData['method'];
				self::$variable($logicData);
			}
		} else {
			redirect('/catalog');
		}

	}

	// Получение информации и инструкций [ сервер / json ]
	public function getLogicData($tree = array(), $j = true)
	{

		$tree = (isset($this->post['tree'])) ? $this->post['tree'] : $tree;
		$r = [
			'item' => [],
			'error404' => true,
			'brb' => [
				[
					'name' => 'Каталог',
					'link' => '/catalog',
				],
			],
		];
		$lastAliase = $tree[count($tree) - 1];

		if (count($tree) > 0) {

			$tovar = $this->mdl_product->queryData([
				'return_type' => 'ARR2',
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

			$link_bs = '/catalog';
			foreach ($tree as $v) {
				$link_bs .= '/' . $v;
			}

			if (count($tovar) > 0) {
				array_pop($tree);
				$r['method'] = 'product';
				$r['item'] = $tovar[0];
				$r['error404'] = ($link_bs === $r['item']['modules']['linkPath']) ? false : true;
			} else {

				$category = $this->mdl_category->queryData([
					'return_type' => 'ARR2',
					'where' => [
						'method' => 'AND',
						'set' => [
							['item' => 'aliase', 'value' => $lastAliase],
						],
					],
					'labels' => ['id', 'aliase', 'name', 'desription'],
				]);

				if (count($category) > 0) {
					array_pop($tree);
					$r['method'] = 'category';
					$r['item'] = $category[0];
					$link_par = $this->mdl_category->getParentCatsTree([$category[0]['id']]);
					$r['error404'] = ($link_bs === $link_par[$category[0]['id']]) ? false : true;
				}
			}

			if (count($tree) > 0) {

				$cats = $this->mdl_category->queryData([
					'return_type' => 'ARR2',
					'in' => [
						'method' => 'AND',
						'set' => [
							['item' => 'aliase', 'values' => $tree],
						],
					],
					'labels' => false,
				]);

				$link = '/catalog';
				$par_id = 0;
				foreach ($cats as $k => $v) {

					if ($par_id == $v['parent_id']) {
						$par_id = $v['id'];

						$link .= '/' . $v['aliase'];

						$r['brb'][] = array(
							'name' => $v['name'],
							'link' => $link,
						);

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

	// Вывести главную страницу каталога
	public function index()
	{

		$start = microtime(true);

		$page_var = 'catalog';

//		$getData = $this->getCategoryHome();
		$getData = $this->getCatData();

		$title = (!isset($getData['setFilters']['category']) ? 'все украшения ' : '') . $getData['filterTitle'];
		$title = mb_strtoupper(mb_substr($title, 0, 1)) . mb_substr($title, 1);

		// Если нет товаров, ставим заглушку
		if (count($getData["products"]) < 1) {
			$descr = "<p style='font-size:18px;font-weight:bold;'>
				Не нашли, что искали? 
				Закажите <a href='#' data-toggle='modal' data-target='#modal_callback' style='color:#337ab7;'>звонок консультанта</a> или напишите в чат - возможно, на сайте идут работы.
				Приносим свои извинения за неудобства!
			</p>";
		} else $descr = "";

		$this->mdl_tpl->view('templates/doctype_catalog.html', array(

			'title' => $title,
			'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'),
			'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),

			'seo' => $this->mdl_tpl->view('snipets/seo_tools.html', array(
				'mk' => (!empty($this->store_info['seo_keys'])) ? $this->store_info['seo_keys'] : '',
				'md' => (!empty($this->store_info['seo_desc'])) ? $this->store_info['seo_desc'] : '',
			), true),

			'navTop' => $this->mdl_tpl->view('snipets/navTop.html', array(
				'store' => $this->store_info,
				'active' => 'home',
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			), true),

			'header' => $this->mdl_tpl->view('snipets/header.html', array(
				'store' => $this->store_info,
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			), true),

			'navMenu' => $this->mdl_tpl->view('snipets/navMenu.html', array(
				'store' => $this->store_info,
				'active' => 'home',
				'itemsTree' => $this->mdl_category->getTreeMenu(),
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			), true),

			'content' => $this->mdl_tpl->view('pages/catalog/home.html', array(
				'blocks' => $this->mdl_tpl->view('pages/catalog/filters/items.html', array(
					'items' => $getData['filter'],
					'Cena' => $getData['cena'],
					'weight' => $getData['weight'],
				), true),
				'textSearch' => $getData['textSearch'],
				'sort' => $getData['sort'],
				'header_title' => $title,
				'collections' => $this->mdl_tpl->view('pages/catalog/category_view_collections.html', array(
					'items' => $getData['collections'],
				), true),
				'products' => $this->mdl_tpl->view('pages/catalog/category_view_products.html', array(
					'items' => $getData['products'],
				), true),
				'pagination' => $getData['products_pag'],
				'description' => $descr,
			), true),

			'footer' => $this->mdl_tpl->view('snipets/footer.html', array(
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			), true),

			'load' => $this->mdl_tpl->view('snipets/load.html', array(
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'utmLabels' => $this->mdl_tpl->view('snipets/utmLabels.html', $this->mdl_seo->utmLabels($this->get), true),
			), true),

			'resorses' => $this->mdl_tpl->view('resorses/catalog/cats_head.html', array(
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'),
				'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path'),
			), true),

		), false);

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
		if (isset($this->get['f']['Cena'])) {
			$prices = explode('|', $this->get['f']['Cena']);
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

		$this->mdl_tpl->view('templates/doctype_catalog.html', array(

			'title' => $title,
			'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'),
			'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),

			'seo' => $this->mdl_tpl->view('snipets/seo_tools.html', array(
				'mk' => (!empty($this->store_info['seo_keys'])) ? $this->store_info['seo_keys'] : '',
				'md' => (!empty($this->store_info['seo_desc'])) ? $this->store_info['seo_desc'] : '',
			), true),

			'navTop' => $this->mdl_tpl->view('snipets/navTop.html', array(
				'store' => $this->store_info,
				'active' => 'home',
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			), true),

			'header' => $this->mdl_tpl->view('snipets/header.html', array(
				'store' => $this->store_info,
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			), true),

			'navMenu' => $this->mdl_tpl->view('snipets/navMenu.html', array(
				'store' => $this->store_info,
				'active' => 'home',
				'itemsTree' => $this->mdl_category->getTreeMenu(),
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			), true),

			'breadcrumb' => $this->mdl_tpl->view('snipets/breadcrumb.html', array(
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
				'title' => $data['item']['name'],
				'array' => $data['brb'],
			), true),

			'content' => $this->mdl_tpl->view('pages/catalog/home.html', array(
				'blocks' => $this->mdl_tpl->view('pages/catalog/filters/items.html', array(
					'items' => $getData['filter'],
					'Cena' => $getData['cena'],
					'weight' => $getData['weight'],
				), true),
				//'snipets' => ($getData['snipet'] !== false) ? $this->mdl_tpl->view('pages/catalog/cats_snipets/' . $data['item']['aliase'] . '.html', array(), true) : '',
				'textSearch' => $getData['textSearch'],
				'sort' => $getData['sort'],
				'header_title' => $title,
				'collections' => $this->mdl_tpl->view('pages/catalog/category_view_collections.html', array(
					'items' => $getData['collections'],
				), true),
				'podcat' => $this->mdl_tpl->view('pages/catalog/category_view_podcat.html', array(
					'items' => $getData['podcat'],
				), true),
				'products' => $this->mdl_tpl->view('pages/catalog/category_view_products.html', array(
					'items' => $getData['products'],
				), true),
				'pagination' => $getData['products_pag'],
				'description' => $descr //$data['item']['desription'],
			), true),

			'footer' => $this->mdl_tpl->view('snipets/footer.html', array(
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			), true),

			'load' => $this->mdl_tpl->view('snipets/load.html', array(
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'utmLabels' => $this->mdl_tpl->view('snipets/utmLabels.html', $this->mdl_seo->utmLabels($this->get), true),
			), true),

			'resorses' => $this->mdl_tpl->view('resorses/catalog/cats_head.html', array(
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'),
				'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path'),
			), true),

		), false);

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

		$query_string = array();
		$query_string = array_merge($query_string, $this->get);
		$query_string = array_merge($query_string, $this->post);

		unset($query_string['page']);
		unset($query_string['limit']);

		$query_string = $this->mdl_helper->clear_array_0($query_string, array(
			'f', 's', 'l', 't', 'brand'
			//'set_id', 'cat_id', 'project_id', 'user_id', 'search'
		));

		$sffix = $query_string;

		$cat_item = false;

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

			$cat_item = $this->mdl_category->queryData([
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

		$filter = $this->mdl_category->queryData([
			'return_type' => 'ARR1',
			'table_name' => 'products_filters',
			'where' => [
				'method' => 'AND',
				'set' => [
					['item' => 'id', 'value' => $cat_item ? $cat_item['filter_id'] : 11],
				],
			],
			'labels' => ['id', 'labels'],
			'module' => false,
		]);

		//$r['filter_id'] = ( $filter ) ? $filter['id']: false;

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

		$r['sort'] = (isset($this->get['s'])) ? $this->get['s'] : 'pop';

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
		if (isset($this->get['f']['Cena'])) {
			$prices = explode('|', $this->get['f']['Cena']);
			$r['cena'] = $prices;
			list($price_from, $price_to) = $prices;
			$price_from = (int)$price_from;
			$price_to = (int)$price_to;
			if ($price_from || $price_to) {
				$filterTitle .= 'ценой' . ($price_from ? ' от ' . $price_from : '') . ($price_to ? ' до ' . $price_to : '').' рублей';
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
				$filterTitle .= ' весом' . ($weight_from ? ' от ' . $weight_from : '') . ($weight_to ? ' до ' . $weight_to : '').' граммов';
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

		$start = microtime(true);

		$dataItem = $this->getProductData($data['item']['id'], $data['item']['aliase'], false);

		if (!empty ($dataItem)) {

			$product = $dataItem['product'];
			$sizes = $dataItem['sizes'];
			// 'seo_keys', 'seo_desc', 'seo_title',
			$title = (!empty($product['seo_title'])) ? $product['seo_title'] : $product['title'];
			$page_var = 'catalog';

			$this->mdl_tpl->view('templates/doctype_catalog.html', array(

				'title' => $title,
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'),
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),

				'seo' => $this->mdl_tpl->view('snipets/seo_tools.html', array(
					'oggMetta' => [
						'title' => $title,
						'url' => $this->mdl_helper->PROTOCOL(true) . $_SERVER['SERVER_NAME'] . $data['item']['modules']['linkPath'],
						'image' => $this->mdl_helper->PROTOCOL(true) . $_SERVER['SERVER_NAME'] . '/uploads/products/100/' . $dataItem['product']['modules']['photos'][0]['photo_name'],
						'site_name' => 'Ювелирный магазин «IVAN TOPAZOV»',
						'description' => (!empty($product['description'])) ? mb_substr($dataItem['product']['description'], 0, 250) : $title . " по разумной цене в магазине «IVAN TOPAZOV»: ✔продажа украшений в Москве с доставкой по России ✔привлекательные цены ✔выгодный в кредит ✔пожизненная гарантия. Звоните круглосуточно: ☎ +7 (4 95 ) 230 26 83",
					],
					'mk' => (!empty($dataItem['product']['seo_keys'])) ? $dataItem['product']['seo_keys'] : "",
					'md' => (!empty($dataItem['product']['seo_desc'])) ? $dataItem['product']['seo_desc'] : (!empty($dataItem['product']['description'])) ? mb_substr($dataItem['product']['description'], 0, 250) : $title . " по разумной цене в магазине «IVAN TOPAZOV»: ✔продажа украшений в Москве с доставкой по России ✔привлекательные цены ✔выгодный в кредит ✔пожизненная гарантия. Звоните круглосуточно: ☎ +7 (4 95 ) 230 26 83",
				), true),

				'navTop' => $this->mdl_tpl->view('snipets/navTop.html', array(
					'store' => $this->store_info,
					'active' => 'home',
					'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
				), true),

				'header' => $this->mdl_tpl->view('snipets/header.html', array(
					'store' => $this->store_info,
					'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
				), true),

				'navMenu' => $this->mdl_tpl->view('snipets/navMenu.html', array(
					'store' => $this->store_info,
					'active' => 'home',
					'itemsTree' => $this->mdl_category->getTreeMenu(),
					'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
				), true),

				'breadcrumb' => $this->mdl_tpl->view('snipets/breadcrumb.html', array(
					'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
					'title' => $product['title'],
					'array' => $data['brb'],
				), true),

				'content' => $this->mdl_tpl->view('pages/catalog/product_view.html', array(
					'product' => $product,
					'brand_desc' => $this->mdl_tpl->view('pages/catalog/brands_descriptions/' . $product['postavchik'] . '.html', array(), true),
					'counter' => $dataItem['timerCount'],
					'sizes' => $sizes,
					'header_title' => $product['title'],
					'oneString' => rand(2000, 1000000) . '_' . rand(2000, 1000000),
					'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
					'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path'),
				), true),

				'komplect' => $this->mdl_tpl->view('snipets/komplect.html', array(
					'items' => $this->getKomplect($product['id'], false),
				), true),

				/*'VamPonravitsa' => $this->mdl_tpl->view('snipets/VamPonravitsa.html', array(
					'items' => $this->getVamPonravitsa($product['id'], false),
				), true),*/

				'preimushchestva' => $this->mdl_tpl->view('snipets/preimushchestva.html', array(
					'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
				), true),

				'footer' => $this->mdl_tpl->view('snipets/footer.html', array(
					'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
				), true),

				'load' => $this->mdl_tpl->view('snipets/load.html', array(
					'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
					'utmLabels' => $this->mdl_tpl->view('snipets/utmLabels.html', $this->mdl_seo->utmLabels($this->get), true),
				), true),

				'resorses' => $this->mdl_tpl->view('resorses/catalog/product_view_head.html', array(
					'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
					'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'),
					'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path'),
				), true),

			), false);

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
					], [
						'item' => 'qty >',
						'value' => 0,
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
				], [
					'module_name' => 'reviews',
					'result_item' => 'reviews',
					'option' => [],
				], [
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

			$getItem = $this->mdl_product->queryData($option);

			if ($getItem) {

				$r['product'] = $getItem;

				$r['sizes'] = $this->mdl_product->queryData([
					'retyrn_type' => 'ARR2',
					'where' => [
						'method' => 'AND',
						'set' => [[
							'item' => 'articul',
							'value' => $getItem['articul'],
						], [
							'item' => 'postavchik',
							'value' => $getItem['postavchik'],
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

				$_time = time();
				$start_day = mktime(0, 0, 0, date("m", $_time), date("d", $_time), date("y", $_time));
				$r['timerCount'] = ($start_day + 86400) - time();

				$this->mdl_db->_update_db("products", "id", $getItem['id'], [
					'view' => ($getItem['view'] + 1),
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
				'labels' => ['id', 'sex', 'cat', 'filters', 'modules'],
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
			$metall = false;

			if (!empty($prod['filters'])) {
				$ddd = json_decode($prod['filters'], true);
				foreach ($ddd as $_v) {
					if ($_v['item'] === 'metall') {
						$metall = (count($_v['values']) > 0) ? $_v['values'][0] : $metall;
					}
				}
			}

			$prod_price = $prod['modules']['price_actual']['number'];
			$price_ot = ($prod_price < 2000) ? 0 : $prod_price - 2000;
			$price_do = $prod_price + 2000;

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
						'item' => 'Cena',
						'type' => 'range-values',
						'values' => [$price_ot, $price_do],
					], [
						'item' => 'metall',
						'type' => 'checkbox-group',
						'values' => ($metall !== false) ? [$metall] : [],
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

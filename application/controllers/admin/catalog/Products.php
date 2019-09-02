<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends CI_Controller
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

		if ($this->mdl_helper->get_cookie('HASH') !== $this->mdl_users->userHach()) {
			$this->user_info['admin_access'] = 0;
		}
	}

	// Защита прямых соединений
	public function access_static()
	{
		if ($this->user_info !== false) {
			if ($this->user_info['admin_access'] < 1) {
				redirect('/login');
			}
		}
	}

	// Защита динамических соединений
	public function access_dynamic()
	{
		if ($this->user_info !== false) {
			if ($this->user_info['admin_access'] < 1) {
				exit('{"err":"1","mess":"Нет доступа"}');
			}
		}
	}

	// Страница со списком товаров
	public function index()
	{

		$this->access_static();

		$title = 'Товары';
		$page_var = 'catalog';

		$data = $this->getStoreProductsData(false);

		$products = $data['result'];
		$pag = $data['option']['pag'];
		$textSearch = $data['textSearch'];

		$this->mdl_tpl->view('templates/doctype_admin.html', array(

			'title' => $title,
			'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'),

			'seo' => $this->mdl_tpl->view('snipets/seo_tools.html', array(
				'mk' => (!empty($this->store_info['seo_keys'])) ? $this->store_info['seo_keys'] : '',
				'md' => (!empty($this->store_info['seo_desc'])) ? $this->store_info['seo_desc'] : '',
			), true),

			'nav' => $this->mdl_tpl->view('snipets/admin_nav.html', array(
				'active' => $page_var,
			), true),

			'breadcrumb' => $this->mdl_tpl->view('snipets/breadcrumb.html', array(
				'title' => $title,
				'array' => [[
					'name' => 'Панель управления',
					'link' => '/admin',
				], [
					'name' => 'Ассортимент',
					'link' => '/admin/catalog',
				]],
			), true),

			'content' => $this->mdl_tpl->view('pages/admin/catalog/products.html', array(
				'products' => $products,
				'pag' => $pag,
				'textSearch' => $textSearch,
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path'),
			), true),

			'load' => $this->mdl_tpl->view('snipets/load.html', array(
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			), true),

			'resorses' => $this->mdl_tpl->view('resorses/admin/catalog/products/head.html', array(
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path'),
			), true),

		), false);

	}

	// Получить список товаров
	public function getStoreProductsData($json = true)
	{

		$this->access_dynamic();

		$r = [];
		$query_string = [];

		$query_string = array_merge($query_string, $this->get);

		unset($query_string['page']);
		unset($query_string['limit']);

		$query_string = $this->mdl_helper->clear_array_0($query_string, array('f', 's', 'l', 't'));
		$sffix = $query_string;

		$page = (isset($this->get['page'])) ? $this->get['page'] : 1;
		$limit = (isset($this->get['l'])) ? $this->get['l'] : 40;

		$option = [
			'return_type' => 'ARR2+',
			'labels' => ['id', 'title', 'articul', 'price', 'qty', 'postavchik', 'modules'],
//			'limit' => "$limit, $offset",
			'pagination' => [
				'on' => true,
				'page' => $page,
				'limit' => $limit,
			],
			'module' => true,
			'modules' => [[
				'module_name' => 'price_actual',
				'result_item' => 'price_actual',
				'option' => [
					'labels' => false,
				],
			], [
				'module_name' => 'photos',
				'result_item' => 'photos',
				'option' => [
					'labels' => ['photo_name', 'define'],
				],
			], [
				'module_name' => 'pagination',
				'result_item' => 'pagination',
				'option' => [
					'path' => $_SERVER['REDIRECT_URL'],
					'option_paginates' => [
						'uri_segment' => 1,
						'num_links' => 4,
						'suffix' => $sffix,
					],
				],
			]],
		];
		$r['textSearch'] = '';
		if (isset($this->get['t'])) {
			$t = $r['textSearch'] = $this->get['t'];
			$option['like'] = [
				'math' => 'both', // '%before', 'after%' и '%both%' - опциональность поиска
				'method' => 'OR', // AND (и) / OR(или) / NOT(за исключением и..) / OR_NOT(за исключением или)
				'set' => [[
					'item' => 'id',
					'value' => $t,
				], [
					'item' => 'title',
					'value' => $t,
				], [
					'item' => 'articul',
					'value' => $t,
				], [
					'item' => 'aliase',
					'value' => $t,
				]]  // [ 'item' => '', 'value' => '' ],[...]
			];
		}

		$result = $this->mdl_product->queryData($option);

		$r['result'] = $result['result'];
		$r['option'] = $result['option'];

		if ($json !== true) {
			return $r;
		} else {
			$this->mdl_helper->__json($r);
		}

	}

	// Страница редактирования товара
	public function edit($GID = 0)
	{

		$this->access_static();

		$getData = $this->getDataEdit($GID, false);

		$title = 'Правка информации о товаре';
		$page_var = 'catalog';

		$this->mdl_tpl->view('templates/doctype_admin.html', array(

			'title' => $title,
			'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'),

			'seo' => $this->mdl_tpl->view('snipets/seo_tools.html', array(
				'mk' => (!empty($this->store_info['seo_keys'])) ? $this->store_info['seo_keys'] : '',
				'md' => (!empty($this->store_info['seo_desc'])) ? $this->store_info['seo_desc'] : '',
			), true),

			'nav' => $this->mdl_tpl->view('snipets/admin_nav.html', array(
				'active' => $page_var,
			), true),

			'breadcrumb' => $this->mdl_tpl->view('snipets/breadcrumb.html', array(
				'title' => $title,
				'array' => [[
					'name' => 'Панель управления',
					'link' => '/admin',
				], [
					'name' => 'Ассортимент',
					'link' => '/admin/catalog',
				], [
					'name' => 'Товары',
					'link' => '/admin/catalog/products',
				]],
			), true),

			'content' => $this->mdl_tpl->view('pages/admin/catalog/editProduct.html', array(
				'good' => $getData['product'],
				//'params_list' => $getData['params_list'],
				'filters' => $this->mdl_tpl->view('pages/admin/catalog/editProducts_filters.html', array(
					'items' => $getData['filter'],
				), true),
				'qty_empty_status' => $getData['qty_empty_status'],
				'prices_empty_list' => $getData['prices_empty_list'],
				'allCats' => $getData['allCats'],
				'images' => $this->mdl_tpl->view('pages/admin/catalog/editProduct_imagesList.html', array(
					'items' => $getData['product']['modules']['photos'],
				), true),
			), true),

			'load' => $this->mdl_tpl->view('snipets/load.html', array(
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			), true),

			'resorses' => $this->mdl_tpl->view('resorses/admin/catalog/products/edit_head.html', array(
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path'),
			), true),

		), false);

	}

	// Информация о товаре для редактирования ( Или добавления )
	public function getDataEdit($ProductID = false, $json = true)
	{

		$this->access_dynamic();

		$ProductID = (isset($this->post['gid'])) ? $this->post['gid'] : $ProductID;

		$r = [];
		if ($ProductID > 0) {
			$PROD = $this->mdl_product->queryData([
				'return_type' => 'ARR1',
				'where' => [
					'method' => 'AND',
					'set' => [[
						'item' => 'id',
						'value' => $ProductID,
					]],
				],
				'labels' => [
					'id', 'articul', 'cat', 'system_cat',
					'qty_empty', 'prices_empty', 'title',
					'description', 'seo_title', 'seo_desc',
					'seo_keys', 'view', 'qty', 'salle_procent',
					'salle_procent', 'sex', 'size', 'weight', 'filters',
					'postavchik', 'parser', 'proba', 'modules'],
				'module' => true,
				'modules' => [[
					'module_name' => 'photos',
					'result_item' => 'photos',
					'option' => [],
				], [
					'module_name' => 'price_actual',
					'result_item' => 'price_actual',
					'option' => [
						'zac' => 1,
					],
				], [
					'module_name' => 'paramsView',
					'result_item' => 'paramsView',
					'option' => [],
				]],
			]);

			if ($PROD) {

				$r['product'] = $PROD;
				$r['allCats'] = $this->mdl_category->allFindCats($PROD['cat'], [
					'id', 'name', 'parent_id', 'active',
				]);
				$r['qty_empty_status'] = $this->mdl_product->queryData([
					'return_type' => 'ARR2',
					'table_name' => 'status_qty_empty',
					'labels' => ['id', 'title'],
				]);
				$r['prices_empty_list'] = $this->mdl_product->queryData([
					'return_type' => 'ARR2',
					'table_name' => 'status_prices_empty',
					'labels' => ['id', 'title'],
				]);

				$r['filter'] = [];

				if ($PROD['cat'] > 0) {
					$cat_item = $this->mdl_category->queryData([
						'return_type' => 'ARR1',
						'where' => [
							'method' => 'AND',
							'set' => [
								['item' => 'id', 'value' => $PROD['cat']],
							],
						],
						'labels' => ['id', 'filter_id'],
						'module' => false,
					]);

					$filter = $this->mdl_category->queryData([
						'return_type' => 'ARR1',
						'table_name' => 'products_filters',
						'where' => [
							'method' => 'AND',
							'set' => [
								['item' => 'id', 'value' => $cat_item['filter_id']],
							],
						],
						'labels' => ['id', 'labels'],
						'module' => false,
					]);

					$filter = ($filter) ? json_decode($filter['labels'], true) : [];
					$setFilters = ($PROD['filters']) ? json_decode($PROD['filters'], true) : [];

					foreach ($filter as $k => $v) {
						foreach ($v['data'] as $kData => $vData) {
							$filter[$k]['data'][$kData]['check'] = 'off';
						}
					}

					foreach ($filter as $k => $v) {
						foreach ($setFilters as $sfv) {
							if ($v['variabled'] === $sfv['item']) {

								foreach ($v['data'] as $kData => $vData) {
									if (in_array($vData['variabled'], $sfv['values'])) {
										$filter[$k]['data'][$kData]['check'] = 'on';
									} else {
										$filter[$k]['data'][$kData]['check'] = 'off';
									}
								}

							}
						}
					};

					$r['filter'] = $filter;

				}

			}
		} else {
			$r['product'] = [
				'id' => 0,
				'title' => 'Новый товар',
				'view' => 0,
				'modules' => [
					'photos' => [],
					'price_actual' => [
						'number' => 0,
						'zac' => [
							'number' => 0,
						],
					],
				],
			];

			$r['allCats'] = $this->mdl_category->allFindCats(0, [
				'id', 'name', 'parent_id', 'active',
			]);

			$r['filter'] = [];

			$r['qty_empty_status'] = $this->mdl_product->queryData([
				'return_type' => 'ARR2',
				'table_name' => 'status_qty_empty',
				'labels' => ['id', 'title'],
			]);

			$r['prices_empty_list'] = $this->mdl_product->queryData([
				'return_type' => 'ARR2',
				'table_name' => 'status_prices_empty',
				'labels' => ['id', 'title'],
			]);

		}

		if ($json !== true) {
			return $r;
		} else {
			$this->mdl_helper->__json($r);
		}

	}

	// Форма редактирования основной информации товара
	public function actEditProductInfo()
	{

		$this->access_dynamic();

		$r = ['err' => '1', 'mess' => 'Неизвестная ошибка'];

		$articul = (isset($this->post['articul'])) ? $this->post['articul'] : '';
		$title = (isset($this->post['title'])) ? $this->post['title'] : false;
		$description = (isset($this->post['description'])) ? $this->post['description'] : '';
		$product_id = (isset($this->post['product_id'])) ? $this->post['product_id'] : 0;
		$shop_category = (isset($this->post['shop_category'])) ? $this->post['shop_category'] : 0;
		$qty = (isset($this->post['qty'])) ? $this->post['qty'] : 0;
		$qty_empty_status = (isset($this->post['qty_empty_status'])) ? $this->post['qty_empty_status'] : 1;
		$view = (isset($this->post['view'])) ? $this->post['view'] : 0;

		$size = (isset($this->post['size'])) ? $this->post['size'] : 0;
		$proba = (isset($this->post['proba'])) ? $this->post['proba'] : '';
		$sex = (isset($this->post['sex'])) ? $this->post['sex'] : 'man,woman';

		$weight = (isset($this->post['weight'])) ? $this->post['weight'] : '0';
		$postavchik = (isset($this->post['postavchik'])) ? $this->post['postavchik'] : '';
		$parser = (isset($this->post['parser'])) ? $this->post['parser'] : '';

		if ($title === false || $shop_category < 1) exit('{"err":"1","mess":"Данные - содержат ошибку"}');

		$data = [
			'articul' => $articul,
			'cat' => $shop_category,
			'title' => $title,
			'description' => $description,
			'view' => $view,
			'qty' => $qty,
			'qty_empty' => $qty_empty_status,
			'size' => $size,

			'weight' => $weight,
			'postavchik' => $postavchik,
			'parser' => $parser,

			'proba' => $proba,
			'sex' => $sex,
		];

		if ($product_id > 0) {
			$PROD = $this->mdl_product->queryData([
				'return_type' => 'ARR1',
				'where' => [
					'method' => 'AND',
					'set' => [[
						'item' => 'id',
						'value' => $product_id,
					]],
				],
			]);
			if (isset($PROD['id'])) {
				$this->mdl_db->_update_db("products", "id", $product_id, $data);
				$r = ['err' => '0', 'mess' => 'Данные товара обновлены'];
			} else {
				$product_id = 0;
			}
		}

		if ($product_id < 1) {
			$data['price_roz'] = 0;
			$this->db->insert('products', $data);
			$update_id = $this->db->insert_id();
			$this->mdl_db->_update_db("products", "id", $update_id, [
				'aliase' => $this->mdl_product->aliase_translite($title . '-' . $articul . '-' . $update_id),
			]);

			$r = ['err' => '0', 'mess' => 'Товар успешно добавлен', 'response_id' => $update_id];
		}

		$this->mdl_helper->__json($r);

	}

	// Форма редактирования основной информации товара
	public function actEditProductPrices()
	{

		$this->access_dynamic();

		$r = ['err' => '1', 'mess' => 'Неизвестная ошибка'];

		$product_id = (isset($this->post['product_id'])) ? $this->post['product_id'] : 0;
		$salle_procent = (isset($this->post['salle_procent'])) ? $this->post['salle_procent'] : 0;

		//$price_roz = ( isset( $this->post['price_roz'] ) ) ? intval( $this->post['price_roz'] * 100 ) : 0;
		$price_zac = (isset($this->post['price_zac'])) ? intval($this->post['price_zac'] * 100) : 0;
		$price_roz = ($price_zac > 0) ? $price_zac * 2.5 : intval($this->post['price_roz'] * 100);

		$prices_empty = (isset($this->post['prices_empty'])) ? $this->post['prices_empty'] : 1;

		if (!$price_zac and !$price_roz) exit('{"err":"1","mess":"Данные - содержат ошибку"}');

		$data = [
			'price_zac' => $price_zac,
			'price_roz' => $price_roz,
			'salle_procent' => $salle_procent,
			'prices_empty' => $prices_empty,
		];

		if ($product_id > 0) {
			$PROD = $this->mdl_product->queryData([
				'return_type' => 'ARR1',
				'where' => [
					'method' => 'AND',
					'set' => [[
						'item' => 'id',
						'value' => $product_id,
					]],
				],
			]);
			if (isset($PROD['id'])) {
				$this->mdl_db->_update_db("products", "id", $product_id, $data);
				$r = ['err' => '0', 'mess' => 'Данные товара обновлены'];
			} else {
				$product_id = 0;
			}
		}

		$this->mdl_helper->__json($r);

	}

	// Форма редактирования СЕО данных товара
	public function actEditProductSeo()
	{

		$this->access_dynamic();
		$r = ['err' => '1', 'mess' => 'Неизвестная ошибка. Перезагрузите страницу!'];

		$seo_title = (isset($this->post['seo_title'])) ? $this->post['seo_title'] : '';
		$seo_keys = (isset($this->post['seo_keys'])) ? $this->post['seo_keys'] : '';
		$seo_desc = (isset($this->post['seo_desc'])) ? $this->post['seo_desc'] : '';
		$product_id = (isset($this->post['product_id'])) ? $this->post['product_id'] : 0;

		if ($product_id !== false) {

			$this->db->where('store_id', $this->store_info['id']);
			$this->db->where('user_id', $this->user_info['id']);
			$this->mdl_db->_update_db("products", "id", $product_id, [
				'seo_title' => $seo_title,
				'seo_desc' => $seo_desc,
				'seo_keys' => $seo_keys,
			]);
			$r = ['err' => '0', 'mess' => 'Сео данные товара - обновлены'];

		}

		$this->mdl_helper->__json($r);

	}

	// Форма редактирования СЕО данных товара
	public function actEditProductParams()
	{

		$this->access_dynamic();

		$r = ['err' => '1', 'mess' => 'Неизвестная ошибка. Перезагрузите страницу!'];

		$setParamsList = (isset($this->post['setParamsList'])) ? $this->post['setParamsList'] : [];
		$product_id = (isset($this->post['product_id'])) ? $this->post['product_id'] : false;

		if ($product_id !== false) {

			$arr = [];
			foreach ($setParamsList as $k => $v) {
				$arr[] = [
					'variabled' => $k,
					'value' => $v,
				];
			}

			$this->mdl_db->_update_db("products", "id", $product_id, [
				'params' => json_encode($arr),
			]);
			$r = ['err' => '0', 'mess' => 'Параметры товара - обновлены'];

		}

		$this->mdl_helper->__json($r);

	}

	// Форма редактирования СЕО данных товара
	public function actEditProductFilters()
	{

		$this->access_dynamic();
		$r = ['err' => '1', 'mess' => 'Неизвестная ошибка. Перезагрузите страницу!'];
		$setFiltersList = (isset($this->post['f'])) ? $this->post['f'] : [];
		$product_id = (isset($this->post['product_id'])) ? $this->post['product_id'] : false;
		if ($product_id !== false) {
			$this->mdl_db->_update_db("products", "id", $product_id, [
				'filters' => json_encode($setFiltersList),
			]);
			$r = ['err' => '0', 'mess' => 'Установки фильтрации товара - обновлены'];
		}
		$this->mdl_helper->__json($r);

	}

	// Форма редактирования фотографий товара
	public function actEditProductImages()
	{

		$this->access_dynamic();
		$this->load->library('images');

		$_index = 'images';
		$_output_dir = "./uploads/products/temp/";

		if (isset($_FILES[$_index])) {
			$error = $_FILES[$_index]["error"];
			if (!is_array($_FILES[$_index]["name"])) {

				$FILES = '';
				$this->load->helper("string");
				$new_name = random_string('alnum', 6) . "_" . time();
				$FILES[$_index] = $this->images->files_array($_FILES, $_index, $new_name);

				$fileName = $FILES[$_index]["name"];

				$if_move = move_uploaded_file($FILES[$_index]["tmp_name"], $_output_dir . $fileName);
				if ($if_move) {

					$path = $_output_dir;
					$prew = "./uploads/products/100/";
					$prew2 = "./uploads/products/250/";
					$grozz = "./uploads/products/500/";

					$this->images->resize_jpeg($FILES[$_index], $path, $prew, $new_name, 100, 100, 100);
					$this->images->resize_jpeg($FILES[$_index], $path, $prew2, $new_name, 100, 250, 100);
					$photo_name = $this->images->resize_jpeg($FILES[$_index], $path, $grozz, $new_name, 100, 500, 100);

					@unlink($_output_dir . $fileName);

					if (isset($this->post['product_id'])) {

						$insert["product_id"] = $this->post['product_id'];
						$insert["photo_name"] = $photo_name;
						$insert["define"] = 0;

						$this->db->insert('products_photos', $insert);
						$insert["id"] = $this->db->insert_id();

						$r = array('err' => 0, 'mess' => 'Файл успешно загружен!', 'response' => $insert);
					} else {
						$r = array('err' => 1, 'mess' => 'System error ( 004 ) !');
					}
				} else {
					$r = array('err' => 1, 'mess' => 'System error ( 003 ) !');
				}
			} else {
				$r = array('err' => 1, 'mess' => 'System error ( 002 ) !');
			}
		} else {
			$r = array('err' => 1, 'mess' => 'System error ( 001 ) !');
		}

		$this->mdl_helper->__json($r);

	}

	//Удаление фото с сервера и из БД
	public function actEditProductImageRemove()
	{

		$this->access_dynamic();
		$PID = (isset($this->post['pid'])) ? $this->post['pid'] : false;
		$r = ['err' => 1, 'mess' => 'Удалить фотографию не удалось'];

		if ($PID !== false) {

			$r = $this->mdl_product->queryData([
				'return_type' => 'ARR1',
				'table_name' => 'products_photos',
				'where' => [
					'method' => 'AND',
					'set' => [[
						'item' => 'id',
						'value' => $PID,
					]],
				],
			]);

			if (isset($r['id'])) {

				$prew = "./uploads/products/100/";
				$grozz = "./uploads/products/250/";
				$grozz2 = "./uploads/products/500/";

				@unlink($prew . $r['photo_name']);
				@unlink($grozz . $r['photo_name']);
				@unlink($grozz2 . $r['photo_name']);

				$this->db->where('id', $r['id']);
				$this->db->delete('products_photos');

				$r = ['err' => 0, 'mess' => 'Фотография успешно удалена', 'remove_id' => $r['id']];

			}

		}

		$this->mdl_helper->__json($r);

	}

	//Удаление фото с сервера и из БД
	public function actEditProductImageSetDefine()
	{

		$this->access_dynamic();
		$PID = (isset($this->post['pid'])) ? $this->post['pid'] : false;
		$r = ['err' => 1, 'mess' => 'Удалить фотографию не удалось'];

		if ($PID !== false) {

			$r = $this->mdl_product->queryData([
				'return_type' => 'ARR1',
				'table_name' => 'products_photos',
				'where' => [
					'method' => 'AND',
					'set' => [[
						'item' => 'id',
						'value' => $PID,
					]],
				],
			]);

			if (isset($r['id'])) {

				$this->mdl_db->_update_db("products_photos", "product_id", $r['product_id'], array(
					'define' => 0,
				));

				$this->mdl_db->_update_db("products_photos", "id", $PID, array(
					'define' => 1,
				));

				$r = ['err' => 0, 'mess' => 'Фотография успешно установлена как основная', 'set_id' => $r['id']];

			}

		}

		$this->mdl_helper->__json($r);

	}

}

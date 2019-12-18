<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Delta extends CI_Controller
{
	protected $user_info = array();
	protected $store_info = array();
	protected $post = array();
	protected $get = array();
	private $imagePath = "./uploads/products/delta/";

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

	// Показать страницу по умолчанию
	public function index()
	{
		$this->access_static();
		$title = 'Парсинг каталога Дельта';
		$page_var = 'parser';
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
				]],
			), true),
			'content' => $this->mdl_tpl->view('pages/admin/parser/delta/delta.html', array(), true),
			'load' => $this->mdl_tpl->view('snipets/load.html', array(
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			), true),
			'resorses' => $this->mdl_tpl->view('resorses/admin/parser/delta.html', array(
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path'),
			), true),
		), false);
	}

	public function parse()
	{
		//echo json_encode($this->post);
		// обновлять фотографии у существующих товаров (медленнее)
		$updatePhotos = false;
//        error_reporting(-1);
//		ini_set('display_errors', 1);
		error_reporting(0);
		ini_set('display_errors', 0);
		// Первоначальная прочистка остатков
		$clear = (isset($this->post['clear'])) ? $this->post['clear'] : false;
		if ($clear === '1') {
			$this->mdl_db->_update_db("products", "postavchik", 'Delta', [
				'qty' => 0,
			]);
		}
		// Получение пакета данных
		$packs = (isset($this->post['pack'])) ? $this->post['pack'] : [];
		// Получить список товаров со схожими сериями в поступлении
		$listSeries = [];
		$err = 0;
		foreach ($packs as $k => $data) {
			// заполняем недостающие поля
			$seria = $data['seria'];
			$articul = $data['articul'];
			$size = $data['size'];
			if ($seria && (!$articul || !$size)) {
				$seria = str_replace(' ', '', $seria);
				if (!$articul) {
					$articul = substr($seria, 0, -2);
				}
				if (!$size) {
					$size = (int)substr($seria, -2);
				}
			}
			// Для размеров 18 и 18,5 seria одинаковая, поэтому создаем её заново из артинула и размера,
			// даже если она была заполнена
			if ($articul && $size) {
				$articul = str_replace(' ', '', $articul);
				$seria = $articul . str_replace(',', '-', $size);
			}
			if ($seria && !in_array($seria, $listSeries)) {
				$listSeries[] = $seria;
			}
			$packs[$k]['seria'] = $seria;
			$packs[$k]['articul'] = $articul;
			$packs[$k]['size'] = $size;
		}
		// Получить список существующих товаров в БД
		$listSeriesIsset = [];
		if (count($listSeries) > 0) {
			$issetProducts = $this->mdl_product->queryData([
				'type' => 'ARR2',
				'in' => [
					'method' => 'AND',
					'set' => [[
						'item' => 'seria',
						'values' => $listSeries,
					]],
				],
				'where' => [
					'method' => 'AND',
					'set' => [[
						'item' => 'postavchik',
						'value' => 'Delta',
					]],
				],
				'labels' => ['id', 'aliase', 'title', 'seria', 'articul', 'aliase'],
			]);
			foreach ($issetProducts as $issetProduct) {
				$seria = trim($issetProduct['seria']);
				if (!in_array($seria, $listSeriesIsset)) {
					$listSeriesIsset[$seria] = [
						'id' => $issetProduct['id'],
						'aliase' => $issetProduct['aliase'],
					];
				}
			}
		}

		// Собрать два массива для обновления и добавления
		$INSERT = [];
		$UPDATE = [];
		if (count($packs) > 0) {
			foreach ($packs as $data) {
				$seria = $data['seria'];
				if (!isset($listSeriesIsset[$seria])) {
					$INSERT[] = $this->getProductData($data);
				} else {
					$UPDATE[] = [
						'ID' => $listSeriesIsset[$seria]['id'],
						'aliase' => $listSeriesIsset[$seria]['aliase'],
						'data' => $this->getProductData($data),
					];
				}
			}
		}
//		var_dump($INSERT);
//		die;
		$IDS = [];
		if (count($UPDATE) > 0) {
			$upd_bh = [];
			foreach ($UPDATE as $k => $updateItem) {
				$upd_bh[] = [
					'id' => $updateItem['ID'],
					'title' => $updateItem['data']['product']['title'],
					'qty' => $updateItem['data']['product']['qty'],
					'price_zac' => $updateItem['data']['product']['price_zac'],
					'params' => $updateItem['data']['product']['params'],
					'filters' => $updateItem['data']['product']['filters'],
				];
				if (!in_array($updateItem['ID'], $IDS)) {
					$IDS[] = $updateItem['ID'];
				}
				if ($updatePhotos && isset($updateItem['data']['photos'])) {
					$r = $this->saveImages($updateItem['data']['photos']['photo_name'], $updateItem['aliase']);
					if ($r !== false) {
						$products_photos = $this->mdl_product->queryData([
							'in' => [
								'method' => 'AND',
								'set' => [[
									'item' => 'product_id',
									'values' => $updateItem['ID'],
								]],
							],
							'labels' => false,
							'table_name' => 'products_photos',
						]);
						if (!count($products_photos)) {
							$updateItem['data']['photos']['product_id'] = $updateItem['ID'];
							$updateItem['data']['photos']['photo_name'] = $updateItem['aliase'] . '.jpg';
//                    $v['data']['photos']['define'] = '1';
							$this->db->insert('products_photos', $updateItem['data']['photos']);
						}
					}
				}
			}
			if (count($upd_bh) > 0) {
				$this->db->update_batch('products', $upd_bh, 'id');
			}
		}
		if (count($INSERT) > 0) {
			$insertedSeries = [];
			foreach ($INSERT as $k => $insertItem) {
				$seria = $insertItem['product']['seria'];
				if (in_array($seria, $insertedSeries)) {
					continue;
				}
				// пропускаем товары без размеров
				if (!$insertItem['product']['size']) {
					continue;
				}
				// пропускаем товары без изображений
				if (!$this->checkImage($insertItem['photos']['photo_name'])) {
					$insertItem['photos']['photo_name'] = str_replace('.jpg', '.png', $insertItem['photos']['photo_name']);
				}
				if (!$this->checkImage($insertItem['photos']['photo_name'])) {
					continue;
				}
				$this->db->insert('products', $insertItem['product']);
				$insID = $this->db->insert_id();
				if (!in_array($insID, $IDS)) {
					$IDS[] = $insID;
				}
				if (!in_array($insertItem['product']['seria'], $insertedSeries)) {
					$insertedSeries[] = $insertItem['product']['seria'];
				}
				$aliase = $this->mdl_product->aliase_translite($insertItem['product']['title'] . '_' . trim($insertItem['product']['articul'])) . '_' . $insID;
				$updProd = [
					'aliase' => $aliase,
				];
				if (isset($insertItem['photos'])) {
					$r = $this->saveImages($insertItem['photos']['photo_name'], $aliase);
				} else {
					$r = false;
				}
				if ($r !== false) {
					$insertItem['photos']['product_id'] = $insID;
					$insertItem['photos']['photo_name'] = $aliase . '.jpg';
					$this->db->insert('products_photos', $insertItem['photos']);
				}
				if (!empty($insertItem['price']['price_item'])) {
					$updProd['price_zac'] = $insertItem['price']['price_item'];
				}
				$this->mdl_db->_update_db("products", "id", $insID, $updProd);
			}
		}
		if (count($IDS) > 0) {
			// Обновление цен
			$this->prices_update($IDS);
		}
		echo json_encode([
			'err' => 0,
			'mess' => 'success',
		]);
		die;
	}

	// Обновить стоимость по всей БД
	public function prices_update($productIds = [])
	{
		$products = [];
		if (count($productIds) > 0) {
			$this->db->where_in('id', $productIds);
			$products = $this->db->get('products')->result_array();
		}
		if (count($products) > 0) {
			$upd = [];
			foreach ($products as $product) {
				if (isset($product['price_zac']) && (int)$product['price_zac'] > 0) {
					$upd[] = [
						'id' => $product['id'],
						'price_roz' => (int)($product['price_zac']) * 2,
						'salle_procent' => 20,
					];
				}
			}
			if (count($upd) > 0) {
				$this->db->update_batch('products', $upd, 'id');
			}
		}
	}

	// Сохранение картинок
	public function saveImages($image = false, $nameProduct = false)
	{
		$r = false;
		if ($image !== false && $nameProduct !== false) {
			$this->load->library('images');
			if (file_exists($this->imagePath . $image)) {
				$prew = "./uploads/products/100/";
				$this->images->imageresize($prew . $nameProduct . '.jpg', $this->imagePath . $image, 100, 100, 100);
				$prew2 = "./uploads/products/250/";
				$this->images->imageresize($prew2 . $nameProduct . '.jpg', $this->imagePath . $image, 250, 250, 100);
				$grozz = "./uploads/products/500/";
				$this->getImage($this->imagePath . $image, $grozz, $nameProduct . ".jpg");
				$r = true;
			}
		}
		return $r;
	}

	public function checkImage($image)
	{
		return ($image && file_exists($this->imagePath . $image));
	}

	// Сохранить пхото как...
	public function getImage($src = false, $path = './', $newName = '1.jpg')
	{
		$t = file_get_contents($src);
		file_put_contents($path . $newName, $t);
	}

	// Получение данных из прайса - готовых для заливки в БД
	public function getProductData($item = false)
	{
		if ($item !== false) {
			$item['vid_izdelia'] = trim($item['vid_izdelia']);
			$cat_ids = [
				'Браслет' => '28', // -ой
				'Цепь' => '40', // ая
			];
			$title = $item['vid_izdelia'] . ' из золота';
			$filterData = [[
				'item' => 'metall',
				'values' => ['JoltZoloto'],
			], [
				'item' => 'kamen',
				'values' => ['empty'],
			], [
				'item' => 'forma_vstavki',
				'values' => [],
			], [
				'item' => 'sex',
				'values' => [],
			], [
				'item' => 'size',
				'values' => [str_replace(",", ".", $item['size'])],
			]];
			$paramItem = [[
				'variabled' => 'metall',
				'value' => 'Желтое Золото',
			], [
				'variabled' => 'material',
				'value' => 'Золото',
			], [
				'variabled' => 'primernyy-ves',
				'value' => str_replace(",", ".", $item['weight']),
			]];

			$r['product'] = [
				'title' => $title,
				'articul' => $item['articul'],
				'cat' => $cat_ids[$item['vid_izdelia']],
				'params' => json_encode($paramItem, JSON_UNESCAPED_UNICODE),
				'size' => str_replace(",", ".", trim($item['size'])),
				'filters' => json_encode($filterData, JSON_UNESCAPED_UNICODE),
				'proba' => (int)preg_replace('/[^\d]+/', '', $item['proba']),
				'seria' => $item['seria'],
				'postavchik' => 'Delta',
				'parser' => 'Delta',
				'weight' => str_replace(",", ".", $item['weight']),
				'qty_empty' => '1',
				'prices_empty' => '1',
				'price_zac' => (float)str_replace(',', '.', trim($item['price'])) * 100,
				'qty' => (int)$item['qty'],
				'sex' => '',//woman/men
				'view' => '1',
				'current' => 'RUR',
				'moderate' => '2',
				'lastUpdate' => time(),
				'optionLabel' => json_encode([]),
			];
			$r['photos'] = [
				'product_id' => 0,
				'photo_name' => $item['articul'] . '.jpg',
				'define' => '1',
			];
			return $r;
		}
	}
}
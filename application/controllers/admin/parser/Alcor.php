<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Alcor extends CI_Controller
{
	protected $user_info = array();
	protected $store_info = array();
	protected $post = array();
	protected $get = array();
	private $imagePath = "./uploads/products/alcor/";

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
		$title = 'Парсинг с сайта Алькора';
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
			'content' => $this->mdl_tpl->view('pages/admin/parser/alcor/alcor.html', array(), true),
			'load' => $this->mdl_tpl->view('snipets/load.html', array(
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			), true),
			'resorses' => $this->mdl_tpl->view('resorses/admin/parser/alcor.html', array(
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path'),
			), true),
		), false);
	}
	// Обработка пакета - заливка его в БД
	/*public function parseAlcor (){
		$packs = ( isset( $this->post['pack'] ) ) ? $this->post['pack'] : [];
		$clear = ( isset( $this->post['clear'] ) ) ? $this->post['clear'] : false;
		if( $clear === '1' ){
			$this->mdl_db->_update_db( "products", "postavchik", 'Alcor', [
				'qty' => 0
			]);
		}
		$listArts = []; $err = 0;
		foreach( $packs as $v ){
			if( $v['articul'] ){
				$art = $this->mdl_product->code_format( $v['articul'], 6 );
				if( !in_array( $art, $listArts ) ){
					$listArts[] = $art;
				}
			}
		}
		$listArtsIsset = []; // список существующих товаров
		$issetProducts = [];
		if( count( $listArts ) > 0 ){
			$issetProducts = $this->mdl_product->queryData([
				'type' => 'ARR2',
				'in' => [
					'method' => 'AND',
					'set' => [[
						'item' => 'articul',
						'values' => $listArts
					]]
				],
				 'where' => [
					'method' => 'AND',
					'set' => [[
						'item' => 'postavchik',
						'value' => 'Alcor'
					]]
				],
				'labels' => ['id', 'aliase', 'title', 'articul']
			]);
			foreach( $issetProducts as $v ){
				$art = $this->mdl_product->code_format( $v['articul'], 6 );
				if( !in_array( $art, $listArtsIsset ) ){
					$listArtsIsset[] = $art;
				}
			}
		}
		$INSERT = [];
		$UPDATE = [];
		if( count( $packs ) > 0 ){
			foreach( $packs as $v ){
				if( !in_array( $v['articul'], $listArtsIsset ) ){
					$size_list = explode( ',', $v['size'] );
					foreach( $size_list as $sl ){
						$iv = $v;
						$iv['size'] = $sl;
						$INSERT[] = $this->getRenderAlcor( $iv );
					}
				}else{
					$UPDATE[] = [
						'ARTICUL' => $v['articul'],
						'data' => $this->getRenderAlcor( $v )
					];
				}
			}
		}
		if( count( $INSERT ) > 0 ){
			foreach( $INSERT as $k => $v ){
				$this->db->insert('products', $v['product'] );
				$insID = $this->db->insert_id();
				$aliase = $this->mdl_product->aliase_translite( $v['product']['title'] ) . '_' . trim( $v['product']['articul']) . '_' . $insID;
				$updProd = [
					'aliase' => $aliase,
					'moderate' => 0
				];
				$r = $this->saveImages( $v['photos']['photo_name'], $aliase );
				if( $r !== false ){
					$v['photos']['product_id'] = $insID;
					$v['photos']['photo_name'] = $aliase.'.jpg';
					$this->db->insert( 'products_photos', $v['photos'] );
					$updProd['moderate'] = 2;
				}
				$ret = $this->mdl_product->getRozCena( $insID );
				if( $ret['price_r'] !== 'МИНУС' ){
					$end = $ret['price_r'] * 100;
					$updProd['price_roz'] = $end;
					$updProd['salle_procent'] = $ret['procSkidca'];
				}
				$this->mdl_db->_update_db( "products", "id", $insID, $updProd );
			}
		}
		if( count( $UPDATE ) > 0 ){
			foreach( $UPDATE as $k => $v ){
				$PID = false;
				foreach( $issetProducts as $ipv ){
					if( $ipv['articul'] === $v['ARTICUL'] ){
						$PID = $ipv['id'];
					}
				}
				if( $PID !== false ){
					$this->mdl_db->_update_db( "products", "id", $PID, [
						'qty' => $v['data']['product']['qty'],
						'price_zac' => $v['data']['product']['price_zac']
					]);
					$ret = $this->mdl_product->getRozCena( $PID );
					if( $ret['price_r'] !== 'МИНУС' ){
						$end = $ret['price_r'] * 100;
						$updProd['price_roz'] = $end;
						$updProd['salle_procent'] = $ret['procSkidca'];
						$this->mdl_db->_update_db( "products", "id", $PID, $updProd );
					}
				}
			}
		}
		echo json_encode([
			'err' => 0,
			'mess' => 'success',
			'debug' => [
				'count-upd' => count( $UPDATE ),
				'count-ins' => count( $INSERT )
			]
		]);
	}*/
	public function parseAlcor()
	{
		//echo "string";
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
			$this->mdl_db->_update_db("products", "postavchik", 'Alcor', [
				'qty' => 0,
			]);
		}
		// Получение пакета данных
		$packs = (isset($this->post['pack'])) ? $this->post['pack'] : [];
		// Получить список товаров со схожими сериями в поступлении
		$listArts = [];
		$err = 0;
		foreach ($packs as $v) {
			if ($v['seria']) {
				$art = trim(mb_strtolower($v['seria']));
				if (!in_array($art, $listArts)) {
					$listArts[] = $art;
				}
			}
		}
		// Получить список существующих товаров в БД
		$listArtsIsset = [];
		if (count($listArts) > 0) {
			$issetProducts = $this->mdl_product->queryData([
				'type' => 'ARR2',
				'in' => [
					'method' => 'AND',
					'set' => [[
						'item' => 'seria',
						'values' => $listArts,
					]],
				],
				'where' => [
					'method' => 'AND',
					'set' => [[
						'item' => 'postavchik',
						'value' => 'Alcor',
					]],
				],
				'labels' => ['id', 'aliase', 'title', 'seria', 'articul', 'aliase'],
			]);
			foreach ($issetProducts as $v) {
				$art = trim(mb_strtolower($v['seria']));
				if (!in_array($art, $listArtsIsset)) {
					$listArtsIsset[$art] = [
						'id' => $v['id'],
						'aliase' => $v['aliase'],
					];
				}
			}
		}
		// Собрать два массива для обновления и добавления
		$INSERT = [];
		$UPDATE = [];
		if (count($packs) > 0) {
			foreach ($packs as $v) {
				$art = trim(mb_strtolower($v['seria']));
				if (!isset($listArtsIsset[$art])) {
					$INSERT[] = $this->getRenderAlcor($v);
				} else {
					$UPDATE[] = [
						'ID' => $listArtsIsset[$art]['id'],
						'aliase' => $listArtsIsset[$art]['aliase'],
						'data' => $this->getRenderAlcor($v),
					];
				}
			}
		}
		$IDS = [];
		if (count($UPDATE) > 0) {
			$upd_bh = [];
			foreach ($UPDATE as $k => $v) {
				$upd_bh[] = [
					'id' => $v['ID'],
					'title' => $v['data']['product']['title'],
					'qty' => '1',
					'price_zac' => $v['data']['product']['price_zac'],
					'params' => $v['data']['product']['params'],
					'filters' => $v['data']['product']['filters'],
				];
				if (!in_array($v['ID'], $IDS)) {
					$IDS[] = $v['ID'];
				}
				if ($updatePhotos && isset($v['data']['photos'])) {
					$r = $this->saveImages($v['data']['photos']['photo_name'], $v['aliase']);
					if ($r !== false) {
						$products_photos = $this->mdl_product->queryData([
							'in' => [
								'method' => 'AND',
								'set' => [[
									'item' => 'product_id',
									'values' => $v['ID'],
								]],
							],
							'labels' => false,
							'table_name' => 'products_photos',
						]);
						if (!count($products_photos)) {
							$v['data']['photos']['product_id'] = $v['ID'];
							$v['data']['photos']['photo_name'] = $v['aliase'] . '.jpg';
//                    $v['data']['photos']['define'] = '1';
							$this->db->insert('products_photos', $v['data']['photos']);
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
			foreach ($INSERT as $k => $v) {
				$art = $v['product']['seria'];
				if (in_array($art, $insertedSeries)) {
					continue;
				}
				if (!$this->checkImage($v['photos']['photo_name'])) {
					continue;
				}
				$this->db->insert('products', $v['product']);
				$insID = $this->db->insert_id();
				if (!in_array($insID, $IDS)) {
					$IDS[] = $insID;
				}
				if (!in_array($v['product']['seria'], $insertedSeries)) {
					$insertedSeries[] = $v['product']['seria'];
				}
				$aliase = $this->mdl_product->aliase_translite($v['product']['title']) . '_' . trim($v['product']['articul']) . '_' . $insID;
				$updProd = [
					'aliase' => $aliase,
				];
				if (isset($v['photos'])) {
					$r = $this->saveImages($v['photos']['photo_name'], $aliase);
				} else {
					$r = false;
				}
				if ($r !== false) {
					$v['photos']['product_id'] = $insID;
					$v['photos']['photo_name'] = $aliase . '.jpg';
					$this->db->insert('products_photos', $v['photos']);
				}
				if (!empty($v['price']['price_item'])) {
					$updProd['price_zac'] = $v['price']['price_item'];
				}
				$this->mdl_db->_update_db("products", "id", $insID, $updProd);
			}
		}
		if (count($IDS) > 0) {
			// Обновление цен
			$this->prices_update($IDS);
			// Обновление ДРАГ-Камней
			$this->getDragValues($IDS);
		}
		echo json_encode([
			'err' => 0,
			'mess' => 'success',
		]);
		die;
	}

	// Обновить стоимость по всей БД
	public function prices_update($pids = [])
	{
		$r = [];
		if (count($pids) > 0) {
			$this->db->where_in('id', $pids);
			$r = $this->db->get('products')->result_array();
		}
		if (count($r) > 0) {
			$upd = [];
			foreach ($r as $v) {
				$_title = json_decode($v['optionLabel'], true);
				$__title = $_title['seria'];
				$res = $this->mdl_product->getProductPrice(array(
					'id' => $v['id'],
					'title' => $__title,
					'price_zac' => $v['price_zac'],
				));
				if (isset($res['price_r']) > 0 && (int)$res['price_r'] > 0 && $res['price_r'] !== 'МИНУС') {
					$end = intval($res['price_r'] * 100);
					$upd[] = [
						'id' => $v['id'],
						'price_roz' => $end,
						'salle_procent' => $res['procSkidca'],
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
				//$itemFile = $this->images->file_item( $this->imagePath . $image, $nameProduct.'.jpg' );
				$prew = "./uploads/products/100/";
				$this->images->imageresize($prew . $nameProduct . '.jpg', $this->imagePath . $image, 100, 100, 100);
				$prew2 = "./uploads/products/250/";
				$this->images->imageresize($prew2 . $nameProduct . '.jpg', $this->imagePath . $image, 250, 250, 100);
				$grozz = "./uploads/products/500/";
				$this->getImage($this->imagePath . $image, $grozz, $nameProduct . ".jpg");
				//$this->images->imageresize( $prew.$nameProduct.'.jpg', $this->imagePath.$image, 500, 500, 100 );
				//$this->images->resize_jpeg( $itemFile, $this->imagePath, $prew, $nameProduct, 100, 100, 100);
				///$this->images->resize_jpeg( $itemFile, $this->imagePath, $prew2, $nameProduct, 100, 250, 250);
				//$this->images->resize_jpeg( $itemFile, $this->imagePath, $grozz, $nameProduct, 100, 1000, 500);
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
	public function getRenderAlcor($item = false)
	{
		if ($item !== false) {
			$cat_ids = [
				'Браслет' => '28', // -ий
				'Брошь' => '35', // -ое
				'Колье' => '36', // -ое
				'Кольцо' => '1', // -ое
				'Пирсинг' => '38', // -ий
				'Подвеска' => '19', // -ая
				'Серьги' => '10', // -ие
				//
				// 'Обручальные кольца' => '1',
				// 'Крест' => '37',
				// 'Пуссеты' => '43',
				// 'Браслеты' => '28',
				'Запонки' => '41',
			];
			$title = $item['vid_izdelia'];
			if (!empty($item['dlaKogo']) && $item['dlaKogo'] === 'Женщине,Мужчине') {
				$title .= ' унисекс';
			}
			if (!empty($item['dlaKogo']) && $item['dlaKogo'] === 'Мужчине') {
				$title .= ' мужское';
			}
			if (!empty($item['dlaKogo']) && $item['dlaKogo'] === 'Детям') {
				$__t = 'ое';
				$__i_int = $cat_ids[$item['vid_izdelia']];
				if (in_array($__i_int, ['28', '38'])) {
					$__t = 'ий';
				}
				if (in_array($__i_int, ['19'])) {
					$__t = 'ая';
				}
				if (in_array($__i_int, ['10'])) {
					$__t = 'ие';
				}
				$title .= ' детск' . $__t;
			}
			$filterData = [[
				'item' => 'metall',
				'values' => [],
			], [
				'item' => 'kamen',
				'values' => [],
			], [
				'item' => 'forma_vstavki',
				'values' => [],
			], [
				'item' => 'sex',
				'values' => [],
			], [
				'item' => 'size',
				'values' => [],
			]];
			if ($item['dlaKogo'] === 'Женщине,Мужчине') {
				$filterData[3]['values'][] = 'woman';
				$filterData[3]['values'][] = 'men';
			}
			if ($item['dlaKogo'] === 'Женщине') {
				$filterData[3]['values'][] = 'woman';
			}
			if ($item['dlaKogo'] === 'Мужчине') {
				$filterData[3]['values'][] = 'men';
			}
			$paramItem = [[
				'variabled' => 'metall',
				'value' => '-',
			], [
				'variabled' => 'material',
				'value' => '-',
			], [
				'variabled' => 'vstavka',
				'value' => '-',
			], [
				'variabled' => 'forma-vstavki',
				'value' => '-',
			], [
				'variabled' => 'primernyy-ves',
				'value' => '-',
			], [
				'variabled' => 'dlya-kogo',
				'value' => $item['dlaKogo'],
			], [
				'variabled' => 'technologiya',
				'value' => '-',
			]];

			$title .= " из ";
			$article = explode("-", $item["articul"]);
			$art = substr($article[1], 0, 1);
			switch ($art) {
				case 1:
					$paramItem[0]['value'] = 'Красное Золото';
					$filterData[0]['values'][] = 'krasnZoloto';
					$title .= 'Красного Золота';
					break;
				case 2:
					$paramItem[0]['value'] = 'Белое Золото';
					$filterData[0]['values'][] = 'belZoloto';
					$title .= 'Белого Золота';
					break;
				case 3:
					$paramItem[0]['value'] = 'Желтое Золото';
					$filterData[0]['values'][] = 'JoltZoloto';
					$title .= 'Желтого Золота';
					break;
				case 4:
					$paramItem[0]['value'] = 'Желтое Золото';
					$filterData[0]['values'][] = 'JoltZoloto';
					$title .= 'Желтого Золота';
					break;
				case 5:
					$paramItem[0]['value'] = 'Белое Золото';
					$filterData[0]['values'][] = 'belZoloto';
					$title .= 'Белого Золота';
					break;
				case 7:
					$paramItem[0]['value'] = 'Красное Золото';
					$filterData[0]['values'][] = 'krasnZoloto';
					$title .= 'Красного Золота';
					break;
				case "А":
					$paramItem[0]['value'] = 'Красное Золото';
					$filterData[0]['values'][] = 'krasnZoloto';
					$title .= 'Красного Золота';
					break;
				case "Б":
					$paramItem[0]['value'] = 'Белое Золото';
					$filterData[0]['values'][] = 'belZoloto';
					$title .= 'Белого Золота';
					break;
				case "В":
					$paramItem[0]['value'] = 'Желтое Золото';
					$filterData[0]['values'][] = 'JoltZoloto';
					$title .= 'Желтого Золота';
					break;

				default:
					$paramItem[0]['value'] = "Золото";
					$filterData[0]['values'][] = 'Zoloto';
					$title .= 'Золота';
					break;
			}

			$paramItem[1]['value'] = 'Золото';
			$stoneList = ['Без камня', 'С камнем', 'Кристалл Swarovski', 'Swarovski Zirconia', 'Бриллиант', 'Сапфир', 'Изумруд', 'Рубин', 'Жемчуг', 'Топаз', 'Аметист', 'Гранат', 'Хризолит', 'Цитрин', 'Агат', 'Кварц', 'Янтарь', 'Опал', 'Фианит',
				'Родолит', 'Ситалл', 'Эмаль', 'Оникс', 'Корунд', 'Коралл прессованный'];
			$stoneListVals = ['empty', 'no_empty', 'swarovski', 'swarovski', 'brilliant', 'sapfir', 'izumrud', 'rubin', 'jemchug', 'topaz', 'ametist', 'granat', 'hrizolit', 'citrin', 'agat', 'kvarc', 'jantar', 'opal', 'fianit',
				'Rodolit', 'Sitall', 'Emal', 'Oniks', 'Korund', 'Corall_pressovannyi'];
			$stoneList2 = ['Без камня', 'С камнем', 'Кристаллом Swarovski', 'Swarovski Zirconia', 'Бриллиантом', 'Сапфиром', 'Изумрудом', 'Рубином', 'Жемчугом', 'Топазом', 'Аметистом', 'Гранатом', 'Хризолитом', 'Цитрином', 'Агатом', 'Кварцом', 'Янтарем', 'Опалом', 'Фианитом',
				'Родолитом', 'Ситаллом', 'Эмалью', 'Ониксом', 'Корундом', 'Кораллом прессованным'];
			$text = $item['optionLabel'];
			$stone_list = [];
			$param_kamen_list = [];
			foreach ($stoneList as $pk => $pv) {
				$str_text = mb_strtolower($text);
				$str_find = '/' . mb_strtolower($pv) . '/iU';
				if (preg_match($str_find, $str_text)) {
					$filterData[1]['values'][] = $stoneListVals[$pk];
					$stone_list[] = $stoneList2[$pk];
					$param_kamen_list[] = $stoneList[$pk];
				}
			}
			if (count($stone_list) > 0) {
				if (count($stone_list) == 1) {
					$title = $title . ' с ' . $stone_list[0];
					$paramItem[2]['value'] = $param_kamen_list[0];
				}
				if (count($stone_list) == 2) {
					$title = $title . ' с ' . $stone_list[0] . ' и ' . $stone_list[1];
					$paramItem[2]['value'] = $param_kamen_list[0] . ', ' . $param_kamen_list[1];
				}
				if (count($stone_list) > 2) {
					$paramItem[2]['value'] = $param_kamen_list;
					$__i = count($stone_list) - 1;
					$last = $stone_list[$__i];
					array_splice($stone_list, -1);
					$title = $title . ' с ' . implode(',', $stone_list) . ' и ' . $last;
				}
			}
			// 'empty','no_empty'
			if (count($filterData[1]['values']) > 0) {
				$filterData[1]['values'][] = 'no_empty';
			} else {
				$filterData[1]['values'][] = 'empty';
			}
			$razmerList = ['2.0', '12.0', '13.0', '13.5', '14.0', '14.5', '15.0', '15.5', '16.0', '16.5', '17.0', '17.5', '18.0', '18.5', '19.0', '19.5', '20.0', '20.5', '21.0', '21.5', '22.0', '22.5', '23.0', '23.5', '24.0', '24.5', '25.0'];
			$razmerListVals = ['2_0', '12_0', '13_0', '13_5', '14_0', '14_5', '15_0', '15_5', '16_0', '16_5', '17_0', '17_5', '18_0', '18_5', '19_0', '19_5', '20_0', '20_5', '21_0', '21_5', '22_0', '22_5', '23_0', '23_5', '24_0', '24_5', '25_0'];
			/*В вес размер */
			if ($item['size']) {
				$sz = str_replace(",", ".", $item['size']);
				$paramItem[4]['value'] = $sz;
				foreach ($razmerList as $rk => $rv) {
					if ($rv === $sz) {
						$filterData[4]['values'][] = $razmerListVals[$rk];
					}
				}
			}
			$sex = null;
			if ($item['dlaKogo'] === 'Женщине') {
				$sex = 'woman';
			}
			if ($item['dlaKogo'] === 'Мужчине') {
				$sex = 'men';
			}
			$r['product'] = [
				'title' => $title,
				'articul' => $item['articul'],
				'cat' => $cat_ids[$item['vid_izdelia']],
				'params' => json_encode($paramItem),
				'size' => str_replace('.0', '', str_replace(',', '.', trim($item['size']))),
				'filters' => json_encode($filterData),
				'proba' => $item['proba'],
				'seria' => trim(mb_strtolower($item['seria'])),
				'postavchik' => 'Alcor',
				'parser' => 'Alcor',
				'weight' => str_replace(",", ".", $item['weight']),
				'qty_empty' => '1',
				'prices_empty' => '1',
				'price_zac' => (isset($item['price'])) ? intval(preg_replace('{[^0-9]}', '', $item['price']) * 100) : 0,
				'qty' => '1',
				'sex' => $sex,//woman/men
				'view' => '1',
				'current' => 'RUR',
				'moderate' => '2',
				'lastUpdate' => time(),
				'optionLabel' => json_encode([
					'collections' => $item['collection'],
					'options' => $item['complect'],
					'vstavki' => $item['vstavki'],
					'seria' => $item['optionLabel'],
				]),
			];
			$r['photos'] = [
				'product_id' => 0,
				'photo_name' => $item['articul'] . '.jpg',
				'define' => '1',
			];
			return $r;
		}
	}

	// Извлечение параметров ДРАГ камней
	public function getDragValues($productIds = [])
	{
//		$productIds = [68359];
		$products = [];
		if (count($productIds) > 0) {
			$this->db->where_in('id', $productIds);
			$this->db->where('postavchik', 'Alcor');
//			$this->db->limit(10000, 50000);
			$products = $this->db->get('products')->result_array();
		}
		if (count($products) > 0) {
			$propertyNamesForStones = [
				'Агат зеленый' => ['Камень', 'Кол-во камней', 'Вес, Ct.', 'Форма огранки', 'Размер камня, мм', 'Кол-во граней'],
				'Бриллиант' => ['Камень', 'Кол-во камней', 'Вес, Ct.', 'Цвет/Чистота', 'Форма огранки', 'Рассев', 'Кол-во граней'],
				'Г.Т.изумруд' => ['Камень', 'Кол-во камней', 'Вес, Ct.', 'Цвет/Чистота', 'Форма огранки', 'Размер камня, мм', 'Кол-во граней'],
				'Г.Т.сапфир' => ['Камень', 'Кол-во камней', 'Вес, Ct.', 'Цвет/Чистота', 'Форма огранки', 'Размер камня, мм', 'Кол-во граней'],
				'Гранат' => ['Камень', 'Кол-во камней', 'Вес, Ct.', 'Цвет/Чистота', 'Форма огранки', 'Размер камня, мм', 'Кол-во граней'],
				'Жемчуг' => ['Камень', 'Кол-во камней', 'Вес, Ct.', 'Цвет/Чистота', 'Форма огранки', 'Размер камня, мм', 'Кол-во граней'],
				'Изумруд' => ['Камень', 'Кол-во камней', 'Вес, Ct.', 'Цвет/Чистота', 'Форма огранки', 'Размер камня, мм', 'Кол-во граней'],
				'Лондон Топаз' => ['Камень', 'Кол-во камней', 'Вес, Ct.', 'Цвет/Чистота', 'Форма огранки', 'Размер камня, мм', 'Кол-во граней'],
				'Оникс' => ['Камень', 'Кол-во камней', 'Вес, Ct.', 'Цвет/Чистота', 'Форма огранки', 'Размер камня, мм', 'Кол-во граней'],
				'Рубин' => ['Камень', 'Кол-во камней', 'Вес, Ct.', 'Цвет/Чистота', 'Форма огранки', 'Размер камня, мм', 'Кол-во граней'],
				'Сапфир' => ['Камень', 'Кол-во камней', 'Вес, Ct.', 'Цвет/Чистота', 'Форма огранки', 'Размер камня, мм', 'Кол-во граней'],
				'Сапфир диффузионный' => ['Камень', 'Кол-во камней', 'Вес, Ct.', 'Цвет/Чистота', 'Форма огранки', 'Размер камня, мм', 'Кол-во граней'],
				'Сапфир звездчатый' => ['Камень', 'Кол-во камней', 'Вес, Ct.', 'Цвет/Чистота', 'Форма огранки', 'Размер камня, мм', 'Кол-во граней'],
				'Синтетический корунд' => ['Камень', 'Кол-во камней', 'Вес, Ct.', 'Цвет/Чистота', 'Форма огранки', 'Размер камня, мм', 'Кол-во граней'],
				'Топаз Swiss' => ['Камень', 'Кол-во камней', 'Вес, Ct.', 'Цвет/Чистота', 'Форма огранки', 'Размер камня, мм', 'Кол-во граней'],
				'Хризолит' => ['Камень', 'Кол-во камней', 'Вес, Ct.', 'Цвет/Чистота', 'Форма огранки', 'Размер камня, мм', 'Кол-во граней'],
				'Цитрин' => ['Камень', 'Кол-во камней', 'Вес, Ct.', 'Цвет/Чистота', 'Форма огранки', 'Размер камня, мм', 'Кол-во граней'],
			];
			// Названия камней
			$stoneNames = array_keys($propertyNamesForStones);
			// Названия камней с замененными пробелами
			$stoneNamesProcessed = [];
			// Позиция названия камня в данных (описании) камня, здесь везде 0
//			$stoneNameIndexes = [];

			foreach ($propertyNamesForStones as $stoneName => $stonePropertyNames) {
				$stoneNamesProcessed[] = str_replace(' ', '+', $stoneName);
//				$stoneNameIndexes[] = array_search('Камень', $stonePropertyNames);
			}

			$upd = [];
			foreach ($products as $product) {
				$optionLabel = json_decode($product['optionLabel'], true);
				$stoneDatasList = explode(',', $optionLabel['seria']);

				foreach ($stoneDatasList as $stoneIndex => $stoneData) {
					$stoneDatasList[$stoneIndex] = trim($stoneData);
				}

				// убираем пустые значения
				$stoneDatasList = array_values(array_filter($stoneDatasList));

				foreach ($stoneDatasList as $stoneDataIndex => $stoneData) {
					foreach ($stoneNames as $stoneIndex => $stoneName) {
						if (strpos($stoneName, ' ') !== false) {
							$stoneNamePlus = $stoneNamesProcessed[$stoneIndex];
							$stoneData = str_replace($stoneName, $stoneNamePlus, $stoneData);
						}
					}
					$stoneProperties = explode(' ', $stoneData);
					$stoneDatasList[$stoneDataIndex] = array_values(array_filter($stoneProperties));
				}

				$caratsRanges = []; // массив для поля 'filter_carats'
				$drag = []; // массив для поля 'drag'
				foreach ($stoneDatasList as $stoneProperties) {
					foreach ($stoneProperties as $stoneProperty) {
//						$__v = mb_strtolower($stoneProperty);
						if (in_array($stoneProperty, $stoneNamesProcessed)) {
							$stoneIndex = array_search($stoneProperty, $stoneNamesProcessed);
//							$stoneNameIndex = $stoneNameIndexes[$stoneIndex];
							$stoneName = $stoneNames[$stoneIndex];

							$dragItem = [
								'kamen' => $stoneName,
								'data' => [],
							];

							$stonePropertyNames = $propertyNamesForStones[$stoneName];
							$caratsRange = null;
							foreach ($stonePropertyNames as $stonePropertyIndex => $stonePropertyName) {
//								if (!isset($stoneProperties[$stonePropertyIndex])) {
//									echo $product['id'];
//								}

								if ($stonePropertyName == 'Камень') { // это название камня
									$stoneProperty = $stoneName;
								} else {
									$stoneProperty = $stoneProperties[$stonePropertyIndex];
								}

								$dragItem['data'][] = [
									'name' => $stonePropertyName,
									'value' => $stoneProperty ?: '-',
								];

								if ($stoneProperty && $stonePropertyName == 'Вес, Ct.') { // это вес камня
									$caratsRange = $this->mdl_product->getCaratsRange($stoneProperty);
								}
							}
							$drag[] = $dragItem;
							if ($caratsRange && !in_array($caratsRange, $caratsRanges)) {
								$caratsRanges[] = $caratsRange;
							}
						}
					}
				}
				$upd[] = [
					'id' => $product['id'],
					'optionLabel' => json_encode($optionLabel, JSON_UNESCAPED_UNICODE),
					'drag' => json_encode($drag, JSON_UNESCAPED_UNICODE),
					'filter_carats' => json_encode($caratsRanges, JSON_UNESCAPED_UNICODE),
				];
			}

			if (count($upd)) {
				$this->db->update_batch('products', $upd, 'id');
//				var_dump(count($upd));
//				die;
			}
		}
		return true;
	}

	public function path_1()
	{
		$this->db->like('title', 'детск');
		$r = $this->db->get('products')->result_array();
		$cat_ids = [
			'28' => 'ий',
			'35' => 'ое',
			'36' => 'ое',
			'1' => 'ое',
			'38' => 'ий',
			'19' => 'ая',
			'10' => 'ие',
		];
		echo count($r);
		foreach ($r as $key => $value) {
			$__t = 'ое';
			if (isset($cat_ids[$value['cat']])) {
				$__t = $cat_ids[$value['cat']];
			}
			$__t_replace = 'детск' . $__t;
			$title = str_replace("детское", $__t_replace, $value['title']);
			$this->db->where('id', $value['id']);
			$this->db->update('products', [
				'title' => $title,
			]);
		}
	}
}
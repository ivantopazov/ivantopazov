<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Master extends CI_Controller
{

	protected $user_info = array();
	protected $store_info = array();

	protected $post = array();
	protected $get = array();

	private $prod_table = "products";
	private $ph_table = "products_photos";

	private $upload_path = "./uploads/products/master/";
	private $type_cat = [
		"Кольцо" => 1,
		"Серьги" => 10,
		"Серьги детские" => 10,
		"Пусеты" => 10,
		"Подвеска" => 19,
		"Браслет" => 28,
		"Брошь" => 35,
		"Колье" => 36,
		"Крест" => 37,
	];

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

		$title = 'Выгрузка Мастер Бриллиант';
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

			'content' => $this->mdl_tpl->view('pages/admin/parser/master/master.html', array(), true),

			'load' => $this->mdl_tpl->view('snipets/load.html', array(
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			), true),

			'resorses' => $this->mdl_tpl->view('resorses/admin/parser/master.html', array(
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path'),
			), true),

		), false);

	}

	public function parse()
	{
		// Принимаем номер строки файла для обработки, считаем дубли и ошибки
		$current_str = $_POST["str"];
		$err = 0;
		$double = 0;

		$file = file($this->upload_path . "/master.csv");
		$data = array();

		$file[$current_str] = mb_convert_encoding($file[$current_str], "utf8", "cp1251");

		$values = explode("\"", $file[$current_str]); // Для разделения дескрипшена и остальных данных
		$val = explode(";", $values[0]); // Делим данные

		$data[0]["article"] = $val[0];
		$data[0]["shk"] = $val[1];
		$data[0]["size"] = $val[2];
		$data[0]["metal"] = $val[3];
		$data[0]["metal_color"] = $val[4];
		$data[0]["probe"] = $val[5];
		$data[0]["type"] = $val[6];
		$data[0]["weight"] = $val[7];
		$data[0]["price"] = $val[8];
		$data[0]["country"] = $val[9];
		$data[0]["garniture"] = $val[10];
		$data[0]["brand"] = $val[11];
		$data[0]["descr"] = $values[1];

		foreach ($data as $key => $dt) {
			$title = $this->title($dt);
			$params = $this->params($dt);
			$filter = $this->filter($dt, $params[2]["value"]);
			$drag = $this->drag($dt["descr"]);
			$caratsRanges = $this->getCaratsRanges($drag);

			$item = [
				'articul' => $dt['article'],
				'cat' => $this->type_cat[$dt['type']],
				'title' => $title,
				'price_zac' => (int)str_replace(" ", "", $dt["price"]) * 100,
				'price_roz' => (int)((int)str_replace(" ", "", $dt["price"]) * 2.5) * 100,
				'current' => 'RUR',
				'salle_procent' => rand(4, 8) * 5,
				'view' => '1',
				'qty' => '1',
				'qty_empty' => '1',
				'prices_empty' => '1',
				'weight' => $dt["weight"],
				'sex' => "woman",
				'postavchik' => 'Master Brilliant',
				'parser' => 'Master',
				'proba' => $dt['probe'],
				'params' => json_encode($params, JSON_UNESCAPED_UNICODE),
				'size' => str_replace('.0', '', str_replace(',', '.', trim($dt['size']))),
				'filters' => json_encode($filter, JSON_UNESCAPED_UNICODE),
				'moderate' => '2',
				'lastUpdate' => time(),
				'optionLabel' => json_encode([
					'collections' => $dt["brand"],
					'options' => $dt['garniture'],
					'vstavki' => str_replace(";", " ", str_replace("#", " ", $dt['descr'])),
					'seria' => "",
				], JSON_UNESCAPED_UNICODE),
				'drag' => json_encode($drag, JSON_UNESCAPED_UNICODE),
				'filter_carats' => json_encode($caratsRanges, JSON_UNESCAPED_UNICODE),
			];

			// Получаем итем с одинаковым артикулом и весом для обновления цены
			$prods = $this->mdl_product->queryData([
				'type' => 'ARR2',
				'where' => [
					'method' => 'AND',
					'set' => [[
						'item' => 'parser',
						'value' => 'Master',
					], [
						'item' => 'articul',
						'value' => $dt["article"],
					]],
				],
				'labels' => ['id', 'weight', 'size'],
				'table_name' => $this->prod_table,
			]);

			// Если есть такой же товар по артиклу и весу, то обновляем его цену, иначе вносим в базу новый
			if (count($prods) > 0) {
				foreach ($prods as $k => $v) {
					if ($v["weight"] == $dt["weight"] and $v["size"] == $dt["size"]) {
						$upd = [ // Сюда можно установить любые значения для обновления
							/*"title" => $item["title"],
							"price_zac" => $item["price_zac"],
							"price_roz" => $item["price_roz"],*/
							"params" => $item["params"],
							"filters" => $item["filters"],
						];
						$this->mdl_db->_update_db($this->prod_table, "id", $v["id"], $upd);

						$double++; // Считаем дубли с обновлениями

						echo json_encode(["err" => $err, "double" => $double]);
						return;
					}
				}
			}

			// Проверка фото
			if (file_exists($this->upload_path . $dt["article"] . ".jpg")) $ph_name = $dt["article"] . ".jpg";

			if (!$ph_name) {
				$err++;
				echo json_encode(["err" => $err, "double" => $double]);
				return;
			}

			$this->db->insert($this->prod_table, $item);
			$id = $this->db->insert_id();
			if (!$id) $err++;
			$aliase = $this->mdl_product->aliase_translite($title) . '_' . trim($dt["article"]) . '_' . $id;
			$this->mdl_db->_update_db($this->prod_table, "id", $id, ["aliase" => $aliase]);

			$this->images($dt["article"]);
			$ph = [
				"product_id" => $id,
				"photo_name" => $dt["article"] . ".jpg", // Вместо алиаса по артиклу
				"define" => 1,
			];
			$this->db->insert($this->ph_table, $ph);

			echo json_encode(["err" => $err, "double" => $double]);
			return;
		}
	}

	private function title($dt)
	{
		$title = $dt["type"] . " из ";

		if (trim($dt["metal_color"]) == "Желтый") $title .= "желтого ";
		if (trim($dt["metal_color"]) == "Красный") $title .= "красного ";
		if (trim($dt["metal_color"]) == "Белый") $title .= "белого ";
		if (trim($dt["metal_color"]) == "Красный желтый белый") $title .= "красного, белого, желтого ";
		$title .= "золота";

		if (strlen($dt["descr"]) < 3) return $title;

		$len = strlen($title) + 3;
		$title .= " с ";
		$kamen = explode(";", $dt["descr"]);
		foreach ($kamen as $key => $val) {
			if (preg_match("/Жемчуг/", $val) and !preg_match("/Жемчуг/", $title)) $title .= "Жемчугом, ";
			if (preg_match("/Фианит/", $val) and !preg_match("/Фианит/", $title)) $title .= "Фианитом, ";
			if (preg_match("/Гранат/", $val) and !preg_match("/Гранат/", $title)) $title .= "Гранатом, ";
			if (preg_match("/Корунд/", $val) and !preg_match("/Корунд/", $title)) $title .= "Корундом, ";
			if (preg_match("/Топаз/", $val) and !preg_match("/Топаз/", $title)) $title .= "Топазом, ";
			if (preg_match("/Аметист/", $val) and !preg_match("/Аметист/", $title)) $title .= "Аметистом, ";
			if (preg_match("/Изумруд/", $val) and !preg_match("/Изумруд/", $title)) $title .= "Изумрудом, ";
			if (preg_match("/Бриллиант/", $val) and !preg_match("/Бриллиант/", $title)) $title .= "Бриллиантом, ";
			if (preg_match("/Родолит/", $val) and !preg_match("/Родолит/", $title)) $title .= "Родолитом, ";
			if (preg_match("/Шпинель/", $val) and !preg_match("/Шпинель/", $title)) $title .= "Шпинелью, ";
			if (preg_match("/Раух-топаз/", $val) and !preg_match("/Раух-топаз/", $title)) $title .= "Раух-топазом, ";
			if (preg_match("/Гранат/", $val) and !preg_match("/Гранат/", $title)) $title .= "Гранатом, ";
			if (preg_match("/Лунный камень/", $val) and !preg_match("/Лунный камень/", $title)) $title .= "Лунным камнем, ";
			if (preg_match("/Агат/", $val) and !preg_match("/Агат/", $title)) $title .= "Агатом, ";
			if (preg_match("/Аквамарин/", $val) and !preg_match("/Аквамарин/", $title)) $title .= "Аквамарином, ";
			if (preg_match("/Александрит/", $val) and !preg_match("/Александрит/", $title)) $title .= "Александритом, ";
			if (preg_match("/Аметрин/", $val) and !preg_match("/Аметрин/", $title)) $title .= "Аметрином, ";
			if (preg_match("/Горный хрусталь/", $val) and !preg_match("/Горный хрусталь/", $title)) $title .= "Горным хрусталем, ";
			if (preg_match("/Кварц/", $val) and !preg_match("/Кварц/", $title)) $title .= "Кварцем, ";
			if (preg_match("/Морганит/", $val) and !preg_match("/Морганит/", $title)) $title .= "Морганитом, ";
			if (preg_match("/Празиолит/", $val) and !preg_match("/Празиолит/", $title)) $title .= "Празиолитом, ";
			if (preg_match("/Сапфир/", $val) and !preg_match("/Сапфир/", $title)) $title .= "Сапфиром, ";
			if (preg_match("/Сердолик/", $val) and !preg_match("/Сердолик/", $title)) $title .= "Сердоликом, ";
			if (preg_match("/Султанит/", $val) and !preg_match("/Султанит/", $title)) $title .= "Султанитом, ";
			if (preg_match("/Турмалин/", $val) and preg_match("/Турмалин/", $title)) $title .= "Турмалином, ";
			if (preg_match("/Цитрин/", $val) and !preg_match("/Цитрин/", $title)) $title .= "Цитрином, ";
			if (preg_match("/Хризолит/", $val) and !preg_match("/Хризолит/", $title)) $title .= "Хризолитом, ";
		}

		if (strlen($title) == $len) $title = substr($title, 0, -3); // Если вдруг не оказалось совпадения по камням
		else $title = substr($title, 0, -2); // Удаление ","

		return $title;
	}

	private function params($dt = array())
	{
		$params = [[
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
			'value' => '-',
		], [
			'variabled' => 'technologiya',
			'value' => '-',
		]];

		if (trim($dt["metal_color"]) == "Желтый" or trim($dt["metal_color"]) == "Жёлтый") $metal = "Желтое ";
		if (trim($dt["metal_color"]) == "Красный") $metal = "Красное ";
		if (trim($dt["metal_color"]) == "Белый") $metal = "Белое ";
		if (trim($dt["metal_color"]) == "Красный желтый белый") $metal = "Красное, белое, желтое ";
		$metal .= $dt["metal"];

		$params[0]["value"] = $metal;
		$params[1]["value"] = $dt["metal"];

		$vstavka = explode(";", $dt["descr"]);
		$vstavka_param = $vstavka[1];
		if ($vstavka[4]) $vstavka_param .= ", " . $vstavka[4];
		if ($vstavka[7]) $vstavka_param .= ", " . $vstavka[7];
		if ($vstavka[10]) $vstavka_param .= ", " . $vstavka[10];
		if ($vstavka[13]) $vstavka_param .= ", " . $vstavka[13];
		if ($vstavka[16]) $vstavka_param .= ", " . $vstavka[16];
		$params[2]["value"] = $vstavka_param;

		$vstavka_form = $vstavka[2];
		if ($vstavka[4]) $vstavka_form .= ", " . $vstavka[5];
		if ($vstavka[7]) $vstavka_form .= ", " . $vstavka[8];
		if ($vstavka[10]) $vstavka_form .= ", " . $vstavka[11];
		if ($vstavka[13]) $vstavka_form .= ", " . $vstavka[14];
		if ($vstavka[16]) $vstavka_form .= ", " . $vstavka[17];
		$params[3]["value"] = $vstavka_form;

		$params[4]["value"] = $dt["weight"];
		if ($dt["type"] == "Серьги детские") $params[5]["value"] = "Детям";
		else $params[5]["value"] = "Женщинам";

		return $params;
	}

	private function filter($dt, $vstavka)
	{
		$filter = [[
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

		if (trim($dt["metal_color"]) == "Красный") $filter[0]["values"][] = "krasnZoloto";
		if (trim($dt["metal_color"]) == "Белый") $filter[0]["values"][] = "belZoloto";
		if (trim($dt["metal_color"]) == "Желтый" or trim($dt["metal_color"]) == "Жёлтый") $filter[0]["values"][] = "JoltZoloto";

		$kamen = explode(",", $vstavka);
		foreach ($kamen as $k => $v) $filter[1]["values"][] = $this->mdl_product->aliase_translite($v);

		if ($dt["type"] == "Серьги детские") $filter[3]["values"][] = "kids";
		else $filter[3]["values"][] = "woman";

		$filter[4]["values"][] = $dt["size"];

		return $filter;
	}

	private function drag($descr)
	{
		$drag = array();

		$kamen = explode("#", $descr);
		foreach ($kamen as $key => $value) {
			$val = explode(";", $value);
			$drag[$key]["kamen"] = $val[1];
			$drag[$key]["data"] = [
				["name" => "Кол-во камней", "value" => $val[0]],
				["name" => "Камень", "value" => $val[1]],
				["name" => "Форма огранки", "value" => $val[2]],
				["name" => "Вес, Ct.", "value" => $val[3]],
			];
		}

		return $drag;
	}

	private function images($article)
	{
		if (!file_exists($this->upload_path . $article . ".jpg")) return;

		$this->load->library('images');

		$to_100 = "./uploads/products/100/";
		$this->images->imageresize($to_100 . $article . '.jpg', $this->upload_path . $article . '.jpg', 100, 100, 100);

		$to_250 = "./uploads/products/250/";
		$this->images->imageresize($to_250 . $article . '.jpg', $this->upload_path . $article . '.jpg', 250, 250, 100);

		$to_500 = "./uploads/products/500/";
		$this->images->imageresize($to_500 . $article . '.jpg', $this->upload_path . $article . '.jpg', 500, 500, 100);
	}

	public function countMaster()
	{
		$file = file($this->upload_path . "/master.csv");
		$count = count($file);
		if ($count == 1 and strlen($file[0]) == 0) $count = 0;
		echo json_encode(['count' => $count]);
		die;
	}

	public function getCaratsRangesAll($productIds = [])
	{
//		$prodictIds = [62284];
//		$products = [];
//		if (count($prodictIds) > 0) {
//			$this->db->where_in('id', $prodictIds);
			$this->db->where('postavchik', 'Master Brilliant');
			$this->db->limit(10000, 30000);
			$products = $this->db->get('products')->result_array();
//		}

		$upd = [];
		if (count($products) > 0) {
			foreach ($products as $product) {
				$drag = json_decode($product['drag'], true);
				$optionLabel = json_decode($product['optionLabel'], true);
				$caratsRanges = $this->getCaratsRanges($drag);
				$upd[] = [
					'id' => $product['id'],
					'optionLabel' => json_encode($optionLabel, JSON_UNESCAPED_UNICODE),
					'drag' => json_encode($drag, JSON_UNESCAPED_UNICODE),
					'filter_carats' => json_encode($caratsRanges, JSON_UNESCAPED_UNICODE),
				];
			}
			if (count($upd)) {
				$this->db->update_batch('products', $upd, 'id');
				var_dump(count($upd));
				die;
			}
		}
	}

	public function decodeOptionLabel($productIds = [])
	{
//		$prodictIds = [62284];
//		$products = [];
//		if (count($prodictIds) > 0) {
//			$this->db->where_in('id', $prodictIds);
			$this->db->where('postavchik', 'Master Brilliant');
			$this->db->limit(10000, 30000);
			$products = $this->db->get('products')->result_array();
//		}

		$upd = [];
		if (count($products) > 0) {
			foreach ($products as $product) {
				$optionLabel = $product['optionLabel'];
				for ($i = 0; $i < 10; $i++) {
					$decoded = json_decode($optionLabel, true);
					if ($decoded) {
						$optionLabel = $decoded;
					} else {
						break;
					}

				}
				$upd[] = [
					'id' => $product['id'],
					'optionLabel' => json_encode($optionLabel, JSON_UNESCAPED_UNICODE),
				];
			}
			if (count($upd)) {
				$this->db->update_batch('products', $upd, 'id');
				var_dump(count($upd));
				die;
			}
		}
	}

	/**
	 * Градации каратности по значению поля drag
	 *
	 * @param array $drag
	 * @return array
	 */
	public function getCaratsRanges($drag)
	{
		$caratsRanges = [];
		foreach ($drag as $item) {
			foreach ($item['data'] as $stoneProperty) {

				if ($stoneProperty['name'] == 'Вес, Ct.') {
					$caratsRange = $this->mdl_product->getCaratsRange($stoneProperty['value']);
					if ($caratsRange && !in_array($caratsRange, $caratsRanges)) {
						$caratsRanges[] = $caratsRange;
					}
				}
			}
		}
		return $caratsRanges;
	}

}

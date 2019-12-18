<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Negarnitury extends CI_Controller
{

	protected $user_info = array();
	protected $store_info = array();

	protected $post = array();
	protected $get = array();

	private $prod_table = "products";
	private $ph_table = "products_photos";

	private $upload_path = "./uploads/products/negarnitury/";
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

		$title = 'Выгрузка Ювелирные Традиции';
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

			'content' => $this->mdl_tpl->view('pages/admin/parser/negarnitury/negarnitury.html', array(), true),

			'load' => $this->mdl_tpl->view('snipets/load.html', array(
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			), true),

			'resorses' => $this->mdl_tpl->view('resorses/admin/parser/negarnitury.html', array(
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

		$file = file($this->upload_path . "/negarnitury.csv");
		$data = array();

		$file[$current_str] = mb_convert_encoding($file[$current_str], "utf8", "cp1251");
		$val = explode(";", $file[$current_str]); // Делим данные

		// Фильтр пустых строк
		if (strlen($val[1]) < 2) {
			$err++;
			echo json_encode(["err" => $err, "double" => $double]);
			return;
		}

		$data[0]["article"] = $val[1];
		$data[0]["vstavka"] = $val[2];
		$data[0]["weight"] = $val[3];
		$data[0]["price"] = $val[4];
		//$data[0]["qty"] = $val[6];
		$data[0]["size"] = $val[7];
		$data[0]["type"] = $val[8];
		$data[0]["metal"] = trim($val[9]);

		foreach ($data as $key => $dt) {
			$size = explode(" ", $dt["size"]);
			foreach ($size as $kk => $sz) {
				$title = $this->title($dt);
				$params = $this->params($dt);
				$filter = $this->filter($dt, $params[2]["value"], $sz);
				$drag = $this->drag($dt["vstavka"]);

				if (empty($dt["price"])) {
					$err++;
					echo json_encode(["err" => $err, "double" => $double]);
					return;
				}

				$price_zac = (float)$dt["weight"] * 1900;
				$price_zac += (float)str_replace(" ", "", $dt["price"]);
				$price_zac *= 100;

				$item = [
					'articul' => $dt['article'],
					'cat' => $this->type_cat[$dt['type']],
					'title' => $title,
					'price_zac' => $price_zac,
					'price_roz' => $price_zac * 2.5,
					'current' => 'RUR',
					'salle_procent' => rand(4, 8) * 5,
					'view' => '1',
					'qty' => '1',
					'qty_empty' => '1',
					'prices_empty' => '1',
					'weight' => $dt["weight"],
					'sex' => "woman",
					'postavchik' => 'Yuvelirnye Traditsii',
					'parser' => 'Negarnitury',
					'proba' => "585",
					'params' => json_encode($params),
					'size' => str_replace(",", ".", trim($sz)),
					'filters' => json_encode($filter),
					'moderate' => '2',
					'lastUpdate' => time(),
					'optionLabel' => json_encode([
						'collections' => "Yuvelirnye Traditsii",
						'options' => "-",
						'vstavki' => str_replace(",", " ", $dt['vstavka']),
						'seria' => "",
					]),
					'drag' => json_encode($drag),
				];

				// Получаем итем с одинаковым артикулом и весом для обновления цены
				$prods = $this->mdl_product->queryData([
					'type' => 'ARR2',
					'where' => [
						'method' => 'AND',
						'set' => [[
							'item' => 'parser',
							'value' => 'Negarnitury',
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
								"price_zac" => $item["price_zac"],
								"price_roz" => $item["price_roz"],
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
				$aliase = $this->mdl_product->aliase_translite($title) . '_' . $this->mdl_product->aliase_translite(trim($dt["article"])) . '_' . $id;
				$this->mdl_db->_update_db($this->prod_table, "id", $id, ["aliase" => $aliase]);

				$this->images($dt["article"], ".jpg");
				$ph = [
					"product_id" => $id,
					"photo_name" => $ph_name, // Вместо алиаса по артиклу
					"define" => 1,
				];
				$this->db->insert($this->ph_table, $ph);
			}

			echo json_encode(["err" => $err, "double" => $double]);
			return;
		}
	}

	private function title($dt)
	{
		$title = $dt["type"] . " из ";

		if (trim($dt["metal"]) == "Желтое золото" or trim($dt["metal"]) == "Жёлтое золото") $title .= "желтого ";
		if (trim($dt["metal"]) == "Красное золото") $title .= "красного ";
		if (trim($dt["metal"]) == "Белое золото") $title .= "белого ";
		$title .= "золота";

		if (strlen($dt["vstavka"]) < 3) return $title;

		$len = strlen($title) + 3;
		$title .= " с ";
		$kamen = explode(" ,", $dt["vstavka"]);
		foreach ($kamen as $key => $val) {
			if (preg_match("/Жемчуг/", $val) and !preg_match("/Жемчуг/", $title)) $title .= "Жемчугом, ";
			if (preg_match("/Фиан/", $val) and !preg_match("/Фианит/", $title)) $title .= "Фианитом, ";
			if (preg_match("/Гранат/", $val) and !preg_match("/Гранат/", $title)) $title .= "Гранатом, ";
			if (preg_match("/Корунд/", $val) and !preg_match("/Корунд/", $title)) $title .= "Корундом, ";
			if (preg_match("/Топаз Sky/", $val) and !preg_match("/Sky/", $title)) $title .= "Топазом Sky, ";
			if (preg_match("/топ-London/", $val) and !preg_match("/Топаз/", $title)) $title .= "Топазом London, ";
			if (preg_match("/Топ-swiss/", $val) and !preg_match("/Топаз/", $title)) $title .= "Топазом Swiss, ";
			if (preg_match("/Амет/", $val) and !preg_match("/Аметист/", $title)) $title .= "Аметистом, ";
			if (preg_match("/Изумруд/", $val) and !preg_match("/Изумруд/", $title)) $title .= "Изумрудом, ";
			if (preg_match("/Бриллиант/", $val) and !preg_match("/Бриллиант/", $title)) $title .= "Бриллиантом, ";
			if (preg_match("/Родолит/", $val) and !preg_match("/Родолит/", $title)) $title .= "Родолитом, ";
			if (preg_match("/Шпинель/", $val) and !preg_match("/Шпинель/", $title)) $title .= "Шпинелью, ";
			if (preg_match("/Р-топ/", $val) and !preg_match("/Раух-топаз/", $title)) $title .= "Раух-топазом, ";
			if (preg_match("/Гр/", $val) and !preg_match("/Гранат/", $title)) $title .= "Гранатом, ";
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
			if (preg_match("/Цит/", $val) and !preg_match("/Цитрин/", $title)) $title .= "Цитрином, ";
			if (preg_match("/Хр/", $val) and !preg_match("/Хризолит/", $title)) $title .= "Хризолитом, ";
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

		$vstavka = explode(" ,", $dt["vstavka"]);

		$vstavka_param = "";
		$vstavka_form = "";
		foreach ($vstavka as $key => $value) {
			$v = explode(" ", $value);

			$vstavka_param .= $v[1] . ", ";
			if (preg_match("/Sky/", $v[2])) $vstavka_form .= $v[3] . ", ";
			else $vstavka_form .= $v[2] . ", ";
		}
		$vstavka_param = substr($vstavka_param, 0, -2);
		$vstavka_form = substr($vstavka_form, 0, -2);

		$params[0]["value"] = $dt["metal"];
		$params[1]["value"] = "Золото";
		$params[2]["value"] = $vstavka_param;
		$params[3]["value"] = $vstavka_form;
		$params[4]["value"] = $dt["weight"];
		$params[5]["value"] = "Женщинам";

		return $params;
	}

	private function filter($dt, $vstavka, $size)
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

		if (trim($dt["metal"]) == "Белое золото") $filter[0]["values"][] = "belZoloto";
		if (trim($dt["metal"]) == "Красное золото") $filter[0]["values"][] = "krasnZoloto";
		if (trim($dt["metal"]) == "Желтое золото" or trim($dt["metal"]) == "Жёлтое золото") $filter[0]["values"][] = "JoltZoloto";

		$kamen = explode(", ", $vstavka);
		foreach ($kamen as $k => $v) {
			$v = str_replace("Гр", "Гранат", $v);
			$v = str_replace("Цит", "Цитрин", $v);
			$v = str_replace("Амет", "Аметист", $v);
			$v = str_replace("Фиан", "Фианит", $v);
			$v = str_replace("Хр", "Хризолит", $v);
			$v = str_replace("Топ-swiss", "Топаз Swiss", $v);
			$v = str_replace("топ-London", "Топаз London", $v);
			$v = str_replace("Р-топ", "Раух-Топаз", $v);
			$filter[1]["values"][] = $this->mdl_product->aliase_translite($v);
		}

		$filter[3]["values"][] = "woman";

		$size = str_replace(",", "_", $size);
		$size = str_replace(".", "_", $size);
		$filter[4]["values"][] = $size;

		return $filter;
	}

	private function drag($descr)
	{
		$drag = array();

		$kamen = explode(" ,", $descr);
		foreach ($kamen as $key => $value) {
			$val = explode(" ", $value);

			if (preg_match("/Sky/", $val[2])) {
				$form = $val[3];
				$name = $val[1] . " " . $val[2];
			} else {
				$form = $val[2];
				$name = $val[1];
			}

			$drag[$key]["kamen"] = $val[1];
			$drag[$key]["data"] = [
				["name" => "Кол-во камней", "value" => $val[0]],
				["name" => "Камень", "value" => $name],
				["name" => "Форма огранки", "value" => $form],
				["name" => "Вес, Ct.", "value" => "-"],
			];
		}

		return $drag;
	}

	private function images($article, $ext)
	{
		if (!file_exists($this->upload_path . $article . $ext)) return;

		$this->load->library('images');

		$to_100 = "./uploads/products/100/";
		$this->images->imageresize($to_100 . $article . $ext, $this->upload_path . $article . $ext, 100, 100, 100);

		$to_250 = "./uploads/products/250/";
		$this->images->imageresize($to_250 . $article . $ext, $this->upload_path . $article . $ext, 250, 250, 100);

		$to_500 = "./uploads/products/500/";
		$this->images->imageresize($to_500 . $article . $ext, $this->upload_path . $article . $ext, 500, 500, 100);
	}

	public function count()
	{
		$file = file($this->upload_path . "/negarnitury.csv");
		$count = count($file);
		if ($count == 1 and strlen($file[0]) == 0) $count = 0;
		echo json_encode(['count' => $count]);
		die;
	}

}

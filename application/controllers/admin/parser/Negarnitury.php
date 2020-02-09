<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Negarnitury extends CI_Controller
{

	protected $user_info = [];
	protected $store_info = [];

	protected $post = [];
	protected $get = [];

	private $prod_table = "products";
	private $ph_table = "products_photos";

	private $upload_path = "./uploads/products/negarnitury/";
	private $type_cat = [
		"Кольцо" => 1,
		"Кольцо обручальное" => 1,
		"Серьги" => 10,
		"Серьги детские" => 10,
		"Серьги пуссеты" => 10,
		"Серьги продевки" => 10,
		"Серьги трансформеры" => 10,
		"Серьги каффы" => 10,
		"Подвеска" => 19,
		"Браслет" => 28,
		"Брошь" => 35,
		"Колье" => 36,
		"Крест" => 37,
		"Крест-трансформер" => 37,
		"Подвеска-крест" => 37,
	];

//	т.к. импорт только золотых изделий, камни только те, что встречавюся в золотых изделиях
//	в последствии возможно придетсядобавлять новые
//
	protected $stones = [
		[
			'title' => 'Агат',
			'in_case' => 'Агатом',
			'translit' => 'agat',
		],
		[
			'title' => 'Алмаз',
			'in_case' => 'Алмазом',
			'translit' => 'almaz',
		],
		[
			'title' => 'Аметист',
			'in_case' => 'Аметистом',
			'translit' => 'ametist',
		],
		[
			'title' => 'Бриллиант',
			'in_case' => 'Бриллиантом',
			'translit' => 'brilliant',
		],
		[
			'title' => 'Гранат',
			'in_case' => 'Гранатом',
			'translit' => 'granat',
		],
		[
			'title' => 'Жемчуг',
			'in_case' => 'Жемчугом',
			'translit' => 'jemchug',
		],
		[
			'title' => 'Изумруд',
			'in_case' => 'Изумрудом',
			'translit' => 'izumrud',
		],
		[
			'title' => 'Кварц',
			'in_case' => 'Кварцем',
			'translit' => 'kvarc',
		],
		[
			'title' => 'Раух-топаз',
			'in_case' => 'Раух-топазом',
			'translit' => 'rauh-topaz',
		],
		[
			'title' => 'Родонит',
			'in_case' => 'Родонитом',
			'translit' => 'rodolit',
		],
		[
			'title' => 'Рубин',
			'in_case' => 'Рубином',
			'translit' => 'rubin',
		],
		[
			'title' => 'Сапфир',
			'in_case' => 'Сапфиром',
			'translit' => 'sapfir',
		],
		[
			'title' => 'Серебро',
			'in_case' => 'Серебром',
			'translit' => 'serebro',
		],
		[
			'title' => 'Ситалл',
			'in_case' => 'Ситаллом',
			'translit' => 'sitall',
		],
		[
			'title' => 'Топаз',
			'in_case' => 'Топазом',
			'translit' => 'topaz',
		],
		[
			'title' => 'Тсаворит',
			'in_case' => 'Тсаворитом',
			'translit' => 'tsavorit',
		],
		[
			'title' => 'Фианит',
			'in_case' => 'Фианитом',
			'translit' => 'fianit',
		],
		['title' => 'Хризолит',
			'in_case' => 'Хризолитом',
			'translit' => 'hrizolit',
		],
		['title' => 'Цитрин',
			'in_case' => 'Цитрином',
			'translit' => 'citrin',
		],
		['title' => 'Шпинель',
			'in_case' => 'Шпинелью',
			'translit' => 'shpinel',
		],
		['title' => 'Ювелирный кристалл',
			'in_case' => 'Ювелирный кристаллом',
			'translit' => 'juvelirnyj-kristall',
		],
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

		$this->mdl_tpl->view('templates/doctype_admin.html', [

			'title' => $title,
			'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'),

			'seo' => $this->mdl_tpl->view('snipets/seo_tools.html', [
				'mk' => (!empty($this->store_info['seo_keys'])) ? $this->store_info['seo_keys'] : '',
				'md' => (!empty($this->store_info['seo_desc'])) ? $this->store_info['seo_desc'] : '',
			], true),

			'nav' => $this->mdl_tpl->view('snipets/admin_nav.html', [
				'active' => $page_var,
			], true),

			'breadcrumb' => $this->mdl_tpl->view('snipets/breadcrumb.html', [
				'title' => $title,
				'array' => [[
					'name' => 'Панель управления',
					'link' => '/admin',
				]],
			], true),

			'content' => $this->mdl_tpl->view('pages/admin/parser/negarnitury/negarnitury.html', [], true),

			'load' => $this->mdl_tpl->view('snipets/load.html', [
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			], true),

			'resorses' => $this->mdl_tpl->view('resorses/admin/parser/negarnitury.html', [
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path'),
			], true),

		], false);

	}

	public function parse()
	{
		// Принимаем номер строки файла для обработки, считаем дубли и ошибки
		$current_str = $_POST["str"];

		// Первоначальная прочистка остатков
		$clear = $current_str == 1;
		if ($clear) {
			$this->mdl_db->_update_db($this->prod_table, 'postavchik', 'Yuvelirnye Traditsii', [
				'qty' => 0,
			]);
		}

		$err = 0;
		$double = 0;

		$file = file("{$this->upload_path}JTOst.csv");
		$data = [];

		$file[$current_str] = mb_convert_encoding($file[$current_str], "utf8", "cp1251");
		$val = explode(";", $file[$current_str]); // Делим данные

		// Фильтр пустых строк
		if (strlen($val[1]) < 2) {
			$err++;
			echo json_encode(["err" => $err, "double" => $double]);
			return;
		}

		$data["article"] = trim($val[0]);
		$data["proba"] = preg_replace('{[^\d]+}', '', trim($val[1]));
		$data["metal"] = trim($val[2]);
		$data["vstavka"] = trim($val[4]);
		$data["type"] = trim($val[5]);
		$data['collection'] = trim($val[6]);
		$data["size"] = str_replace('.0', '', str_replace(',', '.', trim($val[7])));
		$data["weight"] = str_replace(',', '.', trim($val[8]));
		$data["qty"] = trim($val[9]);
		$data["price"] = trim($val[10]);

		// Пока импортируем только золото
		if ($data["metal"] == 'Серебро' || $data["proba"] == '925') {
			$err++;
			echo json_encode(["err" => $err, "double" => $double]);
			return;
		}

		$categoryId = $this->type_cat[$data["type"]];
		if (!$categoryId) {
			$err++;
			echo json_encode(["err" => $err, "double" => $double]);
			return;
		}

		$drag = $this->drag($data["vstavka"]);
		$title = $this->title($data, $drag);
		$params = $this->params($data, $drag);
		$filter = $this->filter($data, $drag);
		$caratsRanges = $this->getCaratsRanges($drag);

		if (empty($data["price"])) {
			$err++;
			echo json_encode(["err" => $err, "double" => $double]);
			return;
		}

//		Это при "давальческой" цене, когда цена только за работу
//		$goldPrice = (float)$this->mdl_stores->getConfig('gold_price');
//		$price_zac = (float)$data['weight'] * $goldPrice;
//		$price_zac += (float)str_replace(" ", "", $data['price']);
//		$price_zac = (int)($price_zac * 100);

//		Сейчас цена полная
		$price_zac = (float)str_replace(" ", "", $data['price']);
		$price_zac = (int)($price_zac * 100);
		$price_roz = (int)($price_zac * 2.5);
		$salle_procent = rand(4, 8) * 5;
		$price_real = (int)($price_roz * (100 - $salle_procent) / 100);

		$item = [
			'articul' => $data['article'],
			'cat' => $categoryId,
			'title' => $title,
			'price_zac' => $price_zac,
			'price_roz' => $price_roz,
			'price_real' => $price_real,
			'current' => 'RUR',
			'salle_procent' => $salle_procent,
			'view' => '1',
			'qty' => '1',
			'qty_empty' => '1',
			'prices_empty' => '1',
			'weight' => $data["weight"],
			'sex' => "woman",
			'postavchik' => 'Yuvelirnye Traditsii',
			'parser' => 'Negarnitury',
			'proba' => "585",
			'params' => json_encode($params, JSON_UNESCAPED_UNICODE),
			'size' => $data["size"],
//			'filters' => json_encode($filter, JSON_UNESCAPED_UNICODE),
			'filter_metall' => json_encode($filter['metall'], JSON_UNESCAPED_UNICODE),
			'filter_kamen' => json_encode($filter['kamen'], JSON_UNESCAPED_UNICODE),
			'filter_sex' => json_encode($filter['sex'], JSON_UNESCAPED_UNICODE),
			'moderate' => '2',
			'lastUpdate' => time(),
			'optionLabel' => json_encode([
				'collections' => $data['collection'],
				'options' => "-",
				'vstavki' => str_replace(",", " ", $data['vstavka']),
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
					'value' => 'Negarnitury',
				], [
					'item' => 'articul',
					'value' => $data["article"],
				]],
			],
			'labels' => ['id', 'weight', 'size'],
			'table_name' => $this->prod_table,
		]);

		// Если есть такой же товар по артиклу и весу, то обновляем его цену, иначе вносим в базу новый
		if (count($prods) > 0) {
			foreach ($prods as $k => $v) {
				if ($v["weight"] == $data["weight"] and $v["size"] == $data["size"]) {
//					$upd = [ // Сюда можно установить любые значения для обновления
//						"title" => $item["title"],
//						"price_zac" => $item["price_zac"],
//						"price_roz" => $item["price_roz"],
//						"lastUpdate" => $item["lastUpdate"],
//						"params" => $item["params"],
//						"filters" => $item["filters"],
//					];
					$upd = $item;
					$this->mdl_db->_update_db($this->prod_table, "id", $v["id"], $upd);

					$double++; // Считаем дубли с обновлениями

					echo json_encode(["err" => $err, "double" => $double]);
					return;
				}
			}
		}

		// Проверка фото
		$article = str_replace('/', '_', $data["article"]);
		if (!file_exists("{$this->upload_path}{$article}.jpg")) {
			$err++;
			echo json_encode(["err" => $err, "double" => $double]);
			return;
		}

		$this->db->insert($this->prod_table, $item);
		$id = $this->db->insert_id();
		if (!$id) $err++;
		$aliase = "{$this->mdl_product->aliase_translite($title)}_{$this->mdl_product->aliase_translite(trim($data["article"]))}_{$id}";
		$this->mdl_db->_update_db($this->prod_table, "id", $id, ["aliase" => $aliase]);

		$this->images($article, ".jpg");
		$ph = [
			"product_id" => $id,
			"photo_name" => "{$article}.jpg", // Вместо алиаса по артиклу
			"define" => 1,
		];
		$this->db->insert($this->ph_table, $ph);

		echo json_encode(["err" => $err, "double" => $double]);
		return;
	}

	private function title($data, $drag = [])
	{
		$title = "{$data["type"]} из золота";

		if (empty($drag)) {
			return $title;
		}

		$len = strlen($title) + 3;
		$title .= " с ";
		foreach ($drag as $dragItem) {
			foreach ($this->stones as $stone) {
				$regexp = "/{$stone['title']}/i";
				if (preg_match($regexp, $dragItem['kamen'])) {
					if (!preg_match($regexp, $title)) {
						$title .= "{$stone['in_case']}, ";
					}
					break;
				}
			}
		}

		if (strlen($title) == $len) $title = substr($title, 0, -3); // Если вдруг не оказалось совпадения по камням
		else $title = substr($title, 0, -2); // Удаление ","

		return $title;
	}

	private function params($data = [], $drag = [])
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

		$vstavka = explode(" ,", $data["vstavka"]);

		$vstavkaStones = "";
		$vstavkaForms = "";
		foreach ($drag as $dragItem) {
			$vstavkaStones .= "{$dragItem['kamen']}, ";
			$vstavkaForms .= "{$dragItem['data'][2]['value']}, ";
		}
		$vstavkaStones = substr($vstavkaStones, 0, -2);
		$vstavkaForms = substr($vstavkaForms, 0, -2);

		$params[0]["value"] = $data["metal"];
		$params[1]["value"] = "Золото";
		$params[2]["value"] = $vstavkaStones;
		$params[3]["value"] = $vstavkaForms;
		$params[4]["value"] = $data["weight"];
		if ($data["type"] == "Серьги детские") {
			$params[5]["value"] = "Детям";
		} else {
			$params[5]["value"] = "Женщинам";
		}

		return $params;
	}

	private function filter($data, $drag = [])
	{
		$filter = [
			'metall' => [],
			'kamen' => [],
//			'forma_vstavki' => [],
			'sex' => [],
//			'size' => [],
		];

		$filter['metall'][] = "krasnZoloto";

		foreach ($drag as $dragItem) {
			foreach ($this->stones as $stone) {
				$regexp = "/{$stone['title']}/i";
				if (preg_match($regexp, $dragItem['kamen'])) {
					$filter['kamen'][] = $stone['translit'];
					break;
				}
			}
		}
		// 'empty','no_empty'
		if (count($filter['kamen']) > 0) {
			$filter['kamen'][] = 'no_empty';
		} else {
			$filter['kamen'][] = 'empty';
		}

		if ($data["type"] == "Серьги детские") {
			$filter['sex'][] = "kids";
		} else {
			$filter['sex'][] = "woman";
		}

//		$size = str_replace(",", "_", $data["size"]);
//		$size = str_replace(".", "_", $size);
//
//		$filter['size'][] = $size;

		return $filter;
	}

	private function drag($vstavka)
	{
		$vstavka = trim($vstavka);
		$drag = [];

		if (!$vstavka || $vstavka == '<без вставок>') {
			return $drag;
		}

		$stone = explode(', ', str_replace(' ,', ', ', $vstavka));
		foreach ($stone as $key => $value) {
			$stoneParms = explode(" ", $value);
			$quantity = array_shift($stoneParms);
			$name = array_shift($stoneParms);

			if (
				$name == 'Топаз' && $stoneParms[0] == 'Sky' ||
				$name == 'Ювелирный' && $stoneParms[0] == 'кристалл' ||
				$name == 'Шпинель' ||
				$stoneParms[0] == 'синт.' ||
				$stoneParms[0] == 'культ' ||
				$stoneParms[0] == 'г/т'
			) {
				$name .= ' ' . array_shift($stoneParms);
			}

			$name = $this->getFullStoneName($name);

			if ($name == 'Лента' || $name == 'Леска') {
				continue;
			}

			$form = array_shift($stoneParms);
			if ($form == 'Кр') {
				$form = 'круг';
			}
			if ($form == 'нити') { // нити 30мм круг
				$name .= ' ' . array_shift($stoneParms) . ' ' . array_shift($stoneParms);

			}

			if ($name == 'Металл') {
				$name = 'Серебро';
				$data = [
					["name" => "Кол-во вставок", "value" => $quantity],
					["name" => "Вставка", "value" => $name],
					["name" => "Проба", "value" => '925'],
				];
			} else {
				$data = [
					["name" => "Кол-во камней", "value" => $quantity],
					["name" => "Камень", "value" => $name],
					["name" => "Форма огранки", "value" => $form],
				];

				if ($name == 'Бриллиант') {
					list($facets, $size, $carats, $sifting, $colorAndPurity) = $stoneParms;
					$data = array_merge($data, [
						["name" => "Кол-во граней", "value" => $facets],
						["name" => "Размер камня, мм", "value" => str_replace('d=', '', $size)],
						["name" => "Вес, Ct.", "value" => str_replace('Ct', '', $carats)],
						["name" => "Рассев", "value" => $sifting],
						["name" => "Цвет/Чистота", "value" => $colorAndPurity],
					]);
				} else {
					list($size, $carats, $color, $color2, $color3) = $stoneParms;
					$data = array_merge($data, [
						["name" => "Размер камня, мм", "value" => $size],
						["name" => "Вес, Ct.", "value" => $carats],
						["name" => "Цвет", "value" => $color . ($color2 ? ' ' . $color2 : '') . ($color3 ? ' ' . $color3 : '')],
					]);
				}
			}
			$drag[$key] = [
				'kamen' => $name,
				'data' => $data,
			];
		}

		return $drag;
	}

	protected function getFullStoneName($name)
	{
		$name = str_replace("Ал", "Алмаз", $name);
		$name = str_replace("Амет", "Аметист", $name);
		$name = str_replace("Бр", "Бриллиант", $name);
		$name = str_replace("Гр", "Гранат", $name);
		$name = str_replace("г/т", "гидротермальный", $name);
		$name = str_replace("Жем", "Жемчуг", $name);
		$name = str_replace("Из", "Изумруд", $name);
		$name = str_replace("культ", "", $name);
		$name = str_replace("Р-топ", "Раух-Топаз", $name);
		$name = str_replace("Род", "Родонит", $name);
		$name = str_replace("Руб", "Рубин", $name);
		$name = str_replace("Сапф", "Сапфир", $name);
		$name = str_replace("синт.", "синтетический", $name);
		$name = str_replace("топ-London", "Топаз London", $name);
		$name = str_replace("Топ-swiss", "Топаз Swiss", $name);
		$name = str_replace("Тсав", "Тсаворит", $name);
		$name = str_replace("Фиан", "Фианит", $name);
		$name = str_replace("Хр", "Хризолит", $name);
		$name = str_replace("Цит", "Цитрин", $name);

		return $name;
	}

	private function images($article, $ext)
	{
		if (!file_exists("{$this->upload_path}{$article}{$ext}")) return;

		$this->load->library('images');

		$to_100 = "./uploads/products/100/";
		$this->images->imageresize("{$to_100}{$article}{$ext}", "{$this->upload_path}{$article}{$ext}", 100, 100, 100);

		$to_250 = "./uploads/products/250/";
		$this->images->imageresize("{$to_250}{$article}{$ext}", "{$this->upload_path}{$article}{$ext}", 250, 250, 100);

		$to_500 = "./uploads/products/500/";
		$this->images->imageresize("{$to_500}{$article}{$ext}", "{$this->upload_path}{$article}{$ext}", 500, 500, 100);
	}

	public function count()
	{
		$file = file("{$this->upload_path}JTOst.csv");
		$count = count($file);
		if ($count == 1 and strlen($file[0]) == 0) {
			$count = 0;
		}
		echo json_encode(['count' => $count]);
		die;
	}

	public function getCaratsRangesAll($productIds = [])
	{
//		$prodictIds = [62284];
		$products = [];
		if (count($productIds) > 0) {
			$this->db->where_in('id', $productIds);
//		$this->db->where('postavchik', 'Yuvelirnye Traditsii');
//		$this->db->limit(10000, 30000);
//		$products = $this->db->get('products')->result_array();
		}

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

<?php

class BaseParser extends CI_Controller
{

	protected $parserCode = '';
	protected $parserTitle = '';

	protected $uploadPath = '';
	protected $fileName = '';

	protected $postavchik = '';

	protected $user_info = [];
	protected $store_info = [];

	protected $post = [];
	protected $get = [];

	protected $prod_table = 'products';
	protected $ph_table = 'products_photos';

	protected $priceType = 'common'; // 'alternative'

	protected $downloadImages = false;
	protected $multipleImages = false;
//	protected $imageFileNameTemplate = '{article}-{index}.jpg';
	protected $imageFileNameTemplate = '{article}.jpg';

	protected $type_cat = [
		'Кольцо' => 1,
		'Кольцо обручальное' => 1,
		'Обручальные кольца' => 1,
		'Печатка' => 1,
		'Серьги' => 10,
		'Серьги детские' => 10,
		'Серьги пуссеты' => 10,
		'Серьги продевки' => 10,
		'Серьги трансформеры' => 10,
		'Серьги каффы' => 10,
		'Серьги конго' => 10,
		'Серьги с цепями' => 10,
		'Серьги_Пусеты' => 10,
		'Серьги_Пусеты с цепями' => 10,
		'Серьга' => 10,
		'Пусеты' => 10,
		'Подвеска' => 19,
		'Подвеска парная' => 19,
		'Подвеска с цепями' => 19,
		'Браслет' => 28,
		'Браслет с цепями' => 28,
		'Браслет трансформер' => 28,
		'Брошь' => 35,
		'Брошь с цепями' => 35,
		'Брошь-Подвеска' => 35,
		'Булавка' => 35,
		'Звезда' => 35,
		'Значок' => 35,
		'Колье' => 36,
		'Колье на леске' => 36,
		'Колье с цепями' => 36,
		'Крест' => 37,
		'Крест-трансформер' => 37,
		'Подвеска-крест' => 37,
		'Пирсинг' => 38,
		'Пирсинг с цепями' => 38,
		'Цепь' => 40,
		'Запонки' => 41,
		'Зажим для галстука' => 42,
		'Зажим д/галстука' => 42,
//		'Сувенир' => ?, // Есть у Дельты
//		'Шнурок' => ?, // Есть у Дельты, может к цепям? Но там только застежка из золота, остальное простой шнурок...
	];

//	т.к. импорт только золотых изделий, камни только те, что встречавюся в золотых изделиях
//	в последствии возможно придется добавлять новые
//

//master-brilliant

	protected $stones = [
		[
			// ситалл первый, т.к. ситалл бывает под любой камень, например "Ситалл рубин",
			// и так в итоге камень определится как "Ситалл", а не "рубин"
			'title' => 'Ситалл',
			'in_case' => 'Ситаллом',
			'translit' => 'sitall',
		],
		[
			'title' => 'Авантюрин',
			'in_case' => 'Авантюрином',
			'translit' => 'avantyurin',
		],
		[
			'title' => 'Агат',
			'in_case' => 'Агатом',
			'translit' => 'agat',
		],
		[
			'title' => 'Аквамарин',
			'in_case' => 'Аквамарином',
			'translit' => 'akvamarin',
		],
		[
			'title' => 'Александрит',
			'in_case' => 'Александритом',
			'translit' => 'aleksandrit',
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
			'title' => 'Аметрин',
			'in_case' => 'Аметрином',
			'translit' => 'ametrin',
		],
		[
			'title' => 'Апатит',
			'in_case' => 'Апатитом',
			'translit' => 'apatit',
		],
		[
			'title' => 'Бирюза',
			'in_case' => 'Бирюзой',
			'translit' => 'biryuza',
		],
		[
			'title' => 'Бриллиант',
			'in_case' => 'Бриллиантом',
			'translit' => 'brilliant',
		],
		[
			'title' => 'Горный хрусталь',
			'in_case' => 'Горным хрусталём',
			'translit' => 'gornyj-hrustal',
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
			'title' => 'Иолит',
			'in_case' => 'Иолитом',
			'translit' => 'iolit',
		],
		[
			'title' => 'Кварц',
			'in_case' => 'Кварцем',
			'translit' => 'kvarc',
		],
		[
			'title' => 'Керамика',
			'in_case' => 'Керамикой',
			'translit' => 'keramika',
		],
		[
			'title' => 'Кианит',
			'in_case' => 'Кианитом',
			'translit' => 'kianit',
		],
		[
			'title' => 'Коралл',
			'in_case' => 'Кораллом',
			'translit' => 'korall',
		],
		[
			'title' => 'Корунд',
			'in_case' => 'Корундом',
			'translit' => 'korund',
		],
		[
			'title' => 'Лунный камень',
			'in_case' => 'Лунным камнем',
			'translit' => 'lunnyj-kamen',
		],
		[
			'title' => 'Морганит',
			'in_case' => 'Морганитом',
			'translit' => 'morganit',
		],
		[
			'title' => 'Муранское Стекло',
			'in_case' => 'Муранским Стеклоом',
			'translit' => 'muranskoe-steklo',
		],
		[
			'title' => 'Нанокерамика',
			'in_case' => 'Нанокерамикой',
			'translit' => 'nanokeramika',
		],
		[
			'title' => 'Наношпинель',
			'in_case' => 'Наношпинелью',
			'translit' => 'nanoshpinel',
		],
		[
			'title' => 'Оникс',
			'in_case' => 'Ониксом',
			'translit' => 'oniks',
		],
		[
			'title' => 'Празиолит',
			'in_case' => 'Празиолитом',
			'translit' => 'praziolit',
		],
		[
			'title' => 'Раух-топаз',
			'in_case' => 'Раух-топазом',
			'translit' => 'rauh-topaz',
		],
		[
			'title' => 'Раухтопаз',
			'in_case' => 'Раух-топазом',
			'translit' => 'rauh-topaz',
		],
		[
			'title' => 'Родолит',
			'in_case' => 'Родолитом',
			'translit' => 'rodolit',
		],
		[
			'title' => 'Родонит',
			'in_case' => 'Родонитом',
			'translit' => 'rodonit',
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
			'title' => 'Сердолик',
			'in_case' => 'Сердоликом',
			'translit' => 'serdolik',
		],
		[
			'title' => 'Спессартин',
			'in_case' => 'Спессартином',
			'translit' => 'spessartin',
		],
		[
			'title' => 'Султанит',
			'in_case' => 'Султанитом',
			'translit' => 'sultanit',
		],
		[
			'title' => 'Серебро',
			'in_case' => 'Серебром',
			'translit' => 'serebro',
		],
		[
			'title' => 'Стекло минеральное',
			'in_case' => 'Минеральным стеклом',
			'translit' => 'steklo-mineralnoe',
		],
		[
			'title' => 'Стекло сапфировое',
			'in_case' => 'Cапфировым стеклом',
			'translit' => 'steklo-sapfirovoe',
		],
		[
			'title' => 'Танзанит',
			'in_case' => 'Танзанитом',
			'translit' => 'tanzanit',
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
			'title' => 'Турмалин',
			'in_case' => 'Турмалином',
			'translit' => 'turmalin',
		],
		[
			'title' => 'Фианит',
			'in_case' => 'Фианитом',
			'translit' => 'fianit',
		],
		['title' => 'Халцедон',
			'in_case' => 'Халцедоном',
			'translit' => 'halcedon',
		],
		['title' => 'Хризолит',
			'in_case' => 'Хризолитом',
			'translit' => 'hrizolit',
		],
		['title' => 'Хризопраз',
			'in_case' => 'Хризопразом',
			'translit' => 'hrizopraz',
		],
		['title' => 'Хромдиопсид',
			'in_case' => 'Хромдиопсидом',
			'translit' => 'hromdiopsid',
		],
		['title' => 'Циркон',
			'in_case' => 'Цирконом',
			'translit' => 'cirkon',
		],
		['title' => 'Цитрин',
			'in_case' => 'Цитрином',
			'translit' => 'citrin',
		],
		['title' => 'Шпинель',
			'in_case' => 'Шпинелью',
			'translit' => 'shpinel',
		],
		['title' => 'Эмаль',
			'in_case' => 'Эмалью',
			'translit' => 'ehmal',
		],
		['title' => 'Яшма',
			'in_case' => 'Яшмой',
			'translit' => 'yashma',
		],
		['title' => 'Ювелирный кристалл',
			'in_case' => 'Ювелирный кристаллом',
			'translit' => 'juvelirnyj-kristall',
		],
		['title' => 'Янтарь',
			'in_case' => 'Янтарем',
			'translit' => 'yantar',
		],
		[
			'title' => 'Nano crystal',
			'in_case' => 'Нанокристаллом',
			'translit' => 'nano-crystal',
		],
		[
			'title' => 'Swarovski Zirconia',
			'in_case' => 'Swarovski Zirconia',
			'translit' => 'swarovski-zirconia',
		],
	];

	// верхние грацицы диапазонов каратности
	protected $caratsRanges = [
		'1' => 0.25,
		'2' => 0.5,
		'3' => 1,
		'4' => 2,
		'5' => 3,
		'6' => 4,
		'7' => 5,
		'8' => false,
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
				exit(json_encode(['err' => 1, 'mess' => 'Нет доступа']));
			}
		}
	}

	// Показать страницу по умолчанию
	public function index()
	{

		$this->access_static();

		$page_var = 'parser';

		$this->mdl_tpl->view('templates/doctype_admin.html', [

			'title' => $this->parserTitle,
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
				'title' => $this->parserTitle,
				'array' => [[
					'name' => 'Панель управления',
					'link' => '/admin',
				]],
			], true),

			'content' => $this->mdl_tpl->view("pages/admin/parser/base.html", [
				'parser' => $this->parserCode,
			], true),

			'load' => $this->mdl_tpl->view('snipets/load.html', [
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			], true),

			'resorses' => $this->mdl_tpl->view("resorses/admin/parser/base.html", [
				'parser' => $this->parserCode,
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path'),
			], true),

		], false);

	}

	public function parse()
	{
		error_reporting(0);
		ini_set('display_errors', 0);

		// Принимаем номер строки файла для обработки, считаем дубли и ошибки
		$current_str = $_POST['str'];

		// Первоначальная прочистка остатков
		$clear = $current_str == 1;
		if ($clear) {
			$this->mdl_db->_update_db($this->prod_table, 'postavchik', $this->postavchik, [
				'qty' => 0,
			]);
		}

		$err = 0;
		$double = 0;

		$data = $this->getDataFromFile($current_str);

		// Пока импортируем только золото
		if ($data['metal'] != 'Золото' || $data['proba'] == '925') {
			$err++;
			echo json_encode(['err' => $err, 'double' => $double, 'error' => 'wrong metal']);
			die;
		}

		$dataForInsert = $this->getDataForInsert($data);

		if ($dataForInsert === false) {

			$err++;
			echo json_encode(['err' => $err, 'double' => $double, 'error' => 'wrong data']);
			die;
		}

		// Получаем итем с одинаковым артикулом и весом для обновления цены
		$product = $this->geProductForUpdate($dataForInsert);

		// Если есть такой же товар по артиклу и весу, то обновляем его цену, иначе вносим в базу новый
		if ($product) {
			$dataForUpdate = $this->getDataForUpdate($dataForInsert);
			$this->mdl_db->_update_db($this->prod_table, 'id', $product['id'], $dataForUpdate);

			$double++; // Считаем дубли с обновлениями

			echo json_encode(['err' => $err, 'double' => $double, 'id' => $product['id']]);
			die;
		}

		// Проверка наличия фото

		$article = str_replace('/', '_', $data['article']);

		if (!$this->checkPhoto($article, $data)) {
			$err++;
			echo json_encode(['err' => $err, 'double' => $double, 'error' => 'no image']);
			die;
		}

		$this->db->insert($this->prod_table, $dataForInsert);
		$productId = $this->db->insert_id();

		if (!$productId) {
			$err++;
		}

		$aliase = "{$this->getTranslit($dataForInsert['title'])}_{$this->getTranslit(trim($article))}_{$productId}";
		$this->mdl_db->_update_db($this->prod_table, 'id', $productId, ['aliase' => $aliase]);

		// Сохранение фото
		$this->savePhotos($article, $productId);

		echo json_encode(['err' => $err, 'double' => $double, 'id' => $productId]);
		die;
	}

	/**
	 * Получение данных из CSV-файла
	 *
	 * @param int $currentLine Номер строки в файле
	 * @return array
	 */
	protected function getDataFromFile($currentLine)
	{
		return [];
	}

	/**
	 * Получение данных для вставки в базу
	 *
	 * @param array $data данные из CSV-файла
	 * @return array|bool
	 */
	protected function getDataForInsert($data)
	{
		$categoryId = $this->type_cat[$data['type']];
		if (!$categoryId) {
			return false;
		}

		$drag = $this->drag($data['vstavki']);
		$title = $this->title($data, $drag);
		$params = $this->params($data, $drag);
		$filter = $this->filter($data, $drag);
		$caratsRanges = $this->getCaratsRanges($drag);
		$getCaratsRangesByStone = $this->getCaratsRangesByStone($drag);

		if (empty($data['price'])) {
			return false;
		}

		$price_zac = $this->getPriceZac($data['price'], $data['weight']);
		$price_roz = $this->getPriceRoz($price_zac, $data);
		$salle_procent = $this->getSalePercent();

		$price_real = (int)($price_roz * (100 - $salle_procent) / 100);
		// округляем до 10
		$price_real = (int)(round($price_real / 1000) * 1000);

		$sizes = $this->getSizesRange($data['size']);

		$dataForInsert = [
			'articul' => $data['article'],
			'cat' => $categoryId,
			'title' => $title,
			'price_zac' => $price_zac,
			'price_roz' => $price_roz,
			'price_real' => $price_real,
			'current' => 'RUR',
			'salle_procent' => $salle_procent,
			'view' => '1',
			'qty' => $data["qty"],
			'qty_empty' => '1',
			'prices_empty' => '1',
			'weight' => $data['weight'],
			'sex' => 'woman',
			'postavchik' => $this->postavchik,
			'parser' => $this->parserCode,
			'proba' => '585',
			'size' => $data['size'],
			'size_min' => $sizes['min'],
			'size_max' => $sizes['max'],
			'drag' => json_encode($drag, JSON_UNESCAPED_UNICODE),
			'params' => json_encode($params, JSON_UNESCAPED_UNICODE),
//			'filters' => json_encode($filter, JSON_UNESCAPED_UNICODE),
			'filter_zoloto' => json_encode($filter['zoloto'], JSON_UNESCAPED_UNICODE),
			'filter_kamen' => json_encode($filter['kamen'], JSON_UNESCAPED_UNICODE),
			'filter_gender' => json_encode($filter['gender'], JSON_UNESCAPED_UNICODE),
			'filter_carats' => json_encode($caratsRanges, JSON_UNESCAPED_UNICODE),
			'filter_kamen_carats' => json_encode($getCaratsRangesByStone, JSON_UNESCAPED_UNICODE),
			'moderate' => '2',
			'lastUpdate' => time(),
			'optionLabel' => json_encode([
				'collections' => $data['collection'],
				'options' => '-',
				'vstavki' => $data['vstavki'],
				'seria' => '',
			], JSON_UNESCAPED_UNICODE),
		];

		return $dataForInsert;
	}

	/**
	 * Получение данных для обновления в базе
	 *
	 * @param array $dataForInsert данные
	 * @return array|bool
	 */
	protected function getDataForUpdate($dataForInsert)
	{
		// пока обновляем всё, в перспективе - только цены и наличие
		return $dataForInsert;

		return [ // Сюда можно установить любые значения для обновления
			'title' => $dataForInsert['title'],
			'qty' => $dataForInsert['qty'],
			'price_zac' => $dataForInsert['price_zac'],
			'price_roz' => $dataForInsert['price_roz'],
			'price_real' => $dataForInsert['price_real'],
			'lastUpdate' => $dataForInsert['lastUpdate'],
			'params' => $dataForInsert['params'],
//			'filters' => $dataForInsert['filters'],
			'filter_zoloto' => $dataForInsert['filter_zoloto'],
			'filter_kamen' => $dataForInsert['filter_kamen'],
			'filter_gender' => $dataForInsert['filter_gender'],
			'filter_carats' => $dataForInsert['filter_carats'],
			'filter_kamen_carats' => $dataForInsert['filter_kamen_carats'],
		];
	}

	/**
	 * Получение товара для обновления (если такой есть)
	 *
	 * @param array $dataForInsert данные
	 * @return array|bool
	 */
	protected function geProductForUpdate($dataForInsert)
	{
		$product = $this->mdl_product->queryData([
			'return_type' => 'ARR1',
			'where' => [
				'method' => 'AND',
				'set' => [[
					'item' => 'postavchik',
					'value' => $this->postavchik,
				], [
					'item' => 'articul',
					'value' => $dataForInsert['articul'],
				], [
					'item' => 'weight',
					'value' => $dataForInsert['weight'],
				], [
					'item' => 'size',
					'value' => $dataForInsert['size'],
				]],
			],
			'labels' => ['id'/*, 'weight', 'size'*/],
			'table_name' => $this->prod_table,
		]);

		return $product;
	}

	protected function getPriceZac($price, $weight)
	{
		$price = str_replace(',', '.', trim($price));
		$price = (float)preg_replace('{[^\d.]+}', '', $price);
//		Это при 'давальческой' цене, когда цена только за работу и нужно прибавлять стоимость золота
		if ($this->priceType == 'alternative') {
			$goldPrice = (float)$this->mdl_stores->getConfig('gold_price');
			$price_zac = (float)$weight * $goldPrice;
			$price_zac += $price;
		} else {
			$price_zac = $price;
		}

		return (int)($price_zac * 100);
	}

	protected function getPriceRoz($price_zac, $data)
	{
		return (int)($price_zac * 2.5);
	}

	protected function getSalePercent()
	{
		return rand(4, 8) * 5;
	}

	protected function drag($vstavka)
	{
		return [];
	}

	protected function title($data, $drag = [])
	{
		$title = "{$data["type"]} из ";
		$metalColor = trim($data["metal_color"]);
		if ($metalColor) {
			if ($metalColor == "Желтый" || $metalColor == "Жёлтый" || $metalColor == "Лимонный") {
				$title .= "желтого ";
			}
			if ($metalColor == "Красный") {
				$title .= "красного ";
			}
			if ($metalColor == "Белый") {
				$title .= "белого ";
			}
			if ($metalColor == "Желтый белый") {
				$title .= "белого и желтого ";
			}
			if ($metalColor == "Красный желтый белый") {
				$title .= "красного, белого и желтого ";
			}
		}
		$title .= "золота";

		if (empty($drag)) {
			return $title;
		}

		$len = strlen($title) + 3;
		$title .= " с ";
		foreach ($drag as $dragItem) {
			$stoneTitleInCase = $this->getStoneTitleInCase($dragItem['kamen']);
			if ($stoneTitleInCase && strpos($title, $stoneTitleInCase) === false) {
				$title .= "{$stoneTitleInCase}, ";
			}
		}

		if (strlen($title) == $len) { // Если вдруг не оказалось совпадения по камням
			$title = substr($title, 0, -3);
		} else { // Удаление ","
			$title = substr($title, 0, -2);
		}

		return $title;
	}

	public function getCaratsRangesAll($productIds = [])
	{

//		$productIds = [112928];
//		$products = [];
//		if (count($productIds) > 0) {
//			$this->db->where_in('id', $productIds);
		$this->db->where('postavchik', $this->postavchik);
		$this->db->limit(10000, 50000);
		$products = $this->db->get($this->prod_table)->result_array();
//		}

		$upd = [];
		if (count($products) > 0) {
			foreach ($products as $product) {
				$drag = json_decode($product['drag'], true);
				$optionLabel = json_decode($product['optionLabel'], true);
				$filter = $this->filter([], $drag);
				$caratsRanges = $this->getCaratsRanges($drag);
				$getCaratsRangesByStone = $this->getCaratsRangesByStone($drag);

				$upd[] = [
					'id' => $product['id'],
					'optionLabel' => json_encode($optionLabel, JSON_UNESCAPED_UNICODE),
					'drag' => json_encode($drag, JSON_UNESCAPED_UNICODE),
					'filter_kamen' => json_encode($filter['kamen'], JSON_UNESCAPED_UNICODE),
					'filter_carats' => json_encode($caratsRanges, JSON_UNESCAPED_UNICODE),
					'filter_kamen_carats' => json_encode($getCaratsRangesByStone, JSON_UNESCAPED_UNICODE),
				];
			}
			if (count($upd)) {
				$this->db->update_batch($this->prod_table, $upd, 'id');
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
		$this->db->where('postavchik', 'master-brilliant');
		$this->db->limit(10000, 30000);
		$products = $this->db->get($this->prod_table)->result_array();
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
				$this->db->update_batch($this->prod_table, $upd, 'id');
				var_dump(count($upd));
				die;
			}
		}
	}

	protected function getFullStoneName($name)
	{
		$replacements = [
			'Ал' => 'Алмаз', // попадпет Александрит!, пока решено костылем
			'Амет' => 'Аметист',
			'Бирюза н.' => 'Бирюза натуральная',
			'Бр' => 'Бриллиант',
			'бц' => 'бесцветный',
			'б/ц' => 'бесцветный',
			'Гр' => 'Гранат',
			'г/т' => 'гидротермальный',
			'Жем' => 'Жемчуг',
			'Жемчуг синтетический пр' => 'Жемчуг синтетический просверленный',
			'Из' => 'Изумруд',
			'иск.' => 'искусственный',
			'культ' => 'культивированный',
			' н. ' => ' н.',
			' н.' => ' натуральный ',
			'нат.' => 'натуральный',
			'п/пр' => 'полупросверленный',
			'пресн.' => 'пресноводный',
			'пресс.' => 'прессованный',
			'Р-топ' => 'Раух-Топаз',
			'Род' => 'Родонит', // попадпет Родолит!, пока решено костылем
			'Руб' => 'Рубин',
			'Сапфироваый' => 'Сапфировый',
			'Сапф' => 'Сапфир',
			'синт.' => 'синтетический',
			'синт' => 'синтетический',
			'топ-London' => 'Топаз London',
			'Топ-swiss' => 'Топаз Swiss',
			'Тсав' => 'Тсаворит',
			'Фиан' => 'Фианит',
			'Хр' => 'Хризолит',
			'цв.' => 'цветной',
			'Цит' => 'Цитрин',
		];

		foreach ($replacements as $search => $replacement) {
			if (strpos($name, $search) !== false && strpos($name, $replacement) === false && strpos($name, 'Александрит' && strpos($name, 'Родолит') === false)) {
				$name = str_replace($search, $replacement, $name);
			}
		}

		return trim($name);
	}

	protected function getStoneAttribute($name, $attribute)
	{
		foreach ($this->stones as $stone) {
			$regexp = "/{$stone['title']}/iu";
			if (preg_match($regexp, $name)) {
				return $stone[$attribute];
			}
		}
		return '';
	}

	protected function getStoneCode($name)
	{
		return $this->getStoneAttribute($name, 'translit') ?: $this->getTranslit($name);
	}

	protected function getTranslit($string)
	{
		return $this->mdl_product->aliase_translite($string);
	}

	protected function getStoneTitleInCase($name)
	{
		return $this->getStoneAttribute($name, 'in_case');
	}

	protected function checkPhoto($article, $data = [], $index = 0)
	{
//		$fileName = "{$article}.jpg";
		$fileName = $this->getImageFileName($article, $index);

		$filePath = "{$this->uploadPath}{$fileName}";

		// загрузка по imageUrl пока только для одного изображения
		if (!file_exists($filePath) && $this->downloadImages && $data['imageUrl']) {
			$this->downloadImage($filePath, $data['imageUrl']);
		}

		return file_exists($filePath);
	}

	protected function savePhotos($article, $productId)
	{
		if ($this->multipleImages) {
			for ($index = 0; $index < 10; $index++) {
				if ($this->checkPhoto($article, [], $index)) {
					$fileName = $this->getImageFileName($article, $index);
					$this->savePhoto($fileName, $productId);
				}
			}
		} else {
			if ($this->checkPhoto($article)) {
				$fileName = $this->getImageFileName($article);
				$this->savePhoto($fileName, $productId);
			}
		}
	}

	protected function getImageFileName($article, $index = 0)
	{
		return str_replace(['{article}', '{index}'], [$article, $index], $this->imageFileNameTemplate);
	}

	protected function savePhoto($fileName, $productId)
	{
		$this->saveImageFiles($fileName);

		$ph = [
			'product_id' => $productId,
			'photo_name' => $fileName,
			'define' => 1,
		];

		$this->db->insert($this->ph_table, $ph);
	}

	protected function saveImageFiles($fileName)
	{
		if (!file_exists("{$this->uploadPath}{$fileName}")) return;

		$this->load->library('images');

		$to_100 = "./uploads/products/100/";
		$this->images->imageresize("{$to_100}{$fileName}", "{$this->uploadPath}{$fileName}", 100, 100, 100);

		$to_250 = "./uploads/products/250/";
		$this->images->imageresize("{$to_250}{$fileName}", "{$this->uploadPath}{$fileName}", 250, 250, 100);

		$to_500 = "./uploads/products/500/";
		$this->images->imageresize("{$to_500}{$fileName}", "{$this->uploadPath}{$fileName}", 500, 500, 100);
	}

	public function count()
	{
		$file = file("{$this->uploadPath}{$this->fileName}");
		$count = count($file);
		if ($count == 1 and strlen($file[0]) == 0) {
			$count = 0;
		}
		echo json_encode(['count' => $count]);
		die;
	}

	/**
	 * Список градаций каратности
	 *
	 * @return array
	 */
	public function caratsRanges()
	{
		return $this->caratsRanges;
	}

	/**
	 * Код градации каратности по значению
	 *
	 * @param double $weight
	 * @return int
	 */
	public function getCaratsRange($weight)
	{
		$rangeId = null;
//		$weight = $this->getWeightDouble($weight);
		if ($weight) {
			foreach ($this->caratsRanges as $rangeId => $rangeMax) {
				if ($rangeMax && $weight <= $rangeMax) {
					break;
				}
			}
		}
		return $rangeId;
	}

	/**
	 * Вес камня в виде числа (double)
	 *
	 * @param array $stoneProperties
	 *
	 * @return double
	 */
	public function getStoneWeight($stoneProperties)
	{
		$stoneWeightProperty = reset(array_filter($stoneProperties, function ($stoneProperty) {
			return $stoneProperty['name'] == 'Вес, Ct.';
		}));

		if ($stoneWeightProperty && isset($stoneWeightProperty['value'])) {
			return $this->getWeightDouble($stoneWeightProperty['value']);
		}
		return 0;
	}

	/**
	 * Вес камня в  виде числа (double)
	 *
	 * @param string $weight
	 * @return double
	 */
	public function getWeightDouble($weight)
	{
		$weight = preg_replace('/[^0-9.,]/', '', $weight);
		if ($weight) {
			$weight = (double)str_replace(',', '.', $weight);
			return $weight;
		}
		return 0;
	}

	/**
	 * Получение градаций каратности по значению поля drag
	 *
	 * @param array $drag
	 * @return array
	 */
	public function getCaratsRanges($drag)
	{
		$caratsRanges = [];
		foreach ($drag as $item) {
			$stoneWeight = $this->getStoneWeight($item['data']);
			if ($stoneWeight) {
				$caratsRange = $this->getCaratsRange($stoneWeight);
				if ($caratsRange && !in_array($caratsRange, $caratsRanges)) {
					$caratsRanges[] = $caratsRange;
				}
			}
		}
		return $caratsRanges;
	}

	/**
	 * Получение градаций каратности, сгруппированных по виду камня, по значению поля drag
	 *
	 * @param array $drag
	 * @return array
	 */
	public function getCaratsRangesByStone($drag)
	{
		$caratsRangesByStone = [];
		foreach ($drag as $item) {
			$stoneCode = $item['kamenCode'];
			if (!$stoneCode) {
				$stoneCode = $this->getStoneCode($item['kamen']);
			}
			if (!$stoneCode) {
				break;
			}
			$stoneWeight = $this->getStoneWeight($item['data']);
			if ($stoneWeight) {
				$caratsRange = $this->getCaratsRange($stoneWeight);
				if ($caratsRange) {
					if (!isset($caratsRangesByStone[$stoneCode])) {
						$caratsRangesByStone[$stoneCode] = [];
					}
					if (!in_array($caratsRange, $caratsRangesByStone[$stoneCode])) {
						$caratsRangesByStone[$stoneCode][] = $caratsRange;
					}
				}
			}
		}
		return $caratsRangesByStone;
	}

	protected function filter($data, $drag = [])
	{
		$filter = [
			'zoloto' => [],
			'kamen' => [],
//			'forma_vstavki' => [],
			'gender' => [],
//			'size' => [],
		];

		$metalColor = trim($data["metal_color"]);
		if ($metalColor) {
			if ($metalColor == "Красный") {
				$filter['zoloto'][] = "krasnoe";
			}
			if ($metalColor == "Белый") {
				$filter['zoloto'][] = "beloe";
			}
			if ($metalColor == "Желтый" || $metalColor == "Жёлтый" || $metalColor == "Лимонный") {
				$filter['zoloto'][] = "zhyoltoe";
			}
			if ($metalColor == "Желтый белый") {
				$filter['zoloto'][] = "beloe";
				$filter['zoloto'][] = "zhyoltoe";
			}
			if ($metalColor == "Красный желтый белый") {
				$filter['zoloto'][] = "beloe";
				$filter['zoloto'][] = "zhyoltoe";
				$filter['zoloto'][] = "krasnoe";
			}
		} else {
			$filter['zoloto'][] = "krasnoe";
		}

		// Сортируем вставки по весу камня по убыванию
		usort($drag, function ($a, $b) {
			$stoneWeightA = $this->getStoneWeight($a['data']);
			$stoneWeightB = $this->getStoneWeight($b['data']);
			return $stoneWeightA == $stoneWeightB ? 0 : ($stoneWeightA > $stoneWeightB ? -1 : 1);
		});

		foreach ($drag as $dragItem) {
			$stoneCode = $this->getStoneCode($dragItem['kamen']);
			if ($stoneCode && !in_array($stoneCode, $filter['kamen'])) {
				$filter['kamen'][] = $stoneCode;
			}
		}

		// 'empty','no-empty'
		if (count($filter['kamen']) > 0) {
			$filter['kamen'][] = 'no-empty';
		} else {
			$filter['kamen'][] = 'empty';
		}

		if ($data["gender"] == "Мужской") {
			$filter['gender'][] = "men";
		} elseif ($data["gender"] == "Унисекс") {
			$filter['gender'][] = "woman";
			$filter['gender'][] = "men";
		} elseif ($data["gender"] == "Детские") {
			$filter['gender'][] = "kids";
		} elseif ($data["type"] == "Серьги детские") {
			$filter['gender'][] = "kids";
		} else {
			$filter['gender'][] = "woman";
		}

//		$size = str_replace(",", "_", $data["size"]);
//		$size = str_replace(".", "_", $size);
//
//		$filter['size'][] = $size;

		return $filter;
	}

	protected function params($data = [], $drag = [])
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

		$vstavkaStones = "";
		$vstavkaForms = "";
		foreach ($drag as $dragItem) {
			$vstavkaStones .= "{$dragItem['kamen']}, ";
			$vstavkaForms .= "{$dragItem['data'][2]['value']}, ";
		}
		$vstavkaStones = substr($vstavkaStones, 0, -2);
		$vstavkaForms = substr($vstavkaForms, 0, -2);

		$metal = '';
		$metalColor = trim($data["metal_color"]);
		if ($metalColor) {
			if ($metalColor == "Желтый" || $metalColor == "Жёлтый" || $metalColor == "Лимонный") {
				$metal = "Желтое ";
			}
			if ($metalColor == "Красный") {
				$metal = "Красное ";
			}
			if ($metalColor == "Белый") {
				$metal = "Белое ";
			}
			if ($metalColor == "Желтый белый") {
				$metal = "Белое, желтое ";
			}
			if ($metalColor == "Красный желтый белый") {
				$metal = "Красное, белое, желтое ";
			}
		}
		$metal .= $data["metal"];

		$params[0]["value"] = $metal;
		$params[1]["value"] = $data["metal"];
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

	/**
	 * Загрузка изображений по урлу
	 *
	 * @param string $filePath
	 * @param string $imageUrl
	 *
	 * @return bool
	 */
	protected function downloadImage($filePath, $imageUrl)
	{
		$ch = curl_init($imageUrl);

		$fp = fopen($filePath, 'wb');

		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$result = curl_exec($ch);
		curl_close($ch);
		fclose($fp);

		return $result;
	}

	/**
	 * Получение минимального и максимального размеров (при наличии)
	 *
	 * @param $size
	 */
	protected function getSizesRange($size)
	{
		$sizes = ['min' => null, 'max' => null];
		if (strpos($size, '-') !== false) {
			$sizesParts = explode('-', $size);
			$sizes['min'] = str_replace('.0', '', str_replace(',', '.', trim($sizesParts[0])));
			$sizes['max'] = str_replace('.0', '', str_replace(',', '.', trim($sizesParts[1])));
		}
		return $sizes;
	}

}
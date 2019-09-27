<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Promocodes extends CI_Controller
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

	// Страница со списком промокодов
	public function index()
	{

		$this->access_static();

		$title = 'Промокоды';
		$page_var = 'catalog';

		$data = $this->getStorePromocodesData(false);

		$promocodes = $data['result'];
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

//			'breadcrumb' => $this->mdl_tpl->view('snipets/breadcrumb.html', array(
//				'title' => $title,
//				'array' => [[
//					'name' => 'Панель управления',
//					'link' => '/admin',
//				], [
//					'name' => 'Ассортимент',
//					'link' => '/admin/catalog',
//				]],
//			), true),

			'content' => $this->mdl_tpl->view('pages/admin/catalog/promocodes.html', array(
				'promocodes' => $promocodes,
				'pag' => $pag,
				'textSearch' => $textSearch,
//				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
//				'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path'),
			), true),

			'load' => $this->mdl_tpl->view('snipets/load.html', array(
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			), true),

//			'resorses' => $this->mdl_tpl->view('resorses/admin/catalog/promocodes/head.html', array(
//				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
//				'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path'),
//			), true),

		), false);

	}

	// Получить список промокодов
	public function getStorePromocodesData($json = true)
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
//			'labels' => ['id', 'title'],
			'pagination' => [
				'on' => true,
				'page' => $page,
				'limit' => $limit,
			],
			'module' => true,
			'modules' => [[
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
			], [
                'module_name' => 'dates',
                'result_item' => 'dates',
                'option' => []
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
					'item' => 'code',
					'value' => $t,
				], [
					'item' => 'amount',
					'value' => $t,
				], [
					'item' => 'percent',
					'value' => $t,
				], [
					'item' => 'min_order',
					'value' => $t,
				]]  // [ 'item' => '', 'value' => '' ],[...]
			];
		}

		$result = $this->mdl_promocode->queryData($option);

		$r['result'] = $result['result'];
		$r['option'] = $result['option'];

		if ($json !== true) {
			return $r;
		} else {
			$this->mdl_helper->__json($r);
		}

	}

	// Страница редактирования промокода
	public function edit($GID = 0)
	{

		$this->access_static();

		$getData = $this->getDataEdit($GID, false);

		$title = 'Правка информации о промокоде';
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

			'content' => $this->mdl_tpl->view('pages/admin/catalog/editPromocode.html', array(
				'promocode' => $getData['promocode'],
			), true),

			'load' => $this->mdl_tpl->view('snipets/load.html', array(
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			), true),

			'resorses' => $this->mdl_tpl->view('resorses/admin/catalog/promocodes/edit_head.html', array(
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path'),
			), true),

		), false);

	}

	// Информация о промокоде для редактирования ( Или добавления )
	public function getDataEdit($PromocodeID = false, $json = true)
	{

		$this->access_dynamic();

		$PromocodeID = (isset($this->post['gid'])) ? $this->post['gid'] : $PromocodeID;

		$r = [];
		if ($PromocodeID > 0) {
			$promocode = $this->mdl_promocode->queryData([
				'return_type' => 'ARR1',
				'where' => [
					'method' => 'AND',
					'set' => [[
						'item' => 'id',
						'value' => $PromocodeID,
					]],
				],
//				'labels' => ['id', 'title'],
			]);

			if ($promocode) {
				$r['promocode'] = $promocode;
			}
		} else {
			$r['promocode'] = [
				'id' => 0,
				'amount' => 0,
				'percent' => 0,
				'min_order' => 0,
//				'title' => 'Новый промокод',
			];
		}

		if ($json !== true) {
			return $r;
		} else {
			$this->mdl_helper->__json($r);
		}

	}

	// Форма редактирования основной информации промокода
	public function actEditPromocodeInfo()
	{

		$this->access_dynamic();

		$r = ['err' => '1', 'mess' => 'Неизвестная ошибка'];

		$date_start = isset($this->post['date_start']) ? strtotime($this->post['date_start']) : 0;
		$date_end = isset($this->post['date_end']) ? strtotime($this->post['date_end']) : 0;
		$code = isset($this->post['code']) ? $this->post['code'] : '';
		$title = isset($this->post['title']) ? $this->post['title'] : '';
		$amount = isset($this->post['amount']) ? (int)$this->post['amount'] : 0;
		$percent = isset($this->post['percent']) ? (int)$this->post['percent'] : 0;
		$min_order = isset($this->post['min_order']) ? (int)$this->post['min_order'] : 0;
		$promocode_id = isset($this->post['promocode_id']) ? $this->post['promocode_id'] : 0;

		if (!$code) exit('{"err":"1","mess":"Код должен быть заполнен!"}');
		if (!$amount && !$percent) exit('{"err":"1","mess":"Должна быть заполнена скидка в рублях или процентах!"}');
		if ($amount && $percent) exit('{"err":"1","mess":"Должна быть заполнена скидка или в рублях или процентах!"}');
		if ($amount < 0 || $percent < 0) exit('{"err":"1","mess":"Скидка должна быть положительным числом!"}');
		if ($percent > 100) exit('{"err":"1","mess":"Скидка не может быть больше 100%!"}');
		if ($date_start && $date_end && $date_start >= $date_end) exit('{"err":"1","mess":"Дата окончания должна быть больше даты начала!"}');

		$data = [
			'date_start' => $date_start,
			'date_end' => $date_end,
			'code' => $code,
			'title' => $title,
			'amount' => $amount,
			'percent' => $percent,
			'min_order' => $min_order,
		];

		if ($promocode_id > 0) {
			$promocode = $this->mdl_promocode->queryData([
				'return_type' => 'ARR1',
				'where' => [
					'method' => 'AND',
					'set' => [[
						'item' => 'id',
						'value' => $promocode_id,
					]],
				],
			]);
			if (isset($promocode['id'])) {
				$this->mdl_db->_update_db("promocodes", "id", $promocode_id, $data);
				$r = ['err' => '0', 'mess' => 'Данные промокода обновлены'];
			} else {
				$promocode_id = 0;
			}
		}

		if ($promocode_id < 1) {
			$this->db->insert('promocodes', $data);
			$update_id = $this->db->insert_id();

			$r = ['err' => '0', 'mess' => 'Промокод успешно добавлен', 'response_id' => $update_id];
		}

		$this->mdl_helper->__json($r);

	}

}

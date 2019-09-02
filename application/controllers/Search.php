<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Search extends CI_Controller
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

	// Вывести главную страницу каталога
	public function index()
	{

		$getData = $this->getSearch(false);

		$title = 'Поиск товара';
		$page_var = 'catalog';

		$this->mdl_tpl->view('templates/doctype_search.html', array(

			'title' => $title,
			'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'),
			'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),

			'seo' => $this->mdl_tpl->view('snipets/seo_tools.html', array(
				'mk' => '',
				'md' => '',
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

			'content' => $this->mdl_tpl->view('pages/search/home.html', array(
				'textSearch' => $getData['textSearch'],
				'header_title' => 'Поиск товаров',
				'sort' => $getData['sort'],
				'products' => $this->mdl_tpl->view('pages/catalog/category_view_products.html', array(
					'items' => $getData['products'],
				), true),
				'pagination' => $getData['products_pag'],
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

	}

	// УСТАРЕЛА
	public function _____getSearch($j = true)
	{

		$r = [];

		$text = (isset($this->get['t'])) ? $this->get['t'] : false;
		$text = (isset($this->post['t'])) ? $this->post['t'] : $text;

		$query_string = array();
		$query_string = array_merge($query_string, $this->get);
		$query_string = array_merge($query_string, $this->post);

		unset($query_string['page']);
		unset($query_string['limit']);

		$query_string = $this->mdl_helper->clear_array_0($query_string, array(
			'f', 's', 'l', 't',
		));

		$sffix = $query_string;

		$option = [
			'return_type' => 'ARR2+',
			'debug' => true,
			'like' => [
				'math' => 'both',
				'method' => 'OR',
				'set' => [[
					'item' => 'title',
					'value' => $text,
				], [
					'item' => 'description',
					'value' => $text,
				], [
					'item' => 'aliase',
					'value' => $text,
				], [
					'item' => 'articul',
					'value' => $text,
				]],
			],
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
			'table_name' => 'sdfsdfsdf',
			'group_by' => 'articul',
			'distinct' => true,

			'labels' => ['id', 'aliase', 'articul', 'title', 'prices_empty', 'filters', 'salle_procent', 'modules'],
			'pagination' => [
				'on' => true,
				'page' => (isset($this->get['page'])) ? $this->get['page'] : 1,
				'limit' => (isset($this->get['l'])) ? $this->get['l'] : 40,
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
						$fNew[] = [
							'item' => $k,
							'type' => $fv['type'],
							'values' => explode('|', $v),
						];
					}
				}
			}

			$option['modules'][] = [
				'module_name' => 'setFilters',
				'result_item' => 'setFilters',
				'option' => [
					'setItems' => $fNew,
				],
			];
			$setFilters = $fNew;
		}

		$r['sort'] = (isset($this->get['s'])) ? $this->get['s'] : 'new';

		if (isset($this->get['s'])) {

			$sort = $this->get['s'];

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
					'item' => 'price_roz',
					'value' => 'ASC',
				];
			}

			if ($sort === 'pricemax') {
				$option['order_by'] = [
					'item' => 'price_roz',
					'value' => 'DESC',
				];
			}

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
		$r['textSearch'] = $text;

		if ($j === true) {
			$this->mdl_helper->__json($r);
		} else {
			return $r;
		}

	}

	public function getSearch($j = true)
	{

		$r = [];

		$text = (isset($this->get['t'])) ? $this->get['t'] : false;
		$text = (isset($this->post['t'])) ? $this->post['t'] : $text;

		$query_string = array();
		$query_string = array_merge($query_string, $this->get);
		$query_string = array_merge($query_string, $this->post);

		unset($query_string['page']);
		unset($query_string['limit']);

		$query_string = $this->mdl_helper->clear_array_0($query_string, array(
			'f', 's', 'l', 't',
		));

		$sffix = $query_string;

		$option = [
			'return_type' => 'ARR2+',
			'like' => [
				'math' => 'both',
				'method' => 'OR',
				'set' => [[
					'item' => 'title',
					'value' => $text,
				], [
					'item' => 'description',
					'value' => $text,
				], [
					'item' => 'aliase',
					'value' => $text,
				], [
					'item' => 'articul',
					'value' => $text,
				]],
			],
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
			'labels' => ['id', 'aliase', 'articul', 'title', 'prices_empty', 'filters', 'salle_procent', 'modules'],
			'group_by' => 'articul',
			'pagination' => [
				'on' => true,
				'page' => (isset($this->get['page'])) ? $this->get['page'] : 1,
				'limit' => (isset($this->get['l'])) ? $this->get['l'] : 40,
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
						$fNew[] = [
							'item' => $k,
							'type' => $fv['type'],
							'values' => explode('|', $v),
						];
					}
				}
			}

			$option['modules'][] = [
				'module_name' => 'setFilters',
				'result_item' => 'setFilters',
				'option' => [
					'setItems' => $fNew,
				],
			];
			$setFilters = $fNew;
		}

		$r['sort'] = (isset($this->get['s'])) ? $this->get['s'] : 'new';

		if (isset($this->get['s'])) {

			$sort = $this->get['s'];

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
					'item' => 'price_roz',
					'value' => 'ASC',
				];
			}

			if ($sort === 'pricemax') {
				$option['order_by'] = [
					'item' => 'price_roz',
					'value' => 'DESC',
				];
			}

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

		/*$_r = $this->mdl_product->queryX( $this->db->select('*')->from('products')
			->group_start()
				->where('view >', 0)
				->where('qty >', 0)
				->where('moderate >', 1)
			->group_end()
			->group_start()
				->like('title', $text)
				->or_like('id', $text)
				->or_like('description', $text)
				->or_like('aliase', $text)
				->or_like('articul', $text)
			->group_end()
			->distinct()
			->limit(10)
			->group_by('articul')
		->get(), $option );*/

		$_r = $this->mdl_product->queryData($option);

		$r['products'] = $_r['result'];
		$r['products_pag'] = $_r['option']['pag'];
		$r['textSearch'] = $text;

		if ($j === true) {
			$this->mdl_helper->__json($r);
		} else {
			return $r;
		}

	}

}
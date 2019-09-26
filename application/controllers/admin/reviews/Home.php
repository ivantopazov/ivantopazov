<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller
{

	protected $user_info = array();
	protected $store_info = array();

	public function __construct()
	{

		parent::__construct();

		$this->user_info = ($this->mdl_users->user_data()) ? $this->mdl_users->user_data() : false;
		$this->store_info = $this->mdl_stores->allConfigs();

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

	// Страница по-умолчанию
	public function index()
	{

		$this->access_static();

		$title = 'Отзывы, ожидающие рассмотрения';
		$page_var = 'reviews';

		$this->mdl_tpl->view('templates/doctype_admin.html', array(

			'title' => $title,
			'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'),

			'seo' => $this->mdl_tpl->view('snipets/seo_tools.html', array(
				'mk' => (!empty($this->store_info['seo_keys'])) ? $this->store_info['seo_keys'] : '',
				'md' => (!empty($this->store_info['seo_desc'])) ? $this->store_info['seo_desc'] : '',
			), true),

			'nav' => $this->mdl_tpl->view('snipets/admin_nav.html', array(
				'active' => 'reviews',
			), true),

			'content' => $this->mdl_tpl->view('pages/admin/reviews/home.html', array(
				'reviews' => $this->getReviews(),
			), true),

			'load' => $this->mdl_tpl->view('snipets/load.html', array(
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			), true),

			'resorses' => $this->mdl_tpl->view('resorses/admin/reviews/head.html', array(
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path'),
			), true),

		), false);

	}

	private function getReviews()
	{
		$review = array();

		$q = $this->mdl_db->_query("
			SELECT products_reviews.id, products_reviews.name, author, email, city, products_reviews.description, rating, products.aliase, title, products_cats.aliase as cat_aliase, date_public
			FROM products_reviews 
			INNER JOIN products ON products_reviews.product_id=products.id 
			INNER JOIN products_cats ON products.cat=products_cats.id 
			WHERE products_reviews.moderate = 0 
			ORDER BY date_public
		");

		foreach ($q as $key => $val) {
			$review[$key]["id"] = $val["id"];
			$review[$key]["name"] = $val["name"];
			$review[$key]["author"] = $val["author"];
			$review[$key]["email"] = $val["email"];
			$review[$key]["city"] = $val["city"];
			$review[$key]["text"] = $val["description"];
			$review[$key]["rating"] = $val["rating"];
			$review[$key]["prod_title"] = $val["title"];
			$review[$key]["date"] = date("d.m.Y", $val["date_public"]);
			$review[$key]["link"] = $val["cat_aliase"] . "/" . $val["aliase"];
		}

		return $review;
	}

	public function accept()
	{
		$err = 0;

		$this->mdl_db->_update_db("products_reviews", "id", $_POST["id"], ["moderate" => 1]);

		echo json_encode(["err" => $err]);
		return;
	}

	public function deny()
	{
		$err = 0;

		$this->mdl_db->_update_db("products_reviews", "id", $_POST["id"], ["moderate" => -1]);

		echo json_encode(["err" => $err]);
		return;
	}

}

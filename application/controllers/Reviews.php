<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Reviews extends CI_Controller
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

	public function index()
	{
		$title = 'Отзывы об интернет-магазине "Иван Топазов"';
		$page_var = 'reviews';

		$this->mdl_tpl->view('templates/doctype_home.html', array(

			'title' => $title,
			'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'),
			'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),

			'seo' => $this->mdl_tpl->view('snipets/seo_tools.html', array(
				'mk' => (!empty($this->store_info['seo_keys'])) ? $this->store_info['seo_keys'] : '',
				'md' => (!empty($this->store_info['seo_desc'])) ? $this->store_info['seo_desc'] : '',
				'oggMetta' => [
					"title" => $title,
					"url" => "https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"],
					"image" => "https://ivantopazov.ru/templates/basic/images/event-4.jpg",
					"site_name" => "Иван Топазов",
					"description" => (!empty($this->store_info['seo_desc'])) ? $this->store_info['seo_desc'] : '',
				],
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

			'content' => $this->mdl_tpl->view('pages/reviews/basic.html', array(
				'reviews' => $this->getReviews(),
			), true),

			'footer' => $this->mdl_tpl->view('snipets/footer.html', array(
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			), true),

			'load' => $this->mdl_tpl->view('snipets/load.html', array(
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'utmLabels' => $this->mdl_tpl->view('snipets/utmLabels.html', $this->mdl_seo->utmLabels($this->get), true),
			), true),

			'resorses' => $this->mdl_tpl->view('resorses/reviews/head.html', array(
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path'),
			), true),

		), false);
	}

	private function getReviews()
	{
		$review = array();

		$q = $this->mdl_db->_query("
			SELECT products_reviews.name, author, city, products_reviews.description, rating, products.aliase, title, photo_name, products_cats.aliase as cat_aliase, date_public
			FROM products_reviews 
			INNER JOIN products ON products_reviews.product_id=products.id 
			INNER JOIN products_photos ON products_reviews.product_id=products_photos.product_id 
			INNER JOIN products_cats ON products.cat=products_cats.id 
			WHERE products_reviews.moderate = 1 
			ORDER BY date_public desc
			limit 10
		");

		foreach ($q as $key => $val) {
			$review[$key]["name"] = $val["name"];
			$review[$key]["author"] = $val["author"];
			$review[$key]["city"] = $val["city"];
			$review[$key]["text"] = $val["description"];
			$review[$key]["rating"] = $val["rating"];
			$review[$key]["prod_title"] = $val["title"];
			$review[$key]["date"] = date("d.m.Y", $val["date_public"]);
			$review[$key]["photo"] = $val["photo_name"];
			$review[$key]["link"] = $val["cat_aliase"] . "/" . $val["aliase"];
		}

		return $review;
	}
}

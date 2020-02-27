<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Favorite extends CI_Controller
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

		$start = microtime(true);

		$title = (!empty($this->store_info['seo_title'])) ? $this->store_info['seo_title'] : $this->store_info['header'];
		$page_var = 'favorite';

		$this->mdl_tpl->view('templates/doctype_home.html', array(

			'title' => $title,
			'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'),
			'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),

			'seo' => $this->mdl_tpl->view('snipets/seo_tools.html', array(
				'mk' => (!empty($this->store_info['seo_keys'])) ? $this->store_info['seo_keys'] : '',
				'md' => (!empty($this->store_info['seo_desc'])) ? $this->store_info['seo_desc'] : '',
			), true),

			'navTop' => $this->mdl_tpl->view('snipets/navTop.html', array(
				'store' => $this->store_info,
				'active' => 'favorite',
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

			'content' => $this->mdl_tpl->view('pages/favorite/basic.html', array(
				'utmLabels' => $this->mdl_tpl->view('snipets/utmLabels.html', $this->mdl_seo->utmLabels($this->get), true),
			), true),

			'footer' => $this->mdl_tpl->view('snipets/footer.html', array(
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			), true),

			'load' => $this->mdl_tpl->view('snipets/load.html', array(
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'utmLabels' => $this->mdl_tpl->view('snipets/utmLabels.html', $this->mdl_seo->utmLabels($this->get), true),
			), true),

			'resorses' => $this->mdl_tpl->view('resorses/home/head.html', array(
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path'),
			), true),

		), false);

		//echo '<p style="background: yellow none repeat scroll 0% 0%; margin: 20px 0px 0px; position: fixed; bottom: 0px;">Время выполнения скрипта: '.(microtime(true) - $start).' сек.</p>';

	}


	/**
	 * Обновление цен заказа
	 */
	public function refresh()
	{
		$favorite = isset($this->post['favorite']) ? $this->post['favorite'] : [];
		

		
		if (empty($favorite) || !array($favorite)) {
			echo json_encode(['success' => false]);
			exit();
		}
		$ids = array_map(function ($favoriteItem) {
			return $favoriteItem['id'];
		}, $favorite);

		$favorite = array_combine($ids, $favorite);

		
		echo json_encode(['success' => true, 'favorite' => $favorite]);
		exit();

	}

}

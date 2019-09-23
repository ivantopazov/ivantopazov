<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	
	class Actions extends CI_Controller{
		
		protected $user_info = array();
		protected $store_info = array();
		
		protected $post = array();
		protected $get = array();
		
		public function __construct(){
			
			parent::__construct();
			
			$this->user_info = ($this->mdl_users->user_data()) ? $this->mdl_users->user_data() : false;
			$this->store_info = $this->mdl_stores->allConfigs();
			
			$this->post = $this->security->xss_clean($_POST);
			$this->get = $this->security->xss_clean($_GET);
			
			$this->load->model('mdl_actions');
			
		}
		
		// Предворительная обработка системными средствами
		public function _remap($method, $params = array()){
			if(method_exists($this, $method)){
				return call_user_func_array(array($this, $method), $params);
			} else{
				return call_user_func_array(array($this, "ExtractTree"), func_get_args());
			}
		}
		
		// Серверное извлечение всех алиасов из URL
		private function ExtractTree(){
			
			$argList = func_get_args();
			$arg_list = ($argList !== false) ? $argList : array();
			$item = (count($arg_list[1]) > 0) ? $arg_list[1][(count($arg_list[1]) - 1)] : $arg_list[0];
			$tree = array();
			$tree[] = $arg_list[0];
			foreach($arg_list[1] as $v) $tree[] = $v;
			
			// Передача алиасов и получение информации и инструкций...
			$logicData = $this->getLogicData($tree, false);
			
			if($logicData['error404'] !== true){
				$variable = 'view_'.$logicData['method'];
				self::$variable($logicData);
			} else{
				show_404();
			}
			
		}
		
		// Получение информации и инструкций [ сервер / json ]
		public function getLogicData($tree = array(), $j = true){
			
			$tree = (isset($this->post['tree'])) ? $this->post['tree'] : $tree;
			$r = [
			 'error404' => true,
			 'brb' => [
			  [
				'name' => 'Акции',
				'link' => '/actions'
			  ]
			 ]
			];
			$lastAliase = $tree[count($tree) - 1];
			
			if(count($tree) > 0){
				
				$colItem = $this->mdl_actions->queryData([
				 'return_type' => 'ARR2',
				 'where' => [
				  'method' => 'AND',
				  'set' => [[
					'item' => 'aliase',
					'value' => $lastAliase
				  ]]
				 ],
				 'labels' => false,
				 'module' => true,
				 'modules' => [[
				  'module_name' => 'getProducts',
				  'result_item' => 'Products',
				  'option' => [
					'limit' => (isset($this->get['limit'])) ? $this->get['limit'] : 42,
					'page' => (isset($this->get['page'])) ? $this->get['page'] : 1,
					'get' => $this->get
				  ]
				 ]]
				]);
				
				if(count($colItem) > 0){
					$r['method'] = 'action';
					$r['item'] = $colItem[0];
					$r['error404'] = false;
				} else{
					$r['method'] = 'action';
					$r['item'] = [];
					$r['error404'] = true;
				}
			}
			
			if($j === true){
				$this->mdl_helper->__json($r);
			} else{
				return $r;
			}
			
		}
		
		public function index(){
			
			$start = microtime(true);
			
			$title = 'Акции сайта';
			$page_var = 'actions';
			
			$this->mdl_tpl->view('templates/doctype_home.html', array(
			 
			 'title' => $title,
			 'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			 'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'),
			 'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			 
			 'seo' => $this->mdl_tpl->view('snipets/seo_tools.html', array(
			  'mk' => (!empty($this->store_info['seo_keys'])) ? $this->store_info['seo_keys'] : '',
			  'md' => (!empty($this->store_info['seo_desc'])) ? $this->store_info['seo_desc'] : ''
			 ), true),
			 
			 'navTop' => $this->mdl_tpl->view('snipets/navTop.html', array(
			  'store' => $this->store_info,
			  'active' => 'actions',
			  'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path')
			 ), true),
			 
			 'header' => $this->mdl_tpl->view('snipets/header.html', array(
			  'store' => $this->store_info,
			  'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path')
			 ), true),
			 
			 'navMenu' => $this->mdl_tpl->view('snipets/navMenu.html', array(
			  'store' => $this->store_info,
			  'active' => 'home',
			  'itemsTree' => $this->mdl_category->getTreeMenu(),
			  'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path')
			 ), true),
			 
			 'content' => $this->mdl_tpl->view('pages/actions/basic.html', array(
			  'items' => $this->get_actions(false)
			 ), true),
			 
			 'preimushchestva' => $this->mdl_tpl->view('snipets/preimushchestva.html', array(
			  'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path')
			 ), true),
			 
			 'footer' => $this->mdl_tpl->view('snipets/footer.html', array(
			  'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path')
			 ), true),
			 
			 'load' => $this->mdl_tpl->view('snipets/load.html', array(
			  'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			  'utmLabels' => $this->mdl_tpl->view('snipets/utmLabels.html', $this->mdl_seo->utmLabels($this->get), true)
			 ), true),
			 
			 'resorses' => $this->mdl_tpl->view('resorses/home/head.html', array(
			  'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			  'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path')
			 ), true)
			
			), false);
			
			//echo '<p style="background: yellow none repeat scroll 0% 0%; margin: 20px 0px 0px; position: fixed; bottom: 0px;">Время выполнения скрипта: '.(microtime(true) - $start).' сек.</p>';
			
		}
		
		// Список акций
		public function get_actions($j = true){
			
			$actions = $this->mdl_actions->queryData([
			 'where' => [
			  'method' => 'AND',
			  'set' => [[
				'item' => 'status_view >',
				'value' => '0'
			  ]]
			 ],
			 'labels' => ['id', 'date_start', 'date_end', 'title', 'aliase', 'photo_name', 'anonse']
			]);
			
			$r = [];
			foreach($actions as $a){
				if($a['date_start'] == '0' && $a['date_end'] == '0'){
					$r[] = $a;
				} else{
					if($a['date_start'] < time() && $a['date_end'] < time()){
						$r[] = $a;
					}
				}
			}
			
			if($j === true){
				$this->mdl_helper->__json($r);
			} else{
				return $r;
			}
			
		}
		
		
		public function view_action($data = false){
			
			//$start = microtime(true);
			
			$title = $data['item']['title'];
			$page_var = 'actions';
			
			$this->mdl_tpl->view('templates/doctype_catalog.html', array(
			 
			 'title' => $title,
			 
			 'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			 'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'),
			 'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			 
			 'seo' => $this->mdl_tpl->view('snipets/seo_tools.html', array(
			  'mk' => '',
			  'md' => ''
			 ), true),
			 
			 'navTop' => $this->mdl_tpl->view('snipets/navTop.html', array(
			  'store' => $this->store_info,
			  'active' => $page_var,
			  'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path')
			 ), true),
			 
			 'header' => $this->mdl_tpl->view('snipets/header.html', array(
			  'store' => $this->store_info,
			  'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path')
			 ), true),
			 
			 'navMenu' => $this->mdl_tpl->view('snipets/navMenu.html', array(
			  'store' => $this->store_info,
			  'active' => $page_var,
			  'itemsTree' => $this->mdl_category->getTreeMenu(),
			  'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path')
			 ), true),
			 
			 'breadcrumb' => $this->mdl_tpl->view('snipets/breadcrumb.html', array(
			  'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			  'title' => $data['item']['title'],
			  'array' => $data['brb']
			 ), true),
			 
			 'content' => $this->mdl_tpl->view('pages/actions/select_item.html', array(
			  'item' => $data['item'],
			  'products' => $this->mdl_tpl->view('pages/collections/items.html', array(
				'items' => $data['item']['modules']['Products']['result']
			  ), true),
			  'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			 ), true),
			 
			 'footer' => $this->mdl_tpl->view('snipets/footer.html', array(
			  'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path')
			 ), true),
			 
			 'load' => $this->mdl_tpl->view('snipets/load.html', array(
			  'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			  'utmLabels' => $this->mdl_tpl->view('snipets/utmLabels.html', $this->mdl_seo->utmLabels($this->get), true)
			 ), true),
			 
			 'resorses' => ''
			
			), false);
			
			//echo '<p style="background: yellow none repeat scroll 0% 0%; margin: 20px 0px 0px; position: fixed; bottom: 0px;">Время выполнения скрипта: '.(microtime(true) - $start).' сек.</p>';
		}
		
		
		/*
		[id] => 1
				  [aliase] => kupil-dva-poluchil-tri-1
				  [title] => Купил два украшения - получил ТРИ!
				  [anonse] => Купил два украшения - получил ТРИ! «Иван Топазов» предоставляет вам возможность получить третье украшение из той же ценовой категории
				  [text] => При покупке двух ювелирных украшения, компания «Иван Топазов» предоставляет вам возможность получить третье украшение из той же ценовой категории, совершенно бесплатно.
				  [photo_name] => 3423432423.jpg
				  [date_start] => 0
				  [date_end] => 0
				  [products_list] => 14874,778,13818
				  [modules] => Array
						(
							 [Products] => Array
								  (
										[result] => Array
											 (
												  [0] => Array
														(
															 [id] => 778
															 [aliase] => kolco-iz-zolota_10001-001_778
															 [articul] => 10001-001
															 [title] => Кольцо из золота
															 [salle_procent] => 0
															 [prices_empty] => 1
															 [filters] => [{"item":"metall","values":["krasnZoloto"]},{"item":"kamen","values":["empty"]},{"item":"forma_vstavki","values":[]},{"item":"sex","values":["woman"]},{"item":"size","values":["17_0"]}]
															 [modules] => Array
																  (
																		[price_actual] => Array
																			 (
																				  [format] => 2 910
																				  [number] => 2910
																				  [cop] => 291000
																				  [zac] => Array
																						(
																						)
  
																			 )
  
																		[photos] => Array
																			 (
																				  [0] => Array
																						(
																							 [id] => 778
																							 [product_id] => 778
																							 [photo_name] => kolco-iz-zolota_10001-001_778.jpg
																							 [define] => 1
																						)
  
																			 )
  
																		[linkPath] => /catalog/kolca/kolco-iz-zolota_10001-001_778
																		[salePrice] => Array
																			 (
																			 )
  
																		[emptyPrice] => Array
																			 (
																				  [id] => 1
																				  [title] => Цена пока недоступна
																			 )
  
																  )
  
														)
  
												  [1] => Array
														(
															 [id] => 13818
															 [aliase] => kolco-iz-krasnogo-zolota-s-sapfirom_12362-102_13818
															 [articul] => 12362-102
															 [title] => Кольцо из красного золота с Сапфиром
															 [salle_procent] => 0
															 [prices_empty] => 1
															 [filters] => [{"item":"metall","values":["krasnZoloto"]},{"item":"kamen","values":["sapfir","no_empty"]},{"item":"forma_vstavki","values":[]},{"item":"sex","values":["woman"]},{"item":"size","values":["15_5"]}]
															 [modules] => Array
																  (
																		[price_actual] => Array
																			 (
																				  [format] => 2 940
																				  [number] => 2940
																				  [cop] => 294000
																				  [zac] => Array
																						(
																						)
  
																			 )
  
																		[photos] => Array
																			 (
																				  [0] => Array
																						(
																							 [id] => 11628
																							 [product_id] => 13818
																							 [photo_name] => kolco-iz-krasnogo-zolota-s-sapfirom_12362-102_13818.jpg
																							 [define] => 1
																						)
  
																			 )
  
																		[linkPath] => /catalog/kolca/kolco-iz-krasnogo-zolota-s-sapfirom_12362-102_13818
																		[salePrice] => Array
																			 (
																			 )
  
																		[emptyPrice] => Array
																			 (
																				  [id] => 1
																				  [title] => Цена пока недоступна
																			 )
  
																  )
  
														)
  
												  [2] => Array
														(
															 [id] => 14874
															 [aliase] => kolco-iz-krasnogo-zolota_1-00088_14874
															 [articul] => 1-00088
															 [title] => Кольцо из красного золота
															 [salle_procent] => 0
															 [prices_empty] => 1
															 [filters] => [{"item":"metall","values":["krasnZoloto"]},{"item":"kamen","values":["empty"]},{"item":"forma_vstavki","values":[]},{"item":"sex","values":["woman"]},{"item":"size","values":["16_5"]}]
															 [modules] => Array
																  (
																		[price_actual] => Array
																			 (
																				  [format] => 2 500
																				  [number] => 2500
																				  [cop] => 250000
																				  [zac] => Array
																						(
																						)
  
																			 )
  
																		[photos] => Array
																			 (
																				  [0] => Array
																						(
																							 [id] => 12486
																							 [product_id] => 14874
																							 [photo_name] => kolco-iz-krasnogo-zolota_1-00088_14874.jpg
																							 [define] => 1
																						)
  
																			 )
  
																		[linkPath] => /catalog/kolca/kolco-iz-krasnogo-zolota_1-00088_14874
																		[salePrice] => Array
																			 (
																			 )
  
																		[emptyPrice] => Array
																			 (
																				  [id] => 1
																				  [title] => Цена пока недоступна
																			 )
  
																  )
  
														)
  
											 )
  
										[option] => Array
											 (
												  [pag] =>
											 )
  
								  )
  
						)
  
		
		*/
		
	}

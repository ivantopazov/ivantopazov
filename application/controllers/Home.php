<?php


defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {
    
    protected $user_info = array();
    protected $store_info = array();
    
    protected $post = array();
    protected $get = array();
	
    public function __construct() {
        parent::__construct();
        
		$this->user_info = ( $this->mdl_users->user_data() )? $this->mdl_users->user_data() : false;
        $this->store_info = $this->mdl_stores->allConfigs();
        
        $this->post = $this->security->xss_clean($_POST);
        $this->get = $this->security->xss_clean($_GET);
        
    }
    
    public function index(){
    
//    	error_reporting(E_ALL);
//		ini_set('display_errors', 1);
    	$start = microtime(true);
     
		$title = ( !empty( $this->store_info['seo_title'] ) ) ? $this->store_info['seo_title'] : $this->store_info['header'];
		$page_var = 'home';
        
        $this->mdl_tpl->view( 'templates/doctype_home.html' , array(
        
            'title' => $title,
            'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
            'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'),
            'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
            
            'seo' => $this->mdl_tpl->view('snipets/seo_tools.html',array(
                'mk' => ( !empty( $this->store_info['seo_keys'] ) ) ? $this->store_info['seo_keys'] : '',
                'md' => ( !empty( $this->store_info['seo_desc'] ) ) ? $this->store_info['seo_desc'] : '',
                'oggMetta' => [
                  "title" => "Ювелирный интернет-магазин в Москве",
                  "url" => "https://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"],
                  "image" => "https://zolotomo.ru/templates/basic/images/event-4.jpg",
                  "site_name" => 'Ювелирная группа компаний («Монарх», «Настоящее золото»)',
                  "description" => ( !empty( $this->store_info['seo_desc'] ) ) ? $this->store_info['seo_desc'] : ''
                ]
            ), true),
            
            'navTop' => $this->mdl_tpl->view('snipets/navTop.html',array(
                'store' => $this->store_info,
                'active' => 'home',
                'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path')
            ),true),
            
            'header' => $this->mdl_tpl->view('snipets/header.html',array(
                'store' => $this->store_info,
                'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path')
            ),true),
            
            'navMenu' => $this->mdl_tpl->view('snipets/navMenu.html',array(
                'store' => $this->store_info,
                'active' => 'home',
                'itemsTree' => $this->mdl_category->getTreeMenu(),
                'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path')
            ),true),
            
            'slider' => $this->mdl_tpl->view('pages/home/home_slider.html',array(
                'items' => $this->getSliderHome( false ),
                'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path')
            ), true ),
            
            'content' => "", //$this->mdl_tpl->view('pages/home/basic.html',array(),true),
            
            'h1Main' => $this->mdl_tpl->view('snipets/h1Main.html',array(
				'h1' => 'Ювелирный интернет-магазин - дешевле купить украшения онлайн!',
				'text' => 'Ювелирная группа компаний («Монарх», «Настоящее золото») — магазин онлайн покупок золотых украшений. Более 50 000 ювелирных изделий в наличии и под заказ. Скидки и акции на покупку ювелирных украшений. Мы работаем только с известными российскими и зарубежными брендами. Все изделия прошли проверку качества и соответствия пробе. Серьги, кольца, браслеты, подвески и другие украшения по низким ценам. '
			),true),

            'h2Main' => $this->mdl_tpl->view('snipets/h2Main.html',array(
				'h2' => 'Купите украшения на сайте с бесплатной доставкой',
				'text' => 'Закажите доставку до двери, она бесплатна! Если украшение вам не понравится, вы просто его возвращаете курьеру. На все изделия распространяется гарантия производителя. Покупать украшения через интернет выгодно и удобно!',
				'h2Second' => 'Ювелирные салоны - примерьте золотые украшения.',
				'textSecond' => 'Примерка осуществляется в наших ювелирных магазинах, в точках выдачи или прямо на дому. Покупатель может заказать до 3х украшений одного вида на выбор. Если ни одно из украшений не подошло, вы ни за что не платите.'
			),true),

            'actionsBlocks' => $this->mdl_tpl->view('snipets/actionsBlocks.html',array(),true),
            
            'novyePostuplenia' => $this->mdl_tpl->view('snipets/novyePostuplenia.html',array(
                'items' => $this->getNovyePostuplenia( false )
            ),true),
            
            /*'lideryProdazh' => $this->mdl_tpl->view('snipets/lideryProdazh.html',array(
                'items' => $this->getLideryProdaj( false )
            ),true),*/
            
            /*'popularnyeTovary' => $this->mdl_tpl->view('snipets/popularnyeTovary.html',array(
                'items' => $this->popularnyeTovary( false )
            ),true),*/
            
            'preimushchestva' => $this->mdl_tpl->view('snipets/preimushchestva.html',array(
                'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path')
            ),true),
            
            'brandsList' => $this->mdl_tpl->view( 'snipets/brandsList.html', array(), true ),
            
            'footer' => $this->mdl_tpl->view('snipets/footer.html',array(
                'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path')
            ), true),
            
            'load' => $this->mdl_tpl->view('snipets/load.html',array(
                'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
                'utmLabels' => $this->mdl_tpl->view('snipets/utmLabels.html', $this->mdl_seo->utmLabels( $this->get ), true )
            ),true),
            
            'resorses' => $this->mdl_tpl->view('resorses/home/head.html',array(
                'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
                'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path')
            ),true)
            
        ), false);
        
        // echo '<p style="background: yellow none repeat scroll 0% 0%; margin: 20px 0px 0px; position: fixed; bottom: 0px;">Время выполнения скрипта: '.(microtime(true) - $start).' сек.</p>';
        
        
    }
    
    // Получить разделы для главной страницы
    public function getSliderHome( $j = true ){
        
        
        $r_0 = $this->mdl_baners->queryData([
            'where' => [
                'method' => 'AND',
                'set' => [[
                    'item' => 'position',
                    'value' => 'homeSlider'
                ],[
                    'item' => 'date_start',
                    'value' => '0'
                ],[
                    'item' => 'date_end',
                    'value' => '0'
                ],[
                    'item' => 'view >',
                    'value' => '0'
                ]]
            ],
            'order_by' => [
                'item' => 'wes',
                'value' => 'ASC'
            ],
            'labels' => [ 'image', 'link' ]
        ]);
        
        $r_1 = $this->mdl_baners->queryData([
            'where' => [
                'method' => 'AND',
                'set' => [[
                    'item' => 'position',
                    'value' => 'homeSlider'
                ],[
                    'item' => 'date_start >',
                    'value' => time()
                ],[
                    'item' => 'date_end <',
                    'value' => time()
                ],[
                    'item' => 'view >',
                    'value' => '0'
                ]]
            ],
            'labels' => [ 'image', 'link' ]
        ]);
        
        $r = array_merge( $r_0, $r_1 );
        
        if( $j === true ){
            $this->mdl_helper->__json( $r );
        }else{
            return $r;
        }
        
    }
    
    // Последние поступления
    public function getNovyePostuplenia( $j = true ){
        
        $r = $this->mdl_product->queryData([
            'return_type' => 'ARR2',
            //'table_name' => '___',
            'where' => [
                'method' => 'AND',
                'set' => [[
                    'item' => 'view >',
                    'value' => 0
                ],[
                    'item' => 'cat >',
                    'value' => 0
                ],[
                    'item' => 'qty >',
                    'value' => 0
                ],[
                    'item' => 'moderate >',
                    'value' => 1
                ]]
            ],
            'limit' => 4,
            'order_by' => [
                'item' => 'id',
                'value' => 'DESC'
            ],
            'group_by' => 'articul',
            'distinct' => true,
            'labels' => ['id', 'aliase', 'title', 'salle_procent', 'modules'],
            'module_queue' => [
                 'limit', 'price_actual',  'salePrice', 'photos'
            ],
            'module' => true,
            'modules' => [[
                'module_name' => 'price_actual',
                'result_item' => 'price_actual',
                'option' => [
                    'labels' => false
                ]
            ],[
                'module_name' => 'salePrice',
                'result_item' => 'salePrice',
                'option' => []
            ],[
                'module_name' => 'linkPath',
                'result_item' => 'linkPath',
                'option' => []
            ],[
                'module_name' => 'photos',
                'result_item' => 'photos',
                'option' => [
                    'no_images_view' => 1
                ]
            ]]
        ]);
        
        if( $j === true ){
            $this->mdl_helper->__json( $r );
        }else{
            return $r;
        }
        
    }
    
    // Лидеры продаж
    public function getLideryProdaj( $j = true ){
   
       error_reporting(-1);
       ini_set('display_errors', 1);
        
        $r = $this->mdl_product->queryData([
            'return_type' => 'ARR2',
            'where' => [
                'method' => 'AND',
                'set' => [[
                    'item' => 'view >',
                    'value' => 0
                ],[
                    'item' => 'cat >',
                    'value' => 0
                ],[
                    'item' => 'qty >',
                    'value' => 0
                ],[
                    'item' => 'moderate >',
                    'value' => 1
                ]]
            ],
            'limit' => 4,
            'order_by' => [
                'item' => 'id',
                'value' => 'RANDOM'
            ],
            'labels' => ['id', 'aliase', 'title', 'salle_procent', 'modules'],
            'module' => true,
            'modules' => [[
                'module_name' => 'price_actual',
                'result_item' => 'price_actual',
                'option' => [
                    'labels' => false
                ]
            ],[
                'module_name' => 'salePrice',
                'result_item' => 'salePrice',
                'option' => []
            ],[
                'module_name' => 'linkPath',
                'result_item' => 'linkPath',
                'option' => []
            ],[
                'module_name' => 'photos',
                'result_item' => 'photos',
                'option' => [
                    'no_images_view' => 1
                ]
            ]]
        ]);
        
        if( $j === true ){
            $this->mdl_helper->__json( $r );
        }else{
            return $r;
        }
        
    }
    
    // Популярные товары
    public function popularnyeTovary( $j = true ){
   
       error_reporting(-1);
       ini_set('display_errors', 1);
        
        $r = $this->mdl_product->queryData([
            'return_type' => 'ARR2',
            'where' => [
                'method' => 'AND',
                'set' => [[
                    'item' => 'view >',
                    'value' => 0
                ],[
                    'item' => 'cat >',
                    'value' => 0
                ],[
                    'item' => 'qty >',
                    'value' => 0
                ],[
                    'item' => 'moderate >',
                    'value' => 1
                ]]
            ],
            'limit' => 4,
            'order_by' => [
                'item' => 'view',
                'value' => 'DESC'
            ],
            'labels' => ['id', 'aliase', 'title', 'salle_procent', 'modules'],
            'module' => true,
            'modules' => [[
                'module_name' => 'price_actual',
                'result_item' => 'price_actual',
                'option' => [
                    'labels' => false
                ]
            ],[
                'module_name' => 'salePrice',
                'result_item' => 'salePrice',
                'option' => []
            ],[
                'module_name' => 'linkPath',
                'result_item' => 'linkPath',
                'option' => []
            ],[
                'module_name' => 'photos',
                'result_item' => 'photos',
                'option' => [
                    'no_images_view' => 1
                ]
            ]]
        ]);
        
        if( $j === true ){
            $this->mdl_helper->__json( $r );
        }else{
            return $r;
        }
        
    }

    
}

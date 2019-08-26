<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Collections extends CI_Controller  {
    
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
        
        $this->load->model('mdl_collections');
        
    }
    
    // Предворительная обработка системными средствами
    public function _remap( $method, $params = array() ){
        if ( method_exists( $this, $method ) ){
            return call_user_func_array(array($this, $method), $params);
        }else{
            return call_user_func_array(array($this, "ExtractTree"), func_get_args() );
        }
    }
    
    // Серверное извлечение всех алиасов из URL
    private function ExtractTree(){ 
        
        $argList = func_get_args();
        $arg_list = ( $argList !== false ) ? $argList : array();
        $item = ( count( $arg_list[1] ) > 0 ) ? $arg_list[1][(count( $arg_list[1] ) - 1)] : $arg_list[0];
        $tree = array();
        $tree[] = $arg_list[0];
        foreach( $arg_list[1] as $v ) $tree[] = $v;
        
        // Передача алиасов и получение информации и инструкций...
        $logicData = $this->getLogicData( $tree, false );
        
        if( $logicData['error404'] !== true ){
            $variable = 'view_'.$logicData['method'];
            self::$variable( $logicData );
        }else{
            show_404();
        }
        
    }    
    
    // Получение информации и инструкций [ сервер / json ]
    public function getLogicData( $tree = array(), $j = true ){
        
        $tree = ( isset( $this->post['tree'] ) ) ? $this->post['tree'] : $tree ;        
        $r = [ 
            'error404' => true, 
            'brb' => [
                [ 
                    'name' => 'Коллекции', 
                    'link' => '/collections' 
                ]
            ]
        ];
        $lastAliase = $tree[count($tree)-1];
        
        if( count( $tree ) > 0 ){
            
            $colItem = $this->mdl_collections->queryData([
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
                        'limit' => ( isset( $this->get['limit'] ) ) ? $this->get['limit'] : 42,
                        'page' => ( isset( $this->get['page'] ) ) ? $this->get['page'] : 1
                    ]
                ]]
            ]);
            
            if( count( $colItem ) > 0 ){
                /*array_pop( $tree );                
                $r['brb'][] = array(
                    'name' => $colItem[0]['title'],
                    'link' => '/collections/' . $colItem[0]['aliase']
                );  */               
                $r['method'] = 'collection';
                $r['item'] = $colItem[0];
                $r['error404'] = false;                
            }else{ 
                $r['method'] = 'collection';
                $r['item'] = [];
                $r['error404'] = true;  
            }
        }
        
        if( $j === true ){
            $this->mdl_helper->__json( $r );
        }else{
            return $r;
        }
        
    }
    
    // Вывести главную страницу со списком коллекций
    public function index(){
        
        $start = microtime(true); 
         
        $title = 'Список коллекций';
        $page_var = 'collections';
        
        $this->mdl_tpl->view( 'templates/doctype_catalog.html' , array(
        
            'title' => $title,
            
            'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
            'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'), 
            'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'), 
            
            'seo' => $this->mdl_tpl->view('snipets/seo_tools.html',array(
                'mk' => ( isset( $data['item']['key'] ) ) ? $data['item']['key'] : '',
                'md' => ( isset( $data['item']['desc'] ) ) ? $data['item']['desc'] : ''
            ), true),
            
            'navTop' => $this->mdl_tpl->view('snipets/navTop.html',array(
                'store' => $this->store_info,
                'active' => $page_var,
                'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path')
            ),true),
            
            'header' => $this->mdl_tpl->view('snipets/header.html',array(
                'store' => $this->store_info,
                'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path')
            ),true),
            
            'navMenu' => $this->mdl_tpl->view('snipets/navMenu.html',array(
                'store' => $this->store_info,
                'active' => $page_var,
                'itemsTree' => $this->mdl_category->getTreeMenu(),
                'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path')
            ),true),
                                   
            'content' => $this->mdl_tpl->view('pages/collections/home_collections.html',array(
                'items' => $this->get_collections( false ),
                'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path')
            ),true),
            
            'footer' => $this->mdl_tpl->view('snipets/footer.html',array(
                'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path')
            ), true),
            
            'load' => $this->mdl_tpl->view('snipets/load.html',array(
                'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
                'utmLabels' => $this->mdl_tpl->view('snipets/utmLabels.html', $this->mdl_seo->utmLabels( $this->get ), true )
            ),true),
            
            'resorses' => ''/*$this->mdl_tpl->view('resorses/catalog/cats_head.html',array(
                'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
                'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'),
                'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path')
            ),true)*/
            
        ), false);
        
        //echo '<p style="background: yellow none repeat scroll 0% 0%; margin: 20px 0px 0px; position: fixed; bottom: 0px;">Время выполнения скрипта: '.(microtime(true) - $start).' сек.</p>'; 
        
        
    }
    
    // Список коллекций
    public function get_collections( $j = true ){
        
        $r = $this->mdl_collections->queryData([
            'where' => [
                'method' => 'AND',
                'set' => [[
                    'item' => 'status_view >',
                    'value' => '0'
                ]]
            ],
            'labels' => ['id', 'title', 'aliase', 'image']
        ]);
        
        if( $j === true ){
            $this->mdl_helper->__json( $r );
        }else{
            return $r;
        }
        
    }
    
    // Просмотр коллекции
    public function view_collection ( $data ){
        
        $start = microtime(true); 
         
		//$title = ( isset( $data['item']['seo_title'] ) && !empty( $data['item']['seo_title'] ) ) ? $data['item']['seo_title'] : $data['item']['title'];
		
		$title = ( isset( $data['item']['seo_title'] ) && !empty( $data['item']['seo_title'] ) ) ? $data['item']['seo_title'] : $data['item']['title'];
		$page_var = 'collections'; 
        $this->mdl_tpl->view( 'templates/doctype_catalog.html' , array(
        
            'title' => $title,
            
            'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
            'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'), 
            'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'), 
            
            'seo' => $this->mdl_tpl->view('snipets/seo_tools.html',array(
                'mk' => ( isset( $data['item']['key'] ) ) ? $data['item']['key'] : '',
                'md' => ( isset( $data['item']['desc'] ) ) ? $data['item']['desc'] : ''
            ), true),
            
            'navTop' => $this->mdl_tpl->view('snipets/navTop.html',array(
                'store' => $this->store_info,
                'active' => $page_var,
                'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path')
            ),true),
            
            'header' => $this->mdl_tpl->view('snipets/header.html',array(
                'store' => $this->store_info,
                'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path')
            ),true),
            
            'navMenu' => $this->mdl_tpl->view('snipets/navMenu.html',array(
                'store' => $this->store_info,
                'active' => $page_var,
                'itemsTree' => $this->mdl_category->getTreeMenu(),
                'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path')
            ),true),
                       
            'breadcrumb' => $this->mdl_tpl->view('snipets/breadcrumb.html',array(
                'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
                'title' => $data['item']['title'],
                'array' => $data['brb']
            ),true), 
            
            'content' => $this->mdl_tpl->view('pages/collections/view_collection.html',array(
                'item' => $data['item'],
                'products' => $this->mdl_tpl->view('pages/collections/items.html',array(
                    'items' => $data['item']['modules']['Products']['result']
                ),true),
                'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
            ),true),
			
            'footer' => $this->mdl_tpl->view('snipets/footer.html',array(
                'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path')
            ), true),
            
            'load' => $this->mdl_tpl->view('snipets/load.html',array(
                'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
                'utmLabels' => $this->mdl_tpl->view('snipets/utmLabels.html', $this->mdl_seo->utmLabels( $this->get ), true )
            ),true),
            
            'resorses' => ''/*$this->mdl_tpl->view('resorses/catalog/cats_head.html',array(
                'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
                'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'),
                'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path')
            ),true)*/
            
        ), false);
        
        //echo '<p style="background: yellow none repeat scroll 0% 0%; margin: 20px 0px 0px; position: fixed; bottom: 0px;">Время выполнения скрипта: '.(microtime(true) - $start).' сек.</p>';         
        
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}

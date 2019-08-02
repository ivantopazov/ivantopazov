<?php
    
defined('BASEPATH') OR exit('No direct script access allowed');

class Tracker extends CI_Controller {
    
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
        
        $start = microtime(true); 
        
        $tracker = $this->getOrderTrack( ( isset( $this->get['code'] ) ) ? $this->get['code'] : false, false );
        
		$title = 'Отслеживание заказа';
		$page_var = 'home';
        
        $this->mdl_tpl->view( 'templates/doctype_home.html' , array(
        
            'title' => $title,
            'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
            'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'), 
            'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'), 
            
            'seo' => $this->mdl_tpl->view('snipets/seo_tools.html',array(
                'mk' => ( !empty( $this->store_info['seo_keys'] ) ) ? $this->store_info['seo_keys'] : '',
                'md' => ( !empty( $this->store_info['seo_desc'] ) ) ? $this->store_info['seo_desc'] : ''
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
            
            'content' => $this->mdl_tpl->view('pages/tracker/basic.html', array(
                'info' => $tracker['info'],
                'history' => $tracker['history'],
                'query' => $tracker['query']
            ),true),
            
            'preimushchestva' => $this->mdl_tpl->view('snipets/preimushchestva.html',array(
                'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path')
            ),true),
            
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
        
        //echo '<p style="background: yellow none repeat scroll 0% 0%; margin: 20px 0px 0px; position: fixed; bottom: 0px;">Время выполнения скрипта: '.(microtime(true) - $start).' сек.</p>'; 
        
        
    }
    
    //
    public function getOrderTrack( $traker = false, $j = true ){
                
        $traker = ( isset( $this->get['code'] ) ) ? $this->get['code'] : $traker;
        
        $r = [
            'query' => $traker,
            'info' => [],
            'history' => []
        ];
        
        if( $traker !== false ){
            
            $r['info'] = $this->mdl_orders->queryData([
                'return_type' => 'ARR1',
                'where' => [
                    'method' => 'AND',
                    'set' => [[
                        'item' => 'traker',
                        'value' => $traker
                    ]]
                ],
                'labels' => [ 'id', 'traker', 'user_id', 'adress', 'summa', 'date', 'modules' ],
                'module' => true,
                'modules' => [[
                    'module_name' => 'getStatus',
                    'result_item' => 'getStatus',
                    'option' => []
                ],[
                    'module_name' => 'getProductList',
                    'result_item' => 'ProductList',
                    'option' => []
                ],[
                    'module_name' => 'getUser',
                    'result_item' => 'user',
                    'option' => []
                ]]
            ]);
            
            if( $r['info'] ){
                
                $r['history'] = $this->mdl_orders->queryData([
                    'return_type' => 'ARR2',
                    'where' => [
                        'method' => 'AND',
                        'set' => [[
                            'item' => 'user_id',
                            'value' => $r['info']['user_id']
                        ]]
                    ],
                    'order_by' => [
                        'item' => 'time',
                        'value' => 'DESC'
                    ],
                    'labels' => [ 'id', 'traker', 'adress', 'date', 'summa', 'modules' ],
                    'module' => true,
                    'modules' => [[
                        'module_name' => 'getStatus',
                        'result_item' => 'getStatus',
                        'option' => []
                    ],[
                        'module_name' => 'getProductList',
                        'result_item' => 'ProductList',
                        'option' => []
                    ],[
                        'module_name' => 'getUser',
                        'result_item' => 'user',
                        'option' => []
                    ]]
                ]);
                
            }
            
            
        }
        
        if( $j === true ){
            $this->mdl_helper->__json( $r );
        }else{
            return $r;
        }
        
    }
    
    
}

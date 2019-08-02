<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Import_products extends CI_Controller {
        
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
        
        
        if( $this->mdl_helper->get_cookie('HASH') !== $this->mdl_users->userHach() ){
            $this->user_info['admin_access'] = 0;
        } 
    }
    
    // Защита прямых соединений
	public function access_static(){
		if( $this->user_info !== false ){
            if( $this->user_info['admin_access'] < 1 ){
                redirect( '/login' );
            }
        }
	}
    
    // Защита динамических соединений
	public function access_dynamic(){
		if( $this->user_info !== false ){
            if( $this->user_info['admin_access'] < 1 ){
                exit('{"err":"1","mess":"Нет доступа"}');
            }
        }
	}
    
    // Показать страницу по умолчанию
    public function index(){
        
        $this->access_static();  
        
		$title = 'Импорт товаров';
		$page_var = 'import_products';
        
        $this->mdl_tpl->view( 'templates/doctype_admin.html' , array(
        
            'title' => $title,
            'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
            'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'), 
            
            'seo' => $this->mdl_tpl->view('snipets/seo_tools.html',array(
                'mk' => ( !empty( $this->store_info['seo_keys'] ) ) ? $this->store_info['seo_keys'] : '',
                'md' => ( !empty( $this->store_info['seo_desc'] ) ) ? $this->store_info['seo_desc'] : ''
            ), true),
            
            'nav' => $this->mdl_tpl->view('snipets/admin_nav.html',array(
                'active' => $page_var
            ),true),
            
            'breadcrumb' => $this->mdl_tpl->view('snipets/breadcrumb.html',array(
                'title' => $title,
                'array' => [[
                    'name' => 'Панель управления',
                    'link' => '/admin'
                ]]
            ),true),
            
            'content' => $this->mdl_tpl->view('pages/admin/catalog/import_products/basic.html',array(),true),
            
            'load' => $this->mdl_tpl->view('snipets/load.html',array(
                'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder')
            ),true),
            
            'resorses' => $this->mdl_tpl->view( 'resorses/admin/catalog/import_products/head.html', array(
                'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path')               
			), true )
            
        ), false);
        
    }
    
    public function run_import(){
        
        $this->access_dynamic();
        /*
        $this->post['pack'] = [[
            'articul' => 37,
            'title' => 't1'
        ],[
            'articul' => 20,
            'title' => 't2'
        ]];
        */
        $packs = [];
        $packs = $this->mdl_helper->clear_array( $this->post['pack'], [
            'articul', 'title', 'qty', 'size', 'proba', 'weight', 
            'price', 'cat', 'seo_title', 'seo_desc', 'seo_keys', 'shoop'
        ]);
        
        $listArts = []; $err = 0;
        foreach( $packs as $v ){
            if( $v['articul'] ){
                //$art = $this->mdl_product->code_format( $v['articul'], 6 );
                if( !in_array( $v['articul'], $listArts ) ){
                    $listArts[] = $v['articul'];
                } 
            }
        }
        
        if( count( $listArts ) > 0 ){
        
            $r = $this->mdl_product->queryData([
                'type' => 'ARR2',
                'in' => [
                    'method' => 'AND',
                    'set' => [[
                        'item' => 'articul',
                        'values' => $listArts
                    ]]
                ],
                'labels' => ['id', 'aliase', 'title', 'articul']
            ]);
            
            $listArtsIsset = [];
            foreach( $r as $v ){
                $art = $this->mdl_product->code_format( $v['articul'], 6 );
                if( !in_array( $art, $listArtsIsset ) ){
                    $listArtsIsset[] = $art;
                } 
            }
            
            foreach( $packs as $pk => $pv ){
                foreach( $r as $rv ){
                    if( $pv['articul'] === $rv['articul'] ){
                        $packs[$pk]['id'] = $rv['id'];   
                    }
                }
            }
            
            foreach( $packs as $v ){
                
                if( isset( $v['id'] ) ){
                    
                    // Обновление
                    
                    $update_id = $v['id'];
                
                    $updateProduct = [];
                        if( isset( $v['qty'] ) ) $updateProduct['qty'] = $v['qty'];
                        if( isset( $v['size'] ) ) $updateProduct['size'] = $v['size'];
                        if( isset( $v['proba'] ) ) $updateProduct['proba'] = $v['proba'];
                        if( isset( $v['weight'] ) ) $updateProduct['weight'] = $v['weight'];
                        if( isset( $v['cat'] ) ) $updateProduct['cat'] = $v['cat'];
                        if( isset( $v['seo_title'] ) ) $updateProduct['seo_title'] = $v['seo_title'];
                        if( isset( $v['seo_desc'] ) ) $updateProduct['seo_desc'] = $v['seo_desc'];
                        if( isset( $v['seo_keys'] ) ) $updateProduct['seo_keys'] = $v['seo_keys'];
                        if( isset( $v['shoop'] ) ) $updateProduct['shoop'] = $v['shoop'];
                        if( isset( $v['price'] ) ) $updateProduct['price_zac'] = $v['price'];
                        
                    $this->mdl_db->_update_db( "products", "id", $update_id, $updateProduct );
                    
                    $ret = $this->mdl_product->getRozCena( $update_id );
                    if( $ret['price_r'] !== 'МИНУС' ){
                        $end = $ret['price_r'] * 100;
                        $updProd['price_roz'] = $end;
                        $updProd['salle_procent'] = $ret['procSkidca'];
                    }
                    $this->mdl_db->_update_db( "products", "id", $update_id, $updProd );
                    
                    
                    
                }else{
                    //добавление
                    if( $this->post['method'] > 0 ){
                        
                        $this->db->insert( 'products', [
                            'title' => 'Новый товар',
                            'view' => 0
                        ]);
                        
                        $insId = $this->db->insert_id();
                        
                        $updateProduct = [];
                            if( isset( $v['title'] ) ){
                                $updateProduct['title'] = $v['title'];
                                $updateProduct['aliase'] = $this->mdl_product->aliase_translite( $insId . '_' .$v['title'] );                           
                            } 
                            if( isset( $v['articul'] ) ) $updateProduct['articul'] = $v['articul'];
                            if( isset( $v['qty'] ) ) $updateProduct['qty'] = $v['qty'];
                            if( isset( $v['size'] ) ) $updateProduct['size'] = str_replace( ",", ".", $v['size'] );
                            if( isset( $v['proba'] ) ) $updateProduct['proba'] = $v['proba'];
                            if( isset( $v['weight'] ) ) $updateProduct['weight'] = str_replace( ",", ".", $v['weight']);
                            if( isset( $v['cat'] ) ) $updateProduct['cat'] = $v['cat'];
                            if( isset( $v['seo_title'] ) ) $updateProduct['seo_title'] = $v['seo_title'];
                            if( isset( $v['seo_desc'] ) ) $updateProduct['seo_desc'] = $v['seo_desc'];
                            if( isset( $v['seo_keys'] ) ) $updateProduct['seo_keys'] = $v['seo_keys'];
                            if( isset( $v['price'] ) ) $updateProduct['price_roz'] = $v['price'];
                            if( isset( $v['shoop'] ) ) $updateProduct['shoop'] = $v['shoop'];
                        
                        $this->mdl_db->_update_db( "products", "id", $insId, $updateProduct );
                        
                        
                    }
                    
                }
            }
            
            
        }
        
        $this->mdl_helper->__json( [ 'err' => 0 ] );
        
    }
   
}
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cats extends CI_Controller {
    
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
    
    // Страница основная для работы с категориями
    public function index(){
        
        $this->access_static();  
        
		$title = 'Категории';
		$page_var = 'catalog';
        
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
                ],[
                    'name' => 'Ассортимент',
                    'link' => '/admin/catalog'
                ]]
            ),true),
            
            'content' => $this->mdl_tpl->view('pages/admin/catalog/basic.html', array(), true),
            
            'load' => $this->mdl_tpl->view('snipets/load.html',array(
                'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder')
            ),true),
            
            'resorses' => $this->mdl_tpl->view( 'resorses/admin/catalog/cats/treeList.html', array(
                'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path')               
			), true )
            
        ), false);
                
    }
    
    // Загрузка основных родительских страниц
    public function getStoreCatsData( $parent_node = 0,  $json = true ){
        
        $PID = ( isset( $this->post['parent_node'] ) ) ? $this->post['parent_node'] : $parent_node;
        
        $r = $this->mdl_category->queryData([
            'return_type' => 'ARR2',
            'where' => [
                'method' => 'AND',
                'set' => [[
                   'item' => 'parent_id', 
                   'value' => $PID 
                ]]
            ],
            'labels' => [ 'id', 'name', 'aliase', 'image', 'parent_id', 'seo_desc', 'seo_title', 'seo_keys', 'description' ]
        ]);
        
        $n = [];
        foreach( $r as $k => $v ){
            $n[] = [
                'id' => $v['id'],
                'text' => $v['name'],
                'children' => true,
                'labels' => $v
            ];
        }
        $n = ( $PID === '0' ) ? ['text' => 'Родительская категория','children' => $n] : $n;
        
        if( $json !== true ){
            return $n;
        }else{
            $this->mdl_helper->__json( $n );
        }
        
    }
    
   // Добавление новой категории
    public function addChild( $json = true ){
        
        $r = [ 'err' => 1, 'mess' => 'Категория не добавилась' ];
        $labels = ['name', 'image', 'aliase', 'parent_id', 'seo_desc', 'seo_title', 'seo_keys', 'description'];
        $na = $this->mdl_helper->clear_array_0( $this->post, $labels );        
        if( isset( $na['name'] ) && isset( $na['aliase'] ) ){            
            if( isset( $na['image'] ) ){
                @rename( "./uploads/cats/temp/" .$na['image'], "./uploads/cats/" . $na['image'] );
            }                   
            $this->mdl_category->create_category( $na );
            $r = [ 'err' => 0, 'mess' => 'Категория добавилась успешно' ];            
        }
        
        if( $json !== true ){
            return $r;
        }else{
            $this->mdl_helper->__json( $r );
        }
        
    }
    
    // Обновление информации о категории
    public function editChild( $CID = false, $json = true ){
        
        $CID = ( isset( $this->post['cid'] ) ) ? $this->post['cid'] : $CID;
        $r = [ 'err' => 1, 'mess' => 'Категория не обновилась' ];
        
        if( $CID !== false ){
            
            $labels = ['name', 'aliase', 'image', 'parent_id', 'seo_desc', 'seo_title', 'seo_keys', 'description'];
            $na = $this->mdl_helper->clear_array_0( $this->post, $labels );
                 
            $item = $this->mdl_category->update_category( $CID, $na );    

            if( $item !== false ){
                $r = [ 'err' => 0, 'mess' => 'Категория обновилась успешно' ];
            }
            
        }
        if( $json !== true ){
            return $r;
        }else{
            $this->mdl_helper->__json( $r );
        }
        
    }
    
    // Удаляет категорию
    public function removeChild( $CID = false, $json = true ){
        
        $CID = ( isset( $this->post['cid'] ) ) ? $this->post['cid'] : $CID;
        $r = ['err'=>1,'mess'=>'Категория не удалилась, возможно необходимо прежде удалить дочерние категории'];

        
        if( $CID !== false ){ 
            $item = $this->mdl_category->remove_category( $CID );             
            if( $item ){
                $r = [ 'err' => 0, 'mess' => 'Категория успешно удалена' ];
            }
        }
        if( $json !== true ){
            return $r;
        }else{
            $this->mdl_helper->__json( $r );
        }
        
    }
    
    
    // Форма редактирования фотографий категории
    public function actAddCategoryImages(){
        
        $this->access_dynamic();        
        $this->load->library('images'); 
        
        $_index = 'images';
		$_output_dir = "./uploads/cats/temp/";
		
		if( isset( $_FILES[ $_index ] ) ){        	
        	$error = $_FILES[ $_index ]["error"];
        	if( !is_array($_FILES[ $_index ]["name"]) ){	
			
				$FILES = '';
				$this->load->helper("string");
				$new_name = random_string('alnum', 6)."_".time();
				$FILES[ $_index ] = $this->images->files_array( $_FILES, $_index, $new_name );

				$fileName = $FILES[ $_index ]["name"];
					
				$if_move = move_uploaded_file( $FILES[ $_index ]["tmp_name"], $_output_dir.$fileName );
				if( $if_move ){
                    
                    $r = array( 'err' => 0, 'mess' => 'Файл успешно загружен!', 'response' => $fileName );
                    
				}else{
					$r = array( 'err' => 1, 'mess' => 'System error ( 003 ) !' );
				}
        	}else{
				$r = array( 'err' => 1, 'mess' => 'System error ( 002 ) !' );
			}
        }else{
			$r = array( 'err' => 1, 'mess' => 'System error ( 001 ) !' );
		}
		
		$this->mdl_helper->__json( $r );
        
        
    }
    
    // Форма редактирования фотографий категории
    public function actEditCategoryImages(){
        
        $this->access_dynamic();        
        $this->load->library('images'); 
        
        $_index = 'images';
		$_output_dir = "./uploads/cats/";
		
		if( isset( $_FILES[ $_index ] ) ){        	
        	$error = $_FILES[ $_index ]["error"];
        	if( !is_array($_FILES[ $_index ]["name"]) ){	
			
				$FILES = '';
				$this->load->helper("string");
				$new_name = random_string('alnum', 6)."_".time();
				$FILES[ $_index ] = $this->images->files_array( $_FILES, $_index, $new_name );

				$fileName = $FILES[ $_index ]["name"];					
				$if_move = move_uploaded_file( $FILES[ $_index ]["tmp_name"], $_output_dir.$fileName );
				if( $if_move ){                    
                    $r = array( 'err' => 0, 'mess' => 'Файл успешно загружен!', 'response' => $fileName );  
                    if( $this->post['alt'] ){
                        unlink('./uploads/cats/' . $this->post['alt'] );
                    }                    
				}else{
					$r = array( 'err' => 1, 'mess' => 'System error ( 003 ) !' );
				}
                
        	}else{
				$r = array( 'err' => 1, 'mess' => 'System error ( 002 ) !' );
			}
        }else{
			$r = array( 'err' => 1, 'mess' => 'System error ( 001 ) !' );
		}
		
		$this->mdl_helper->__json( $r );
        
        
    }
    
    
}

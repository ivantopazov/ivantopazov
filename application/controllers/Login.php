<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {
    
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
        
    // КОнтроль авторизованности
    public function routed( $index = false ){
        
        if( $this->mdl_helper->get_cookie('HASH') === $this->mdl_users->userHach() ){          
            redirect('/admin'); 
        }else{
            if( $index !== true ) redirect('/login');
        }
    }
            
    public function index(){
        
        // $start = microtime(true); 
        
        $this->routed( true );
        
		$title = ( !empty( $this->store_info['seo_title'] ) ) ? $this->store_info['seo_title'] : $this->store_info['header'];
		$page_var = 'login';
        
        $this->mdl_tpl->view( 'templates/doctype_home.html' , array(
        
            'title' => $title,
            
            'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
            
            'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'), 
            'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'), 
            
            'seo' => $this->mdl_tpl->view('snipets/seo_tools.html',array(
                'mk' => '',
                'md' => ''
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
                        
            'content' => $this->mdl_tpl->view('pages/login/page_auth.html',array(
            
            ),true),
            
            'footer' => $this->mdl_tpl->view('snipets/footer.html',array(
                'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path')
            ), true),
            
            'load' => $this->mdl_tpl->view('snipets/load.html',array(
                'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
                'utmLabels' => $this->mdl_tpl->view('snipets/utmLabels.html', $this->mdl_seo->utmLabels( $this->get ), true )
            ),true),
            
            'resorses' => $this->mdl_tpl->view('resorses/login/home.html',array(
                'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
                'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path')
            ),true)
            
        ), false);
        
        //echo '<p style="background: yellow none repeat scroll 0% 0%; margin: 20px 0px 0px; position: fixed; bottom: 0px;">Время выполнения скрипта: '.(microtime(true) - $start).' сек.</p>'; 
        
        
    }
    
    public function auth(){
       
        $login = ( isset($this->post['login']) ) ? $this->post['login'] : false;
        $password = ( isset($this->post['password']) ) ? $this->post['password'] : false;
        $captcha = ( isset($this->post['captcha']) ) ? $this->post['captcha'] : false;
        
        if( $login === false or $password === false){
            $response['err'] = 1;
            $response['mess'] = 'Поля login и пароль пустые. Необходимо их заполнить.';
        }else{            
        
            //$cap = $this->mdl_helper->get_captcha('the_captcha');
            //if($captcha === $cap){
                ///$response = $this->mdl_users->auth( $login, $password ); 

                if( $login == 'admin' and $password == $this->mdl_users->pass ){
                    
                    $this->mdl_helper->set_cookie('HASH', $this->mdl_users->userHach() );
                    
                    $response['err'] = 0;
                    $response['mess'] = 'Авторизация успешно пройдена!'; 
                }else{
                    $response['err'] = 1;
                    $response['mess'] = 'Логин или пароль содержат ошибку.'; 
                }
 
           //}else{
            //    $response['err'] = 1;
            //    $response['mess'] = 'Не удалось подтвердить правильность цифр с картинки.'; 
            //}
        }        
        $this->mdl_helper->__json($response);	
    
        
    }
    
    // Удаляет куки авторизации ( Выход )
    public function logout (){
        $this->mdl_users->remove_all_cookie();
        redirect('/login');
    }
    
}

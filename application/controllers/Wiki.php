<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Wiki extends CI_Controller {
    
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
        
    // Предворительная обработка системными средствами
    public function _remap( $method, $params = array() ){
        if ( method_exists( $this, $method ) ){
            return call_user_func_array(array($this, $method), $params);
        }else{
            return call_user_func_array(array($this, "get_item"), $method );
        }
    }
    
    public function index(){
        
        $start = microtime(true); 
        
        
		$title = ( !empty( $this->store_info['seo_title'] ) ) ? $this->store_info['seo_title'] : $this->store_info['header'];
		$page_var = 'wiki';
        
        $this->mdl_tpl->view( 'templates/doctype_wiki.html' , array(
        
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
                        
            'content' => $this->mdl_tpl->view('pages/wiki/home.html',array(),true),
                        
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
    
    public function get_item( $f_name ){
        
        $path = './templates/';
        $path .= $this->mdl_stores->getConfig('template_select_name') . '/';
        $path .= 'views/pages/wiki/';
         
        if( file_exists( $path .  $f_name . '.html' )  ){
            
            $r = $this->mdl_category->queryData([
                'return_type' => 'ARR1',
                'where' => [
                    'method' => 'AND',
                    'set' => [[
                        'item' => 'view >',
                        'value' => 0
                    ],[
                        'item' => 'file_name',
                        'value' => $f_name
                    ]]
                ],
                'table_name' => 'wiki_pages'          
            ]);
            
            
            
            if( $r ){
                
                $start = microtime(true); 
            
                $title = ( !empty( $r['seo_title'] ) ) ? $r['seo_title'] : $r['title'];
                $page_var = 'wiki';
                
                $this->mdl_tpl->view( 'templates/doctype_wiki.html' , array(
                
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
                        'title' => $title,
                        'array' => [[
                            'name' => 'Информация',
                            'link' => '/wiki'
                        ]]
                    ),true),
            
                    'content' => $this->mdl_tpl->view('pages/wiki/basic.html',array(
                        'header' => $r['title'],
                        'html_block' => $this->mdl_tpl->view('pages/wiki/'.$f_name.'.html',array(
                            'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
                            'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path')
                        ),true)
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
                    
                    'resorses' => $this->mdl_tpl->view('resorses/wiki/head.html',array(
                        'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
                        'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path')
                    ),true)
                    
                ), false);
                
                //echo '<p style="background: yellow none repeat scroll 0% 0%; margin: 20px 0px 0px; position: fixed; bottom: 0px;">Время выполнения скрипта: '.(microtime(true) - $start).' сек.</p>'; 
                
            }else{
                show_404();
            } 
            
        }else{
            return call_user_func_array(array($this, $f_name), [] );
        }
        
    }
    
    public function act_certificatePay(){
        //[phone] => 78888888888
        //[type] => Подарочный сертификан на сумму 3000 рублей
        
        
        if( count( $this->post ) > 0 ){
                
            $name = ( isset( $this->post['name'] ) )  ?  $this->post['name'] : ' Не указано ';
            $phone = ( isset( $this->post['phone'] ) )  ?  $this->post['phone'] : ' Не указан ';
            $type = ( isset( $this->post['type'] ) )  ?  $this->post['type'] : ' Не указано ';
            
            $html_content = $this->mdl_tpl->view( 'email/wiki/cert_form.html', array( 
                'name' => $name,
                'phone' => $phone,
                'type' => $type,
                'ulmLabels' => $this->mdl_tpl->view( 'email/ulmLabels/labelItems.html', $this->mdl_seo->getUtmData(), true ),
                'date' => date('d.m.Y H.i')
            ), true ); 
        
            $this->load->model('mdl_mail');			
    		$this->mdl_mail->set_ot_kogo_from( 'order@ivantopazov.ru', 'IVAN TOPAZOV' );
    		//$this->mdl_mail->set_komu_to( 'korchma-kursk@yandex.ru', 'Покупатель');
    		$this->mdl_mail->set_tema_subject( $type. " от " . $phone ." на странице сертификатов - " . date('d.m.Y H:i:s') );
    		$this->mdl_mail->set_tema_message( $html_content );
    		//$this->mdl_mail->send();
			
    		$this->mdl_mail->set_komu_to( '2Kem@mail.ru', 'Покупатель');
			$this->mdl_mail->send();
			
            $this->mdl_mail->set_komu_to( 'ivantopazov@bk.ru', 'Покупатель');
    		$this->mdl_mail->send();
            
            $this->mdl_mail->set_komu_to( 'info.nikoniki@gmail.com', 'Покупатель');
    		$this->mdl_mail->send();
            
            //$t = urlencode('Новое обращение на сайте');
            //file_get_contents('http://sms.ru/sms/send?api_id=6E517E0A-4F5A-1796-752A-7C24A05F81DD&to=79103116249&text=' . $t );
            
            echo json_encode( array( 'err' => 0, 'mess' => 'Ваше заявка успешно отправленна!' ) );
       
        }else{
            
            echo json_encode([ 'err' => 1, 'mess' => 'Ошибка отправки данных' ]);
        }
        
        
    }
    
    
}

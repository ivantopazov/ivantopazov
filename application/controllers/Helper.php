<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Helper extends CI_Controller {
    
    protected $post = array();
    protected $get = array();
	
    protected $user_info = array();
    protected $store_info = array();
	
    public function __construct() {        
        parent::__construct();
        
		$this->user_info = ( $this->mdl_users->user_data() )? $this->mdl_users->user_data() : false; 
        $this->store_info = $this->mdl_stores->allConfigs();  
        
        $this->post = $this->security->xss_clean($_POST);
        $this->get = $this->security->xss_clean($_GET);	
	    
    }

	
	// Получение картинки - капчи
	public function get_captcha (){
		echo $this->mdl_helper->captcha('the_captcha');
	}
	
    /**
    * Получение шаблона
    **/
    public function get_tpl_data ( $tpl_name = false ){
		$tpl_name = ( isset( $this->post['tpl_name'] ) ) ? $this->post['tpl_name'] : $tpl_name ;
		if( $tpl_name ){
			$f_name = $this->config->item('config_tpl_path') . '/' . $tpl_name . '.html';
			if( file_exists( './' .  $f_name )  ){
                echo file_get_contents ( './' .  $f_name );
            }else{
                echo './' . $f_name;
            }
			die;
        }
    }
    
}
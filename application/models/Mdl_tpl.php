<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mdl_tpl extends CI_Model {
    
    protected $TWIG = false;
    
    function __construct() {
        
        parent::__construct();
		
		$threme_folder_name = $this->mdl_stores->getConfig('template_select_name');
		
		$this->config->set_item('templates', $threme_folder_name );
		$this->config->set_item('threme_folders', $this->config->item('templates_folder').'/'.$threme_folder_name );
		
		$this->config->set_item('config_tpl_path', $this->config->item('threme_folders').'/'. $this->config->item('tpl_folder'));
		
		$this->config->set_item('config_styles_path', $this->config->item('threme_folders').'/'. $this->config->item('styles_folder_name'));
		$this->config->set_item('config_scripts_path', $this->config->item('threme_folders').'/'. $this->config->item('scripts_folder_name'));
		$this->config->set_item('config_images_path', $this->config->item('threme_folders').'/'. $this->config->item('images_folder_name'));
		
        require_once './' . $this->config->item('addons_folder').'/twig/lib/Twig/Autoloader.php';
        Twig_Autoloader::register();
            
        $loader = new Twig_Loader_Filesystem($this->config->item('config_tpl_path'));
        
		$param = array(
            'cache'       => '"./' . $this->config->item('addons_folder') . '/twig/compilation_cache"',
            'auto_reload' => true,
            'autoescape' => false
        );
		
		if( $_SERVER['REMOTE_ADDR'] === '127.0.0.1' ){
			$param['cache'] = false;
		}
		$this->TWIG = new Twig_Environment($loader, $param);
        
    }

    function view( $tpl_file , $data, $return = false ){
        if( $return ){
            return $this->TWIG->render($tpl_file, $data );
        }else{
            echo $this->TWIG->render($tpl_file, $data );
        }
    }
    
}

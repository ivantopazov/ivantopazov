<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mdl_stores extends CI_Model {
    
    public function __construct() {
        parent::__construct();              
    }
    
    // получить общие настройки магазина
    public function allConfigs(){
        $this->db->where("view >", 0);
        $_c = $this->mdl_db->_all_query_db("store_config");
        $c = array();
        foreach ($_c as $value) {
            $c[$value['key']] = $value['value'];
        }
        return $c;
    }
    
    // Получить конкретную установку магазина
    public function getConfig( $key ){
        $_c = $this->mdl_db->_query_db_2("store_config", 'key', $key);
        return $_c['value'];
    }
       
    // полчить установку из файла конфигурации
    public function getСonfigFile($name){
        $conig = & get_config();
        return $conig[$name];
    }
    
}
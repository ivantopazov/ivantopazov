<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mdl_helper extends CI_Model {
    
    public function __json($array, $type = FALSE ){
        if($type === TRUE){
            return json_encode($array);
        }else{
           echo json_encode($array); 
        } 
    }
    
    // возвращает http или https [ TRUE] => + '://'
    // $this->mdl_helper->PROTOCOL( true )
    public function PROTOCOL ( $symb = false ){        
        $p = 'http';
        if( isset( $_SERVER['REQUEST_SCHEME'] ) ){
            $p = $_SERVER['REQUEST_SCHEME'];
        }else{
            if( isset( $_SERVER['HTTPS'] ) ){
                $p = 'https';
            }
        }        
        return ( $symb !== false ) ? $p . '://' : $p;        
    }
    
    // список ассоциативных массивов
    public function clear_array($array, $is = array('title')){
        $new = array();
        foreach($array as $keys => $value){
            foreach($value as $key => $val){
                if(in_array($key, $is)) $new[$keys][$key] = $val;
            }
        }
        return $new;
    }
    
    // ассоциативный массив
    public function clear_array_0($array, $is = array('title')){
        $new = array();
        foreach( $array as $key => $val){
            if( in_array($key, $is) ) $new[$key] = $val;
        }
        return $new;
    }
    
    // Получить куку
    public function get_cookie( $name ){
        return get_cookie( $this->mdl_stores->getСonfigFile('cookie_prefix') . $name );
    }
    
    // Установить Куку
    public function set_cookie($name = false, $value = false){
        set_cookie(array(
           'name'   => $name,
           'value'  => $value,
           'expire' => 3661200,
           'path'   => $this->mdl_stores->getСonfigFile('cookie_path'),
           'prefix' => $this->mdl_stores->getСonfigFile('cookie_prefix')
        ));
    }
	
    // Получить каптчу
    public function captcha( $name = 'captha' ){
		
        $this->load->helper('string');
        $this->load->helper('captcha');
        $rnd_str = random_string('numeric', 5);
        
        $vals = array(
            'word'		 => $rnd_str,
            'img_path'	 => './uploads/captcha/',
            'img_url'	 => '/uploads/captcha/',
            'font_path'	 => '../system/fonts/texb.ttf',
            'img_width'	 => '150',
            'img_height' => 25,
            'expiration' => 1
        );

        $this->session->set_userdata( $name, $rnd_str );
    	$cap = create_captcha($vals); 
		
		return '/uploads/captcha/' . $cap['filename'];
    }
	
    // Получить значение капчи
	public function get_captcha ( $name ){
		return $this->session->userdata( $name );
	}
	
    // Получить хеш МД5
    public function pass_md5( $pass, $sool = '' ){
        $data = md5(md5(md5($sool).$pass).$sool);
        return $data;
    }
	
    // Дата в юних [ 31.12.2000 -> 1234567800 ]
    public function date_str_to_unix ( $date_str ){
        $day = substr($date_str, 0, 2);
        $mes = substr($date_str, 3, 2);
        $god = substr($date_str, 6, 4);        
        $hours = ( mb_strlen( $date_str ) > 10 ) ? substr($date_str, 11, 2) : 0;
        $mim = ( mb_strlen( $date_str ) > 13 ) ?  substr($date_str, 14, 2) : 0;        
        $new_date = mktime ($hours, $mim, 0, $mes,$day,$god);        
        return $new_date;
    }
    
    // Создатель алиасов из текста
    public function aliase_translite ( $string = false ){        
        if( $string !== false ){
            $replace = array(
                "'"=>"",
                "`"=>"",
                "а"=>"a","А"=>"a",
                "б"=>"b","Б"=>"b",
                "в"=>"v","В"=>"v",
                "г"=>"g","Г"=>"g",
                "д"=>"d","Д"=>"d",
                "е"=>"e","Е"=>"e",
                "ё"=>"e","Ё"=>"e",
                "ж"=>"zh","Ж"=>"zh",
                "з"=>"z","З"=>"z",
                "и"=>"i","И"=>"i",
                "й"=>"y","Й"=>"y",
                "к"=>"k","К"=>"k",
                "л"=>"l","Л"=>"l",
                "м"=>"m","М"=>"m",
                "н"=>"n","Н"=>"n",
                "о"=>"o","О"=>"o",
                "п"=>"p","П"=>"p",
                "р"=>"r","Р"=>"r",
                "с"=>"s","С"=>"s",
                "т"=>"t","Т"=>"t",
                "у"=>"u","У"=>"u",
                "ф"=>"f","Ф"=>"f",
                "х"=>"h","Х"=>"h",
                "ц"=>"c","Ц"=>"c",
                "ч"=>"ch","Ч"=>"ch",
                "ш"=>"sh","Ш"=>"sh",
                "щ"=>"sch","Щ"=>"sch",
                "ъ"=>"","Ъ"=>"",
                "ы"=>"y","Ы"=>"y",
                "ь"=>"","Ь"=>"",
                "э"=>"e","Э"=>"e",
                "ю"=>"yu","Ю"=>"yu",
                "я"=>"ya","Я"=>"ya",
                "і"=>"i","І"=>"i",
                "ї"=>"yi","Ї"=>"yi",
                "є"=>"e","Є"=>"e"
            );            
            $str=iconv("UTF-8","UTF-8//IGNORE",strtr($string,$replace));
            $str = preg_replace ("/[^a-z0-9-]/i"," ",$str);
            $str = preg_replace("/ +/", "-", trim($str));            
            return strtolower($str);
        }
        
    }
    
    
    
}

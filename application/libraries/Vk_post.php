<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Vk_post {
    
    private $access_token;
    private $url = "https://api.vk.com/method/";
	
	public function set_token( $token = false ){
		$this->access_token = $token;
	}
	
    public function method( $method, $params = null ) {
        $p = "";
        if( $params && is_array($params) ) {
            foreach($params as $key => $param) {
                $p .= ($p == "" ? "" : "&") . $key . "=" . urlencode($param);
            }
        }
        
        $link = $this->url . $method . "?" . ($p ? $p . "&" : "") . "access_token=" . $this->access_token;        
        //echo '<br />' . $link;        
        $response = file_get_contents( $link );
        if( $response ) {
            return json_decode($response, true);
        }
        return false;
    }

	public function sendPhoto( $urlServer = false, $photo = false ){
        $ch = curl_init(); $r = false;
        if( $ch ){            
            curl_setopt( $ch, CURLOPT_URL, $urlServer );
            curl_setopt( $ch, CURLOPT_POST, true);
            curl_setopt( $ch, CURLOPT_SAFE_UPLOAD, false);
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt( $ch, CURLOPT_POSTFIELDS, [ "file1" => '@'. $photo ] );
            $r = json_decode(curl_exec($ch),true);
            curl_close($ch);
        }
        return $r;
    }
    
	public function sendPhotoMarket( $urlServer = false, $photo = false ){
        $ch = curl_init(); $r = false;
        if( $ch ){            
            curl_setopt( $ch, CURLOPT_URL, $urlServer );
            curl_setopt( $ch, CURLOPT_POST, true);
            curl_setopt( $ch, CURLOPT_SAFE_UPLOAD, false);
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt( $ch, CURLOPT_POSTFIELDS, [ "file" => '@'. $photo ] );
            $r = json_decode(curl_exec($ch),true);
            curl_close($ch);
        }
        return $r;
    }
    
    
}	
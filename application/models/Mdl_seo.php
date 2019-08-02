<?php
	
	
	if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	class Mdl_seo extends CI_Model {
		
		public function utmLabels ( $getArrays = array() ){
			
			$phrase = 'Невозможно узнать поисковый запрос';
			$referer = 'Прямой заход'; 
			
			if (isset($_SERVER['HTTP_REFERER'])) {  $referer = $_SERVER['HTTP_REFERER'];}
			
			if (stristr($referer, 'yandex.ru')) {   $search = 'text='; $crawler = 'Yandex'; }
			if (stristr($referer, 'yandex.ua')) {   $search = 'text='; $crawler = 'Yandex'; }
			if (stristr($referer, 'rambler.ru')) {  $search = 'query='; $crawler = 'Rambler'; }
			if (stristr($referer, 'google.ua')) {   $search = 'q='; $crawler = 'Google'; }
			if (stristr($referer, 'google.ru')) {   $search = 'q='; $crawler = 'Google'; }
			if (stristr($referer, 'google.com')) {  $search = 'q='; $crawler = 'Google'; }
			if (stristr($referer, 'mail.ru')) {     $search = 'q='; $crawler = 'Mail.Ru'; }
			if (stristr($referer, 'bing.com')) {    $search = 'q='; $crawler = 'Bing'; }
			if (stristr($referer, 'qip.ru')) {      $search = 'query='; $crawler = 'QIP'; }
			
			/* 
			if ( isset($crawler) ){
				$phrase = urldecode( $referer );
				eregi($search.'([^&]*)', $phrase.'&', $phrase2);
				$phrase = $phrase2[1];
				$referer = $crawler;
				}
			*/
			
			if( isset( $getArrays['utm_source'] ) ){
				$this->mdl_helper->set_cookie( 'utm_source', $getArrays['utm_source'] );
			}
			
			if( isset( $getArrays['utm_medium'] ) ){
				$this->mdl_helper->set_cookie( 'utm_medium', $getArrays['utm_medium'] );
			}
			
			if( isset( $getArrays['utm_campaign'] ) ){
				$this->mdl_helper->set_cookie( 'utm_campaign', $getArrays['utm_campaign'] );
			}
			
			if( isset( $getArrays['utm_term'] ) ){
				$this->mdl_helper->set_cookie( 'utm_term', $getArrays['utm_term'] );
				}
			
			if( isset( $getArrays['utm_content'] ) ){
				$this->mdl_helper->set_cookie( 'utm_content', $getArrays['utm_content'] );
			}
			
			if( isset( $getArrays['referer'] ) ){
				$this->mdl_helper->set_cookie( 'referer', $referer );
			}
			
			if( isset( $getArrays['phrase'] ) ){
				$this->mdl_helper->set_cookie( 'phrase', $phrase );
			}
			
			return $this->getUtmData();
			
		}
		
		public function getUtmData(){        
			return [
            'utmData' => [
			'utm' => [
			'utm_source' => $this->mdl_helper->get_cookie('utm_source'),
			'utm_medium' => $this->mdl_helper->get_cookie('utm_medium'),
			'utm_campaign' => $this->mdl_helper->get_cookie('utm_campaign'),
			'utm_term' => $this->mdl_helper->get_cookie('utm_term'),
			'utm_content' => $this->mdl_helper->get_cookie('utm_content')
			],
			'referer' => $this->mdl_helper->get_cookie('referer'),
			'phrase' => $this->mdl_helper->get_cookie('phrase')
            ]
			];        
		}
		
		
	}	
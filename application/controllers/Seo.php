<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Seo extends CI_Controller  {
    
    protected $post = array();
    protected $get = array();
    
    public function __construct() {
        parent::__construct();        
        $this->post = $this->security->xss_clean($_POST);
        $this->get = $this->security->xss_clean($_GET);
    }

    public function sitemap(){
		
		$pages = array('catalog', 'deliver', 'contacts', 'news', 'awards', 'actions', 'wiki');
		
		$cats = $this->mdl_catalog->get_all_category( array(
			'id', 'last_modify', 'weight'
		));
		
		$this->mdl_actions->set_param('view_actions_cat', true);
		$this->mdl_actions->set_param('labels_actions', array('id','date_action_end'));
		$actions = $this->mdl_actions->get_all_actions();
		
		$this->mdl_awards->set_param('labels_awards', array('id','create_date'));
		$awards = $this->mdl_awards->get_all_awards();
		
		$news = $this->mdl_news->get_all( array( 'labels' => array('id', 'date') ));
		
		$date = date("Y-m-d", time() );
        $date_modif = "Last-Modified: ".gmdate("D, d M Y H:i:s", time())." GMT";

        header ($date_modif);
        header ("Etag: ". time() );
        header ("Content-Type:text/xml");
        $_url = $this->mdl_helper->PROTOCOL( true ) . "$_SERVER[SERVER_NAME]/";     
        $s_map = '<?xml version="1.0" encoding="UTF-8"?>
        <urlset
              xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
              xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
              xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">'."\r\n";

        $s_map .= '<url>'."\r\n";
        $s_map .= '<loc>'.$_url.'</loc>'."\r\n";
        $s_map .= '<lastmod>'.$date.'</lastmod>'."\r\n";
        $s_map .= '<changefreq>daily</changefreq>'."\r\n";
        $s_map .= '<priority>1.0</priority>'."\r\n";
        $s_map .= '</url>'."\r\n";

		foreach ($pages as $v){
			$s_map .= '<url>'."\r\n";
			$s_map .= '<loc>'.$_url.$v.'</loc>'."\r\n";
			$s_map .= '<lastmod>'.$date.'</lastmod>'."\r\n";
			$s_map .= '<changefreq>daily</changefreq>'."\r\n";
			$s_map .= '<priority>0.9</priority>'."\r\n";
			$s_map .= '</url>'."\r\n";
        }
		
		foreach ($cats as $k => $v){
            $s_map .= '<url>'."\r\n";

            $s_map .= '<loc>'.$_url.'catalog/'.$v['id'].'</loc>'."\r\n";
            $s_map .= '<lastmod>'.date( "Y-m-d", $v['last_modify'] ).'</lastmod>'."\r\n";
            $s_map .= '<changefreq>daily</changefreq>'."\r\n";
            $s_map .= '<priority>0.8</priority>'."\r\n";
            $s_map .= '</url>'."\r\n";
        }
		
		foreach ($cats as $k => $v){
            $s_map .= '<url>'."\r\n";
            $s_map .= '<loc>'.$_url.'sitemap_goods_category_'.$v['id'].'.xml</loc>'."\r\n";
            $s_map .= '<lastmod>'.date( "Y-m-d", $v['last_modify'] ).'</lastmod>'."\r\n";
            $s_map .= '<changefreq>daily</changefreq>'."\r\n";
            $s_map .= '<priority>0.8</priority>'."\r\n";
            $s_map .= '</url>'."\r\n";
        }
		
		foreach ($news as $v){
            $s_map .= '<url>'."\r\n";
            $s_map .= '<loc>'.$_url.'news/'.$v['id'].'</loc>'."\r\n";
            $s_map .= '<lastmod>'.date("Y-m-d", $v['date']).'</lastmod>'."\r\n";
            $s_map .= '<changefreq>daily</changefreq>'."\r\n";
            $s_map .= '<priority>0.7</priority>'."\r\n";
            $s_map .= '</url>'."\r\n";
        }
		
		foreach ($awards as $v){
            $s_map .= '<url>'."\r\n";
            $s_map .= '<loc>'.$_url.'awards/item/'.$v['id'].'</loc>'."\r\n";
            $s_map .= '<lastmod>'.date("Y-m-d", $v['create_date']).'</lastmod>'."\r\n";
            $s_map .= '<changefreq>daily</changefreq>'."\r\n";
            $s_map .= '<priority>0.7</priority>'."\r\n";
            $s_map .= '</url>'."\r\n";
        }
		
		foreach ($actions as $v){
            $s_map .= '<url>'."\r\n";
            $s_map .= '<loc>'.$_url.'actions/item/'.$v['id'].'</loc>'."\r\n";
            $s_map .= '<lastmod>'.date("Y-m-d", $v['date_action_end']).'</lastmod>'."\r\n";
            $s_map .= '<changefreq>daily</changefreq>'."\r\n";
            $s_map .= '<priority>0.7</priority>'."\r\n";
            $s_map .= '</url>'."\r\n";
        }
		
        $s_map .= '</urlset>';
        echo $s_map;
		
	}
	
	public function robots(){

		$URL = $_SERVER['HTTP_HOST'];
		
		$t = "";
		$t .= "User-agent: * \n";
		$t .= "Allow: / \n";
		$t .= "Disallow: /card \n";
		//$t .= "Host: https://{$URL} \n\n";
		
        if( $this->mdl_helper->PROTOCOL( false ) === 'http' ){
            $t .= "Host: {$URL} \n\n"; 
        }else{
           $t .= "Host: https://{$URL} \n\n"; 
        }
        
        $cats = $this->mdl_category->queryData([            
            'return_type' => 'ARR2',
            'labels' => [ 'id' ]
        ]);  
        
		foreach ( $cats as $value ){
			//$t .= "Sitemap: https://{$URL}/sitemap_goods_category_{$value['id']}.xml \n\n";
            $t .= "Sitemap: ".$this->mdl_helper->PROTOCOL( true )."{$URL}/sitemapProductsCategory_{$value['id']}.xml \n";
		}
		
		header("Content-Type: text/plain");
		
        echo $t;


    }
	
    public function sitemapProducts( $catId = false ){
        if( $catId !== false ){
            
            $products = $this->mdl_product->queryData([
                'return_type' => 'ARR2',
                'debug' => false,
                'where' => [
                    'method' => 'AND',
                    'set' => [[
                        'item' => 'cat',
                        'value' => $catId
                    ],[
                        'item' => 'view >',
                        'value' => 0
                    ],[
                        'item' => 'qty >',
                        'value' => 0
                    ],[
                        'item' => 'moderate >',
                        'value' => 1
                    ]]
                ],
                'labels' => ['id', 'lastUpdate', 'modules'],
                'module' => true,
                'modules' => [[
                    'module_name' => 'linkPath', 
                    'result_item' => 'linkPath', 
                    'option' => []
                ]]
            ]);
            
            $date = date("Y-m-d", time() );
			$date_modif = "Last-Modified: ".gmdate("D, d M Y H:i:s", time())." GMT";

			header ($date_modif);
			header ("Etag: ". time() );
			header ("Content-Type:text/xml");
			$_url = $this->mdl_helper->PROTOCOL( true ) . "$_SERVER[SERVER_NAME]";     
			$s_map = '<?xml version="1.0" encoding="UTF-8"?>
			<urlset
				  xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
				  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
				  xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
			http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">'."\r\n";

			$s_map .= '<url>'."\r\n";
			$s_map .= '<loc>'.$_url.'</loc>'."\r\n";
			$s_map .= '<lastmod>'.$date.'</lastmod>'."\r\n";
			$s_map .= '<changefreq>daily</changefreq>'."\r\n";
			$s_map .= '<priority>1.0</priority>'."\r\n";
			$s_map .= '</url>'."\r\n";
			
			foreach ($products as $v){
				$s_map .= '<url>'."\r\n";
				$s_map .= '<loc>'.$_url.$v['modules']['linkPath'].'</loc>'."\r\n";
				$s_map .= '<lastmod>'.date("Y-m-d", $v['lastUpdate']).'</lastmod>'."\r\n";
				$s_map .= '<changefreq>daily</changefreq>'."\r\n";
				$s_map .= '<priority>0.7</priority>'."\r\n";
				$s_map .= '</url>'."\r\n";
			}
			
			$s_map .= '</urlset>';
			echo $s_map;
            
        }
    }
    
}
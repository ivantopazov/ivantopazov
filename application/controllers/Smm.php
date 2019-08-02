<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Smm extends CI_Controller  {
    
    protected $post = array();
    protected $get = array();
    
    public function __construct() {
        parent::__construct();        
        $this->post = $this->security->xss_clean($_POST);
        $this->get = $this->security->xss_clean($_GET);
    }
    
    public function listVkPosting(){
        
        $idsList = file_get_contents('./smmIDS.txt');
        $idsList = explode(',', $idsList);
        
        $getItems = $this->mdl_product->queryData([
            'return_type' => 'ARR1',
            'where' => [
                'method' => 'AND',
                'set' => [[
                    'item' => 'qty >',
                    'value' => 0
                ],[
                    'item' => 'moderate >',
                    'value' => 1
                ]]
            ],
            'limit' => 1,
            'in' => [
                'method' => 'NOT',
                'set' => [[
                    'item' => 'articul',
                    'values' => $idsList
                ]]
            ],
            'labels' => [
                'id', 'articul', 'title', 'cat', 'modules'
            ],
            'module' => true,
            'modules' => [[
                'module_name' => 'photos', 
                'result_item' => 'photos', 
                'option' => [
                ]
            ],[
                'module_name' => 'linkPath', 
                'result_item' => 'linkPath', 
                'option' => [
                ]
            ],[
				'module_name' => 'price_actual',
				'result_item' => 'price_actual',
				'option' => [
					'labels' => false
				]
			]]
        ]); 
         
        $cat_ids = [
            '1' => '244790087', // Кольца
            '10' => '244790099', //Серьги
            '19' => '244790106', //Подвески
            '28' => '244790116', //Браслеты
            '35' => '244790126', //Брошь
            '36' => '244790135', //Колье
            '37' => '244790148', //Крест
            '38' => '244790157', //Пирсинг
            '39' => '244790374', //Часы
            '40' => '244790387', //Цепочки
            '41' => '244790409', //Запонки
            '42' => '244790437' //Зажимы для галстука
        ];
             
        if( isset( $getItems['articul'] ) ){
            
            $idsList[] = $getItems['articul'];
            $_textIDS = implode(',', $idsList );
            file_put_contents( './smmIDS.txt', $_textIDS );
				
			$this->load->library('vk_post');
			$this->vk_post->set_token('8105248bee75d7242ec3e076d79c66ca65efbacecc0a09868616732d8eb368149fc59361bf8293beff186');
            
            $url = $this->vk_post->method('photos.getUploadServer', [
                'album_id' => $cat_ids[$getItems['cat']],
                'group_id' => 147452125
            ]); 
            
            
            
            
            $urlServer = $url['response']['upload_url'];
            
            $photo = false;
            $path = FCPATH . 'uploads/products/500/';
            
            foreach( $getItems['modules']['photos'] as $pv ){
                if( $pv['define'] > 0 ){
                    $photo = $pv['photo_name'];
                }
            }
           
            $result = $this->vk_post->sendPhoto( $urlServer, $path.$photo );
			
            
            $safe = $this->vk_post->method('photos.save', [
                'server' => $result['server'],
                'photos_list' => $result['photos_list'],
                'album_id' => $result['aid'],
                'hash' => $result['hash'],
                'gid' => $result['gid'],
                'caption' => $getItems['title'] . ' по цене ' . $getItems['modules']['price_actual']['format'] . ' рублей в магазине ИванТопазов. https://ivantopazov.ru' . $getItems['modules']['linkPath']
            ]);
            
            
            echo json_encode(['err' => 0]);
           
        }
        
    }
	
	
    public function listVkMarket(){
        
        $idsList = file_get_contents('./smmIDSmarket.txt');
        $idsList = explode(',', $idsList);
        
        $getItems = $this->mdl_product->queryData([
            'return_type' => 'ARR1',
            'where' => [
                'method' => 'AND',
                'set' => [[
                    'item' => 'qty >',
                    'value' => 0
                ],[
                    'item' => 'moderate >',
                    'value' => 1
                ]]
            ],
            'limit' => 1,
            'in' => [
                'method' => 'NOT',
                'set' => [[
                    'item' => 'articul',
                    'values' => $idsList
                ]]
            ],
            'labels' => [
                'id', 'articul', 'title', 'cat', 'modules'
            ],
            'module' => true,
            'modules' => [[
                'module_name' => 'photos', 
                'result_item' => 'photos', 
                'option' => [
                ]
            ],[
                'module_name' => 'linkPath', 
                'result_item' => 'linkPath', 
                'option' => [
                ]
            ],[
				'module_name' => 'price_actual',
				'result_item' => 'price_actual',
				'option' => [
					'labels' => false
				]
			]]
        ]); 
                
        $cat_ids = [
            '1' => '13', // Кольца
            '10' => '2', //Серьги
            '19' => '3', //Подвески
            '28' => '4', //Браслеты
            '35' => '5', //Брошь
            '36' => '6', //Колье
            '37' => '7', //Крест
            '38' => '8', //Пирсинг
            '39' => '9', //Часы
            '40' => '10', //Цепочки
            '41' => '11', //Запонки
            '42' => '12' //Зажимы для галстука
        ];
             
        if( isset( $getItems['articul'] ) ){
            
            $idsList[] = $getItems['articul'];
            $_textIDS = implode(',', $idsList );
            file_put_contents( './smmIDSmarket.txt', $_textIDS );
				
			$this->load->library('vk_post');
			$this->vk_post->set_token('90104017591037c78730e5bd1e4b2c74282c7b666e7957ab11031a2130d1711eee012d81f6302953da4f4');
            
            $url = $this->vk_post->method('photos.getMarketUploadServer', [
                //'album_id' => $cat_ids[$getItems['cat']],
                'group_id' => 147452125,
                'v' => '5.65',
                'main_photo' => '1',
                'crop_width' => '400'
            ]); 
            
            
            $urlServer = $url['response']['upload_url'];
            
            
            
            $photo = false;
            $path = FCPATH . 'uploads/products/500/';
            
            foreach( $getItems['modules']['photos'] as $pv ){
                if( $pv['define'] > 0 ){
                    $photo = $pv['photo_name'];
                }
            }
           
            $result = $this->vk_post->sendPhotoMarket( $urlServer, $path.$photo );
			/*
            echo "<pre>";
            print_r( $result );
            echo "</pre>";*/
            
            $safe = $this->vk_post->method('photos.saveMarketPhoto', [
                 'group_id' => 147452125,
                'server' => $result['server'],
                'photo' => $result['photo'],
                'crop_data' => $result['crop_data'],
                'hash' => $result['hash'],
                'crop_hash' => $result['crop_hash'],
                'v' => '5.65'
            ]);
			/*
            echo "<pre>";
            print_r( $safe );
            echo "</pre>";*/
            
            
			// ид картинки
			$main_photo_id = $safe['response'][0]['id'];
			 // Категория товара
			$category_id = 5;
			
			// Дескрипт
			$desc = $getItems['title'] . ' по цене ' . $getItems['modules']['price_actual']['format'] . ' рублей в магазине ИванТопазов. https://ivantopazov.ru' . $getItems['modules']['linkPath'];
			$description = $desc;	
			// цена
			$price = $getItems['modules']['price_actual']['number'];
            //$price = 100;            
			// Delete 0 no 1 yes
			$deleted = 0;	
            
             
            $safe2 = $this->vk_post->method('market.add', [
                'owner_id' => -147452125,
                'name' => $getItems['title'],
                'description' => $description,
                'category_id' => $category_id,
                'price' => $price,
                'deleted' => $deleted,
                'main_photo_id' => $main_photo_id,
                'v' => '5.65'
            ]);
            /*
            echo "<pre>";
            print_r( $safe2 );
            echo "</pre>";*/
			
            $safe3 = $this->vk_post->method('market.addToAlbum', [
                'owner_id' => -147452125,
                'item_id' => $safe2['response']['market_item_id'],
                'album_ids' => $cat_ids[$getItems['cat']],
                'v' => '5.65'
            ]);
            
            $this->db->insert( 'products_market_vk', [
                'market_product_id' => $safe2['response']['market_item_id'],
                'market_price' => $price,
                'market_photo_id' => $main_photo_id,
                'market_album_id' => $cat_ids[$getItems['cat']],
                'product_id' => $getItems['id']
            ]);
            
            
            /*
            echo "<pre>";
            print_r( $safe3);
            echo "</pre>";*/
            
            echo json_encode(['err' => 0]);
           
        }
        
    }
    
    
    public function updateVkMarketPrices(){
        
        
        sleep( 1 ); 
        
        $lastID = file_get_contents('./smmIDSmarketUPD.txt');
        
        
        $getVk = $this->mdl_product->queryData([
            'return_type' => 'ARR1',
            'where' => [
                'method' => 'AND',
                'set' => [[
                    'item' => 'id >',
                    'value' => $lastID
                ]]
            ],
            'limit' => 1,
            'table_name' => 'products_market_vk'
        ]);
        
        
        $getItems = $this->mdl_product->queryData([
            'return_type' => 'ARR1',
            'where' => [
                'method' => 'AND',
                'set' => [[
                    'item' => 'qty >',
                    'value' => 0
                ],[
                    'item' => 'moderate >',
                    'value' => 1
                ],[
                    'item' => 'id',
                    'value' => $getVk['product_id']
                ]]
            ],
            'limit' => 1,
            'labels' => [
                'id', 'articul', 'title', 'cat', 'modules'
            ],
            'module' => true,
            'modules' => [[
                'module_name' => 'photos', 
                'result_item' => 'photos', 
                'option' => [
                ]
            ],[
                'module_name' => 'linkPath', 
                'result_item' => 'linkPath', 
                'option' => [
                ]
            ],[
				'module_name' => 'price_actual',
				'result_item' => 'price_actual',
				'option' => [
					'labels' => false
				]
			]]
        ]);
                
        $cat_ids = [
            '1' => '13', // Кольца
            '10' => '2', //Серьги
            '19' => '3', //Подвески
            '28' => '4', //Браслеты
            '35' => '5', //Брошь
            '36' => '6', //Колье
            '37' => '7', //Крест
            '38' => '8', //Пирсинг
            '39' => '9', //Часы
            '40' => '10', //Цепочки
            '41' => '11', //Запонки
            '42' => '12' //Зажимы для галстука
        ];
             
        if( isset( $getItems['articul'] ) ){
            
            //$idsList[] = $getItems['articul'];
            //$_textIDS = implode(',', $idsList );
            $_textIDS = $getVk['id'];
            file_put_contents( './smmIDSmarketUPD.txt', $_textIDS );
				
			$this->load->library('vk_post');
			$this->vk_post->set_token('90104017591037c78730e5bd1e4b2c74282c7b666e7957ab11031a2130d1711eee012d81f6302953da4f4');
            
            
            $market_item = $this->mdl_product->queryData([
                'return_type' => 'ARR1',
                'where' => [
                    'method' => 'AND',
                    'set' => [[
                        'item' => 'product_id',
                        'value' => $getItems['id']
                    ]]
                ],
                'table_name' => 'products_market_vk'
            ]);
            
            if( isset( $market_item['id'] ) ){
                
                // Дескрипт
                $desc = $getItems['title'] . ' по цене ' . $getItems['modules']['price_actual']['format'] . ' рублей в магазине ИванТопазов. https://ivantopazov.ru' . $getItems['modules']['linkPath'];
                $description = $desc;
                
                // цена
                $price = $getItems['modules']['price_actual']['number'];
                $deleted = 0;	
                
               /* $url = $this->vk_post->method('market.edit', [
                    'owner_id' => -147452125,
                    'item_id' => $market_item['market_product_id'],
                    'name' => $getItems['title'],
                    'description' => $description,
                    'category_id' => $market_item['market_album_id'],
                    'price' => $price,
                    'deleted' => $deleted,
                    'main_photo_id' => $market_item['market_photo_id'],
                    'v' => '5.65'
                ]); */
				
				
                $url = $this->vk_post->method('market.delete', [
                    'owner_id' => -147452125,
                    'item_id' => $market_item['market_product_id'],
                    'v' => '5.65'
                ]); 
                
            }
            
            echo json_encode(['err' => 0]);
           
        }
        
        
    }
    
    /*
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		*/
    /*
        
        1. https://oauth.vk.com/authorize?client_id=6064540&redirect_uri=https://oauth.vk.com/blank.html&display=page&scope=market,groups&response_type=code&v=5.65
            l#code=346ac8d9f99bb36e51
        2. https://oauth.vk.com/access_token?client_id=6064540&client_secret=qy8Vy18MhL8Ns0xUFSFZ&redirect_uri=https://oauth.vk.com/blank.html&code=346ac8d9f99bb36e51
            {"access_token":"5e5fec2ada505500ddf34c6d67a87aa1be90b59f47f3dcc56691b9d318e13861f15fdcc63c1a15579a23b","expires_in":86252,"user_id":55836959}
        3. https://api.vk.com/method/photos.getMarketUploadServer?group_id=6064540&main_photo=1&v=5.65&access_token=179b2817158ae3174f8d303d8925afcbe1b2747c64bc612f1691ebe4b6e4405198a7a046cbd4f005cfc22
        4. https://api.vk.com/method/market.get?owner_id=55836959&v=5.65&access_token=5e5fec2ada505500ddf34c6d67a87aa1be90b59f47f3dcc56691b9d318e13861f15fdcc63c1a15579a23b
    
    
        КОД приложения АПИ
        ИД группы - 124375976
        ИД пользователя - 55836959
        179b2817158ae3174f8d303d8925afcbe1b2747c64bc612f1691ebe4b6e4405198a7a046cbd4f005cfc22
    */
    
    public function yandexMarket_YML(){
        
        
		$this->mdl_goods->set_query( 'yandex_market', true );
		$goods = $this->mdl_goods->get_goods(array('id','title','description','category','select_curent',
		'price_actual','photo','free_deliver','count'));
		
	    $this->load->model('mdl_cats');
	    $cats = $this->mdl_cats->query( array(
			'sort' => 'asc',
			'sort_item' => 'weight',
			'labels' => array('id', 'name_cat')
	    ));
		   
		$cats_items = array();
		  
		foreach ( $goods as $key => $value ) {
			$c_ = json_decode( $value['category'], true );
			foreach ($cats as $val) {
				if( (int)$val['id'] === (int)$c_[0] ) {
					$goods[$key]['cat'] = $val;
					$math = false;
					foreach ( $cats_items as $cv ) {
						if( (int)$cv['id'] === (int)$val['id'] ){
							$math = true;
							break;
						}
					}
					if( $math === false ){
						$cats_items[] = $val;
					}
				}
			}
			
			$title = strip_tags( $value['title'] ); 
			$goods[$key]['description'] = strip_tags( $value['description'] ); 
			$goods[$key]['title'] = ucfirst(mb_strtoupper(mb_substr($title, 0, 1)) . mb_substr($title, 1));
			$goods[$key]['description'] = str_replace( "&", "&amp;", $goods[$key]['description']  );
		}
		
		
		$confid = $this->mdl_helper->get_gb_config_list();  
		
		$date = date("Y-m-d H:i", time() );
		header ("Last-Modified: ".gmdate("D, d M Y H:i:s", time())." GMT");
        header ("Etag: ". time() );
        header ("Content-Type:text/xml");
        $_url = "https://$_SERVER[SERVER_NAME]/";   
		
		
		$s_map = '<yml_catalog date="'.$date.'">'."\r\n";
			$s_map .= '<shop>'."\r\n";
				$s_map .= '<name>'.$confid['header'].'</name>'."\r\n";
				$s_map .= '<company> Магазин "'.$confid['header'].'" </company>'."\r\n";
				$s_map .= '<url>'.$_url.'</url>'."\r\n";   
				
				$s_map .= '<currencies>'."\r\n";
				$s_map .= '     <currency id="RUR" rate="1" plus="0"></currency>'."\r\n";
				$s_map .= '</currencies>'."\r\n";
			
				$s_map .= '<categories>'."\r\n";   
				foreach ( $cats_items as $value ) {
					$s_map .= '<category id="' . $value['id']. '">' . $value['name_cat']. '</category>'."\r\n"; 
				}
				$s_map .= '</categories>'."\r\n"; 
				
				$s_map .= '<offers>'."\r\n";
				foreach ( $goods as $v ) {
					$avl = ( ($v['count'] > 0)?'true':'false' );
					$s_map .= '<offer id="'.$v['id'].'" available="true">'."\r\n";
					//$s_map .= '<offer id="'.$v['id'].'" >'."\r\n";
						$s_map .= '<name>'. $v['title'] .'</name>'."\r\n";
						$s_map .= '<description>'.$v['description'].'</description>'."\r\n";
						$s_map .= '<url>'.$_url.'catalog/good/'.$v['id'].'</url>'."\r\n";    
						$s_map .= '<price>'.number_format($v['price_actual']['item'], 2, '.', '').'</price>'."\r\n";
						$s_map .= '<currencyId>RUR</currencyId>'."\r\n";
						$s_map .= '<categoryId>'.$v['cat']['id'].'</categoryId>'."\r\n";
						$s_map .= '<picture>'.$_url.'uploads/goods/maxi/'.$v['photo']['photo_name'].'</picture>'."\r\n";
						
                        $s_map .= '<delivery>true</delivery>'."\r\n";
                        $s_map .= '<pickup>true</pickup>'."\r\n";
                        $s_map .= '<store>true</store>'."\r\n";
                        $s_map .= '<outlets>'.$avl.'</outlets>'."\r\n";
                               
                        if( $v['free_deliver'] > 0 ){
                            //$s_map .= '<delivery-options>'."\r\n";
                            //$s_map .= '   <option cost="0" days="32" order-before="24" />'."\r\n";
                            //$s_map .= '</delivery-options>'."\r\n";
                            $s_map .= '<local_delivery_cost>0</local_delivery_cost>'."\r\n";
                        }else{ 
                            //$s_map .= '<delivery-options>'."\r\n";
                            //$s_map .= '   <option cost="250" days="1-7" order-before="24" />'."\r\n";
                            //$s_map .= '</delivery-options>'."\r\n";
                            //$s_map .= '<local_delivery_cost>250</local_delivery_cost>'."\r\n";
                        }
                              
						$s_map .= '<manufacturer_warranty>true</manufacturer_warranty>'."\r\n";
					$s_map .= '</offer>'."\r\n";
				}
				$s_map .= '</offers>'."\r\n";
				
			$s_map .= '</shop>';
        $s_map .= '</yml_catalog>';
		
		echo $s_map;
        
        
    }
    
}
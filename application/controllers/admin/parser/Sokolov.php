<?php


defined('BASEPATH') OR exit('No direct script access allowed');

class Sokolov extends CI_Controller {
        
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
        
        if( $this->mdl_helper->get_cookie('HASH') !== $this->mdl_users->userHach() ){
            $this->user_info['admin_access'] = 0;
        } 
        
        
    }
    
    // Защита прямых соединений
	public function access_static(){
		if( $this->user_info !== false ){
            if( $this->user_info['admin_access'] < 1 ){
                redirect( '/login' );
            }
        }
	}
    
    // Защита динамических соединений
	public function access_dynamic(){
		if( $this->user_info !== false ){
            if( $this->user_info['admin_access'] < 1 ){
                exit('{"err":"1","mess":"Нет доступа"}');
            }
        }
	}
    
    
    // Показать страницу по умолчанию
    public function index(){
        
        $this->access_static();  
        
		$title = 'Парсинг с сайта соколова';
		$page_var = 'parser';
        
        $this->mdl_tpl->view( 'templates/doctype_admin.html' , array(
        
            'title' => $title,
            'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
            'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'), 
           
            'seo' => $this->mdl_tpl->view('snipets/seo_tools.html',array(
                'mk' => ( !empty( $this->store_info['seo_keys'] ) ) ? $this->store_info['seo_keys'] : '',
                'md' => ( !empty( $this->store_info['seo_desc'] ) ) ? $this->store_info['seo_desc'] : ''
            ), true),
            
            'nav' => $this->mdl_tpl->view('snipets/admin_nav.html',array(
                'active' => $page_var
            ),true),
            
            'breadcrumb' => $this->mdl_tpl->view('snipets/breadcrumb.html',array(
                'title' => $title,
                'array' => [[
                    'name' => 'Панель управления',
                    'link' => '/admin'
                ]]
            ),true),
             
            'content' => $this->mdl_tpl->view('pages/admin/parser/sokolov/sokolov.html', array(), true),
            
            'load' => $this->mdl_tpl->view('snipets/load.html',array(
                'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder')
            ),true),
            
            'resorses' => $this->mdl_tpl->view( 'resorses/admin/parser/sokolov.html', array(
                'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path')               
			), true )
            
        ), false);
        
    }
    
    // Обработка позиции
    public function parseWebSokolov(){
        $packs = ( isset( $this->post['pack'] ) ) ? $this->post['pack'] : [];
        
        if( count( $packs ) > 0 ){
            $product = $packs[0];
            
            $option = [
                'type' => 'ARR1',
                'where' => [
                    'method' => 'AND',
                    'set' => [[
                        'item' => 'articul',
                        'value' => $product['articul']
                    ],[
                        'item' => 'postavchik',
                        'value' => 'sokolov'
                    ]]
                ],
                'labels' => ['id', 'aliase', 'title', 'articul']
            ];
            
            if( isset( $product['size'] ) ){
                $option['where']['set'][] = [
                    'item' => 'size',
                    'value' => $product['size']
                ];
            }
            
            
            $issetProducts = $this->mdl_product->queryData( $option );  
            
            $status = '';
            
            if( !$issetProducts ){
                
                $v = $this->getRenderSokolov( $product );
                
                if( $v ){
                
                    $status = 'insert';
                    $this->db->insert('products', $v['product'] );
                    $insID = $this->db->insert_id();
                    
                    $aliase = $this->mdl_product->aliase_translite( $v['product']['title'] ) . '_' . trim( $v['product']['articul'] ) . '_' . $insID;
                    
                    $updProd = [
                        'aliase' => $aliase,
                        'moderate' => 0
                    ];
                    
                    $r = $this->saveImages( $v['photos']['photo_name'], $aliase );
                       
                    if( $r !== false ){ 
                    
                        $v['photos']['product_id'] = $insID;
                        $v['photos']['photo_name'] = $aliase.'.jpg';
                        $this->db->insert( 'products_photos', $v['photos'] );
                        $updProd['moderate'] = 2;
                    }
                    
                    $this->mdl_db->_update_db( "products", "id", $insID, $updProd );                
                    $v['price']['product_id'] = $insID;
                    
                    $this->db->insert( 'products_prices', $v['price'] );  
                }
               
            }else{
                
                $status = 'update';
                $PID = $issetProducts['id'];
                $this->mdl_db->_update_db( "products", "id", $PID, [
                    'qty' => $product['qty']
                ]);  
                
                $this->db->where("current_id", 1);
                $this->db->where("price_id", 1);
                $this->mdl_db->_update_db( "products_prices", "product_id", $PID, [
                    'price_item' => intval( $product['price'] * 100 )
                ]); 
                
            }
            
            
        }
        
        echo json_encode([
            'err' => 0,
            'mess' => 'success',
            'status' => $status
        ]);
    }
        
    // Сохранение картинок
    public function saveImages ( $link_image = false, $nameProduct = false ){        
        $r = false;
        if( $link_image !== false && $nameProduct !== false ){            
            $this->load->library('images');  
            $path = "./uploads/products/temp/";
            
            $this->getImage( $link_image, $path, $nameProduct.".jpg" );
            
            if ( file_exists( $path.$nameProduct.".jpg" ) ) {
                         
                $prew = "./uploads/products/100/";
                $prew2 = "./uploads/products/250/";
                $grozz = "./uploads/products/500/";   
                
                $this->images->imageresize( $prew.$nameProduct.'.jpg', $path.$nameProduct.".jpg", 100, 100, 100 );
                $this->images->imageresize( $prew2.$nameProduct.'.jpg', $path.$nameProduct.".jpg", 250, 250, 100 );
                $this->images->imageresize( $grozz.$nameProduct.'.jpg', $path.$nameProduct.".jpg", 500, 500, 100 );
                
                $r = true;                
            }
        }        
        return $r;            
    }
    
    // Выгрузить картинку
    public function getImage( $src = false, $path = './', $newName = '1.jpg' ){
        $t = @file_get_contents( $src );
        @file_put_contents( $path . $newName, $t );
    }
    
    // Получить данные c сайта 
    public function getRenderSokolov( $product = false ){
                
        $cat_ids = [
            'Кольцо' => '1',
            'Цепь' => '40',
            'Подвеска' => '19',
            'Крест' => '37',
            'Серьги' => '10',
            'Серьга' => '10',
            'Колье' => '36',
            'Пуссеты' => '43',
            'Браслет' => '28',
            'Брошь' => '35',
            'Пирсинг' => '38',
            'Часы' => '39',
            'Запонки' => '41'
        ];
               
        $r = [];
        
        if( $product !== false ) {
            
            $articul = trim( $product['articul'] );
                
            $link = 'https://sokolov.ru/jewelry-catalog/product/'. $articul;
                        
            $this->load->library('simple_html_dom');
            
            $html = file_get_html( $link  );
               
            if( $html ) {
                if( !$html->find('.b-not-found__title', 0) ){
                    
                    $item = [];
                    
                    $item['cat'] = $cat_ids[ $product['title'] ];
                    
                    $item['h1'] = $html->find('h1', 0)->plaintext;                        
                    $item['description'] = ( $html->find('div.b-product-description__text-description', 0) ) ? $html->find('div.b-product-description__text-description', 0)->find('p', 0)->plaintext : '';      
                                                                    
                    $paramItem = [[
                        'variabled' => 'metall',
                        'holders' => 'Металл',
                        'defined_value' => '-'
                    ],[
                        'variabled' => 'material',
                        'holders' => 'Материал',
                        'defined_value' => '-'
                    ],[
                        'variabled' => 'vstavka',
                        'holders' => 'Вставка',
                        'defined_value' => '-'
                    ],[
                        'variabled' => 'forma-vstavki',
                        'holders' => 'Форма вставки',
                        'defined_value' => '-'
                    ],[
                        'variabled' => 'primernyy-ves',
                        'holders' => 'Примерный вес',
                        'defined_value' => '-'
                    ],[
                        'variabled' => 'dlya-kogo',
                        'holders' => 'Для кого',
                        'defined_value' => '-'
                    ],[
                        'variabled' => 'technologiya',
                        'holders' => 'Технология',
                        'defined_value' => '-'
                    ]];
                        
                    $params = []; $i = 0;
                    foreach( $html->find('.b-quick-preview__item-property') as $element ){         
                        foreach( $paramItem as $vv ){
                            if( $vv['holders'] === trim( $element->plaintext ) ){
                                $params[$i] = [
                                'variabled' => $vv['variabled'],
                                'value' => ''
                                ];
                            }
                        }
                        $i++;
                    }    
                    
                    $_new_params = [];
                    for( $i = 0; $i < count( $params ); $i++ ){
                        $_new_params[] = $params[$i];
                        
                        if( $params[$i]['variabled'] == 'primernyy-ves' ){
                            $_i_ves = $i;
                        }
                    }
                    
                    $params = $_new_params;
                    
                    $filterBlock = [];
                    
                    $filterData = [[
                        'item' => 'metall',
                        'values' => []
                    ],[
                        'item' => 'kamen',
                        'values' => []
                    ],[
                        'item' => 'forma_vstavki',
                        'values' => []
                    ],[
                        'item' => 'sex',
                        'values' => []
                    ],[
                        'item' => 'size',
                        'values' => []  
                    ]];
                    
                    $kamenList = ['Без камня','С камнем','Кристалл Swarovski','Swarovski Zirconia','Бриллиант','Сапфир','Изумруд','Рубин','Жемчуг','Топаз','Аметист','Гранат','Хризолит','Цитрин','Агат','Кварц','Янтарь','Опал','Фианит',
                    'Родолит', 'Ситалл', 'Эмаль', 'Оникс', 'Корунд', 'Коралл прессованный'];
                    
                    $kamenListVals = ['empty','no_empty','swarovski','swarovski','brilliant','sapfir','izumrud','rubin','jemchug','topaz','ametist','granat','hrizolit','citrin','agat','kvarc','jantar','opal','fianit',
                    'Rodolit', 'Sitall', 'Emal', 'Oniks', 'Korund', 'Corall_pressovannyi'];
                    
                    $razmerList = [
                        '2.0','12','13','13.5','14','14.5','15',
                        '15.5','16','16.5','17','17.5','18','18.5','19',
                        '19.5','20','20.5','21','21.5','22','22.5','23','23.5','24','24.5','25'];
                    $razmerListVals = [
                        '2_0','12_0','13_0','13_5','14_0','14_5','15_0',
                        '15_5','16_0','16_5','17_0','17_5','18_0','18_5','19_0',
                        '19_5','20_0','20_5','21_0','21_5','22_0','22_5','23_0','23_5','24_0','24_5','25_0'];
                    
                    $metallList = ['Комбинированное золото','Красное золото','Белое золото','Серебро'];
                    $metallListVals = ['kombinZoloto','krasnZoloto','belZoloto','serebro'];
                    
                    $formaList = ['Кабошон','Круг','Овал','Груша','Маркиз', 'Багет', 'Квадрат', 'Октагон', 'Триллион', 'Сердце', 'Кушон', 'Пятигранник', 'Шестигранник', 'Восьмигранник'];
                    $formaListVals = ['Kaboshon','Krug','Oval','Grusha','Markiz', 'Baget', 'Kvadrat', 'Oktagon', 'Trillion', 'Serdtce', 'Kushon', 'Piatigranniq', 'Shestigranniq', 'Vosmigrannic'];
                    
                    $dlaKogo = ['Для женщин','Для мужчин', 'Для женщин, Для мужчин'];
                    $dlaKogoVals = ['woman','men', 'unisex'];
                        
                    $i = 0;
                    $item['sex'] = 'man,woman'; 
                    
                    
                    foreach( $html->find('.b-quick-preview__property') as $element ){
                        $text = $element->plaintext;
                        
                        $e = 0;
                        foreach( $metallList as $pk => $pv ){
                            $str_text = mb_strtolower( $text );
                            $str_find = '/' . mb_strtolower( $pv ) . '/iU';
                            if ( preg_match($str_find, $str_text)) {
                                $filterData[0]['values'][] = $metallListVals[$pk];
                            }
                        }
                        
                        foreach( $kamenList as $pk => $pv ){
                            $str_text = mb_strtolower( $text );
                            $str_find = '/' . mb_strtolower( $pv ) . '/iU';
                            if ( preg_match($str_find, $str_text)) {
                                $filterData[1]['values'][] = $kamenListVals[$pk];
                            }
                        }
                        
                        foreach( $formaList as $pk => $pv ){
                            $str_text = mb_strtolower( $text );
                            $str_find = '/' . mb_strtolower( $pv ) . '/iU';
                            if ( preg_match($str_find, $str_text)) {
                                $filterData[2]['values'][] = $formaListVals[$pk];
                            }
                        }
                        
                        $__i = 0; $__t = '';
                        foreach( $dlaKogo as $pk => $pv ){
                            $str_text = mb_strtolower( $text );
                            $str_find = '/' . mb_strtolower( $pv ) . '/iU';                                
                            if ( preg_match($str_find, $str_text)) {
                                $filterData[3]['values'][] = $dlaKogoVals[$pk];
                                $__t = $dlaKogo[$pk];
                                $params[$i]['value'] = $__t;                                    
                                $item['sex'] = $dlaKogoVals[$pk];                                    
                                $e++;
                                $__i++;
                            }
                        }
                        
                        if( $__i > 1 ){
                            $item['sex'] = 'man,woman';                                
                            $params[$i]['value'] = 'Для женщин, Для мужчин';                                
                        }
                        
                        if( $e < 1 ) $params[$i]['value'] = trim( $element->plaintext );
                        
                        if( $_i_ves == $i ){   
                            $params[$i]['value'] = $product['weight'];
                            $e++;
                            $__i++;
                        }
                        
                        $i++;
                    }  
                        
                    foreach( $razmerList as $pk => $pv ){
                        $str_text = str_replace( ",", ".", trim( $product['size'] ));
                        if ( $pv === $str_text ) {
                            $filterData[4]['values'][] = $razmerListVals[$pk];
                        }
                    }
                    
                    if( count($filterData[1]['values']) > 0 ){
                        $filterData[1]['values'][] = 'no_empty';
                    }
                    
                    if( count($filterData[1]['values']) < 1 ){
                        $filterData[1]['values'][] = 'empty';
                    }
                        
                   
                    $r['product'] = [
                        'title' => $item['h1'],
                        'articul' => trim( $product['articul'] ),
                        'cat' => $item['cat'],
                        'params' => json_encode( $params ),
                        'size' => str_replace( ",", ".", trim( $product['size'] )),
                        'filters' => json_encode( $filterData ),
                        'proba' => $product['proba'],
                        'description' => $item['description'],
                        'postavchik' => 'sokolov',
                        'parser' => 'sokolov',
                        'weight' => str_replace( ",", ".", $product['weight'] ),
                        'qty_empty' => '1',
                        'prices_empty' => '1',
                        'qty' => $product['qty'],
                        'view' => '1',
                        'sex' => $item['sex'],
                        'current' => 'RUR',
                        'moderate' => '2',
                        'lastUpdate' => time()
                    ];
                    
                    $r['price'] = [
                        'product_id' => 0,
                        'price_id' => 1,
                        'current_id' => 1,
                        'price_item' => intval( $product['price'] * 100 )
                    ];
                    
                    $r['photos'] = [
                        'product_id' => 0,
                        'photo_name' => 'https://sokolov.ru/ru/images/jewelry/500/'.$articul.'.jpg',
                        'define' => '1'
                    ];
                    
                }
            }
            
        }
        
        return $r;
        
    }
    
    
    
   
}

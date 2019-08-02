<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Estet extends CI_Controller {
        
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
        
        $this->load->library('images'); 
        
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
        
		$title = 'Парсинг с сайта Эстет';
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
             
            'content' => $this->mdl_tpl->view('pages/admin/parser/estet/basic.html', array(), true),
            
            'load' => $this->mdl_tpl->view('snipets/load.html',array(
                'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder')
            ),true),
            
            'resorses' => $this->mdl_tpl->view( 'resorses/admin/parser/estet.html', array(
                'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path')               
			), true )
            
        ), false);
        
    }
    
    // Парсинг вставок
    public function parseVstavki( $t ){
        
         $this->access_dynamic();
         
        //$t = "САПФИР    3/3  1шт.,1.58ct # фианит    0/0  0шт.,0ct";
        // $t = "БРИЛЛИАНТ  КР-57  2/3  5шт.,0.11ct # БРИЛЛИАНТ  КР-57  2/4  305шт.,0.59ct # БРИЛЛИАНТ  КР-57  2/6  2шт.,0.02ct # БРИЛЛИАНТ  КР-57  3/4  182шт.,0.37ct # БРИЛЛИАНТ  КР-57  3/6  39шт.,0.29ct # ЗОЛОТО585    0/0  1шт.,0.5ct # ИЗУМРУД  КАБАШОН КРУГ  3/2  22шт.,0.44ct # ИЗУМРУД  КАБАШОН ОВАЛ  2/1  2шт.,0.52ct # ПЛАТИНА950    0/0  1шт.,0.04ct # РУБИН  КАБАШОН КРУГ  2/2  22шт.,0.65ct # РУБИН  КВАДРАТ  2/2  14шт.,0.76ct # РУБИН  КРУГ  2/2  44шт.,0.51ct # САПФИР  КАБАШОН КРУГ  2/2  22шт.,0.64ct";
        
        
        $i_forma = [
            'КР-17' => 'Круг',
            'Кр-17' => 'Круг',
            'КР-33' => 'Круг',
            'Кр-33' => 'Круг',
            'КР-57' => 'Круг',
            'Кр-57' => 'Круг',
            'ОВ-57' => 'Овал',
            'Ов-57' => 'Овал',
            'КРУГ' => 'Круг',
            'ОВАЛ' => 'Овал',
            'КВАДРАТ' => 'Квадрат',
            'ГРУША' => 'Груша',
            'СЕРДЦЕ' => 'Сердце',
            'ТРИЛЛИОН' => 'Триллион',
            'ДРУЗА' => 'Друза',
            'круг' => 'Круг',
            'овал' => 'Овал',
            'квадрат' => 'Квадрат',
            'груша' => 'Груша',
            'сердце' => 'Сердце',
            'триллион' => 'Триллион',
            'друза' => 'Друза'
        ];
        
        $i_kamen = [
            'ПЛАТИНА950' => 'Платина',
            'РУБИН' => 'Рубин',
            'ИЗУМРУД' => 'Изумруд',
            'ЗОЛОТО585' => 'Золото',
            'БРИЛЛИАНТ' => 'Бриллиант',
            'бриллиант' => 'Бриллиант',
            'Бриллиант' => 'Бриллиант',
            'КОРИЧНЕВЫЙ' => 'Бриллиант',
            'ЧЁРНЫЙ' => 'Бриллиант',
            'САПФИР' => 'Сапфир',
            'АГАТ' => 'Агат',
            'ТОПАЗ' => 'Топаз',
            'ТАНЗАНИТ' => 'Танзанит',
            'ТУРМАЛИН' => 'Турмалин',
            'ЖЕМЧУГ' => 'Жемчуг',
            'ЭМАЛЬ' => 'Эмаль',
            'ОНИКС' => 'Оникс',
            'сапфир' => 'Сапфир',
            'Сапфир' => 'Сапфир',
            'ДЕМАНТОИД' => 'Демантоид',
            'демантоит' => 'Демантоид',
            'ЛУННЫЙ' => 'Лунный камень',
            'СПЕКТРОЛИТ' => 'Спектролит',
            'подшипник' => 'Подшипник стальной',
            'ВЕЗУВИАН' => 'Везувиан',
            'ШНУРОК' => 'Шнурок',
            'КОСТЬ' => 'Кость луна',
            'СЕРДОЛИК' => 'Сердолик',
            'ЯШМА' => 'Яшма',
            'КОРАЛЛ' => 'Коралл',
            'ПЕРИДОТ' => 'Перидот',
            'АМЕТИСТ' => 'Оникс',
            'МОРГАНИТ' => 'Оникс',
            'ХРИЗОЛИТ' => 'Хризолит',
            'ФИАНИТ' => 'Фианит',
            'фианит' => 'Фианит',
            'НАНОКРИСТАЛЛ' => 'Нанокристалл',
            'КУБ' => 'Куб циркония',
            'ЦИРКОНИЯ' => 'Куб циркония',
            'Родолит' => 'Родолит',
            'КВАРЦ' => 'Кварц',
            'Циркон' => 'Циркон',
            'Рубин' => 'Рубин',
            'КОРУНД' => 'Корунд',
            'ЦИРКОН' => 'Циркон',
            'ПРАЗИОЛИТ' => 'Празиолит',
            'ЛАЗУРИТ' => 'Лазурит',
            'ТСАВОРИТ' => 'Тсаворит',
            'РОДОЛИТ' => 'Родолит',
            'БИРЮЗА' => 'Бирюза',
            'РАУХ-ТОПАЗ' => 'Раух-топаз',
            'ШПИНЕЛЬ' => 'Шпинель',
            'АКВАМАРИН' => 'Аквамарин',
            'ЦИТРИН' => 'Цитрин',
            'ХАЛЦЕДОН' => 'Халцедон',
            'СПЕССАРТИН' => 'Спессартин',
            'ТАНЗАНИТ' => 'Танзанит',
            'ШНУРОК-ТЕКСТИЛЬ' => 'Шнурок-текстиль',
            'ГИАЦИНТ' => 'Гиацинт',
            'ЗОЛОТО999.9' => 'Золото',
            'СЕРЕБРО925' => 'Серебро',
            'ЦИРКОНИЙ' => 'Цирконий',
            'ПЛАТИНА999' => 'Платина',
            'СЕРЕБРО999.9' => 'Серебро',
            'СТЕКЛО' => 'Стекло',
            'топаз' => 'Топаз',
            'ОПАЛ' => 'Опал',
            'БЕРИЛЛ' => 'Берилл',
            'КУНЦИТ' => 'Кунцит',
            'ГРАНАТ' => 'Гранат',
            'СИНТ.РУБИН' => 'Синтетический рубин',
            'РУБЕЛЛИТ' => 'Рубеллит',
            'ПЕРЛАМУТР' => 'Перламутр',
            'ЖЕМ' => 'Жем',
            'КУНЦИТ' => 'Кунцит',
            'ИОЛИТ' => 'Иолит',
            'МОРИОН' => 'Морион'
        ];
        
        $r = explode( '#', $t );
        $r2 = [];
        foreach( $r as $v ){
            $r2[] = explode( ' ', trim( $v ) );
        }
                
        $it = []; $it_i = 0;
        foreach( $r2 as $k => $v ){
            $param = (explode( ',', array_slice( $v, -1 )[0] ));
            $kamen = (@$i_kamen[array_slice( $v, 0 )[0]])?:false;
            $it[$it_i]['kamen'] = ($kamen)?$kamen:(@$i_kamen[array_slice( $v, 1 )[0]])?:'-';
            $it[$it_i]['colVo'] = (@$param[0])?:'-';
            $it[$it_i]['carat'] = (@$param[1])?:'-';
            $it[$it_i]['forma'] = ( @$i_forma[array_slice( $v, -5 )[0]] )?:'-';
            $it_i++;
        }
    
        return $it;
        
    }
    
    // Парметры по артиклу
    public function parseArticul ( $art = false ){
        
        $art = mb_strtoupper( $art );
        
        $r = [
            'vi' => '',
            'vv' => '',
            'vm' => '',
            'vm2' => ''
        ];
        
        if( $art !== false ){
            
            preg_match_all( '#.{1}#uis', urldecode( $art ), $out );
            $artArr = $out[0];
            
            $_vidIzd = array_slice( $artArr, 2, 1 )[0]; 
            $_vidVst = array_slice( $artArr, 3, 1 )[0];   
            $_vidSpl = array_slice( $artArr, 4, 1 )[0]; 
            
            $vidIzdelia = [
                'Б' => 'браслет',
                'В' => 'булавки',
                'Г' => 'брелок для ключей',
                'Д' => 'знак зодиака',
                'Е' => 'браслет для часов',
                'Ж' => 'зажим для денег',
                'З' => 'зажим для галстука',
                'И' => 'пирсинг',
                'К' => 'кольцо',
                'Л' => 'колье',
                'Н' => 'запонки',
                'О' => 'обручальное кольцо',
                'П' => 'подвеска',
                'Р' => 'крест',
                'С' => 'серьги',
                'Т' => 'печатка',
                'У' => 'сувенир',
                'Ц' => 'цепь',
                'Ш' => 'брошь'
            ];

            $vidVstavki = [
                '0' => 'без вставок',
                '1' => 'фианиты бесцветные',
                '2' => 'цветные синтетичекие вставки',
                '3' => 'полудраги',
                '4' => 'подделочные вставки',
                '5' => 'драгоценные вставки',
                '6' => 'бриллианты',
                '7' => 'алмазная обработка'
            ];

            $vidMetalla = [
                '0' => 'красное золото',
                '1' => 'красное золото',
                '2' => 'белое золото',
                '3' => 'желтое золото',
                '4' => 'желтое золото',
                '5' => 'серебро',
                '6' => 'комбинированное золото',
                '7' => 'белое золото',
                '8' => 'комбинированное золото',
                '9' => 'платина',
            ];
            
            $vidMetalla2 = [
                '0' => ' из красного золота',
                '1' => ' из красного золота',
                '2' => ' из белого золота',
                '3' => ' из желтого золота',
                '4' => ' из желтого золота',
                '5' => ' из серебра',
                '6' => ' из комбинированного золота',
                '7' => ' из белого золота',
                '8' => ' из комбинированного золота',
                '9' => ' из платины',
            ];
            
            $vidMetalla2 = [
                '0' => ' из красного золота',
                '1' => ' из красного золота',
                '2' => ' из белого золота',
                '3' => ' из желтого золота',
                '4' => ' из желтого золота',
                '5' => ' из серебра',
                '6' => ' из комбинированного золота',
                '7' => ' из белого золота',
                '8' => ' из комбинированного золота',
                '9' => ' из платины',
            ];
            
            $mett_fx_2 = [
                '0' => 'krasnZoloto',
                '1' => 'krasnZoloto',
                '2' => 'belZoloto',
                '3' => 'JoltZoloto',
                '4' => 'JoltZoloto',
                '5' => 'serebro',
                '6' => 'kombinZoloto',
                '7' => 'belZoloto',
                '8' => 'kombinZoloto',
                '9' => 'platina'
            ];
            
            $r = [
                'vi' => (isset($vidIzdelia[$_vidIzd])) ? $vidIzdelia[$_vidIzd] : [],
                'vv' => (isset($vidVstavki[$_vidVst])) ? $vidVstavki[$_vidVst]: [],
                'vm' => (isset($vidMetalla[$_vidSpl])) ? $vidMetalla[$_vidSpl]: [],
                'vm2' => (isset($vidMetalla2[$_vidSpl])) ? $vidMetalla2[$_vidSpl]: [],
                'vm3' => (isset($mett_fx_2[$_vidSpl])) ? $mett_fx_2[$_vidSpl]: []
            ];
            
        }
        
        /* echo "<pre>";
        print_r( $r );
        echo "</pre>"; */
        
        return $r;
    }
    
    // Получение актуального прайс листа с серсера
    public function estet_parser_get_xml (){
         
         $this->access_dynamic();
         
         $fileXml = '/Stock%20ballance%20for%20internet-shops/New/br.xml';
         $file_name_estet = 'temp_estet_' . date('d-m-Y') . '.xml';
         $this->getFile( $fileXml, './', $file_name_estet );
         
         $s1 = @simplexml_load_string(file_get_contents( "./$file_name_estet" ));
         $s2 = $s1->List1->list1_r_Collection->list1_r->table1->Detail_Collection;
         
         unlink( './' . $file_name_estet );
         
         $s3 = json_decode(json_encode($s2),true);
         
         $new = [];
         foreach( $s3['Detail'] as $v ){
             $val = $v['@attributes'];
             $val['Textbox3'] = str_replace( "ftp://ftp1808:ftp1808@ftp.estet.ru", "", $val['Textbox3'] );
             $new[] = [
                'articul' => $val['Artikul'],
                'identifier' => $val['identifier'],
                'size' => ( (int)$val['size'] < 1 ) ? NULL : $val['size'],
                'proba' => ( (int)$val['Proba'] < 1 ) ? NULL : $val['Proba'],
                'title' => $val['Typ'],
                'vstavkiString' => (isset($val['Vstavki']))?$val['Vstavki']:'',
                'vstavki' => (isset($val['Vstavki']))?$this->parseVstavki( $val['Vstavki'] ):'',
                'ves' => $val['Ves'],
                'cena' => $val['Cena'],
                'image' => $val['Textbox3']
             ];
         }
         
         $new2 = [];
         foreach( $new as $v ){
             $m = 0;
             foreach( $new2 as $v2 ){
                 
                 if( empty( $v['size'] ) ){
                    $v['size'] = '0';
                 } 
                 if( empty( $v2['size'] ) ){
                    $v2['size'] = '0';
                 } 
                 
                 if( $v['articul'] === $v2['articul'] && $v['size'] === $v2['size'] ) {
                    $m++;
                 }
             }
             if( $m < 1 ){
                 $new2[] = $v;
             }
         }
         
         
          
         echo json_encode( $new2 );
          /* 
         $r1 = [];
         foreach( $new as $v ){
             if( !in_array($v['proba'], $r1) ){
                 $r1[] = $v['proba'];
                 echo "'".$v['proba']."',";
             } 
         } 
          
         echo "<pre>";
        print_r( $new2 );
        echo "</pre>";
         */
         //$this->getFile('/Stock%20ballance%20for%20internet-shops/Photos/0%D0%9E1880.jpg', './', '2.jpg');
         
         
    }
    
    // Получение пакета из 10\100 позиций для обработки
    public function parseEstet(){
        
        $this->access_dynamic();
         
        $packs = ( isset( $this->post['pack'] ) ) ? $this->post['pack'] : [];
        $clear = ( isset( $this->post['clear'] ) ) ? $this->post['clear'] : false;
        
        if( $clear === '1' ){
            $this->mdl_db->_update_db( "products", "postavchik", 'Estet', [
                'qty' => 0
            ]);
        }
                
        $PACK = [];
        if( count( $packs ) > 0 ){
            foreach( $packs as $v ){
                $PACK[] = $this->getRenderEstet( $v );
            }          
        }
        
        $w = ['i' => 0, 'u' => 0, 'img' => 0 ];
        
        foreach( $PACK as $v ){
            
            $option = [
                'return_type' => 'ARR1',
                'where' => [
                    'method' => 'AND',
                    'set' => [[
                        'item' => 'articul',
                        'value' => $v['articul']
                    ],[
                        'item' => 'postavchik',
                        'value' => 'Estet'
                    ]]
                ],
                'labels' => ['id', 'aliase', 'title', 'articul', 'size']
            ];
            
            if( isset( $v['size'] ) ){
                $option['where']['set'][] = [
                    'item' => 'size',
                    'value' => $v['size']
                ];
            }
            
            if( isset( $v['weight'] ) ){
                $option['where']['set'][] = [
                    'item' => 'weight',
                    'value' => $v['weight']
                ];
            }
            
            $issetProducts = $this->mdl_product->queryData( $option ); 
            
            if( empty( $issetProducts ) ){
                
                //echo 'IIII';
                
                $this->db->insert('products', $this->mdl_helper->clear_array_0( $v, [
                    "aliase", "articul", "articul", "price_zac",
                    "cat", "qty","current","view", "qty_empty",
                    "moderate","postavchik", "parser", "prices_empty",
                    "size","proba", "title","weight", "lastUpdate",
                    "params", "filters", "drag", "optionLabel"
                ]));
                $insID = $this->db->insert_id();
                $w['i']++;
                
                $aliase = $this->mdl_product->aliase_translite( $v['title'] ) . '_' . trim( $v['identifier'] ) . '_' . $insID;
                $updProd = [
                    'aliase' => $aliase,
                    'moderate' => 0
                ];
                
                $photoName = $aliase;
                $rImg = false;
                if( $this->getFile( $v['image'], './uploads/products/estet/', $photoName.'.jpg' ) ){
                    $rImg = $this->saveImages( $photoName.'.jpg', $photoName );
                }
                
                if( $rImg !== false ){                  
                    $this->db->insert( 'products_photos', [
                        'product_id' => $insID,
                        'photo_name' => $aliase.'.jpg',
                        'define' => '1'
                    ]);                    
                    $updProd['moderate'] = 2;
                    $w['img']++;
                }  
                
                $ret = $this->mdl_product->getRozCena( $insID );
                if( $ret['price_r'] !== 'МИНУС' ){
                    $end = $ret['price_r'] * 100;
                    $updProd['price_roz'] = $end;
                    $updProd['salle_procent'] = $ret['procSkidca'];
                }
                
                $this->mdl_db->_update_db( "products", "id", $insID, $updProd );
                 
            }else{
                
                $w['u']++;
                $PID = $issetProducts['id'];                
                $updProd = [
                    'qty' => $v['qty'],
                    'price_zac' => $v['price_zac']
                ];  
                $this->mdl_db->_update_db( "products", "id", $PID, $updProd );
                
                $ret = $this->mdl_product->getRozCena( $PID );
                if( $ret['price_r'] !== 'МИНУС' ){
                    $end = $ret['price_r'] * 100;
                    $updProd['price_roz'] = $end;
                    $updProd['salle_procent'] = $ret['procSkidca'];
                }   
                
                $this->mdl_db->_update_db( "products", "id", $PID, $updProd ); 
                 
            } 
        }
        echo json_encode(['err'=>'0','mess' => 'success', 'w' => $w ]);
    }
    
    // Рендеринг позиции
    public function getRenderEstet( $item = false ){     
       
        
       $this->access_dynamic();

       /* 
       $item = [
            "articul" => "01Б531527",
            "size" => '17,5',
            "proba" => "585",
            "title" => "БРАСЛЕТ",
            "vstavkiString" => "БРИЛЛИАНТ  КР-57  2/6  1шт.,0.01ct # БРИЛЛИАНТ  КР-57  3/6  11шт.,0.08ct # ЭМАЛЬ    0/0  1шт.,0ct",
            "vstavki" => [[
                'kamen' => 'Сапфир',
                'colVo' => '1шт.',
                'carat' => '0.8ct',
                'forma' => 'Круг'
            ],[
                'kamen' => 'Бриллиант',
                'colVo' => '1шт.',
                'carat' => '0.8ct',
                'forma' => 'Круг'
            ],[
                'kamen' => 'Бриллиант',
                'colVo' => '1шт.',
                'carat' => '0.8ct',
                'forma' => 'квадрат'
            ]], 
            "ves" => 1.17,
            "cena" => 5556.32,
            "image" => "/Stock ballance for internet-shops/Photos/01Б531527.jpg"
        ];  */
    
        $prod = [
            'title' => 'Нет названия',
            'cat' => 0,
            'qty' => '1',
            'view' => '1',
            'current' => 'RUR',
            'moderate' => '2',
            'postavchik' => 'Estet',
            'parser' => 'Estet',
            'qty_empty' => '1',
            'prices_empty' => '1',
            'articul' => $item['articul'],
            'proba' => @$item['proba'],
            'weight' => (isset($item['ves']))?str_replace(",",".",$item['ves']):0,
            'price_zac' => ( isset( $item['cena'] ) ) ? intval( $item['cena'] * 100 ) : 0,
            'image' => (isset($item['image'])) ? $item['image'] : false,
            'identifier' => (isset($item['identifier'])) ? $item['identifier'] : false,
            'lastUpdate' => time(),
            'optionLabel' => json_encode( $item )
        ];

        if( $item !== false ){
           
            $TI = [
                'БРАСЛЕТ' => '28',
                'БУЛАВКА' => '35',
                'ЗАЖИМ' => '42',
                'зажим' => '42',
                'КОЛЬЦО' => '1',
                'кольцо' => '1',
                'КОЛЬЕ' => '36',
                'ЗАПОНКИ' => '35',
                'ПОДВЕСКА' => '19',
                'СЕРЬГИ' => '10',
                'СЕРЬГА ОДИНОЧНАЯ' => '10',
                'СУВЕНИР' => false,
                'цепь' => '40',
                'БРОШЬ' => '35',
                'ЭМБЛЕМА' => false,
                'ЧАСЫ' => false,
                'СТАТУЭТКА' => false,
                'ЦЕПЬ С ПОДВЕСКОЙ' => '40'
            ];
            
            $PI = [
                '585' => 'Золото',
                '925' => 'Серебро',
                '750' => 'Золото'
            ];
            
            
            $cat_fx_1 = [
                'БРАСЛЕТ' => 'Браслет',
                'БУЛАВКА' => 'Булавка',
                'ЗАЖИМ' => 'Зажим',
                'зажим' => 'Зажим',
                'КОЛЬЦО' => 'Кольцо',
                'кольцо' => 'Кольцо',
                'КОЛЬЕ' => 'Колье',
                'ЗАПОНКИ' => 'Запонка',
                'ПОДВЕСКА' => 'Подвеска',
                'СЕРЬГИ' => 'Серьги',
                'СЕРЬГА ОДИНОЧНАЯ' => 'Серьга',
                'СУВЕНИР' => false,
                'цепь' => 'Цепочка',
                'БРОШЬ' => 'Брошь',
                'ЭМБЛЕМА' => false,
                'ЧАСЫ' => false,
                'СТАТУЭТКА' => false,
                'ЦЕПЬ С ПОДВЕСКОЙ' => 'Цепь с подвеской'
            ];
            
            $mett_fx_1 = [
                '585' => ' из золота',
                '925' => ' из серебра',
                '750' => ' из золота'
            ];
            
            $drag = ['бриллиант' => 'бриллиантом','сапфир' => 'сапфиром','рубин' => 'рубином','изумруд' => 'изумрудом'];
            
            $drag_set = [];
            
            if( isset( $item['vstavki'] )) {
                foreach( $item['vstavki'] as $vs ){
                    foreach( $drag as $dk => $dv ){
                        if( mb_strtolower( $vs['kamen'] ) == $dk ){
                            if( !in_array( $dv, $drag_set ) ){
                                $drag_set[] = $dv;
                            } 
                        }
                    }
                }
            }
            
            $pius_vstavki = '';
            if( count( $drag_set ) == 1 ){
                $pius_vstavki = ' c ' . $drag_set[0];
            }
            if( count( $drag_set ) == 2 ){
                $pius_vstavki = ' c ' . $drag_set[0] . ' и ' . $drag_set[1];
            }
            if( count( $drag_set ) > 2 ){
                $last = array_slice( $drag_set, -1 )[0];
                $list = array_slice( $drag_set, 0, count( $drag_set )-1 );
                $items_list = implode( ', ', $list );
                $pius_vstavki = ' c ' . $items_list . ' и ' . $last;
            }
            
            $title = '';
            if( isset( $item['title'] ) ){
                if( $cat_fx_1[$item['title']] ){
                    $title .= $cat_fx_1[$item['title']];
                }
            }else{
                $title .= "Ювелирное изделие";
            }
            
            $paramART = $this->parseArticul( $item['articul'] );
            
            if( isset( $paramART['vm2'] ) ){
                $title .= $paramART['vm2'];
            }
            if( !empty( $pius_vstavki ) ){
                $title .= $pius_vstavki;
            } 
            $prod['title'] = $title;
            
            
            $cat = false;
            if( isset( $item['title'] ) ){
                if( $TI[$item['title']] ){
                    $cat = $TI[$item['title']];
                } 
            }
            $prod['cat'] = $cat;
            
            $drag = [];
            if( isset( $item['vstavki'] ) ){
                
                foreach( $item['vstavki'] as $vs ){
                    $drag[] = [
                        'kamen' => mb_strtolower( $vs['kamen'] ),
                        'data' => [[
                            'name' => 'Камень',
                            'value' => ( isset( $vs['kamen'] ) ) ? mb_strtolower( $vs['kamen'] ) : '-'
                        ],[
                            'name' => 'Кол-во камней',
                            'value' => ( isset( $vs['colVo'] ) ) ? mb_strtolower( $vs['colVo'] ) : '-'
                        ],[
                            'name' => 'Вес, Ct.',
                            'value' => ( isset( $vs['carat'] ) ) ? mb_strtolower( $vs['carat'] ) : '-'
                        ],[
                            'name' => 'Форма огранки',
                            'value' => ( isset( $vs['forma'] ) ) ? mb_strtolower( $vs['forma'] ) : '-'
                        ]]
                    ];
                }

            }
            $prod['drag'] = json_encode( $drag );
            
            
            $paramItem = [[
                'variabled' => 'metall',
                'value' => '-'
            ],[
                'variabled' => 'material',
                'value' => '-'
            ],[
                'variabled' => 'vstavka',
                'value' => '-'
            ],[
                'variabled' => 'forma-vstavki',
                'value' => '-'
            ],[
                'variabled' => 'primernyy-ves',
                'value' => '-'
            ],[
                'variabled' => 'dlya-kogo',
                'value' => '-'
            ],[
                'variabled' => 'technologiya',
                'value' => '-'
            ]];
            
            
            if( isset( $paramART['vm'] ) ){
                $paramItem[0]['value'] = $paramART['vm'];
            }
            
            $paramItem[1]['value'] = 'Металл';
            if( isset( $item['vstavkiString'] ) ) $paramItem[2]['value'] = $item['vstavkiString'];
            if( isset( $item['ves'] ) ) $paramItem[4]['value'] = $item['ves'];
            
            $prod['params'] = json_encode( $paramItem );
            
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
            
            $kamenList = ['Без камня','С камнем','Кристалл Swarovski','Swarovski Zirconia','Бриллиант','Сапфир','Изумруд','Рубин','Жемчуг','Топаз','Аметист','Гранат','Хризолит','Цитрин','Агат','Кварц','Янтарь','Опал','Фианит','Родолит', 'Ситалл', 'Эмаль', 'Оникс', 'Корунд', 'Коралл прессованный'];
            
            $kamenListVals = ['empty','no_empty','swarovski','swarovski','brilliant','sapfir','izumrud','rubin','jemchug','topaz','ametist','granat','hrizolit','citrin','agat','kvarc','jantar','opal','fianit',
            'Rodolit', 'Sitall', 'Emal', 'Oniks', 'Korund', 'Corall_pressovannyi'];
            
            
            if( isset( $paramART['vm3'] ) ){
                $filterData[0]['values'][] = $paramART['vm3'];
            }
            
            
            $text_vstavki = '';
            if( isset( $item['vstavki'] ) ){
                foreach( $item['vstavki'] as $vs ){
                    $text_vstavki .= ' ' . $vs['kamen'];
                }
            }
            
            foreach( $kamenList as $pk => $pv ){
                $str_text = mb_strtolower( $text_vstavki );
                $str_find = '/' . mb_strtolower( $pv ) . '/iU';
                if ( preg_match($str_find, $str_text)) {
                    $filterData[1]['values'][] = $kamenListVals[$pk];
                }
            }
            
            if( count( $filterData[1]['values'] ) > 1 ){
                $filterData[1]['values'][] = 'no_empty';
            }else{
                $filterData[1]['values'][] = 'empty';
            }
            
            
            $razmerList = ['2.0','12.0','13.0','13.5','14.0','14.5','15.0','15.5','16.0','16.5','17.0','17.5','18.0','18.5','19.0','19.5','20.0','20.5','21.0','21.5','22.0','22.5','23.0','23.5','24.0','24.5','25.0'];
            $razmerListVals = ['2_0','12_0','13_0','13_5','14_0','14_5','15_0','15_5','16_0','16_5','17_0','17_5','18_0','18_5','19_0','19_5','20_0','20_5','21_0','21_5','22_0','22_5','23_0','23_5','24_0','24_5','25_0'];
            
            if( isset( $item['size'] ) ){
                $sz = str_replace( ",", ".", $item['size'] );
                $paramItem[4]['value'] = $sz;
                foreach( $razmerList as $rk => $rv ){
                    if( $rv === $sz ){
                        $filterData[4]['values'][] = $razmerListVals[$rk];
                    }
                }                    
            }
            
            $prod['filters'] = json_encode( $filterData );
            $prod['size'] = ( isset( $item['size'] ) ) ? str_replace( ",", ".", $item['size'] ) : '0';
            
            
            return $prod;
            /* 
            echo "<pre>";
            print_r( $prod );
            echo "</pre>";  
           */
       }
    }
    
    // Сохранить пхото как...
    public function getFile( $otcuda = false, $kuda = './', $newName = '1' ){
        
         $this->access_dynamic();
         
        if( $otcuda !== false ){
            
            $conn_id = ftp_connect('ftp.estet.ru');
        
            // вход с именем пользователя и паролем
            $login_result = ftp_login( $conn_id, 'ftp1808', 'ftp1808');
            
            $server_file = urldecode( $otcuda ); //картинка которую скачиваем 
            $local_file = $kuda.$newName; //имя картинка под которым сохраняем 
                        
            // включение пассивного режима
            ftp_pasv($conn_id, true);
            
            if ( @ftp_get( $conn_id, $local_file, $server_file, FTP_BINARY ) ) {
                return true;
            } else {
                return false;
            }
            
            ftp_close($conn_id);
        }
        
    }
    
    // Сохранение картинок
    public function saveImages ( $image = false, $nameProduct = false ){        
        $r = false;
        if( $image !== false && $nameProduct !== false ){            
                       
            $path = "./uploads/products/estet/"; 
            if ( file_exists( $path.$image ) ) {           
                $itemFile = $this->images->file_item( $path . $image, $nameProduct.'.jpg' );            
                $prew = "./uploads/products/100/";
                $prew2 = "./uploads/products/250/";
                $grozz = "./uploads/products/500/";
                
                $this->images->imageresize( $prew.$nameProduct.'.jpg', $path.$image, 100, 100, 100 );
                $this->images->imageresize( $prew2.$nameProduct.'.jpg', $path.$image, 250, 250, 100 );   
                $this->images->imageresize( $grozz.$nameProduct.'.jpg', $path.$image, 500, 500, 100 );     
                //$this->getImage( $path.$image, $grozz, $nameProduct.".jpg" );
                //$this->images->imageresize( $prew.$nameProduct.'.jpg', $path.$image, 500, 500, 100 );
                
                //$this->images->resize_jpeg( $itemFile, $path, $prew, $nameProduct, 100, 100, 100);
                ///$this->images->resize_jpeg( $itemFile, $path, $prew2, $nameProduct, 100, 250, 250);    
                //$this->images->resize_jpeg( $itemFile, $path, $grozz, $nameProduct, 100, 1000, 500); 
                $r = true;
            }
        }    
        return $r;            
    }
    
}


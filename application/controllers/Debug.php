<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Debug extends CI_Controller  {
    
    public function __construct() {    
        parent::__construct();  
    } 
    
    // Убрать товары из выдачи у которых нет картинок
    public function mathImages(){
        $allPhotos = $this->mdl_product->queryData([
            'return_type' => 'ARR2',
            'table_name' => 'products_photos'
        ]);      
        $path = "./uploads/products/500/"; 
        $c = 0;
        foreach( $allPhotos as $v ){
            $f = $path.$v['photo_name'];
            if ( filesize( $f ) < 2 ) {
                $this->mdl_db->_update_db( "products", "id", $v['product_id'], [
                    'moderate' => '0'
                ]);  
                $c++;        
            }
        }
        echo $c;
    }
       
    // Удалить весь каталог Алькора
    public function removeAlcorCatalog(){
        
        
        $r = $this->mdl_product->queryData([
            'return_type' => 'ARR2',
            'where' => [
                'method' => 'AND',
                'set' => [[
                    'item' => 'postavchik',
                    'value' => 'alcor'
                ]]
            ],
            'labels' => ['id', 'aliase', 'title',  'modules']
        ]);
        
        $PIDs = [];
        foreach( $r as $v ){
            if( !in_array( $v['id'], $PIDs ) ) $PIDs[] = $v['id'];
        }
        
        
        $phList = $this->mdl_product->queryData([
            'table_name' => 'products_photos',
            'in' => [
                'method' => 'AND',
                'set' => [[
                    'item' => 'product_id',
                    'values' => $PIDs
                ]]
            ]
        ]);
        
        foreach( $phList as $v ){
            @unlink('./uploads/products/100/' . $v['photo_name'] );
            @unlink('./uploads/products/250/' . $v['photo_name'] );
            @unlink('./uploads/products/500/' . $v['photo_name'] );
        }
        
        
        $this->db->where_in( 'id', $PIDs );
        $this->db->delete( 'products' );
        
        $this->db->where_in( 'product_id', $PIDs );
        $this->db->delete( 'products_photos' );
        
        $this->db->where_in( 'product_id', $PIDs );
        $this->db->delete( 'products_prices' );
        
    }
    
    // Удалить всё лишнее ( Оптимизировать  размер )
    public function removeAllCatalog(){
        
        // Взять все существующие товары
        $r = $this->mdl_product->queryData([
            'return_type' => 'ARR2',
            'labels' => ['id', 'aliase', 'title',  'modules']
        ]);
        
        // Собрать ИДтификаторы
        $PIDs = [];
        foreach( $r as $v ){
            if( !in_array( $v['id'], $PIDs ) ) $PIDs[] = $v['id'];
        }
        
        // Все фотки этих товаров
        $phList = $this->mdl_product->queryData([
            'return_type' => 'ARR2',
            'table_name' => 'products_photos',
            'in' => [
                'method' => 'AND',
                'set' => [[
                    'item' => 'product_id',
                    'values' => $PIDs
                ]]
            ]
        ]);
        
        // Фсе фотки - АБСОЛЮТНО
        $issetPhotos = $this->mdl_product->queryData([
            'return_type' => 'ARR2',
            'table_name' => 'products_photos'
        ]);
        
        // Названия фоток существующих товаров
        $isset100 = [];
        foreach ( $phList as $pl ) {
           if( !in_array( $pl['photo_name'], $isset100 ) ) $isset100[] = $pl['photo_name'];
        }
        
        // Если остались лишние записи в БД - удалить
        // Если остались левые картинки в Директории - удалить их
        foreach ( $issetPhotos as $ip ) {
            if( !in_array( $ip['photo_name'], $isset100 ) ){
                @unlink('./uploads/products/100/' . $ip['photo_name'] );
                @unlink('./uploads/products/250/' . $ip['photo_name'] );
                @unlink('./uploads/products/500/' . $ip['photo_name'] );                
                $this->db->where( 'photo_name', $ip['photo_name'] );
                $this->db->delete( 'products_photos' );                
            }
        }
        
        // Если есть битые картинки - удаляем их и записи в БД
        // Товары ставим как неотмодерированные
        $path = "./uploads/products/500/";
        foreach ( $phList as $v ) {
            $f = $path.$v['photo_name'];
            if ( filesize( $f ) < 1 ) {
                $this->mdl_db->_update_db( "products", "id", $v['product_id'], [
                    'moderate' => '0'
                ]);    
                @unlink('./uploads/products/100/' . $v['photo_name'] );
                @unlink('./uploads/products/250/' . $v['photo_name'] );
                @unlink('./uploads/products/500/' . $v['photo_name'] );     
            }
        }
        
        // Найти все ненужные фотки в директории
        $mask = "./uploads/products/100/*.jpg";
        foreach ( glob( $mask ) as $filename ) {
            $fileName2 = str_replace( "./uploads/products/100/", "", $filename );
            if( !in_array( $fileName2, $isset100 ) ){
                @unlink('./uploads/products/100/' . $fileName2 );
                @unlink('./uploads/products/250/' . $fileName2 );
                @unlink('./uploads/products/500/' . $fileName2 ); 
            }
        }
        
    }
    
    public function unitTest( $id ){
        
        $ret = $this->setRozCena( $id );
                
        echo "<pre>";
        print_r( $ret );
        echo "</pre>";
        
    }
    
    // массовое обновление цен
    public function prices_update(){
        
        $r = $this->mdl_product->queryData([
            'return_type' => 'ARR2',
            'where' => [
                'method' => 'AND',
                'set' => [[
                    'item' => 'view >',
                    'value' => 0
                ],[
                    'item' => 'cat >',
                    'value' => 0
                ],[
                    'item' => 'qty >',
                    'value' => 0
                ],[
                    'item' => 'moderate >',
                    'value' => 1
                ]]
            ],
            'labels' => ['id']
        ]);
        
        foreach( $r as $v ){
            $ret = $this->setRozCena( $v['id'] );
            if( $ret['price_r'] !== 'МИНУС' ){
                
                $end = $ret['price_r'] * 100;
                
                $upd = [
                    'price_roz' => $end,
                    'salle_procent' => $ret['procSkidca']
                ];
                
                $this->mdl_db->_update_db( "products", "id", $v['id'], $upd );
                
            }
        }
        
    }
    
    // Рендер розничной цены
    public function setRozCena( $product_id = false ){
        
        $res = [];
        if( $product_id !== false ){
            
            $r = $this->mdl_product->queryData([
                'return_type' => 'ARR1',
                'where' => [
                    'method' => 'AND',
                    'set' => [[
                        'item' => 'id',
                        'value' => $product_id
                    ]]
                ],
                'labels' => ['id', 'title', 'price_zac']
            ]);
            
            $F1 = [
                'бриллиант' => 1,
                'сапфир' => 1,
                'рубин' => 1,
                'изумруд' => 1,
                'авантюрин' => 2,
                'азурит' => 2,
                'аквамарин' => 2,
                'аметист' => 2,
                'бирюза' => 2,
                'берилл' => 2,
                'везувиан' => 2,
                'варисцид' => 2,
                'агат' => 2,
                'горный хрусталь' => 2,
                'гранат' => 2,
                'жадеит' => 2,
                'жемчуг' => 2,
                'змеевик' => 2,
                'зонохлорит' => 2,
                'кварц' => 2,
                'корунд' => 2,
                'кошачий глаз' => 2,
                'лунный камень' => 2,
                'лазурит' => 2,
                'малахит' => 2,
                'макаит' => 2,
                'нефрит' => 2,
                'нефелин' => 2,
                'обсидиан' => 2,
                'опал' => 2,
                'оникс' => 2,
                'родонит' => 2,
                'раухтопаз' => 2,
                'сардоникс' => 2,
                'сердолик' => 2,
                'топаз' => 2,
                'тигровый глаз' => 2,
                'турмалин' => 2,
                'халцедон' => 2,
                'хризоберилл' => 2,
                'хризолит' => 2,
                'цитрин' => 2,
                'циркон' => 2,
                'янтарь' => 2,
                'яшма' => 2
            ];
                    
            $title = $r['title'];
            
            $setFormula = 3;
            
            foreach( $F1 as $k => $v ){
                $str_text = mb_strtolower( $title );
                $str_find = '/' . mb_strtolower( $k ) . '/iU';
                if ( preg_match($str_find, $str_text)) {
                    $setFormula = $v;
                }
            }
            
            $price_z = $r['price_zac'] / 100;
            $price_r = 0;
            
            if( $setFormula == 1 ){
                
                $proc_nacenca = 135;
                $proc_skidka = rand( 32, 40 );
                $summa_nacenka = ( $price_z * ( $proc_nacenca * 0.01 )); // 23692,5
                $summa_plus_nacenka = $price_z + $summa_nacenka;
                $summa_skidki = $summa_plus_nacenka * ( $proc_skidka * 0.01 ); // 9477
                $summa_minus_skidka = $summa_plus_nacenka - $summa_skidki;
                $price_r = ( $summa_minus_skidka < $price_z ) ? 'МИНУС' : $summa_minus_skidka;
                $summa_navara = $price_r - $price_z;
                
                
            }
            
            if( $setFormula == 2 ){
                
                $proc_nacenca = 200;
                $proc_skidka = rand( 42, 50 );
                $summa_nacenka = ( $price_z * ( $proc_nacenca * 0.01 )); // 23692,5
                $summa_plus_nacenka = $price_z + $summa_nacenka;
                $summa_skidki = $summa_plus_nacenka * ( $proc_skidka * 0.01 ); // 9477
                $summa_minus_skidka = $summa_plus_nacenka - $summa_skidki;
                $price_r = ( $summa_minus_skidka < $price_z ) ? 'МИНУС' : $summa_minus_skidka;
                $summa_navara = $price_r - $price_z;
                
            }
            
            if( $setFormula == 3 ){
                
                $proc_nacenca = 100;
                $proc_skidka = rand( 26, 30 );
                $summa_nacenka = ( $price_z * ( $proc_nacenca * 0.01 )); // 23692,5
                $summa_plus_nacenka = $price_z + $summa_nacenka;
                $summa_skidki = $summa_plus_nacenka * ( $proc_skidka * 0.01 ); // 9477
                $summa_minus_skidka = $summa_plus_nacenka - $summa_skidki;
                $price_r = ( $summa_minus_skidka < $price_z ) ? 'МИНУС' : $summa_minus_skidka;
                $summa_navara = $price_r - $price_z;
                
            }
            
            $res = [
                'title' => $title,
                'formula' => $setFormula,
                'nachStoimost' => $price_z,
                'procNacenca' => $proc_nacenca,
                'procSkidca' => $proc_skidka,
                'summaNacenki' => $summa_nacenka,
                'summSkidki' => $summa_skidki,
                'pribil' => $summa_navara,
                'summaSnacenkoy' => $summa_plus_nacenka,
                'price_r' => $price_r
            ];
            
        }
        
        return $res;
        
    }
    
    // Исправление для веса
    public function updWeight(){
        
        $r = $this->mdl_product->queryData([
            'return_type' => 'ARR2',
            'where' => [
                'method' => 'AND',
                'set' => [[
                    'item' => 'view >',
                    'value' => 0
                ],[
                    'item' => 'cat >',
                    'value' => 0
                ],[
                    'item' => 'qty >',
                    'value' => 0
                ],[
                    'item' => 'moderate >',
                    'value' => 1
                ]]
            ],
            'labels' => ['id','weight','params']
        ]);
        
        foreach( $r as $v ){
            
            if( $v['weight'] > 0 ){
                
                $param = json_decode( $v['params'], true );  
                
                $new_param = [];
                
                foreach( $param as $v2 ){
                    if( $v2['variabled'] === 'primernyy-ves' ){
                        $new_param[] = [
                            'variabled' => $v2['variabled'],
                            'value' => $v['weight']
                        ];
                    }else{
                        $new_param[] = $v2;
                    }
                }
                
                $upd['params'] = json_encode( $new_param );
                
                $this->mdl_db->_update_db( "products", "id", $v['id'], $upd );
                
            }
            
        }
        
    }
    
    public function test(){
        /*
        $r = $this->mdl_product->queryData([
            'return_type' => 'ARR1',
            'where' => [
                'method' => 'AND',
                'set' => [[
                    'item' => 'id',
                    'value' => 769
                ]]
            ]
        ]);
        
        $r = $this->mdl_product->getRozCena( 769 );
       
        echo "<pre>";
        print_r( $r );
        echo "</pre>";
         */
         
        /* $r = $this->mdl_product->queryData();
        foreach( $r as $v ){
            $this->mdl_product->getRozCena( $v['id'] );
        } */
        
        //
        /* 
        echo "<pre>";
        print_r( count( $r ) );
        echo "</pre>";
         
       foreach( $r as $v ){
            $this->mdl_db->_update_db( "products", "id", $v['id'], [
                'price_zac' => ( $v['price_zac'] * 100 ),
                'price_roz' => ( $v['price_roz'] * 100 )
            ]);
        } */
        
    }
    
    
    
    public function setDragSet(){
        
        $r = $this->mdl_product->queryData([
            'return_type' => 'ARR2',
            'where' => [
                'method' => 'AND',
                'set' => [[
                    'item' => 'postavchik',
                    'value' => 'alcor'
                ]]
            ],
            'labels' => ['id', 'title', 'optionLabel']
        ]);
        
        foreach( $r as $prod ){
            
            
            $F1 = [
                /*'авантюрин' => 2,
                'азурит' => 2,
                'аквамарин' => 2,
                'аметист' => 2,
                'бирюза' => 2,
                'берилл' => 2,
                'везувиан' => 2,
                'варисцид' => 2,
                'агат' => 2,
                'горный хрусталь' => 2,
                'гранат' => 2,
                'жадеит' => 2,
                'жемчуг' => 2,
                'змеевик' => 2,
                'зонохлорит' => 2,
                'кварц' => 2,
                'корунд' => 2,
                'кошачий глаз' => 2,
                'лунный камень' => 2,
                'лазурит' => 2,
                'малахит' => 2,
                'макаит' => 2,
                'нефрит' => 2,
                'нефелин' => 2,
                'обсидиан' => 2,
                'опал' => 2,
                'оникс' => 2,
                'родонит' => 2,
                'раухтопаз' => 2,
                'сардоникс' => 2,
                'сердолик' => 2,
                'топаз' => 2,
                'тигровый глаз' => 2,
                'турмалин' => 2,
                'халцедон' => 2,
                'хризоберилл' => 2,
                'хризолит' => 2,
                'цитрин' => 2,
                'циркон' => 2,
                'янтарь' => 2,
                'яшма' => 2,*/
                'Бриллиант' => ['Камень', 'Кол-во камней', 'Вес, Ct.', '-', 'Форма огранки', '-', '-', 'Кол-во граней'],
                'Сапфир' => ['Камень', 'Кол-во камней', 'Вес, Ct.', 'Чистота/Цвет', 'Форма огранки', '-', '-', 'Кол-во граней'],
                'г.т.сапфир' => ['Камень', 'Кол-во камней', 'Вес, Ct.', 'Чистота/Цвет', 'Форма огранки', '-', '-', 'Кол-во граней'],
                'рубин' => ['Камень', 'Кол-во камней', 'Вес, Ct.', 'Чистота/Цвет', 'Форма огранки', '-', '-', 'Кол-во граней'],
                'изумруд' => ['Камень', 'Кол-во камней', 'Вес, Ct.', 'Чистота/Цвет', 'Форма огранки', '-', '-', 'Кол-во граней']
            ];
                    
            $title = $prod['title'];
            
            $setFormula = [];
            
            foreach( $F1 as $k => $v ){
                $str_text = mb_strtolower( $title );
                $str_find = '/' . mb_strtolower( $k ) . '/iU';
                if ( preg_match($str_find, $str_text)) {
                    $setFormula[$k] = $v;
                }
            }
            
            
            
            if( count( $setFormula ) > 0 ){
                $ol = json_decode( $prod['optionLabel'], true );                
                echo $ol['seria'] . '<br />';                
                $textOptions = explode( ',', $ol['seria']);                
                $itog = [];
                foreach( $textOptions as $tok => $tov ){
                    $setArr = explode( ' ', $tov );
                    if( count( $setArr ) > 0 ){
                        $setArr = array_values(array_diff($setArr, array('')));                        
                        $formula = ( isset( $setFormula[$setArr[0]] ) ) ? $setFormula[$setArr[0]] : false;
                        if( $formula !== false ){
                            foreach( $formula as $fk => $fv ){
                                $itog[$setArr[0]][] = [
                                    'name' => $fv,
                                    'value' => ( isset( $setArr[$fk] ) ) ? $setArr[$fk] : '-'
                                ];
                            }
                        }
                    }
                }
                
                
                if( count( $itog ) > 0 ){
                    $this->mdl_db->_update_db( "products", "id", $prod['id'], [
                        'drag' => json_encode( $itog )
                    ]);
                }
                
                /*echo "<pre>";
                print_r( $itog );
                echo "</pre>";                
                
                echo json_encode( $itog );
                */
            }
            
            
        }
        
        
        
    }
    
    public function formulaPatterns ( $product_id = false ){
        
        $res = [];
        if( $product_id !== false ){
            
            $r = $this->mdl_product->queryData([
                'return_type' => 'ARR1',
                'where' => [
                    'method' => 'AND',
                    'set' => [[
                        'item' => 'id',
                        'value' => $product_id
                    ]]
                ],
                'labels' => ['id', 'title', 'optionLabel']
            ]);
        
            
            $F1 = [
                'авантюрин' => 2,
                'азурит' => 2,
                'аквамарин' => 2,
                'аметист' => 2,
                'бирюза' => 2,
                'берилл' => 2,
                'везувиан' => 2,
                'варисцид' => 2,
                'агат' => 2,
                'горный хрусталь' => 2,
                'гранат' => 2,
                'жадеит' => 2,
                'жемчуг' => 2,
                'змеевик' => 2,
                'зонохлорит' => 2,
                'кварц' => 2,
                'корунд' => 2,
                'кошачий глаз' => 2,
                'лунный камень' => 2,
                'лазурит' => 2,
                'малахит' => 2,
                'макаит' => 2,
                'нефрит' => 2,
                'нефелин' => 2,
                'обсидиан' => 2,
                'опал' => 2,
                'оникс' => 2,
                'родонит' => 2,
                'раухтопаз' => 2,
                'сардоникс' => 2,
                'сердолик' => 2,
                'топаз' => 2,
                'тигровый глаз' => 2,
                'турмалин' => 2,
                'халцедон' => 2,
                'хризоберилл' => 2,
                'хризолит' => 2,
                'цитрин' => 2,
                'циркон' => 2,
                'янтарь' => 2,
                'яшма' => 2,
                'Бриллиант' => ['Камень', 'Кол-во камней', 'Вес в каратах', 'Чистота/Цвет', 'Форма огранки', '?1', '?2', 'Кол-во граней'],
                'Сапфир' => ['Камень', 'Кол-во камней', 'Вес в каратах', 'Чистота/Цвет', 'Форма огранки', '?1', '?2', 'Кол-во граней'],
                'г.т.сапфир' => ['Камень', 'Кол-во камней', 'Вес в каратах', 'Чистота/Цвет', 'Форма огранки', '?1', '?2', 'Кол-во граней'],
                'рубин' => ['Камень', 'Кол-во камней', 'Вес в каратах', 'Чистота/Цвет', 'Форма огранки', '?1', '?2', 'Кол-во граней'],
                'изумруд' => ['Камень', 'Кол-во камней', 'Вес в каратах', 'Чистота/Цвет', 'Форма огранки', '?1', '?2', 'Кол-во граней']
            ];
                    
            $title = $r['title'];
            
            $setFormula = [];
            
            foreach( $F1 as $k => $v ){
                $str_text = mb_strtolower( $title );
                $str_find = '/' . mb_strtolower( $k ) . '/iU';
                if ( preg_match($str_find, $str_text)) {
                    $setFormula[$k] = $v;
                }
            }
            
            
            
            if( count( $setFormula ) > 0 ){
                $ol = json_decode( $r['optionLabel'], true );                
                echo $ol['seria'] . '<br />';                
                $textOptions = explode( ',', $ol['seria']);                
                $itog = [];
                foreach( $textOptions as $tok => $tov ){
                    $setArr = explode( ' ', $tov );
                    if( count( $setArr ) > 0 ){
                        $setArr = array_values(array_diff($setArr, array('')));                        
                        $formula = ( isset( $setFormula[$setArr[0]] ) ) ? $setFormula[$setArr[0]] : false;
                        if( $formula !== false ){
                            foreach( $formula as $fk => $fv ){
                                $itog[$setArr[0]][] = [
                                    'name' => $fv,
                                    'value' => ( isset( $setArr[$fk] ) ) ? $setArr[$fk] : '-'
                                ];
                            }
                        }
                    }
                }
                echo "<pre>";
                print_r( $itog );
                echo "</pre>";                
                /*
                echo json_encode( $itog );
                */
            }
        }
        
        
        
    }
    
    
}
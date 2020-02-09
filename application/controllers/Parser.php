<?php

	defined('BASEPATH') OR exit('No direct script access allowed');
	
	class Parser extends CI_Controller  {
		
		public function __construct() {			
			parent::__construct(); 			
		} 
		
		public function index(){
			
		}
				
        public function parseProductSokolovtm(){
            
            $r = [ 'err' => 1, 'mess' => '', 'link' => false ];
            
            $product = $this->mdl_product->queryData([
                'return_type' => 'ARR1',
                'where' => [
                    'method' => 'AND',
                    'set' => [[
                        'item' => 'moderate <',
                        'value' => 1
                    ]]
                ]
			]);
            
            if( $product ){
                
                $this->mdl_db->_update_db( "products", "id", $product['id'], [
                    'moderate' => 1
                ]);
                
                $result = $this->getRender( $product['articul'], true );
                
                if( $result !== false ){
                    
                    $item = $result['item'];
                    $upd = $result['upd']; 
                    $link = $result['link']; 
                    
                    $this->mdl_db->_update_db( "products", "id", $product['id'], $upd );
                    
                    $this->getImage( $item['img100'], './uploads/products/100/', $upd['aliase'].".jpg" );
                    $this->getImage( $item['img250'], './uploads/products/250/', $upd['aliase'].".jpg" );
                    $this->getImage( $item['img500'], './uploads/products/500/', $upd['aliase'].".jpg" );
                    
                    $this->db->insert('products_photos', [
                        'product_id' => $product['id'],
                        'photo_name' => $upd['aliase'].".jpg",
                        'define' => '1'
                    ]);
                    
                    $r = [ 'err' => 0, 'mess' => 'success', 'link' => $link ];
                    
                }
                
            }
            
            echo json_encode( $r );
            
        }
        
        public function parseProductSokolovtmToItem( $articul = false ){
            
            $r = [ 'err' => 1, 'mess' => '', 'link' => false ];
            
            $product = $this->mdl_product->queryData([
                'return_type' => 'ARR1',
                'where' => [
                    'method' => 'AND',
                    'set' => [[
                        'item' => 'articul',
                        'value' => $articul
                    ]]
                ]
			]);
            
            if( $product ){
                
                $this->mdl_db->_update_db( "products", "id", $product['id'], [
                    'moderate' => 1
                ]);
                
                $result = $this->getRender( $product['articul'], true );
                /*
				echo "<pre>";
				print_r( $result );
				echo "</pre>";
				
				*/
                if( $result !== false ){
                    
                    $item = $result['item'];
                    $upd = $result['upd']; 
                    $link = $result['link']; 
                    
                    $this->mdl_db->_update_db( "products", "id", $product['id'], $upd );
                    
                    $this->getImage( $item['img100'], './uploads/products/100/', $upd['aliase'].".jpg" );
                    $this->getImage( $item['img250'], './uploads/products/250/', $upd['aliase'].".jpg" );
                    $this->getImage( $item['img500'], './uploads/products/500/', $upd['aliase'].".jpg" );
                    
                    $this->db->insert('products_photos', [
                        'product_id' => $product['id'],
                        'photo_name' => $upd['aliase'].".jpg",
                        'define' => '1'
                    ]);
                    
                    $r = [ 'err' => 0, 'mess' => 'success', 'link' => $link ];
                    
                }
                
            }
            
            echo json_encode( $r );
            
        }
        
        
        public function getRender( $articul = false, $return_type = false ){
            
            $r = false;
            $product = false;
            
            if( $articul !== false ){
                $product = $this->mdl_product->queryData([
                    'return_type' => 'ARR1',
                    'where' => [
                        'method' => 'AND',
                        'set' => [[
                            'item' => 'articul',
                            'value' => $articul
                        ]]
                    ]
                ]);
            }
            
            if( $product !== false ) {
                
                $articul = $product['articul'];
                    
                $link = 'https://sokolov.ru/jewelry-catalog/product/'. $articul;
                include FCPATH . 'addons/SDH/simple_html_dom.php';
                $html = file_get_html( $link  );
                    
                if( $html ) {
                    if( !$html->find('.b-not-found__title', 0) ){
                        
                        $item = [];
                        
                        $item['h1'] = $html->find('h1', 0)->plaintext;                        
                        $item['description'] = ( $html->find('div.b-product-description__text-description', 0) ) ? $html->find('div.b-product-description__text-description', 0)->find('p', 0)->plaintext : '';      
                        
                        $item['img100'] = 'https://sokolov.ru/ru/images/jewelry/100/'.$articul.'.jpg';
                        $item['img250'] = 'https://sokolov.ru/ru/images/jewelry/250/'.$articul.'.jpg';
                        $item['img500'] = 'https://sokolov.ru/ru/images/jewelry/500/'.$articul.'.jpg';
                                                    
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
                        
                        $razmerList = ['2.0','12.0','13.0','13.5','14.0','14.5','15.0','15.5','16.0','16.5','17.0','17.5','18.0','18.5','19.0','19.5','20.0','20.5','21.0','21.5','22.0','22.5','23.0','23.5','24.0','24.5','25.0'];
                        $razmerListVals = ['2_0','12_0','13_0','13_5','14_0','14_5','15_0','15_5','16_0','16_5','17_0','17_5','18_0','18_5','19_0','19_5','20_0','20_5','21_0','21_5','22_0','22_5','23_0','23_5','24_0','24_5','25_0'];
                        
                        $metallList = ['Комбинированное золото','Красное золото','Белое золото','Серебро'];
                        $metallListVals = ['kombinZoloto','krasnZoloto','belZoloto','serebro'];
                        
                        $formaList = ['Кабошон','Круг','Овал','Груша','Маркиз', 'Багет', 'Квадрат', 'Октагон', 'Триллион', 'Сердце', 'Кушон', 'Пятигранник', 'Шестигранник', 'Восьмигранник'];
                        $formaListVals = ['Kaboshon','Krug','Oval','Grusha','Markiz', 'Baget', 'Kvadrat', 'Oktagon', 'Trillion', 'Serdtce', 'Kushon', 'Piatigranniq', 'Shestigranniq', 'Vosmigrannic'];
                        
                        $dlaKogo = ['Для женщин','Для мужчин', 'Для женщин, Для мужчин'];
                        $dlaKogoVals = ['woman','men', 'unisex'];
                            
                        $i = 0;
                        
                        
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
                            $str_text = trim( $product['size'] );
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
                            
                        $item['params'] = $params;
                        $item['filters'] = $filterData;
                        
                        $upd = [];
                        $upd['sex'] = $item['sex'];
                        
                        $upd['aliase'] = $this->mdl_product->aliase_translite(  $item['h1'] .'-'. $product['articul']  .'-'. $product['id'] );
                        $upd['title'] = $item['h1'];
                        $upd['description'] = $item['description'];
                        $upd['params'] = json_encode( $item['params'] );
                        $upd['filters'] = json_encode( $item['filters'] );
                        
                        $upd['moderate'] = 2;
                        $upd['view'] = 1;
                            
                        $r = [
                            'upd' => $upd,
                            'item' => $item,
                            'link' => $link
                        ];
                    
                    }
                }
                
            }
            
            if ( $return_type !== false ){
                return $r;
            }else{
                
                
                
                /*
                echo "<pre>";
                print_r( $r );
                echo "</pre>";*/
            }
            
        }
                
		public function getImage( $src = false, $path = './', $newName = '1.jpg' ){
			$t = file_get_contents( $src );
			file_put_contents( $path . $newName, $t );
		}
				
		// Создание параметров характеристик для товара
		public function paramSet (){
			
			$paramItem = [[
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
			
			echo json_encode( $paramItem, true );
			
		}
				
		public function setRangePrice (){
			
			$r = [
            'type' => 'range-values',
            'title' => 'Цена',
            'variabled' => 'price',
            'impact' => 'price',
            'data' => [[
			'title' => 'Цена от:',
			'value' => '0',
			'variabled' => 'ot'
			],[
			'title' => 'Цена до:',
			'value' => '900000',
			'variabled' => 'do'
			]
            ]
			];
			
			echo json_encode( $r );
		}
				
		public function paramsFilter(){
			
			/*$filterData = [[
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
			]];*/
			
			$f = [];
			
			// Установка металла
			$add = [
            'type' => "checkbox-group",
            'title' => 'Металл',
            'variabled' => 'metall',
            'impact' => 'filter',
            'data' => []
			];              
			$metallList = ['Комбинированное золото','Красное золото','Белое золото','Золочёное серебро','Чернёное серебро'];
			$metallListVals = ['kombinZoloto','krasnZoloto','belZoloto','zoloZoloto','chernZoloto'];            
			foreach( $metallList as $k => $v ){
				$add['data'][] = [
                'title' => $v,
                'variabled' => $metallListVals[$k]                        
				];
			}            
			$f[] = $add;
			
			// Установка для кого
			$add = [
            'type' => "checkbox-group",
            'title' => 'Для кого',
            'variabled' => 'sex',
            'impact' => 'filter',
            'data' => []
			];              
			$dlaKogo = ['Для женщин','Для мужчин', 'Для женщин, Для мужчин'];
			$dlaKogoVals = ['woman','men', 'unisex'];          
			foreach( $dlaKogo as $k => $v ){
				$add['data'][] = [
                'title' => $v,
                'variabled' => $dlaKogoVals[$k]                        
				];
			}            
			$f[] = $add;
			
			// Установка размера
			$add = [
            'type' => "checkbox-group",
            'title' => 'Размер',
            'variabled' => 'size',
            'impact' => 'filter',
            'data' => []
			];              
			$razmerList = ['2.0','12.0','13.0','13.5','14.0','14.5','15.0','15.5','16.0','16.5','17.0','17.5','18.0','18.5','19.0','19.5','20.0','20.5','21.0','21.5','22.0','22.5','23.0','23.5','24.0','24.5','25.0'];
			$razmerListVals = ['2_0','12_0','13_0','13_5','14_0','14_5','15_0','15_5','16_0','16_5','17_0','17_5','18_0','18_5','19_0','19_5','20_0','20_5','21_0','21_5','22_0','22_5','23_0','23_5','24_0','24_5','25_0'];        
			foreach( $razmerList as $k => $v ){
				$add['data'][] = [
                'title' => $v,
                'variabled' => $razmerListVals[$k]                        
				];
			}     
			$f[] = $add;     
			
			// Установка вставки
			$add = [
            'type' => "checkbox-group",
            'title' => 'Вставка',
            'variabled' => 'kamen',
            'impact' => 'filter',
            'data' => []
			];              
			$kamenList = ['Бриллиант','Сапфир','Изумруд','Рубин','Жемчуг','Топаз','Аметист','Гранат','Хризолит','Цитрин','Агат','Кварц','Янтарь','Опал','Фианит'];
			$kamenListVals = ['brilliant','sapfir','izumrud','rubin','jemchug','topaz','ametist','granat','hrizolit','citrin','agat','kvarc','jantar','opal','fianit'];     
			foreach( $kamenList as $k => $v ){
				$add['data'][] = [
                'title' => $v,
                'variabled' => $kamenListVals[$k]                        
				];
			}     
			$f[] = $add;
			
			// Установка формы вставки
			$add = [
            'type' => "checkbox-group",
            'title' => 'Форма вставки',
            'variabled' => 'forma_vstavki',
            'impact' => 'filter',
            'data' => []
			];			
			$formaList = ['Кабошон','Круг','Овал','Груша','Маркиз', 'Багет', 'Квадрат', 'Октагон', 'Триллион', 'Сердце', 'Кушон', 'Пятигранник', 'Шестигранник', 'Восьмигранник'];
			$formaListVals = ['Kaboshon','Krug','Oval','Grusha','Markiz', 'Baget', 'Kvadrat', 'Oktagon', 'Trillion', 'Serdtce', 'Kushon', 'Piatigranniq', 'Shestigranniq', 'Vosmigrannic']; 
			foreach( $formaList as $k => $v ){
				$add['data'][] = [
                'title' => $v,
                'variabled' => $formaListVals[$k]                        
				];
			}     
			$f[] = $add;
            
            /*
				echo json_encode( $f );
			*/
			echo "<pre>";
			print_r( $f );
			echo "</pre>";	
			
			
            
            
            // металл
            
            // для кого
            
            // размер
            
            // Камень
            
            // Форма вставки
            
            
            /*{"type":"checkbox-group","title":"Размер","variabled":"Razmer","impact":"products","data":[{"title":"2.0","value":"","variabled":"2_0"},{"title":"12.0","value":"","variabled":"12_0"},{"title":"13.0","value":"","variabled":"13_0"},{"title":"13.5","value":"","variabled":"13_5"},{"title":"14.0","value":"","variabled":"14_0"},{"title":"14.5","value":"","variabled":"14_5"},{"title":"15.0","value":"","variabled":"15_0"},{"title":"15.5","value":"","variabled":"15_5"},{"title":"16.0","value":"","variabled":"16_0"}]},
				
				{"type":"checkbox-group","title":"Проба","variabled":"Proba","impact":"products","data":[{"title":"585","value":"","variabled":"proba_585"},{"title":"925","value":"","variabled":"proba_925"}]},
				
			{"type":"checkbox-group","title":"Металл","variabled":"Metall","impact":"products","data":[{"title":"Золото","value":"","variabled":"met_zoloto"},{"title":"Серебро","value":"","variabled":"met_serebro"}]}*/
            
			/*
			[{"type":"range-values","title":"Цена","variabled":"Cena","impact":"price","data":[{"title":"Цена от:","value":"0","variabled":"_ot"},{"title":"Цена до:","value":"900000","variabled":"_do"}]},{"type":"checkbox-group","title":"Размер","variabled":"Razmer","impact":"products","data":[{"title":"2.0","value":"","variabled":"2_0"},{"title":"12.0","value":"","variabled":"12_0"},{"title":"13.0","value":"","variabled":"13_0"},{"title":"13.5","value":"","variabled":"13_5"},{"title":"14.0","value":"","variabled":"14_0"},{"title":"14.5","value":"","variabled":"14_5"},{"title":"15.0","value":"","variabled":"15_0"},{"title":"15.5","value":"","variabled":"15_5"},{"title":"16.0","value":"","variabled":"16_0"}]},{"type":"checkbox-group","title":"Проба","variabled":"Proba","impact":"products","data":[{"title":"585","value":"","variabled":"proba_585"},{"title":"925","value":"","variabled":"proba_925"}]},{"type":"checkbox-group","title":"Металл","variabled":"Metall","impact":"products","data":[{"title":"Золото","value":"","variabled":"met_zoloto"},{"title":"Серебро","value":"","variabled":"met_serebro"}]}]*/
			
			
		}
		
		// Масово обновить алиасы
		public function setUpdate (){
			
			//[{"item":"metall","values":["krasnZoloto"]},{"item":"kamen","values":["brilliant"]},{"item":"forma_vstavki","values":["Krug"]},{"item":"sex","values":["woman"]}]
			
			$product = $this->mdl_product->queryData([
			'return_type' => 'ARR2'
			]);
			foreach( $product  as $v ){
				
				$f = json_decode( $v['filters'], true );
				$a = [
                'item' => 'size',
                'values' => []
				];
				
				$razmerList = ['2.0','12.0','13.0','13.5','14.0','14.5','15.0','15.5','16.0','16.5','17.0','17.5','18.0','18.5','19.0','19.5','20.0','20.5','21.0','21.5','22.0','22.5','23.0','23.5','24.0','24.5','25.0'];
				$razmerListVals = ['2_0','12_0','13_0','13_5','14_0','14_5','15_0','15_5','16_0','16_5','17_0','17_5','18_0','18_5','19_0','19_5','20_0','20_5','21_0','21_5','22_0','22_5','23_0','23_5','24_0','24_5','25_0'];    
				
				foreach( $razmerList as $sk => $sv ){
					if( $v['size'] > 0 &&  $v['size'] === $sv ){
						$a['values'][] = $razmerListVals[$sk];
					}
				}    
				
				$f[] = $a;
				
				$upd['filters'] = json_encode( $f );
				$this->mdl_db->_update_db( "products", "id", $v['id'], $upd );
			}
		}
				
		// Об работка товаров ( Добавление серебра)
		public function setUpdateSerebro (){
			
			$product = $this->mdl_product->queryData([
			'return_type' => 'ARR2'/*,
				'like' => array( 
                'math' => 'both', // '%before', 'after%' и '%both%' - опциональность поиска
                'method' => 'AND', // AND (и) / OR(или) / NOT(за исключением и..) / OR_NOT(за исключением или)
                'set' => [[
				'item' => 'title',
				'value' => 'еребр'
                ]]  // [ 'item' => '', 'value' => '' ],[...]
			)*/
			]);
			
			
			
			foreach( $product  as $pk => $pv ){
				$f = json_decode( $pv['filters'], true );
				foreach( $f as $k => $v ){
					if( $v['item'] === 'kamen' ) {
						if( count( $v['values'] ) < 1 ){
							$f[$k]['values'][] = 'empty';
							$product[$pk]['new_filters'] = json_encode( $f );
							}else{
							$f[$k]['values'][] = 'no_empty';
							$product[$pk]['new_filters'] = json_encode( $f );
						}
					}
				}
			}
			
			/*foreach( $product as $pv ){
				$upd['filters'] = $pv['new_filters'];
				$this->mdl_db->_update_db( "products", "id", $pv['id'], $upd );
			}*/
			
			echo "<pre>";
			print_r( $product );
			echo "</pre>";
			
			//serebro
			
		}
		
		
	}					
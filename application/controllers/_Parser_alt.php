
		public function parseProductSokolovtm(){
			
			//$articul = $articul || '750228';
			
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
			
			if( $product ) {
                
				$articul = $product['articul'];
                
				$link = 'https://sokolov.ru/jewelry-catalog/product/'. $articul;
				include FCPATH . 'addons/SDH/simple_html_dom.php';
				$html = file_get_html( $link  );
							

                    $this->mdl_db->_update_db( "products", "id", $product['id'], [
                        'moderate' => 1
                    ]);
                            
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
						
						/*
							echo "<pre>";
							print_r( $params );
						echo "</pre>";*/
						
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
						
						$kamenList = ['Без камня','С камнем','Кристалл Swarovski','Swarovski Zirconia','Бриллиант','Сапфир','Изумруд','Рубин','Жемчуг','Топаз','Аметист','Гранат','Хризолит','Цитрин','Агат','Кварц','Янтарь','Опал','Фианит'];
						$kamenListVals = ['empty','no_empty','swarovski','swarovski','brilliant','sapfir','izumrud','rubin','jemchug','topaz','ametist','granat','hrizolit','citrin','agat','kvarc','jantar','opal','fianit'];
						
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
							//echo  $element->plaintext . '<br />';   
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
									/*
										$paramItem[$i] = [
										'item' => $paramItem[$i]['variabled'],
										'value' => trim( $element->plaintext )
									];*/
								}
								
								
							}
							if( $__i > 1 ){
                                $item['sex'] = 'man,woman';
								
								$params[$i]['value'] = 'Для женщин, Для мужчин';
								
							}
							
							if( $e < 1 ) $params[$i]['value'] = trim( $element->plaintext );
                            
                            if( $_i_ves == $i ){   
                                $params[$i]['value'] = $product['weight'];
                                //echo '--' . $text .'--';
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
                        
                        
                        
                        
						$this->mdl_db->_update_db( "products", "id", $product['id'], $upd );
						
						$this->getImage( $item['img100'], './uploads/products/100/', $upd['aliase'].".jpg" );
						$this->getImage( $item['img250'], './uploads/products/250/', $upd['aliase'].".jpg" );
						$this->getImage( $item['img500'], './uploads/products/500/', $upd['aliase'].".jpg" );
						
						$this->db->insert('products_photos', [
                            'product_id' => $product['id'],
                            'photo_name' => $upd['aliase'].".jpg",
                            'define' => '1'
						]);
						
						/*
							echo "<pre>";
							print_r( $item );
							echo "</pre>";
							
							echo "<pre>";
							print_r( $upd );
						echo "</pre>";*/
						/*
							echo $upd['aliase'].".jpg";
							
							$this->getImage( $item['img100'], './uploads/products/100/', $upd['aliase'].".jpg" );
							$this->getImage( $item['img250'], './uploads/products/250/', $upd['aliase'].".jpg" );
							$this->getImage( $item['img500'], './uploads/products/500/', $upd['aliase'].".jpg" );
							
							$this->db->insert('products_photos', [
							'product_id' => $product['id'],
							'photo_name' => $upd['aliase'].".jpg",
							'define' => '1'
							]);
							
							
							echo "<pre>";
							print_r( $upd );
							echo "</pre>";
							
						*/
						//
						
						
						
						
					}
				}
                
                
				$r = json_encode([ 'err' => 0, 'link' => $link ]);
				
			}else{
                $r = json_encode([ 'err' => 1, 'link' => false ]);
            }
			               
			echo $r;     
			
		}
		
		public function getRender( $articul = false ){
            
            // 6059001
            if( $articul === false ) exit('articul!!');
            
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
            
            if( ! $product ) exit('product!!!');
            
            $link = '';
            
            $articul = $product['articul'];
                
            $link = 'https://sokolov.ru/jewelry-catalog/product/'. $articul;
            include FCPATH . 'addons/SDH/simple_html_dom.php';
            $html = file_get_html( $link  );
            
            if( $html ) {
                
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
						
						$_new_params = []; $_i_ves = false;
						for( $i = 0; $i < count( $params ); $i++ ){
							$_new_params[] = $params[$i];
                            
                            if( $params[$i]['variabled'] == 'primernyy-ves' ){
                                $_i_ves = $i;
                            }
						}
						
						$params = $_new_params;
						
						/*
							echo "<pre>";
							print_r( $params );
						echo "</pre>";*/
						
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
						
						$kamenList = ['Без камня','С камнем','Кристалл Swarovski','Swarovski Zirconia','Бриллиант','Сапфир','Изумруд','Рубин','Жемчуг','Топаз','Аметист','Гранат','Хризолит','Цитрин','Агат','Кварц','Янтарь','Опал','Фианит'];
						$kamenListVals = ['empty','no_empty','swarovski','swarovski','brilliant','sapfir','izumrud','rubin','jemchug','topaz','ametist','granat','hrizolit','citrin','agat','kvarc','jantar','opal','fianit'];
                        
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
							//echo  $element->plaintext . '<br />';   
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
									$params[$i]['value'] = $dlaKogoVals[$pk];
                                    
                                    $item['sex'] = $dlaKogoVals[$pk];
                                       
									$e++;
									$__i++;
									/*
										$paramItem[$i] = [
										'item' => $paramItem[$i]['variabled'],
										'value' => trim( $element->plaintext )
									];*/
								}
								
								
							}
                            
                            
                            
							if( $__i > 1 ){
                                $item['sex'] = 'man,woman';
								$params[$i]['value'] = 'Для женщин, Для мужчин';
							}
							
                            
                            
                            //echo $e . ' -> ' .trim( $element->plaintext ) . '<br />';
                            
                            
							if( $e < 1 ) $params[$i]['value'] = trim( $element->plaintext );
                            
                            if( $_i_ves == $i ){   
                                $params[$i]['value'] = $product['weight'];
                                //echo '--' . $text .'--';
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
						$upd['params'] = $item['params'] ;
						$upd['filters'] =  $item['filters'] ;
                
                
                echo "<pre>";
                print_r ( $upd );
                echo "</pre>";
                
                
            }
            
            
            
        }
        
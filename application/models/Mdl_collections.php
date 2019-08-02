<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mdl_collections extends CI_Model {
    
    protected $get = array();
    
    public function __construct() {
        parent::__construct();             
    }
    
    // Выполнение запросов   
    protected $_query = array();
    public function queryData( $settings = array() ){
        
        $index = rand(1,1000);
        $_query = array(
           'where' => array(
                'method' => 'AND', // AND (и) / OR(или) 
                'set' => array() // [ 'item' => '', 'value' => '' ],[...]
            ),
           'in' => array(
                'method' => 'AND', // AND (и) / OR(или) / NOT(за исключением и..) / OR_NOT(за исключением или)
                'set' => array()  // [ 'item' => '', 'values' => '' ],[...]              
           ),
           'like' => array(
                'math' => 'both', // '%before', 'after%' и '%both%' - опциональность поиска
                'method' => 'AND', // AND (и) / OR(или) / NOT(за исключением и..) / OR_NOT(за исключением или)
                'set' => array()  // [ 'item' => '', 'value' => '' ],[...]
           ),
           'group_by' => false, // ["title", "date"] || "title"
           'order_by' => false,
           'distinct' => false, // +DISTINCT
           'limit' => false, // 10 || "10, 2"
           'labels' => false,
           'result' => array(), // Хранилище для обработки
           'result_option' => array(), // Резервное. хранилище
           'return_type' => 'ARR2', // ARR1 - одном. массив || ARR2 - многом. массив || ARR1+,ARR2+  - Упаковать с резервным хранилищем
           'debug' => false, // Диагностическая линия ( Необходим активный "+" в "return_type" )
           'module' => false,
           'modules' => array(),
           'pagination' => array(
                'on' => false,
                'page' => 1,
                'limit' => 'all',
                'count_all' => 0
            ),           
           'table_name' => 'collections'
        );
        $this->_query[$index] = array_merge( $_query, $settings );
        
        if( count ( $this->_query[$index]['where']['set'] ) > 0 ) {
            $lc = 0;
            foreach( $this->_query[$index]['where']['set'] as $value ){            
                $this->db->where( $value['item'], $value['value'] );
                if( $this->_query[$index]['where']['method'] === 'OR' && $lc > 0 ){
                    $this->db->or_where( $value['item'], $value['value'] );
                }
                $lc++;                
            }
        }
        
        if( count ( $this->_query[$index]['in']['set'] ) > 0 ) {
            $lc = 0;
            foreach( $this->_query[$index]['in']['set'] as $value ){                
                if( $this->_query[$index]['in']['method'] === 'OR' || $this->_query[$index]['in']['method'] === 'AND' ){
                    if( $this->_query[$index]['in']['method'] === 'OR' && $lc > 0 ){
                        $this->db->or_where_in( $value['item'], $value['values'] );
                    }else{
                        $this->db->where_in( $value['item'], $value['values'] );  
                    }
                    $lc++;
                }                
                if( $this->_query[$index]['in']['method'] === 'NOT' || $this->_query[$index]['in']['method'] === 'OR_NOT' ){
                    if( $this->_query[$index]['in']['method'] === 'OR_NOT' && $lc > 0 ){
                        $this->db->or_where_not_in( $value['item'], $value['values'] );
                    }else{
                        $this->db->where_not_in( $value['item'], $value['values'] );
                    }
                    $lc++;
                }                
            }
        }
        
        if( count ( $this->_query[$index]['like']['set'] ) > 0 ) {
            $lc = 0;
            foreach( $this->_query[$index]['like']['set'] as $value ){                
                if( $this->_query[$index]['like']['method'] === 'OR' || $this->_query[$index]['like']['method'] === 'AND' ){
                    
                    if( $this->_query[$index]['like']['method'] === 'OR' && $lc > 0 ){
                        $this->db->or_like( $value['item'], $value['value'], $this->_query[$index]['like']['math'] );
                    }else{
                        $this->db->like( $value['item'], $value['value'], $this->_query[$index]['like']['math'] );
                    }
                    $lc++;
                }                
                if( $this->_query[$index]['like']['method'] === 'NOT' || $this->_query[$index]['like']['method'] === 'OR_NOT' ){
                    
                    if( $this->_query[$index]['like']['method'] === 'OR_NOT' && $lc > 0 ){
                        $this->db->or_not_like( $value['item'], $value['value'], $this->_query[$index]['like']['math'] );
                    }else{
                        $this->db->not_like( $value['item'], $value['value'], $this->_query[$index]['like']['math'] );
                    }
                    $lc++;
                }                
            }
        }
        
        if( $this->_query[$index]['group_by']  !== false ) {
            $this->db->group_by( $this->_query[$index]['group_by'] ); 
        }
        
        if( $this->_query[$index]['order_by'] !== false ) {
            $this->db->order_by( $this->_query[$index]['order_by']['item'], $this->_query[$index]['order_by']['value'] );
        }
        
        if( $this->_query[$index]['distinct'] !== false ) {
            $this->db->distinct();
        }
        
        if( $this->_query[$index]['limit'] !== false ) {
            $this->db->limit( $this->_query[$index]['limit'] );
        }
        
        $this->_query[$index]['result'] = array();
        $this->_query[$index]['result'] = $this->mdl_db->_all_query_db( $this->_query[$index]['table_name'] );
                
        $this->_query[$index]['pagination']['count_all'] = count( $this->_query[$index]['result'] );
        
        if( $this->_query[$index]['pagination']['on'] !== false ){
            if( $this->_query[$index]['module'] === false ){
                $this->_query[$index]['module'] = true;
            } 
            if( count( $this->_query[$index]['modules'] ) < 1 ){
                $this->_query[$index]['modules'][] = [
                    'module_name' => 'pagination',
                    'result_item' => 'pagination',
                    'option' => array()
                ];
            }else{
                $m = 0;
                foreach( $this->_query[$index]['modules'] as $mv ){
                    if( $mv['module_name'] === 'pagination'){
                        $m++;
                    }
                }                
                if( $m < 1 ){
                    $this->_query[$index]['modules'][] = [
                        'module_name' => 'pagination',
                        'result_item' => 'pagination',
                        'option' => array()
                    ];
                }
            }
        }
        
        if( $this->_query[$index]['module'] !== false && count( $this->_query[$index]['modules'] ) > 0 ){
            foreach( $this->_query[$index]['modules'] as $v ){
                $variable = 'mod_'.$v['module_name'];
                $option = ( isset( $v['option'] ) ) ? $v['option'] : array();
                if( !in_array( 'index', $option ) ) $option['index'] = $index;
                self::$variable( $v['result_item'], $option );
            }
        }
        
        if( $this->_query[$index]['labels'] !== false  ){
            $this->_query[$index]['result'] = $this->mdl_helper->clear_array( $this->_query[$index]['result'], $this->_query[$index]['labels'] );
        }
        
        
        // Вернуть результат....
        $returnData = array();
        
        // Одномерный массив
        if( $this->_query[$index]['return_type'] === 'ARR1' || $this->_query[$index]['return_type'] === 'ARR1+' ){
            $returnData = ( count( $this->_query[$index]['result'] ) > 0 ) ? $this->_query[$index]['result'][0] : array();
        }   
        
        // Многомерный массив
        if( $this->_query[$index]['return_type'] === 'ARR2' || $this->_query[$index]['return_type'] === 'ARR2+' ){
            $returnData = ( count( $this->_query[$index]['result'] ) > 0 ) ? $this->_query[$index]['result'] : array();
        }
        
        // Массив с доп параметрами. ( напр: Режимом отладки )
        if( $this->_query[$index]['return_type'] === 'ARR1+' || $this->_query[$index]['return_type'] === 'ARR2+' ){
            $returnData = array(
                'result' => $returnData,
                'option' => $this->_query[$index]['result_option']
            );
            
            if( $this->_query[$index]['debug'] !== false ){
                $returnData['option']['debug'] = $this->_query[$index];
            }
        }     
        
        return $returnData;
        
    }
    
     // Модуль для работы с пагинацией ( return_type === + )
    public function mod_pagination ( $item = 'pagination', $option = array() ){  
        
        // Сбор данных
        $index = ( isset( $option['index'] ) ) ? $option['index'] : false; 
        $path = ( isset( $option['path'] ) ) ? $option['path'] : false; 
        $option_paginates = ( isset( $option['option_paginates'] ) ) ? $option['option_paginates'] : false; 

        // Срез массива
        $page = (int)$this->_query[$index]['pagination']['page'];
        $limit = ( $this->_query[$index]['pagination']['limit'] === 'all' )?$this->_query[$index]['pagination']['count_all']:$this->_query[$index]['pagination']['limit'];
        $start = ( $page < 1 ) ? (int)0 : ( (($page) - 1) * $limit ); // Старт вырезки
        $this->_query[$index]['result'] = array_slice( $this->_query[$index]['result'], $start, $limit ); 
        
        // Установка пагинации
        $this->load->library('pagination');  

        
        $config['base_url'] = $this->mdl_helper->PROTOCOL( true ). $_SERVER['SERVER_NAME'] . '/' . $path;
        $config['first_url'] = $config['base_url'];
        
        $config['total_rows'] = $this->_query[$index]['pagination']['count_all'];
        $config['per_page'] = ( $this->_query[$index]['pagination']['limit'] === 'all' )?$this->_query[$index]['pagination']['count_all']:$this->_query[$index]['pagination']['limit'];
        $config['use_page_numbers'] = TRUE;
        //$config['uri_segment'] = 3;
        $config['cur_page'] = $page;
        
        $config['query_string_segment'] = 'page';
        $config['page_query_string'] = TRUE;
        
        $config['suffix'] = '';
        
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';

        $config['first_link'] = ' << ';
        $config['first_tag_open'] = '<li class="footable-page">';
        $config['first_tag_close'] = '</li>';

        $config['prev_link'] = ' < ';
        $config['prev_tag_open'] = '<li class="footable-page">';
        $config['prev_tag_close'] = '</li>';

        $config['cur_tag_open'] = '<li class="footable-page active"><a>';
        $config['cur_tag_close'] = '</a></li>';

        $config['num_tag_open'] = '<li class="footable-page">';
        $config['num_tag_close'] = '</li>';

        $config['next_link'] = ' > ';
        $config['next_tag_open'] = '<li class="footable-page">';
        $config['next_tag_close'] = '</li>';

        $config['last_link'] = ' >> ';
        $config['last_tag_open'] = '<li class="footable-page">';
        $config['last_tag_close'] = '</li>';
        
        // Загрузка / Перезапись настроек от пользователя
		if( $option_paginates ){
			foreach( $option_paginates as $key => $value ){
				$config[$key] = $value;
			}
		}
        
        if( count( $config['suffix'] ) > 0 ){
            $sfx = http_build_query( $config['suffix'] );
            $config['suffix'] = '&'. $sfx;
            $config['first_url'] = $config['base_url'] .'?'. $sfx;
        }else{
            $config['suffix'] = '';
            $config['first_url'] = $config['base_url'];
        }
            
        $this->pagination->initialize($config);
        $this->_query[$index]['result_option']['pag'] = $this->pagination->create_links();
        
        return true;
    }
    
     // Товары данной коллекции
    public function mod_getProducts ( $item = 'products', $option = array() ){  
        
        // Сбор данных
        $index = ( isset( $option['index'] ) ) ? $option['index'] : false; 
        
        $page = 1;
        $limit = 40;
        
        if( $option !== false ){
            $page = ( isset( $option['page'] )) ? $option['page'] : $page; 
            $limit = ( isset( $option['limit'] )) ? $option['limit'] : $limit;
        }
        
        foreach( $this->_query[$index]['result'] as $k => $v ){
            
            if( $v['type'] == 1 ){
                
                $this->get = [];
                parse_str( $v['data'], $this->get );
                $this->get = $this->mdl_helper->clear_array_0( $this->get, array( 
                    'f', 's', 'l', 't', 'brand'
                ));
               
                $option = [
                    'return_type' => 'ARR2+',
                    'debug' => false,
                    'where' => [
                        'method' => 'AND',
                        'set' => [[
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
                    //'limit' => $limit,
                    'order_by' => [
                        'item' => 'id',
                        'value' => 'DESC'
                    ], 
                    'group_by' => 'articul',
                    'distinct' => true,
                    'labels' => ['id', 'aliase', 'articul', 'title', 'prices_empty', 'filters', 'salle_procent', 'modules'],
                    'pagination' => [
                        'on' => true,
                        'page' => $page,
                        'limit' => $limit
                    ],
                    'module' => true,
                    'modules' => [[
                        'module_name' => 'linkPath', 
                        'result_item' => 'linkPath', 
                        'option' => []
                    ],[
                        'module_name' => 'price_actual',
                        'result_item' => 'price_actual',
                        'option' => [
                            'labels' => false
                        ]
                    ],[
                        'module_name' => 'salePrice',
                        'result_item' => 'salePrice',
                        'option' => []
                    ],[
                        'module_name' => 'photos',
                        'result_item' => 'photos',
                        'option' => [
                            'no_images_view' => 1
                        ]
                    ],[
                        'module_name' => 'emptyPrice',
                        'result_item' => 'emptyPrice',
                        'option' => [
                            'labels' => false
                        ]
                    ]]
                ];
                
                if( isset( $this->get['f'] ) && is_array( $this->get['f'] ) ){
                    
                    $f = $this->get['f']; $fNew = [];
                    
                    foreach( $f as $fk => $fv ){
                        $fNew[] = [
                            'item' => $fk,
                            'type' => 'checkbox-group',
                            'values' => explode('|', $fv)
                        ];
                    }
                    
                    $option['modules'][] = [
                        'module_name' => 'setFilters',
                        'result_item' => 'setFilters',
                        'option' => [
                            'setItems' => $fNew
                        ]
                    ];             
                    
                }
                
                $r['sort'] = ( isset( $this->get['s'] ) ) ? $this->get['s'] : 'new';
                
                if( isset( $this->get['s'] ) ){
                    
                    $sort = $this->get['s'];
                    
                    if( $sort === 'pop' ){
                        $option['order_by'] = [
                            'item' => 'view',
                            'value' => 'DESC'
                        ]; 
                    }
                    
                    if( $sort === 'new' ){
                        $option['order_by'] = [
                            'item' => 'id',
                            'value' => 'DESC'
                        ]; 
                    }
                    
                    if( $sort === 'upsells' ){
                        $option['order_by'] = [
                            'item' => 'salle_procent',
                            'value' => 'DESC'
                        ]; 
                    }
                    
                    if( $sort === 'pricemin' || $sort === 'pricemax' ){
                        $option['modules'][] = [
                            'module_name' => 'setSortPrices',
                            'result_item' => 'setSortPrices',
                            'option' => [
                                'sort' => $sort
                            ]
                        ];
                    }
                }
                
                $r['textSearch'] = '';
                if( isset( $this->get['t'] ) ) {
                    $t = $r['textSearch'] = $this->get['t'];
                    $option['like'] = [
                        'math' => 'both',
                        'method' => 'AND', 
                        'set' => [[
                            'item' => 'title',
                            'value' => $t
                        ]]
                    ];
                }
                
                if( isset( $this->get['brand'] ) ) {
                    $b = $this->get['brand'];
                    $option['where']['set'][] = [
                        'item' => 'postavchik',
                        'value' => $b
                    ];
                }
                
                $option['modules'][] = [
                    'module_name' => 'pagination',
                    'result_item' => 'pagination',
                    'option' => [
                        'path' => $_SERVER['REDIRECT_URL'],
                        'option_paginates' => [
                            'uri_segment' => 1,
                            'num_links' => 3,
                            'suffix' => []
                        ]
                    ]
                ];
                
                $_r = $this->mdl_product->queryData( $option );                
                $this->_query[$index]['result'][$k]['modules'][$item] = $_r;
                                
            }
            
            
            if( $v['type'] == 2 ){
                
                $option = [
                    'return_type' => 'ARR2+',
                    'debug' => false,
                    'where' => [
                        'method' => 'AND',
                        'set' => [[
                            'item' => 'view >',
                            'value' => 0
                        ],[
                            'item' => 'qty >',
                            'value' => 0
                        ]]
                    ],
                    'in' => [
                        'method' => 'AND',
                        'set' => [[
                            'item' => 'id',
                            'values' => explode(',', $v['data'] )
                        ]]
                    ],
                    'labels' => ['id', 'aliase', 'articul', 'title', 'prices_empty', 'filters', 'salle_procent', 'modules'],
                    'pagination' => [
                        'on' => true,
                        'page' => $page,
                        'limit' => $limit
                    ],
                    'module' => true,
                    'modules' => [[
                        'module_name' => 'linkPath', 
                        'result_item' => 'linkPath', 
                        'option' => [
                            //'cat_aliase' => $catAliase
                        ]
                    ],[
                        'module_name' => 'pagination',
                        'result_item' => 'pagination',
                        'option' => [
                            'path' => $_SERVER['REDIRECT_URL'],
                            'option_paginates' => [
                                'uri_segment' => 1,
                                'num_links' => 3,
                                'suffix' => $this->get
                            ]
                        ]
                    ],[
                        'module_name' => 'price_actual',
                        'result_item' => 'price_actual',
                        'option' => [
                            'labels' => false
                        ]
                    ],[
                        'module_name' => 'salePrice',
                        'result_item' => 'salePrice',
                        'option' => []
                    ],[
                        'module_name' => 'photos',
                        'result_item' => 'photos',
                        'option' => [
                            'no_images_view' => 1
                        ]
                    ],[
                        'module_name' => 'emptyPrice',
                        'result_item' => 'emptyPrice',
                        'option' => [
                            'labels' => false
                        ]
                    ]]
                ];     
                
                $_r = $this->mdl_product->queryData( $option );
                $this->_query[$index]['result'][$k]['modules'][$item] = $_r;
            }
            
            
        }
        
    
    }
    
    
}
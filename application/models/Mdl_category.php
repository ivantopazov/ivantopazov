<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mdl_category extends CI_Model {
    
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
           'table_name' => 'products_cats'
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
    
    // Модуль собирает буквенный путь для категорий
    public function mod_linkPath( $item = 'linkPath', $option = array() ){
             
        $modules = ( isset( $option['modules'] ) ) ? $option['modules'] : false;     
        $index = ( isset( $option['index'] ) ) ? $option['index'] : false;     
        $cat_aliase = ( isset( $option['cat_aliase'] ) ) ? $option['cat_aliase'] : false;
        
        if( $this->_query[$index]['module'] !== false ){
            if( $this->_query[$index]['labels'] !== false ){
                if( !in_array('modules', $this->_query[$index]['labels'] ) ) $this->_query[$index]['labels'][] = 'modules';
            }
        }
        /*
        if( count( $this->_query[$index]['result'] ) > 0 ){
            $path = $this->getParentCatTree( $cat_aliase );
                
            foreach( $this->_query[$index]['result'] as  $k => $v ){
               $this->_query[$index]['result'][$k]['modules'][$item] = $path . '/' . $cat_aliase . '/' .$v['aliase'];
            }
        }*/
        
        if( count( $this->_query[$index]['result'] ) > 0 ){
            
            $ids = [];
            foreach( $this->_query[$index]['result'] as  $k => $v ){
                if( !in_array( $v['id'], $ids ) ){
                    $ids[] = $v['id'];
                }
            }
            
            $path = $this->getParentCatsTree( $ids );
                
            foreach( $this->_query[$index]['result'] as  $k => $v ){
               $this->_query[$index]['result'][$k]['modules'][$item] = $path[ $v['id'] ];
            }
        }
        
        return true;      
        
    }
    
    // Модуль собирает буквенный путь для категорий
    public function mod_findCats( $item = 'findCats', $option = array() ){
             
        $modules = ( isset( $option['modules'] ) ) ? $option['modules'] : false;     
        $index = ( isset( $option['index'] ) ) ? $option['index'] : false;     
        $store_id = ( isset( $option['store_id'] ) ) ? $option['store_id'] : false; 
        
        if( $this->_query[$index]['module'] !== false ){
            if( $this->_query[$index]['labels'] !== false ){
                if( !in_array('modules', $this->_query[$index]['labels'] ) ) $this->_query[$index]['labels'][] = 'modules';
            }
        }
        
        if( count( $this->_query[$index]['result'] ) > 0 ){
            foreach( $this->_query[$index]['result'] as $k => $v ){
                $this->_tree_find_items = [];
                $this->__get_find_cats( $this->queryData([
                    'order_by' => ['item' => 'parent_id', 'value' => 'desc']
                ]), $v['id'], 0, [
                    'id', 'name'
                ]);
                $this->_query[$index]['result'][$k]['modules'][$item] = $this->_tree_find_items;
            }
        }
        
        return true;      
        
    }    
    
    // ------------------------------------------------------------------------------------ 
    // ------------------------------------------------------------------------------------ 
    // ------------------------------------------------------------------------------------ 
    
    // КАТЕГОРИИ для Расширенного меню
    public function getTreeMenu(){
        
        $labels = [];
        $this->reqFindCats( $this->queryData([            
            'return_type' => 'ARR2',
            'order_by' => ['item' => 'parent_id', 'value' => 'desc'],
            'labels' => [ 'id', 'parent_id', 'aliase', 'name', 'modules' ],
            'module' => true,
            'modules' => [[
                'module_name' => 'linkPath', 
                'result_item' => 'linkPath', 
                'option' => []
            ]]
        ]), 0, 0, $labels);  
        
        $r = $this->rlist;        
        
        $m = [];
        foreach( $r as $v ){
            if( $v['data']['parent_id'] < 1 ){
                $addChild = [
                    'id' => $v['data']['id'],
                    'name' => $v['data']['name'],
                    'link' => $v['data']['orig']['modules']['linkPath'],
                    'parents' => []                    
                ];                
                foreach( $r as $v_par ){
                    if( $v_par['data']['parent_id'] === $v['data']['id'] ){
                        $addChild['parents'][] = [
                            'id' => $v_par['data']['id'],
                            'name' => $v_par['data']['name'],
                            'link' => $v_par['data']['orig']['modules']['linkPath']          
                        ];
                    }
                }                
                $m[] = $addChild;
            }
        }
        
        return $m;
        
    }
    
    
    
    // ------------------------------------------------------------------------------------ 
    // ------------------------------------------------------------------------------------ 
    // ------------------------------------------------------------------------------------ 
    
    // КАТЕГОРИИ для ЧПУ навигации
    
    // Получить полный путь до категории, зная алиас категории
    // УСТАРЕЛО ]
    public function getParentCatTree( $aliase = false ){
        
        $string = '/catalog';
        if( $aliase !== false  ){
            
            $catAll = $this->queryData([
                'return_type' => 'ARR2',
                'labels' => ['id', 'parent_id', 'aliase']
            ]);
            
            $catItem = $this->queryData([
                'return_type' => 'ARR2',
                'where' => [
                    'method' => 'AND',
                    'set' => [
                        [ 'item' => 'aliase' , 'value' => $aliase ]
                    ]
                ],
                'labels' => ['id', 'parent_id', 'aliase']
            ]);
            
            if( count( $catItem ) > 0 ){
                $catItem = $catItem[0];
                $this->_tree_items = [];
                $this->__get_cats( $catAll, $catItem['parent_id'] );
                foreach( $this->_tree_items as $v ){
                    $string .= '/'. $v['aliase'];
                }
            }
            
        }
        
        return $string;
        
    }
    
    // Получить полный путь до категории, зная алиас категории
    public function getParentCatsTree( $IDsCats = array() ){
        
        $r = [];
        
        if( count( $IDsCats > 0 ) ){
            
            $catAll = $this->queryData([
                'return_type' => 'ARR2',
                'labels' => ['id', 'parent_id', 'aliase']
            ]);
            
            $catsItems = $this->queryData([
                'return_type' => 'ARR2',
                'in' => [
                    'method' => 'AND',
                    'set' => [
                        [ 'item' => 'id','values' => $IDsCats ]
                    ]
                ],
                'labels' => ['id', 'parent_id', 'aliase']
            ]);
            
            if( count( $catsItems ) > 0 ){
                foreach( $catsItems as $item ){
                    $string = '/catalog';
                    $this->_tree_items = [];
                    $this->__get_cats( $catAll, $item['parent_id'] );
                    foreach( $this->_tree_items as $v ){
                        $string .= '/'. $v['aliase'];
                    }
                    $r[ $item['id'] ] = $string . '/' . $item['aliase'] ;
                }
                
            }
            
        }
        
        return $r;
        
    }
    
    // system - Рекурсивный сбор родительский категорий
    protected $_tree_items = [];
    private function __get_cats( $array, $par_id ) {      
        foreach ($array as $key => $value) {
            if( $value['id'] == $par_id ){
                $getPagrent = $this->__get_cats( $array, $value['parent_id'] );
                if( $getPagrent ){
                    $this->_tree_items[] = $getPagrent;
                }else{
                    $this->_tree_items[] = $value;
                }
            }
        }
    }
        
    // ---------------------------------------------------------    
        
        
        
        
    // ------------------------------------------------------------------------------------ 
    // ------------------------------------------------------------------------------------ 
    // ------------------------------------------------------------------------------------ 
    
    // КАТЕГОРИИ для эллемента SELECT
       
    // Сбор всего древа категорий для селекта
    public function allFindCats ( $active = false, $labels = [], $table_name = 'products_cats' ){
        $this->rlist = [];
        if( $active !== false ){
            $this->_tree_find_item_active = $active;
        }        
        $this->reqFindCats( $this->queryData([
            'order_by' => ['item' => 'parent_id', 'value' => 'desc'],
            'table_name' => $table_name
        ]), 0, 0, $labels);        
        return $this->rlist;
    }    
        
    // system - Рекурсивный сбор дочерних категорий в одномерный массив
    protected $rlist = [];
    protected $_tree_find_item_active = 0;
    public function reqFindCats( $all, $PID = 0, $level = 0, $labels = false  ){
        $level++;
        foreach( $all as $v ){
            if( $v['parent_id'] == $PID ){
                $this->rlist[] = array(
                    'level' => $this->label_symb( '&#8985;', $level),
                    'active' => ( $this->_tree_find_item_active == $v['id'] ) ? '1' : '0',
                    'data' => [
                        'id' => $v['id'],
                        'name' => ( isset( $v['name']) ) ? $v['name'] : '',
                        'parent_id' => $v['parent_id'],
                        'orig' => $v
                    ]
                );
                $this->reqFindCats( $all, $v['id'], $level, $labels );
            }
        }
    }
        
    // SELECT кол-во отступов
    private function label_symb ( $s = '&#8866', $c = 0 ){
        /*$ss = '&#747;';
        for( $i=0; $i < $c; $i++ ){
            $ss .= $s;
        }
        return $ss;*/
        // &#8866;
        
        $delimiter = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        $spec = ( $s !== false ) ? $s :'&#8985;';
        
        $ss = ''; 
        if( $c > 1 ){
            for( $i=0; $i < $c; $i++ ){
                if( $i == ( $c -1 ) ){
                    $ss .= $spec;
                }else{
                  $ss .= $delimiter;  
                }
            }
        }
        return $ss;
        
    }
    
    // ------------------------------------------------------------------------------------
    
        
    // ------------------------------------------------------------------------------------ 
    // ------------------------------------------------------------------------------------ 
    // ------------------------------------------------------------------------------------ 
    
    // КАТЕГОРИИ Операции административного направления
    
    // Создать новую категорию
    public function create_category( $created_add = array() ){
        $insert_id = false;
        if( count( $created_add ) > 0 ){
            $addArr = $this->mdl_helper->clear_array_0( $created_add, array(
                'aliase','parent_id','user_id','store_id',
                'name','desription','seo_title','seo_desc',
                'seo_keys','date','view'
            ));            
            $this->db->insert( 'products_cats', $addArr );    
            $insert_id = $this->db->insert_id();
        }
        
        return $insert_id;
    }
        
    // Обновить информацию о категории
    public function update_category( $UpdateID = false, $UpdateArr = array() ){        
        $response = false;
        if( $UpdateID !== false ){        
            if( isset( $UpdateArr['name'] ) && isset( $UpdateArr['aliase'] ) ){
                $this->mdl_db->_update_db( "products_cats", "id", $UpdateID, $UpdateArr );
                $response = true;
            }        
        }
        return $response;        
    }
    
    // Удаляет категорию
    public function remove_category ( $RemoveID = false ){        
        $response = false;        
        if( $RemoveID !== false ){            
            $rPar = $this->mdl_category->queryData([
                'return_type' => 'ARR2',
                'where' => [
                    'method' => 'AND',
                    'set' => [[
                        'item' => 'parent_id',
                        'value' => $RemoveID
                    ]]
                ]
            ]);            
            if( count( $rPar ) < 1 ){                
                $this->db->where( 'id', $RemoveID );
                $this->db->delete( 'products_cats' );    
                $response = true;
            }
        }
        return $response;
    }
    
    // ------------------------------------------------------------------------------------
}
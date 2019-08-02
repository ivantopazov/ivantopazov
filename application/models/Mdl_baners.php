<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mdl_baners extends CI_Model {
    
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
           'table_name' => 'baners'
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
    
    public function mod_dates ( $item = 'dates', $option = array() ){  
    
        $index = ( isset( $option['index'] ) ) ? $option['index'] : false;
        
        if( count( $this->_query[$index]['result'] ) > 0 ){ 
            foreach( $this->_query[$index]['result'] as $k => $v ){
                $this->_query[$index]['result'][$k]['modules'][$item] = [
                    'Start_date_mdY' => ( $v['date_start'] > 0 ) ? date('m.d.Y', $v['date_start'] ) : '',
                    'End_date_mdY' => ( $v['date_end'] > 0 ) ? date('m.d.Y', $v['date_end'] ) : '',
                ];
            }
        }
        return true;
        
    }
    
    // Получить разделы для главной страницы
    public function getBanersTop( $store_id = false, $j = true ){
        
        $r = [];
        
        if(  $store_id !== false ){
            
            $r_0 = $this->mdl_baners->queryData([
                'where' => [
                    'method' => 'AND',
                    'set' => [[
                        'item' => 'store_id',
                        'value' => $store_id
                    ],[
                        'item' => 'position',
                        'value' => 'top'
                    ],[
                        'item' => 'date_start',
                        'value' => '0'
                    ],[
                        'item' => 'date_end',
                        'value' => '0'
                    ],[
                        'item' => 'view >',
                        'value' => '0'
                    ]]
                ],
                'labels' => [ 'image', 'link' ]
            ]);
            
            $r_1 = $this->mdl_baners->queryData([
                'where' => [
                    'method' => 'AND',
                    'set' => [[
                        'item' => 'store_id',
                        'value' => $store_id
                    ],[
                        'item' => 'position',
                        'value' => 'top'
                    ],[
                        'item' => 'date_start >',
                        'value' => time()
                    ],[
                        'item' => 'date_end <',
                        'value' => time()
                    ],[
                        'item' => 'view >',
                        'value' => '0'
                    ]]
                ],
                'labels' => [ 'image', 'link' ]
            ]);
            
            $r = array_merge( $r_0, $r_1 );
            
        }
        
        if( $j === true ){
            $this->mdl_helper->__json( $r );
        }else{
            return $r;
        }
        
    }
    
    // Получить разделы для главной страницы
    public function getBanersleftBar( $store_id = false, $j = true ){
        $r = [];
        
        if( $store_id !== false ){
            $r_0 = $this->mdl_baners->queryData([
                'where' => [
                    'method' => 'AND',
                    'set' => [[
                        'item' => 'store_id',
                        'value' => $store_id
                    ],[
                        'item' => 'position',
                        'value' => 'leftBar'
                    ],[
                        'item' => 'date_start',
                        'value' => '0'
                    ],[
                        'item' => 'date_end',
                        'value' => '0'
                    ],[
                        'item' => 'view >',
                        'value' => '0'
                    ]]
                ],
                'labels' => [ 'image', 'link' ]
            ]);
            
            $r_1 = $this->mdl_baners->queryData([
                'where' => [
                    'method' => 'AND',
                    'set' => [[
                        'item' => 'store_id',
                        'value' => $store_id
                    ],[
                        'item' => 'position',
                        'value' => 'leftBar'
                    ],[
                        'item' => 'date_start >',
                        'value' => time()
                    ],[
                        'item' => 'date_end <',
                        'value' => time()
                    ],[
                        'item' => 'view >',
                        'value' => '0'
                    ]]
                ],
                'labels' => [ 'image', 'link' ]
            ]);
            
            $r = array_merge( $r_0, $r_1 );
        }
        
        if( $j === true ){
            $this->mdl_helper->__json( $r );
        }else{
            return $r;
        }
        
    }
    
    // Получить разделы для главной страницы
    public function getBanersCategoryLeftBar( $store_id = false, $catId = false, $j = true ){
        
        $r = [];
        if( $catId !== false && $store_id !== false ){
        
            $r_0 = $this->mdl_baners->queryData([
                'where' => [
                    'method' => 'AND',
                    'set' => [[
                        'item' => 'store_id',
                        'value' => $store_id
                    ],[
                        'item' => 'position',
                        'value' => 'categoryLeftBar'
                    ],[
                        'item' => 'cat',
                        'value' => $catId
                    ],[
                        'item' => 'date_start',
                        'value' => '0'
                    ],[
                        'item' => 'date_end',
                        'value' => '0'
                    ],[
                        'item' => 'view >',
                        'value' => '0'
                    ]]
                ],
                'labels' => [ 'image', 'link' ]
            ]);
            
            $r_1 = $this->mdl_baners->queryData([
                'where' => [
                    'method' => 'AND',
                    'set' => [[
                        'item' => 'store_id',
                        'value' => $store_id
                    ],[
                        'item' => 'position',
                        'value' => 'categoryLeftBar'
                    ],[
                        'item' => 'cat',
                        'value' => $catId
                    ],[
                        'item' => 'date_start >',
                        'value' => time()
                    ],[
                        'item' => 'date_end <',
                        'value' => time()
                    ],[
                        'item' => 'view >',
                        'value' => '0'
                    ]]
                ],
                'labels' => [ 'image', 'link' ]
            ]);
            $r = array_merge( $r_0, $r_1 );
        }
        
        if( $j === true ){
            $this->mdl_helper->__json( $r );
        }else{
            return $r;
        }
        
    }
    
}
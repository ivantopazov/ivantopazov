<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Mdl_product extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    // Выполнение запросов
    protected $_query = array();

    public function queryData($settings = array())
    {

        $index = rand(1, 1000);
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
            'group_by' => false, // "title"
            'order_by' => [], // [ item -> , value -> ]
            'distinct' => false, // +DISTINCT
            'limit' => false, // 10 || "10, 2"
            'labels' => false,
            'result' => array(), // Хранилище для обработки
            'result_option' => array(), // Резервное. хранилище
            'return_type' => 'ARR2', // ARR1 - одном. массив || ARR2 - многом. массив || ARR1+,ARR2+  - Упаковать с резервным хранилищем
            'debug' => false, // Диагностическая линия ( Необходим активный "+" в "return_type" )
            'module_queue' => [
                'setFilters', 'limit', 'pagination',
                'setSortPrices', 'price_actual',
                'prices_all', 'photos',
                'reviews', 'linkPath', 'salePrice',
                'emptyPrice', 'qty_empty_status', 'paramsView'
            ],
            'module' => false,
            'modules' => array(),
            'pagination' => array(
                'on' => false,
                'page' => 1,
                'limit' => 'all',
                'count_all' => 0
            ),
            'table_name' => 'products'
        );
        $this->_query[$index] = array_merge($_query, $settings);

        if (count($this->_query[$index]['where']['set']) > 0) {
            $lc = 0;
            foreach ($this->_query[$index]['where']['set'] as $value) {
                $this->db->where($value['item'], $value['value']);
                if ($this->_query[$index]['where']['method'] === 'OR' && $lc > 0) {
                    $this->db->or_where($value['item'], $value['value']);
                }
                $lc++;
            }
        }

        if (count($this->_query[$index]['in']['set']) > 0) {
            $lc = 0;
            foreach ($this->_query[$index]['in']['set'] as $value) {
                if ($this->_query[$index]['in']['method'] === 'OR' || $this->_query[$index]['in']['method'] === 'AND') {
                    if ($this->_query[$index]['in']['method'] === 'OR' && $lc > 0) {
                        $this->db->or_where_in($value['item'], $value['values']);
                    } else {
                        $this->db->where_in($value['item'], $value['values']);
                    }
                    $lc++;
                }
                if ($this->_query[$index]['in']['method'] === 'NOT' || $this->_query[$index]['in']['method'] === 'OR_NOT') {
                    if ($this->_query[$index]['in']['method'] === 'OR_NOT' && $lc > 0) {
                        $this->db->or_where_not_in($value['item'], $value['values']);
                    } else {
                        $this->db->where_not_in($value['item'], $value['values']);
                    }
                    $lc++;
                }
            }
        }

        if (count($this->_query[$index]['like']['set']) > 0) {
            $lc = 0;
            foreach ($this->_query[$index]['like']['set'] as $value) {
                if ($this->_query[$index]['like']['method'] === 'OR' || $this->_query[$index]['like']['method'] === 'AND') {

                    if ($this->_query[$index]['like']['method'] === 'OR' && $lc > 0) {
                        $this->db->or_like($value['item'], $value['value'], $this->_query[$index]['like']['math']);
                    } else {
                        $this->db->like($value['item'], $value['value'], $this->_query[$index]['like']['math']);
                    }
                    $lc++;
                }
                if ($this->_query[$index]['like']['method'] === 'NOT' || $this->_query[$index]['like']['method'] === 'OR_NOT') {

                    if ($this->_query[$index]['like']['method'] === 'OR_NOT' && $lc > 0) {
                        $this->db->or_not_like($value['item'], $value['value'], $this->_query[$index]['like']['math']);
                    } else {
                        $this->db->not_like($value['item'], $value['value'], $this->_query[$index]['like']['math']);
                    }
                    $lc++;
                }
            }
        }

        if ($this->_query[$index]['group_by'] !== false) {
            $this->db->group_by($this->_query[$index]['group_by']);
        }


        if (isset($this->_query[$index]['order_by']['item'])) {
            //if( !$this->_query[$index]['order_by']  ) {
            $this->db->order_by($this->_query[$index]['order_by']['item'], $this->_query[$index]['order_by']['value']);
        }

        if ($this->_query[$index]['distinct'] !== false) {
            $this->db->distinct();
        }

        if ($this->_query[$index]['limit'] !== false) {
            $this->_query[$index]['module'] = true;
            $m = 0;
            foreach ($this->_query[$index]['modules'] as $v) {
                if ($v['module_name'] === 'limit') {
                    $m++;
                }
			}
            if ($m < 1) {
                $this->_query[$index]['modules'][] = [
                    'module_name' => 'limit',
                    'result_item' => 'limit',
                    'option' => [
                        'limit' => $this->_query[$index]['limit']
                    ]
                ];
            }
            //$this->db->limit( $this->_query[$index]['limit'] );
        }

        $this->_query[$index]['result'] = array();
        $this->_query[$index]['result'] = $this->mdl_db->_all_query_db($this->_query[$index]['table_name']);

        $this->_query[$index]['pagination']['count_all'] = count($this->_query[$index]['result']);

        if ($this->_query[$index]['pagination']['on'] !== false) {

        	if ($this->_query[$index]['module'] === false) {
                $this->_query[$index]['module'] = true;
            }

            if (count($this->_query[$index]['modules']) < 1) {
                $this->_query[$index]['modules'][] = [
                    'module_name' => 'pagination',
                    'result_item' => 'pagination',
                    'option' => array()
                ];
            } else {
                $m = 0;
                foreach ($this->_query[$index]['modules'] as $mv) {
                    if ($mv['module_name'] === 'pagination') {
                        $m++;
                    }
                }
                if ($m < 1) {
                    $this->_query[$index]['modules'][] = [
                        'module_name' => 'pagination',
                        'result_item' => 'pagination',
                        'option' => array()
                    ];
                }
            }


            /*
            // Переместить модуль пагинации в конец
            $saveModule = false;
            $newArray = [];
            foreach( $this->_query[$index]['modules'] as $mk => $mv ){
                if( $mv['module_name'] === 'pagination' ){
                    $saveModule = $mv;
                }else{
                    $newArray[] = $mv;
                }
            }

            if( $saveModule !== false ){
                $newArray[] = $saveModule;
                $this->_query[$index]['modules'] = $newArray;
            }*/

        }


        // Очередность запуска модулей
        $newArray = [];
        foreach ($this->_query[$index]['module_queue'] as $v) {
            foreach ($this->_query[$index]['modules'] as $mk => $mv) {
                if ($mv['module_name'] === $v) {
                    $newArray[] = $mv;
                }
            }
        }

        foreach ($this->_query[$index]['modules'] as $mk => $mv) {
            if (!in_array($mv['module_name'], $this->_query[$index]['module_queue'])) {
                $newArray[] = $mv;
            }
        }

        $this->_query[$index]['modules'] = $newArray;

        if ($this->_query[$index]['module'] !== false && count($this->_query[$index]['modules']) > 0) {
            foreach ($this->_query[$index]['modules'] as $v) {
                $variable = 'mod_' . $v['module_name'];
                //echo $variable . '<br />';
                $option = (isset($v['option'])) ? $v['option'] : array();
                if (!in_array('index', $option)) $option['index'] = $index;
                self::$variable($v['result_item'], $option);
            }
        }

        if ($this->_query[$index]['labels'] !== false) {
            $this->_query[$index]['result'] = $this->mdl_helper->clear_array($this->_query[$index]['result'], $this->_query[$index]['labels']);
        }

        // Вернуть результат....
        $returnData = array();

        // Одномерный массив
        if ($this->_query[$index]['return_type'] === 'ARR1' || $this->_query[$index]['return_type'] === 'ARR1+') {
            $returnData = (count($this->_query[$index]['result']) > 0) ? $this->_query[$index]['result'][0] : array();
        }

        // Многомерный массив
        if ($this->_query[$index]['return_type'] === 'ARR2' || $this->_query[$index]['return_type'] === 'ARR2+') {
            $returnData = (count($this->_query[$index]['result']) > 0) ? $this->_query[$index]['result'] : array();
        }

        // Массив с доп параметрами. ( напр: Режимом отладки )
        if ($this->_query[$index]['return_type'] === 'ARR1+' || $this->_query[$index]['return_type'] === 'ARR2+') {
            $returnData = array(
                'result' => $returnData,
                'option' => $this->_query[$index]['result_option']
            );

            if ($this->_query[$index]['debug'] !== false) {
                $returnData['option']['debug'] = $this->_query[$index];
            }
        }

        return $returnData;

    }

    public function queryDataAdmin($settings = array())
    {

        $index = rand(1, 1000);
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
            'group_by' => false, // "title"
            'order_by' => [], // [ item -> , value -> ]
            'distinct' => false, // +DISTINCT
            'limit' => false, // 10 || "10, 2"
            'labels' => false,
            'result' => array(), // Хранилище для обработки
            'result_option' => array(), // Резервное. хранилище
            'return_type' => 'ARR2', // ARR1 - одном. массив || ARR2 - многом. массив || ARR1+,ARR2+  - Упаковать с резервным хранилищем
            'debug' => false, // Диагностическая линия ( Необходим активный "+" в "return_type" )
            'module_queue' => [
                'setFilters', 'limit', 'pagination',
                'setSortPrices', 'price_actual',
                'prices_all', 'photos',
                'reviews', 'linkPath', 'salePrice',
                'emptyPrice', 'qty_empty_status', 'paramsView'
            ],
            'module' => false,
            'modules' => array(),
            'pagination' => array(
                'on' => false,
                'page' => 1,
                'limit' => 'all',
                'count_all' => 0
            ),
            'table_name' => 'products'
        );
        $this->_query[$index] = array_merge($_query, $settings);

        if (count($this->_query[$index]['where']['set']) > 0) {
            $lc = 0;
            foreach ($this->_query[$index]['where']['set'] as $value) {
                $this->db->where($value['item'], $value['value']);
                if ($this->_query[$index]['where']['method'] === 'OR' && $lc > 0) {
                    $this->db->or_where($value['item'], $value['value']);
                }
                $lc++;
            }
        }

        if (count($this->_query[$index]['in']['set']) > 0) {
            $lc = 0;
            foreach ($this->_query[$index]['in']['set'] as $value) {
                if ($this->_query[$index]['in']['method'] === 'OR' || $this->_query[$index]['in']['method'] === 'AND') {
                    if ($this->_query[$index]['in']['method'] === 'OR' && $lc > 0) {
                        $this->db->or_where_in($value['item'], $value['values']);
                    } else {
                        $this->db->where_in($value['item'], $value['values']);
                    }
                    $lc++;
                }
                if ($this->_query[$index]['in']['method'] === 'NOT' || $this->_query[$index]['in']['method'] === 'OR_NOT') {
                    if ($this->_query[$index]['in']['method'] === 'OR_NOT' && $lc > 0) {
                        $this->db->or_where_not_in($value['item'], $value['values']);
                    } else {
                        $this->db->where_not_in($value['item'], $value['values']);
                    }
                    $lc++;
                }
            }
        }

        if (count($this->_query[$index]['like']['set']) > 0) {
            $lc = 0;
            foreach ($this->_query[$index]['like']['set'] as $value) {
                if ($this->_query[$index]['like']['method'] === 'OR' || $this->_query[$index]['like']['method'] === 'AND') {

                    if ($this->_query[$index]['like']['method'] === 'OR' && $lc > 0) {
                        $this->db->or_like($value['item'], $value['value'], $this->_query[$index]['like']['math']);
                    } else {
                        $this->db->like($value['item'], $value['value'], $this->_query[$index]['like']['math']);
                    }
                    $lc++;
                }
                if ($this->_query[$index]['like']['method'] === 'NOT' || $this->_query[$index]['like']['method'] === 'OR_NOT') {

                    if ($this->_query[$index]['like']['method'] === 'OR_NOT' && $lc > 0) {
                        $this->db->or_not_like($value['item'], $value['value'], $this->_query[$index]['like']['math']);
                    } else {
                        $this->db->not_like($value['item'], $value['value'], $this->_query[$index]['like']['math']);
                    }
                    $lc++;
                }
            }
        }

        if ($this->_query[$index]['group_by'] !== false) {
            $this->db->group_by($this->_query[$index]['group_by']);
        }


        if (isset($this->_query[$index]['order_by']['item'])) {
            //if( !$this->_query[$index]['order_by']  ) {
            $this->db->order_by($this->_query[$index]['order_by']['item'], $this->_query[$index]['order_by']['value']);
        }

        if ($this->_query[$index]['distinct'] !== false) {
            $this->db->distinct();
        }

        if ($this->_query[$index]['limit'] !== false) {

            if (is_int($this->_query[$index]['limit'])) {
            	$limit = $this->_query[$index]['limit'];
            	$offset = 0;
			} elseif (is_string($this->_query[$index]['limit'])) {
				list($limit, $offset) = explode(',', $this->_query[$index]['limit']);
			}
			$this->db->limit( $limit, $offset );
        }

        if ($this->_query[$index]['pagination']['on'] !== false) {

        	$this->_query[$index]['pagination']['count_all'] = $this->db->count_all_results($this->_query[$index]['table_name']);

//            if (!$this->_query[$index]['modules']['setFilters']) {
				$page = (int)$this->_query[$index]['pagination']['page'] ?: 1;
				$limit = (int)$this->_query[$index]['pagination']['limit'] ?: 10;
				$offset = ($page - 1) * $limit;
				$this->db->limit( $limit, $offset );
//			}
        	if ($this->_query[$index]['module'] === false) {
                $this->_query[$index]['module'] = true;
            }

            if (count($this->_query[$index]['modules']) < 1) {
                $this->_query[$index]['modules'][] = [
                    'module_name' => 'paginationAdmin',
                    'result_item' => 'pagination',
                    'option' => array()
                ];
            } else {
                $m = 0;
                foreach ($this->_query[$index]['modules'] as $mv) {
                    if ($mv['module_name'] === 'paginationAdmin') {
                        $m++;
                    }
                }
                if ($m < 1) {
                    $this->_query[$index]['modules'][] = [
                        'module_name' => 'paginationAdmin',
                        'result_item' => 'pagination',
                        'option' => array()
                    ];
                }
            }


            /*
            // Переместить модуль пагинации в конец
            $saveModule = false;
            $newArray = [];
            foreach( $this->_query[$index]['modules'] as $mk => $mv ){
                if( $mv['module_name'] === 'pagination' ){
                    $saveModule = $mv;
                }else{
                    $newArray[] = $mv;
                }
            }

            if( $saveModule !== false ){
                $newArray[] = $saveModule;
                $this->_query[$index]['modules'] = $newArray;
            }*/

        }

        $this->_query[$index]['result'] = array();
        $this->_query[$index]['result'] = $this->mdl_db->_all_query_db($this->_query[$index]['table_name']);


        // Очередность запуска модулей
        $newArray = [];
        foreach ($this->_query[$index]['module_queue'] as $v) {
            foreach ($this->_query[$index]['modules'] as $mk => $mv) {
                if ($mv['module_name'] === $v) {
                    $newArray[] = $mv;
                }
            }
        }

        foreach ($this->_query[$index]['modules'] as $mk => $mv) {
            if (!in_array($mv['module_name'], $this->_query[$index]['module_queue'])) {
                $newArray[] = $mv;
            }
        }

        $this->_query[$index]['modules'] = $newArray;

        if ($this->_query[$index]['module'] !== false && count($this->_query[$index]['modules']) > 0) {
            foreach ($this->_query[$index]['modules'] as $v) {
                $variable = 'mod_' . $v['module_name'];
                //echo $variable . '<br />';
                $option = (isset($v['option'])) ? $v['option'] : array();
                if (!in_array('index', $option)) $option['index'] = $index;
                self::$variable($v['result_item'], $option);
            }
        }

        if ($this->_query[$index]['labels'] !== false) {
            $this->_query[$index]['result'] = $this->mdl_helper->clear_array($this->_query[$index]['result'], $this->_query[$index]['labels']);
        }

        // Вернуть результат....
        $returnData = array();

        // Одномерный массив
        if ($this->_query[$index]['return_type'] === 'ARR1' || $this->_query[$index]['return_type'] === 'ARR1+') {
            $returnData = (count($this->_query[$index]['result']) > 0) ? $this->_query[$index]['result'][0] : array();
        }

        // Многомерный массив
        if ($this->_query[$index]['return_type'] === 'ARR2' || $this->_query[$index]['return_type'] === 'ARR2+') {
            $returnData = (count($this->_query[$index]['result']) > 0) ? $this->_query[$index]['result'] : array();
        }

        // Массив с доп параметрами. ( напр: Режимом отладки )
        if ($this->_query[$index]['return_type'] === 'ARR1+' || $this->_query[$index]['return_type'] === 'ARR2+') {
            $returnData = array(
                'result' => $returnData,
                'option' => $this->_query[$index]['result_option']
            );

            if ($this->_query[$index]['debug'] !== false) {
                $returnData['option']['debug'] = $this->_query[$index];
            }
        }

        return $returnData;

    }

    // Получение и обработка данных
    public function queryX($get = array(), $settings = array())
    {


        $index = rand(1, 1000);
        $_query = array(
            'limit' => false, // 10 || "10, 2"
            'labels' => false,
            'result' => array(), // Хранилище для обработки
            'result_option' => array(), // Резервное. хранилище
            'return_type' => 'ARR2', // ARR1 - одном. массив || ARR2 - многом. массив || ARR1+,ARR2+  - Упаковать с резервным хранилищем
            'debug' => false, // Диагностическая линия ( Необходим активный "+" в "return_type" )
            'module_queue' => [
                'setFilters', 'limit', 'pagination',
                'setSortPrices', 'price_actual',
                'prices_all', 'photos',
                'reviews', 'linkPath', 'salePrice',
                'emptyPrice', 'qty_empty_status', 'paramsView'
            ],
            'module' => false,
            'modules' => array(),
            'pagination' => array(
                'on' => false,
                'page' => 1,
                'limit' => 'all',
                'count_all' => 0
            )
        );

        $this->_query[$index] = array_merge($_query, $settings);


        $this->_query[$index]['result'] = array();
        $this->_query[$index]['result'] = $get->result_array();

        $this->_query[$index]['pagination']['count_all'] = count($this->_query[$index]['result']);

        if ($this->_query[$index]['pagination']['on'] !== false) {

            if ($this->_query[$index]['module'] === false) {
                $this->_query[$index]['module'] = true;
            }

            if (count($this->_query[$index]['modules']) < 1) {
                $this->_query[$index]['modules'][] = [
                    'module_name' => 'pagination',
                    'result_item' => 'pagination',
                    'option' => array()
                ];
            } else {
                $m = 0;
                foreach ($this->_query[$index]['modules'] as $mv) {
                    if ($mv['module_name'] === 'pagination') {
                        $m++;
                    }
                }
                if ($m < 1) {
                    $this->_query[$index]['modules'][] = [
                        'module_name' => 'pagination',
                        'result_item' => 'pagination',
                        'option' => array()
                    ];
                }
            }


            /*
            // Переместить модуль пагинации в конец
            $saveModule = false;
            $newArray = [];
            foreach( $this->_query[$index]['modules'] as $mk => $mv ){
                if( $mv['module_name'] === 'pagination' ){
                    $saveModule = $mv;
                }else{
                    $newArray[] = $mv;
                }
            }

            if( $saveModule !== false ){
                $newArray[] = $saveModule;
                $this->_query[$index]['modules'] = $newArray;
            }*/

        }


        // Очередность запуска модулей
        $newArray = [];
        foreach ($this->_query[$index]['module_queue'] as $v) {
            foreach ($this->_query[$index]['modules'] as $mk => $mv) {
                if ($mv['module_name'] === $v) {
                    $newArray[] = $mv;
                }
            }
        }

        foreach ($this->_query[$index]['modules'] as $mk => $mv) {
            if (!in_array($mv['module_name'], $this->_query[$index]['module_queue'])) {
                $newArray[] = $mv;
            }
        }

        $this->_query[$index]['modules'] = $newArray;

        if ($this->_query[$index]['module'] !== false && count($this->_query[$index]['modules']) > 0) {
            foreach ($this->_query[$index]['modules'] as $v) {
                $variable = 'mod_' . $v['module_name'];
                //echo $variable . '<br />';
                $option = (isset($v['option'])) ? $v['option'] : array();
                if (!in_array('index', $option)) $option['index'] = $index;
                self::$variable($v['result_item'], $option);
            }
        }

        if ($this->_query[$index]['labels'] !== false) {
            $this->_query[$index]['result'] = $this->mdl_helper->clear_array($this->_query[$index]['result'], $this->_query[$index]['labels']);
        }

        // Вернуть результат....
        $returnData = array();

        // Одномерный массив
        if ($this->_query[$index]['return_type'] === 'ARR1' || $this->_query[$index]['return_type'] === 'ARR1+') {
            $returnData = (count($this->_query[$index]['result']) > 0) ? $this->_query[$index]['result'][0] : array();
        }

        // Многомерный массив
        if ($this->_query[$index]['return_type'] === 'ARR2' || $this->_query[$index]['return_type'] === 'ARR2+') {
            $returnData = (count($this->_query[$index]['result']) > 0) ? $this->_query[$index]['result'] : array();
        }

        // Массив с доп параметрами. ( напр: Режимом отладки )
        if ($this->_query[$index]['return_type'] === 'ARR1+' || $this->_query[$index]['return_type'] === 'ARR2+') {
            $returnData = array(
                'result' => $returnData,
                'option' => $this->_query[$index]['result_option']
            );

            if ($this->_query[$index]['debug'] !== false) {
                $returnData['option']['debug'] = $this->_query[$index];
            }
        }

        return $returnData;

    }

    // Модуль для работы с пагинацией ( return_type === + )
    public function mod_pagination($item = 'pagination', $option = array())
    {

        // Сбор данных
        $index = (isset($option['index'])) ? $option['index'] : false;
        $path = (isset($option['path'])) ? $option['path'] : false;
        $option_paginates = (isset($option['option_paginates'])) ? $option['option_paginates'] : false;

        // Срез массива
        $page = (int)$this->_query[$index]['pagination']['page'];
        $limit = ($this->_query[$index]['pagination']['limit'] === 'all') ? $this->_query[$index]['pagination']['count_all'] : $this->_query[$index]['pagination']['limit'];
        $start = ($page < 1) ? (int)0 : ((($page) - 1) * $limit); // Старт вырезки
        $this->_query[$index]['result'] = array_slice($this->_query[$index]['result'], $start, $limit);

        // Установка пагинации
        $this->load->library('pagination');


        $config['base_url'] = $this->mdl_helper->PROTOCOL(true) . $_SERVER['SERVER_NAME'] . $path;
        $config['first_url'] = $config['base_url'];

        $config['total_rows'] = $this->_query[$index]['pagination']['count_all'];
        $config['per_page'] = ($this->_query[$index]['pagination']['limit'] === 'all') ? $this->_query[$index]['pagination']['count_all'] : $this->_query[$index]['pagination']['limit'];
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
        if ($option_paginates) {
            foreach ($option_paginates as $key => $value) {
                $config[$key] = $value;
            }
        }

        if (count($config['suffix']) > 0) {
            $sfx = http_build_query($config['suffix']);
            $config['suffix'] = '&' . $sfx;
            $config['first_url'] = $config['base_url'] . '?' . $sfx;
        } else {
            $config['suffix'] = '';
            $config['first_url'] = $config['base_url'];
        }

        $this->pagination->initialize($config);
        $this->_query[$index]['result_option']['pag'] = $this->pagination->create_links();

        return true;
    }

    // Модуль для работы с пагинацией ( return_type === + )
    public function mod_paginationAdmin($item = 'pagination', $option = array())
    {

        // Сбор данных
        $index = (isset($option['index'])) ? $option['index'] : false;
        $path = (isset($option['path'])) ? $option['path'] : false;
        $option_paginates = (isset($option['option_paginates'])) ? $option['option_paginates'] : false;

        // Срез массива
        $page = (int)$this->_query[$index]['pagination']['page'];
//        $limit = ($this->_query[$index]['pagination']['limit'] === 'all') ? $this->_query[$index]['pagination']['count_all'] : $this->_query[$index]['pagination']['limit'];
//        $start = ($page < 1) ? (int)0 : ((($page) - 1) * $limit); // Старт вырезки
//        $this->_query[$index]['result'] = array_slice($this->_query[$index]['result'], $start, $limit);

        // Установка пагинации
        $this->load->library('pagination');


        $config['base_url'] = $this->mdl_helper->PROTOCOL(true) . $_SERVER['SERVER_NAME'] . $path;
        $config['first_url'] = $config['base_url'];

        $config['total_rows'] = $this->_query[$index]['pagination']['count_all'];
        $config['per_page'] = ($this->_query[$index]['pagination']['limit'] === 'all') ? $this->_query[$index]['pagination']['count_all'] : $this->_query[$index]['pagination']['limit'];
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
        if ($option_paginates) {
            foreach ($option_paginates as $key => $value) {
                $config[$key] = $value;
            }
        }

        if (count($config['suffix']) > 0) {
            $sfx = http_build_query($config['suffix']);
            $config['suffix'] = '&' . $sfx;
            $config['first_url'] = $config['base_url'] . '?' . $sfx;
        } else {
            $config['suffix'] = '';
            $config['first_url'] = $config['base_url'];
        }

        $this->pagination->initialize($config);
        $this->_query[$index]['result_option']['pag'] = $this->pagination->create_links();

        return true;
    }

    // Модуль создает цену для отображения в редактировании
    public function mod_price_actual($item = 'price_actual', $option = array())
    {

        $index = (isset($option['index'])) ? $option['index'] : false;
        $zacup = (isset($option['zac'])) ? true : false; // Включить ли закуп цену?

        if (count($this->_query[$index]['result']) > 0) {
            foreach ($this->_query[$index]['result'] as $k => $v) {
                $roboCop = $v['price_roz'];
                $roboRub = $roboCop / 100;

                $it = [
                    'format' => number_format($roboRub, 0, '.', ' '),
                    'number' => $roboRub,
                    'cop' => $roboCop,
                    'zac' => []
                ];

                if ($zacup !== false) {
                    $roboCop = $v['price_zac'];
                    $roboRub = $roboCop / 100;
                    $it['zac'] = [
                        'format' => number_format($roboRub, 0, '.', ' '),
                        'number' => $roboRub,
                        'cop' => $roboCop
                    ];
                }

                $this->_query[$index]['result'][$k]['modules'][$item] = $it;
            }
        }
        return true;
    }

    // Модуль создает цену для отображения
    public function mod_photos($item = 'photos', $option = array())
    {

        $labels = (isset($option['labels'])) ? $option['labels'] : false;
        $modules = (isset($option['modules'])) ? $option['modules'] : array();
        $index = (isset($option['index'])) ? $option['index'] : false;
        $no_images_view = (isset($option['no_images_view'])) ? $option['no_images_view'] : 0;

        if ($this->_query[$index]['module'] !== false) {
            if ($this->_query[$index]['labels'] !== false) {
                if (!in_array('modules', $this->_query[$index]['labels'])) $this->_query[$index]['labels'][] = 'modules';
            }
            $this->_query[$index]['module'] = true;
        }

        $GIDs = array();
        foreach ($this->_query[$index]['result'] as $k => $v) {
            if (!in_array($v['id'], $GIDs)) $GIDs[] = $v['id'];
        }

        $products_photos = [];
        if (count($GIDs) > 0) {
            $products_photos = $this->queryData([
                'in' => [
                    'method' => 'AND',
                    'set' => [[
                        'item' => 'product_id',
                        'values' => $GIDs
                    ]]
                ],
                'labels' => false,
                'table_name' => 'products_photos'
            ]);
        }

        if (count($this->_query[$index]['result']) > 0) {
            foreach ($this->_query[$index]['result'] as $k => $v) {
                $this->_query[$index]['result'][$k]['modules'][$item] = array();

                $m = 0;
                foreach ($products_photos as $pk => $pv) {
                    if ($pv['product_id'] === $v['id']) {
                        $this->_query[$index]['result'][$k]['modules'][$item][] = ($labels !== false) ? $this->mdl_helper->clear_array_0($pv, $labels) : $pv;
                        $m++;
                    }
                }

                if ($no_images_view > 0 && $m < 1) {
                    $this->_query[$index]['result'][$k]['modules'][$item][] = [
                        'photo_name' => 'no_images.jpg',
                        'define' => '1'
                    ];
                }
            }

        }
        return true;

    }

    // Модуль возвращает все отзывы к товару
    public function mod_reviews($item = 'reviews', $option = array())
    {

        $labels = (isset($option['labels'])) ? $option['labels'] : false;
        $index = (isset($option['index'])) ? $option['index'] : false;

        $GIDs = array();
        foreach ($this->_query[$index]['result'] as $k => $v) {
            if (!in_array($v['id'], $GIDs)) $GIDs[] = $v['id'];
        }

        $products_reviews = [];
        if (count($GIDs) > 0) {
            $products_reviews = $this->queryData([
                'return_type' => 'ARR2',
                'in' => [
                    'method' => 'AND',
                    'set' => [[
                        'item' => 'product_id',
                        'values' => $GIDs
                    ]]
                ],
                'order_by' => ['date_public', 'desc'],
                'labels' => false,
                'table_name' => 'products_reviews'
            ]);
        }

        if (count($this->_query[$index]['result']) > 0) {
            foreach ($this->_query[$index]['result'] as $k => $v) {
                $this->_query[$index]['result'][$k]['modules'][$item] = array();

                foreach ($products_reviews as $pk => $pv) {
                    if ($pv['product_id'] === $v['id']) {
                        $this->_query[$index]['result'][$k]['modules'][$item][] = ($labels !== false) ? $this->mdl_helper->clear_array_0($pv, $labels) : $pv;
                    }
                }

            }

        }
        return true;

    }

    // Модуль возвращает все отзывы к товару
    public function mod_drags($item = 'drags', $option = array())
    {

        $labels = (isset($option['labels'])) ? $option['labels'] : false;
        $index = (isset($option['index'])) ? $option['index'] : false;

        $GIDs = array();
        foreach ($this->_query[$index]['result'] as $k => $v) {
            if (!in_array($v['id'], $GIDs)) $GIDs[] = $v['id'];
        }

        if (count($this->_query[$index]['result']) > 0) {
            foreach ($this->_query[$index]['result'] as $k => $v) {
                $this->_query[$index]['result'][$k]['modules'][$item] = array();
                if ($v['drag']) {
                    $this->_query[$index]['result'][$k]['modules'][$item] = json_decode($v['drag'], true);
                }
            }
        }
        return true;

    }

    // Модуль собирает буквенный путь-ссылку для товара(ов)
    public function mod_linkPath($item = 'linkPath', $option = array())
    {

        $labels = (isset($option['labels'])) ? $option['labels'] : false;
        $modules = (isset($option['modules'])) ? $option['modules'] : false;
        $index = (isset($option['index'])) ? $option['index'] : false;

        if (count($this->_query[$index]['result']) > 0) {
            $IDsCats = [];
            foreach ($this->_query[$index]['result'] as $v) {
                if (!in_array($v['cat'], $IDsCats)) $IDsCats[] = $v['cat'];
            }
            $path_list = $this->mdl_category->getParentCatsTree($IDsCats);
            foreach ($this->_query[$index]['result'] as $k => $v) {

                if (isset($path_list[$v['cat']])) {
                    /*
                    echo "<pre>  -- " . $v['cat']  ;
                    print_r( $path_list );
                    echo "</pre> --";*/

                    $this->_query[$index]['result'][$k]['modules'][$item] = $path_list[$v['cat']] . '/' . $v['aliase'];
                }
            }

        }

        return true;

    }

    // Модуль формирует сумму с учетом статичной скидки
    // Зависимости: price_actual
    public function mod_salePrice($item = 'salePrice', $option = array())
    {

        $modules = (isset($option['modules'])) ? $option['modules'] : false;
        $index = (isset($option['index'])) ? $option['index'] : false;

        if (count($this->_query[$index]['result']) > 0) {
            foreach ($this->_query[$index]['result'] as $k => $v) {

                if ($v['salle_procent'] > 0) {

                    /*
                    $prodCop = $v['modules']['price_actual']['cop']; // Сумма текущая ( зачеркнутая ) коп
                    $minus = $prodCop * ( $v['salle_procent'] / 100 ); // Сумма экономии коп
                    $prodSale = $prodCop - $minus; // Сумма со скидкой в коп

                    $prodCop = $v['modules']['price_actual']['cop'] * 2; // Сумма текущая ( зачеркнутая ) коп
                    $minus = $prodCop * ( $v['salle_procent'] / 100 ); // Сумма экономии коп
                    $prodSale = $prodCop - $minus; // Сумма со скидкой в коп
                    */

                    $_proc = $v['salle_procent'];
                    $_roz = $v['modules']['price_actual']['cop'];
                    $_old = $_roz / ((100 - $_proc) / 100);

                    $minus = $_old - $_roz;
                    $prodSale = $_roz;
                    $prodCop = $_old;

                    // $prodCop = $v['modules']['price_actual']['cop']; // Сумма текущая ( зачеркнутая ) коп
                    // $minus = $prodCop * ( $v['salle_procent'] / 100 ); // Сумма экономии коп
                    // $prodSale = $prodCop; // Сумма со скидкой в коп
                    // $prodCop = $prodCop + $minus; // Сумма со скидкой в коп

                    //30.000=21.000/((100-30%)/100)

                    $this->_query[$index]['result'][$k]['modules'][$item] = [
                        'COP' => [
                            'orig' => $prodCop,
                            'saleMinus' => $minus,
                            'salePrice' => $prodSale
                        ],
                        'VAL_00' => [
                            'orig' => number_format($prodCop / 100, 0, '.', ' '),
                            'saleMinus' => number_format($minus / 100, 0, '.', ' '),
                            'salePrice' => number_format($prodSale / 100, 0, '.', ' '),
                        ],
                        'VAL_' => [
                            'orig' => number_format($prodCop / 100, 0, '.', ' '),
                            'saleMinus' => number_format($minus / 100, 0, '.', ' '),
                            'salePrice' => number_format($prodSale / 100, 0, '.', ' '),
                        ]
                    ];

                } else {
                    $this->_query[$index]['result'][$k]['modules'][$item] = [];
                }
            }

        }

        return true;

    }

    // Модуль собирает буквенный путь-ссылку для товара(ов)
    public function mod_emptyPrice($item = 'emptyPrice', $option = array())
    {

        $labels = (isset($option['labels'])) ? $option['labels'] : false;
        $modules = (isset($option['modules'])) ? $option['modules'] : false;
        $index = (isset($option['index'])) ? $option['index'] : false;

        $prices_empty_list = $this->mdl_product->queryData([
            'return_type' => 'ARR2',
            'table_name' => 'status_prices_empty',
            'labels' => ['id', 'title']
        ]);

        if (count($this->_query[$index]['result']) > 0) {
            foreach ($this->_query[$index]['result'] as $k => $v) {
                $this->_query[$index]['result'][$k]['modules'][$item] = [
                    'id' => 0,
                    'title' => 'Цену уточняйте'
                ];
                if ($v['prices_empty'] > 0) {
                    foreach ($prices_empty_list as $pk => $pv) {
                        if ($v['prices_empty'] == $pv['id']) {
                            $this->_query[$index]['result'][$k]['modules'][$item] = ($labels !== false) ? $this->mdl_helper->clear_array_0($pv, $labels) : $pv;
                        }
                    }
                }
            }

        }

        return true;

    }

    // Модуль собирает буквенный путь-ссылку для товара(ов)
    public function mod_qtyEmptyStatus($item = 'emptyQty', $option = array())
    {

        $labels = (isset($option['labels'])) ? $option['labels'] : false;
        $modules = (isset($option['modules'])) ? $option['modules'] : false;
        $index = (isset($option['index'])) ? $option['index'] : false;

        //qty_empty_status

        $qty_empty_status = $this->mdl_product->queryData([
            'return_type' => 'ARR2',
            'table_name' => 'status_qty_empty',
            'labels' => ['id', 'title']
        ]);

        if (count($this->_query[$index]['result']) > 0) {
            foreach ($this->_query[$index]['result'] as $k => $v) {
                $this->_query[$index]['result'][$k]['modules'][$item] = [
                    'id' => 0,
                    'title' => 'Наличие уточняйте'
                ];
                if ($v['qty_empty'] > 0) {
                    foreach ($qty_empty_status as $pk => $pv) {
                        if ($v['qty_empty'] == $pv['id']) {
                            $this->_query[$index]['result'][$k]['modules'][$item] = ($labels !== false) ? $this->mdl_helper->clear_array_0($pv, $labels) : $pv;
                        }
                    }
                }
            }

        }

        return true;

    }

    // Модуль для отображения параметров
    public function mod_paramsView($item = 'paramsView', $option = array())
    {

        $labels = (isset($option['labels'])) ? $option['labels'] : false;
        $modules = (isset($option['modules'])) ? $option['modules'] : false;
        $index = (isset($option['index'])) ? $option['index'] : false;

        if (count($this->_query[$index]['result']) > 0) {

            $IDsCats = [];
            foreach ($this->_query[$index]['result'] as $v) {
                if (!in_array($v['cat'], $IDsCats)) $IDsCats[] = $v['cat'];
            }

            if (count($IDsCats) > 0) {

                $allCats = $this->mdl_category->queryData([
                    'return_type' => 'ARR2',
                    'in' => [
                        'method' => 'AND',
                        'set' => [[
                            'item' => 'id',
                            'values' => $IDsCats
                        ]]
                    ],
                    'labels' => ['id', 'param_id']
                ]);


                if (count($allCats) > 0) {

                    $IDsPatt = [];
                    foreach ($allCats as $v) {
                        if (!in_array($v['param_id'], $IDsPatt)) $IDsPatt[] = $v['param_id'];
                    }


                    if (count($IDsPatt) > 0) {

                        $allParams = $this->mdl_category->queryData([
                            'return_type' => 'ARR2',
                            'in' => [
                                'method' => 'AND',
                                'set' => [[
                                    'item' => 'id',
                                    'values' => $IDsPatt
                                ]]
                            ],
                            'table_name' => 'products_params',
                            'labels' => ['id', 'name', 'labels']
                        ]);

                        foreach ($allParams as $k => $v) {
                            $allParams[$k]['labels'] = json_decode($allParams[$k]['labels'], true);
                        }

                        foreach ($allCats as $k => $v) {
                            foreach ($allParams as $pk => $pv) {
                                if ($v['param_id'] == $pv['id']) {
                                    $allCats[$k]['param'] = $pv;
                                }
                            }
                        }


                        foreach ($this->_query[$index]['result'] as $k => $v) {
                            $this->_query[$index]['result'][$k]['params'] = json_decode($this->_query[$index]['result'][$k]['params'], true);
                        }

                        foreach ($this->_query[$index]['result'] as $k => $v) {
                            $params = [];
                            foreach ($allCats as $ck => $cv) {
                                if ($v['cat'] == $cv['id']) {
                                    $add = [];
                                    foreach ($cv['param']['labels'] as $pk => $pv) {
                                        foreach ($v['params'] as $vk => $vv) {
                                            if ($pv['variabled'] == $vv['variabled']) {
                                                $add[] = [
                                                    'i' => $vv['variabled'],
                                                    't' => $pv['holders'],
                                                    'v' => $vv['value']
                                                ];
                                            }
                                        }
                                    }
                                    $params = $add;
                                }
                            }

                            $this->_query[$index]['result'][$k]['modules'][$item] = $params;
                        }
                        /*
                        echo "<pre>";
                        print_r($this->_query[$index]['result']);
                        echo "</pre>";
                        */


                    }


                }

                /*
                $path_list = $this->mdl_category->getParentCatsTree( $IDsCats );
                foreach( $this->_query[$index]['result'] as $k => $v ){
                    $this->_query[$index]['result'][$k]['modules'][$item] = $path_list[$v['cat']] . '/' . $v['aliase'];
                }*/
            }

        }

        return true;


    }

    // Модуль фильтрации
    /*
        Зависимости:
            1. Необходимо что бы модуль пагинации вызывался последним
    */
    public function mod_setFilters($item = 'setFilters', $option = array())
    {
        $index = (isset($option['index'])) ? $option['index'] : false;
        $setItems = (isset($option['setItems'])) ? $option['setItems'] : [];

        if (count($setItems) > 0 && count($this->_query[$index]['result']) > 0) {


            //--------------------------------------
            // Отберём только чекбоксы

            $setItems_checkboxes = [];
            foreach ($setItems as $SIv) {
                if ($SIv['type'] === 'checkbox-group') {
                    $setItems_checkboxes[] = $SIv;
                }
            }/*

            echo "<pre>";
            print_r( $setItems_checkboxes );
            echo "</pre>";*/


            $newArrayProducts = [];
            foreach ($this->_query[$index]['result'] as $k => $v) {
                $f = json_decode($v['filters'], true);
//              if( count( $f ) > 0 ){
                $cou = count($setItems_checkboxes); // кол-во обязательных параметров
                $set_cou = 0; // кол-во ВЫПОЛНЕННЫХ обязательных параметров
                foreach ($setItems_checkboxes as $SIv) {
                    $e = 0; // кол-во совпадений для одного обхода
                    if ($SIv['item'] == 'brand') {
                        if (in_array($v['postavchik'], $SIv['values'])) {
                            $e++;
                        }
                    } else {

                        foreach ($f as $PRf) {
                            if ($SIv['item'] == $PRf['item']) {
                                foreach ($SIv['values'] as $SIv_value) {
                                    if(preg_match("/,/", $SIv_value)) $PRf['values']=implode(",", $PRf['values']); // Имитирует операцию AND по "," - например, если нужно найти унисекс товар, то запрашивается он, как женский И мужской
                                    if (in_array($SIv_value, $PRf['values']) OR $SIv_value==$PRf['values']) { // Второй аргумент добавлен для операции выше, так как $PRf['values'] теперь строка
                                        $e++;
                                    }
                                }
                            }
                        }
                    }
                    if ($e > 0) {
                        $set_cou++;
                    }
                }
                if ($set_cou == $cou) {
                    $newArrayProducts[] = $v;
                }
//              }
            }

            $this->_query[$index]['result'] = $newArrayProducts;

            $this->_query[$index]['pagination']['count_all'] = count($this->_query[$index]['result']);
            //--------------------------------------


            //--------------------------------------
            // Отберём только поле с диапазоном цены

            $ot = 0;
            $do = 0;
            $ok_cena = false;

            foreach ($setItems as $SIv) {
                if ($SIv['type'] === 'range-values') {
                    $SIv = $SIv['values'];
                    if (count($SIv) > 1) {
                        if (is_numeric($SIv[0]) && is_numeric($SIv[1])) {
                            $ot = $SIv[0] * 100;
                            $do = $SIv[1] * 100;
                            $ok_cena = true;
                        }
                    }
                }
            }


            if ($ok_cena !== false) {
                $newArrayProducts = [];
                foreach ($this->_query[$index]['result'] as $k => $v) {
                    $price = $v['modules']['price_actual']['cop'];
                    if ($price >= $ot && $price <= $do) {
                        $newArrayProducts[] = $v;
                    }
                }


                $this->_query[$index]['result'] = $newArrayProducts;
                $this->_query[$index]['pagination']['count_all'] = count($this->_query[$index]['result']);
            }

            // --------------------------------------


        }

        return true;

    }

    // Модуль сортировки по цене
    /*
        Зависимости:
            1. Обязателен предварительный запуск модуля цены
            2. Метод $this->price_sortable
    */
    public function mod_setSortPrices($item = 'setSortPrices', $option = array())
    {
        $index = (isset($option['index'])) ? $option['index'] : false;
        $sort = (isset($option['sort'])) ? $option['sort'] : 'pricemin';
        if (count($this->_query[$index]['result']) > 0) {

            $formulaSort = array();
            foreach ($this->_query[$index]['result'] as $k => $v) {

                $formulaSort[] = [
                    'PID' => $v['id'],
                    'VALUE' => $v['modules']['price_actual']['cop'] / 100
                ];


                //if( !in_array( $v['id'], $GIDs) ) $GIDs[] = $v['id'];
            }


            if ($sort === 'pricemax') $formulaSort = $this->price_sortable($formulaSort, 'VALUE', SORT_DESC);
            if ($sort === 'pricemin') $formulaSort = $this->price_sortable($formulaSort, 'VALUE', SORT_ASC);

            $newArrayResult = [];
            foreach ($formulaSort as $fsv) {
                foreach ($this->_query[$index]['result'] as $rv) {
                    if ($fsv['PID'] === $rv['id']) {
                        $newArrayResult[] = $rv;
                    }
                }
            }

            $this->_query[$index]['result'] = $newArrayResult;
            /*
            echo "<pre>";
            print_r( $formulaSort );
            echo "</pre>";
            */
            //$this->_query[$index]['result'][$k]['modules'][$item] = $formulaSort;

            /*$limit = ( $limit === 'all' ) ? count( $this->_query[$index]['result'] ) : $limit;
            $this->_query[$index]['result'] = array_slice( $this->_query[$index]['result'], 0, $limit ); */
        }
        return true;
    }

    // Модуль лимита
    public function mod_limit($item = 'limit', $option = array())
    {
        $index = (isset($option['index'])) ? $option['index'] : false;
        $limit = (isset($option['limit'])) ? $option['limit'] : 'all';
        if (count($this->_query[$index]['result']) > 0) {
            $limit = ($limit === 'all') ? count($this->_query[$index]['result']) : $limit;
            $this->_query[$index]['result'] = array_slice($this->_query[$index]['result'], 0, $limit);
        }
        return true;
    }

    // Получить полный путь до товарной позиции
    public function getParentProductTree($store_id = false, $productAliase = false)
    {

        $string = '';

        if ($productAliase !== false && $store_id !== false) {

            $tovar = $this->queryData([
                'return_type' => 'ARR1',
                'where' => [
                    'method' => 'AND',
                    'set' => [[
                        'item' => 'store_id',
                        'value' => $store_id
                    ], [
                        'item' => 'aliase',
                        'value' => $productAliase
                    ]]
                ],
                'labels' => ['id', 'aliase', 'title', 'cat']
            ]);

            if (!empty($tovar)) {

                $catItems = $this->mdl_category->queryData([
                    'return_type' => 'ARR2',
                    'where' => [
                        'method' => 'AND',
                        'set' => [
                            ['item' => 'store_id', 'value' => $store_id],
                            ['item' => 'id', 'value' => $tovar['cat']]
                        ]
                    ],
                    'labels' => ['id', 'parent_id', 'aliase']
                ]);


                if (count($catItems) > 0) {
                    $catItem = $catItems[0];
                    $_s_plus = $this->mdl_category->getParentCatsTree($store_id, [$catItem['id']]);
                    $string .= $_s_plus[$catItem['id']];
                }

                //$string .= '/'.$catItem['aliase'];
                $string .= '/' . $productAliase;
            }
        }

        return $string;
    }

    // подставить нули в артикул без нулей
    public function code_format($code, $lenght)
    {
        /* $l = (int)$lenght - (int)mb_strlen($code);
         $s = '';
         for($i=0; $i<$l; $i++){ $s = $s . '0';  }
         return "$s" . "$code";*/
        return $code;
    }

    // Создатель алиасов из текста
    public function aliase_translite($string = false)
    {
        if ($string !== false) {
            $replace = array(
                "'" => "",
                "`" => "",
                "а" => "a", "А" => "a",
                "б" => "b", "Б" => "b",
                "в" => "v", "В" => "v",
                "г" => "g", "Г" => "g",
                "д" => "d", "Д" => "d",
                "е" => "e", "Е" => "e",
                "ё" => "e", "Ё" => "e",
                "ж" => "zh", "Ж" => "zh",
                "з" => "z", "З" => "z",
                "и" => "i", "И" => "i",
                "й" => "y", "Й" => "y",
                "к" => "k", "К" => "k",
                "л" => "l", "Л" => "l",
                "м" => "m", "М" => "m",
                "н" => "n", "Н" => "n",
                "о" => "o", "О" => "o",
                "п" => "p", "П" => "p",
                "р" => "r", "Р" => "r",
                "с" => "s", "С" => "s",
                "т" => "t", "Т" => "t",
                "у" => "u", "У" => "u",
                "ф" => "f", "Ф" => "f",
                "х" => "h", "Х" => "h",
                "ц" => "c", "Ц" => "c",
                "ч" => "ch", "Ч" => "ch",
                "ш" => "sh", "Ш" => "sh",
                "щ" => "sch", "Щ" => "sch",
                "ъ" => "", "Ъ" => "",
                "ы" => "y", "Ы" => "y",
                "ь" => "", "Ь" => "",
                "э" => "e", "Э" => "e",
                "ю" => "yu", "Ю" => "yu",
                "я" => "ya", "Я" => "ya",
                "і" => "i", "І" => "i",
                "ї" => "yi", "Ї" => "yi",
                "є" => "e", "Є" => "e"
            );
            $str = iconv("UTF-8", "UTF-8//IGNORE", strtr($string, $replace));
            $str = preg_replace("/[^a-z0-9-]/i", " ", $str);
            $str = preg_replace("/ +/", "-", trim($str));
            return strtolower($str);
        }

    }

    //Сортировка ассоциативного массива по заданной колонке
    public function price_sortable()
    {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row)
                    $tmp[$key] = $row[$field];
                $args[$n] = $tmp;
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return array_pop($args);
    }

    // Получить розничную стоимость
    public function getRozCena($product_id = false)
    {

        $res = [
            'asdad' => 0
        ];
        // if( $product_id !== false ){
        //
        //     $r = $this->db->get_where( 'products', [
        //         'id' => $product_id
        //     ])->row_array();
        //
        //     $res = $this->getProductPrice( $r );
        //
        // }

        return $res;

    }

    // Получение стоимости с учётом наценок и скидок
    public function getProductPrice($product_data)
    {

        $res = false;

        if (isset($product_data['price_zac']) && !empty($product_data['price_zac']) && (string)$product_data['price_zac'] !== '0') {

            $price_z = $product_data['price_zac'] / 100;
            $price_r = 0;

            $proc_nacenca = 250;
            $proc_skidka = rand(20, 40);

            // Розничная стоимость
            $price_r = ($price_z * ($proc_nacenca * 0.01)); // 23692,5

            $res = [
                'procSkidca' => $proc_skidka,
                'price_r' => $price_r
            ];

        }

        return $res;

    }

    // Получение стоимости с учётом наценок и скидок
    public function getProductPrice_old($product_data)
    {

        $res = false;

        if (isset($product_data['id']) && isset($product_data['title']) && isset($product_data['price_zac'])) {

            $F1 = [
                'серебр' => 4,
                'авантюрин' => 2,
                'азурит' => 2,
                'аквамарин' => 2,
                'аметист' => 2,
                'бирюз' => 2,
                'берил' => 2,
                'бриллиант' => 1,
                'везувиан' => 2,
                'варисцид' => 2,
                'агат' => 2,
                'горн' => 2,
                'гранат' => 2,
                'жадеит' => 2,
                'жемчуг' => 2,
                'змеевик' => 2,
                'зонохлорит' => 2,
                'изумруд' => 1,
                'кварц' => 2,
                'корунд' => 2,
                'кошач' => 2,
                'лун' => 2,
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
                'рубин' => 1,
                'сардоникс' => 2,
                'сердолик' => 2,
                'топаз' => 2,
                'тигров' => 2,
                'турмалин' => 2,
                'халцедон' => 2,
                'хризоберил' => 2,
                'хризолит' => 2,
                'цитрин' => 2,
                'циркон' => 2,
                'янтар' => 2,
                'яшм' => 2,
                'сапфир' => 1
            ];

            $title = $product_data['title'];
            $setFormula = 3;

            if (!empty($title)) {
                foreach ($F1 as $k => $v) {
                    // $str_text = mb_strtolower( $title );
                    // $str_find = '' . mb_strtolower( $k ) . 'iU';
                    // if ( preg_match($str_find, $str_text) )
                    // {
                    //    $setFormula = $v;
                    // }

                    $mystring = mb_strtolower($title);
                    $findme = mb_strtolower($k);
                    $pos = mb_strpos($mystring, $findme);
                    if ($pos !== false) {
                        $setFormula = $v;
                    }

                }
            }

            $price_z = $product_data['price_zac'] / 100;
            $price_r = 0;

            if ($setFormula == 1) {
                $proc_nacenca = 135;
                $proc_skidka = rand(32, 40);
                $summa_nacenka = ($price_z * ($proc_nacenca * 0.01)); // 23692,5
                $summa_plus_nacenka = $price_z + $summa_nacenka;
                $summa_skidki = $summa_plus_nacenka * ($proc_skidka * 0.01); // 9477
                $summa_minus_skidka = $summa_plus_nacenka - $summa_skidki;
                $price_r = ($summa_minus_skidka < $price_z) ? 'МИНУС' : $summa_minus_skidka;
                $summa_navara = $price_r - $price_z;
            }


            if ($setFormula == 2) {

                $proc_nacenca = 200;
                $proc_skidka = rand(42, 50);
                $summa_nacenka = ($price_z * ($proc_nacenca * 0.01)); // 23692,5
                $summa_plus_nacenka = $price_z + $summa_nacenka;
                $summa_skidki = $summa_plus_nacenka * ($proc_skidka * 0.01); // 9477
                $summa_minus_skidka = $summa_plus_nacenka - $summa_skidki;
                $price_r = ($summa_minus_skidka < $price_z) ? 'МИНУС' : $summa_minus_skidka;
                $summa_navara = $price_r - $price_z;

            }

            if ($setFormula == 3) {

                $proc_nacenca = 100;
                $proc_skidka = rand(26, 30);
                $summa_nacenka = ($price_z * ($proc_nacenca * 0.01)); // 23692,5
                $summa_plus_nacenka = $price_z + $summa_nacenka;
                $summa_skidki = $summa_plus_nacenka * ($proc_skidka * 0.01); // 9477
                $summa_minus_skidka = $summa_plus_nacenka - $summa_skidki;
                $price_r = ($summa_minus_skidka < $price_z) ? 'МИНУС' : $summa_minus_skidka;
                $summa_navara = $price_r - $price_z;

            }

            if ($setFormula == 4) {

                $proc_nacenca = 150;
                $proc_skidka = rand(26, 30);
                $summa_nacenka = ($price_z * ($proc_nacenca * 0.01)); // 23692,5
                $summa_plus_nacenka = $price_z + $summa_nacenka;
                $summa_skidki = $summa_plus_nacenka * ($proc_skidka * 0.01); // 9477
                $summa_minus_skidka = $summa_plus_nacenka - $summa_skidki;
                $price_r = ($summa_minus_skidka < $price_z) ? 'МИНУС' : $summa_minus_skidka;
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


}

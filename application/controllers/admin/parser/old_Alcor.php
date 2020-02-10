<?php


defined('BASEPATH') OR exit('No direct script access allowed');

class Alcor extends CI_Controller
{

    protected $user_info = array();
    protected $store_info = array();

    protected $post = array();
    protected $get = array();

    public function __construct()
    {

        parent::__construct();

		$this->user_info = ( $this->mdl_users->user_data() )? $this->mdl_users->user_data() : false;
        $this->store_info = $this->mdl_stores->allConfigs();

        $this->post = $this->security->xss_clean($_POST);
        $this->get = $this->security->xss_clean($_GET);

        if( $this->mdl_helper->get_cookie('HASH') !== $this->mdl_users->userHach() )
        {
            $this->user_info['admin_access'] = 0;
        }

    }

    // Защита прямых соединений
	public function access_static()
    {
        if( $this->user_info !== false )
        {
            if( $this->user_info['admin_access'] < 1 )
            {
                redirect( '/login' );
            }
        }
	}

    // Защита динамических соединений
	public function access_dynamic()
    {
        if( $this->user_info !== false )
        {
            if( $this->user_info['admin_access'] < 1 )
            {
                exit('{"err":"1","mess":"Нет доступа"}');
            }
        }
	}

    // Показать страницу по умолчанию
    public function index()
    {

        $this->access_static();

		$title = 'Парсинг с сайта Алькора';
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

            'content' => $this->mdl_tpl->view('pages/admin/parser/alcor/alcor.html', array(), true),

            'load' => $this->mdl_tpl->view('snipets/load.html',array(
                'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder')
            ),true),

            'resorses' => $this->mdl_tpl->view( 'resorses/admin/parser/alcor.html', array(
                'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path')
			), true )

        ), false);

    }

    // Обработка пакета - заливка его в БД
    /*public function parseAlcor (){

        $packs = ( isset( $this->post['pack'] ) ) ? $this->post['pack'] : [];
        $clear = ( isset( $this->post['clear'] ) ) ? $this->post['clear'] : false;

        if( $clear === '1' ){
            $this->mdl_db->_update_db( "products", "postavchik", 'alcor', [
                'qty' => 0
            ]);
        }



        $listArts = []; $err = 0;
        foreach( $packs as $v ){
            if( $v['articul'] ){
                $art = $this->mdl_product->code_format( $v['articul'], 6 );
                if( !in_array( $art, $listArts ) ){
                    $listArts[] = $art;
                }
            }
        }

        $listArtsIsset = []; // список существующих товаров
        $issetProducts = [];
        if( count( $listArts ) > 0 ){
            $issetProducts = $this->mdl_product->queryData([
                'type' => 'ARR2',
                'in' => [
                    'method' => 'AND',
                    'set' => [[
                        'item' => 'articul',
                        'values' => $listArts
                    ]]
                ],
                 'where' => [
                    'method' => 'AND',
                    'set' => [[
                        'item' => 'postavchik',
                        'value' => 'alcor'
                    ]]
                ],
                'labels' => ['id', 'aliase', 'title', 'articul']
            ]);
            foreach( $issetProducts as $v ){
                $art = $this->mdl_product->code_format( $v['articul'], 6 );
                if( !in_array( $art, $listArtsIsset ) ){
                    $listArtsIsset[] = $art;
                }
            }
        }

        $INSERT = [];
        $UPDATE = [];

        if( count( $packs ) > 0 ){
            foreach( $packs as $v ){
                if( !in_array( $v['articul'], $listArtsIsset ) ){
                    $size_list = explode( ',', $v['size'] );
                    foreach( $size_list as $sl ){
                        $iv = $v;
                        $iv['size'] = $sl;
                        $INSERT[] = $this->getRenderAlcor( $iv );
                    }
                }else{
                    $UPDATE[] = [
                        'ARTICUL' => $v['articul'],
                        'data' => $this->getRenderAlcor( $v )
                    ];
                }
            }
        }


        if( count( $INSERT ) > 0 ){
            foreach( $INSERT as $k => $v ){

                $this->db->insert('products', $v['product'] );
                $insID = $this->db->insert_id();

                $aliase = $this->mdl_product->aliase_translite( $v['product']['title'] ) . '_' . trim( $v['product']['articul']) . '_' . $insID;
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

                $ret = $this->mdl_product->getRozCena( $insID );
                if( $ret['price_r'] !== 'МИНУС' ){
                    $end = $ret['price_r'] * 100;
                    $updProd['price_roz'] = $end;
                    $updProd['salle_procent'] = $ret['procSkidca'];
                }

                $this->mdl_db->_update_db( "products", "id", $insID, $updProd );


            }

        }


        if( count( $UPDATE ) > 0 ){
            foreach( $UPDATE as $k => $v ){


                $PID = false;
                foreach( $issetProducts as $ipv ){
                    if( $ipv['articul'] === $v['ARTICUL'] ){
                        $PID = $ipv['id'];
                    }
                }

                if( $PID !== false ){




                    $this->mdl_db->_update_db( "products", "id", $PID, [
                        'qty' => $v['data']['product']['qty'],
                        'price_zac' => $v['data']['product']['price_zac']
                    ]);

                    $ret = $this->mdl_product->getRozCena( $PID );
                    if( $ret['price_r'] !== 'МИНУС' ){
                        $end = $ret['price_r'] * 100;
                        $updProd['price_roz'] = $end;
                        $updProd['salle_procent'] = $ret['procSkidca'];
                        $this->mdl_db->_update_db( "products", "id", $PID, $updProd );
                    }

                }

            }
        }

        echo json_encode([
            'err' => 0,
            'mess' => 'success',
            'debug' => [
                'count-upd' => count( $UPDATE ),
                'count-ins' => count( $INSERT )
            ]
        ]);

    }*/

    public function parseAlcor ()
    {

        // Первоначальная прочистка остатков
        if( isset( $this->post['clear'] ) && (int)$this->post['clear'] === '1' )
        {
            $this->mdl_db->_update_db( "products", "postavchik", 'alcor', [
                'qty' => 0
            ]);
        }

        // Получение пакета данных
        $packs = ( isset( $this->post['pack'] ) ) ? $this->post['pack'] : [];

        // Получить список товаров со схожими сериями в поступлении
        $listArts = []; $err = 0;
        foreach( $packs as $v )
        {
            if( $v['seria'] )
            {
                $art = trim(mb_strtolower($v['seria']));
                if( !in_array( $art, $listArts ) ){
                    $listArts[] = $art;
                }
            }
        }

        // Получить список существующих товаров в БД
        $listArtsIsset = [];
        if( count( $listArts ) > 0 )
        {
            $issetProducts = $this->mdl_product->queryData([
                'type' => 'ARR2',
                'in' => [
                    'method' => 'AND',
                    'set' => [[
                        'item' => 'seria',
                        'values' => $listArts
                    ]]
                ],
                 'where' => [
                    'method' => 'AND',
                    'set' => [[
                        'item' => 'postavchik',
                        'value' => 'alcor'
                    ]]
                ],
                'labels' => ['id', 'aliase', 'title', 'seria', 'articul']
            ]);
            foreach( $issetProducts as $v )
            {
                $art = trim(mb_strtolower($v['seria']));
                if( !in_array( $art, $listArtsIsset ) )
                {
                    $listArtsIsset[$art] = $v['id'];
                }
            }
        }

        // Собрать два массива для обновления и добавления
        $INSERT = [];
        $UPDATE = [];
        if( count( $packs ) > 0 )
        {
            foreach( $packs as $v )
            {
                $art = trim(mb_strtolower($v['seria']));
                if( !isset( $listArtsIsset[$art] ) )
                {
                    $INSERT[] = $this->getRenderAlcor( $v );
                }
                else
                {
                    $UPDATE[] = [
                        'ID' => $listArtsIsset[$art],
                        'data' => $this->getRenderAlcor( $v )
                    ];
                }
            }
        }

        //
        // $PACK = [];
        // if( count( $packs ) > 0 )
        // {
        //     foreach( $packs as $v )
        //     {
        //         $size_list = explode( ',', $v['size'] );
        //         if( count( $size_list ) > 0 )
        //         {
        //             foreach( $size_list as $sl )
        //             {
        //                 $v['size'] = $sl;
        //                 $PACK[] = $this->getRenderAlcor( $v );
        //             }
        //         }
        //         else
        //         {
        //             $PACK[] = $this->getRenderAlcor( $v );
        //         }
        //     }
        // }
        //
        // foreach( $PACK as $v )
        // {
        //
        //     $option = [
        //         'return_type' => 'ARR1',
        //         'where' => [
        //             'method' => 'AND',
        //             'set' => [[
        //                 'item' => 'articul',
        //                 'value' => $v['product']['articul']
        //             ],[
        //                 'item' => 'postavchik',
        //                 'value' => 'alcor'
        //             ]]
        //         ],
        //         'labels' => ['id', 'aliase', 'title', 'articul', 'size']
        //     ];
        //
        //     if( isset( $v['product']['size'] ) )
        //     {
        //         $option['where']['set'][] = [
        //             'item' => 'size',
        //             'value' => $v['product']['size']
        //         ];
        //     }
        //
        //     $issetProducts = $this->mdl_product->queryData( $option );
        //
        //     if( !$issetProducts )
        //     {
        //
        //         // !! INSERT
        //         //echo 'INSERT <br />';
        //
        //         $this->db->insert('products', $v['product'] );
        //         $insID = $this->db->insert_id();
        //
        //         $aliase = $this->mdl_product->aliase_translite( $v['product']['title'] ) . '_' . trim( $v['product']['articul']) . '_' . $insID;
        //         $updProd = [
        //             'aliase' => $aliase,
        //             'moderate' => 0
        //         ];
        //
        //         $r = $this->saveImages( $v['photos']['photo_name'], $aliase );
        //
        //         if( $r !== false ){
        //             $v['photos']['product_id'] = $insID;
        //             $v['photos']['photo_name'] = $aliase.'.jpg';
        //             $this->db->insert( 'products_photos', $v['photos'] );
        //             $updProd['moderate'] = 2;
        //         }
        //
        //         $ret = $this->mdl_product->getRozCena( $insID );
        //         if( $ret['price_r'] !== 'МИНУС' ){
        //             $end = $ret['price_r'] * 100;
        //             $updProd['price_roz'] = $end;
        //             $updProd['salle_procent'] = $ret['procSkidca'];
        //         }
        //
        //         $this->mdl_db->_update_db( "products", "id", $insID, $updProd );
        //
        //     }
        //     else
        //     {
        //
        //         // !!! UPDATE
        //
        //         //echo 'update <br />';
        //
        //         $PID = $issetProducts['id'];
        //         $updProd = [
        //             'qty' => $v['product']['qty'],
        //             'price_zac' => $v['product']['price_zac']
        //         ];
        //
        //         $ret = $this->mdl_product->getRozCena( $PID );
        //         if( $ret['price_r'] !== 'МИНУС' ){
        //             $end = $ret['price_r'] * 100;
        //             $updProd['price_roz'] = $end;
        //             $updProd['salle_procent'] = $ret['procSkidca'];
        //         }
        //
        //         $this->mdl_db->_update_db( "products", "id", $PID, $updProd );
        //
        //     }
        // }
        //
        // echo json_encode([
        //     'err' => 0,
        //     'mess' => 'success'
        // ]);

    }

    // Сохранение картинок
    public function saveImages ( $image = false, $nameProduct = false )
    {
        $r = false;
        if( $image !== false && $nameProduct !== false ){
            $this->load->library('images');
            $path = "./uploads/products/alcor/";
            if ( file_exists( $path.$image ) ) {
                $itemFile = $this->images->file_item( $path . $image, $nameProduct.'.jpg' );
                $prew = "./uploads/products/100/";
                $prew2 = "./uploads/products/250/";
                $grozz = "./uploads/products/500/";

                $this->images->imageresize( $prew.$nameProduct.'.jpg', $path.$image, 100, 100, 100 );
                $this->images->imageresize( $prew2.$nameProduct.'.jpg', $path.$image, 250, 250, 100 );
                //$this->images->imageresize( $prew.$nameProduct.'.jpg', $path.$image, 500, 500, 100 );

                //$this->images->resize_jpeg( $itemFile, $path, $prew, $nameProduct, 100, 100, 100);
                ///$this->images->resize_jpeg( $itemFile, $path, $prew2, $nameProduct, 100, 250, 250);
                $this->getImage( $path.$image, $grozz, $nameProduct.".jpg" );
                //$this->images->resize_jpeg( $itemFile, $path, $grozz, $nameProduct, 100, 1000, 500);
                $r = true;
            }
        }
        return $r;
    }

    // Сохранить пхото как...
    public function getImage( $src = false, $path = './', $newName = '1.jpg' )
    {
        $t = file_get_contents( $src );
        file_put_contents( $path . $newName, $t );
    }

    // Получение данных из прайса - готовых для заливки в БД
    public function getRenderAlcor( $item = false )
    {

       if( $item !== false )
       {

            $cat_ids = [
                'Браслет' => '28',
                'Брошь' => '35',
                'Колье' => '36',
                'Кольцо' => '1',
                'Пирсинг' => '38'
                'Подвеска' => '19',
                'Серьги' => '10',
                //
                // 'Обручальные кольца' => '1',
                // 'Крест' => '37',
                // 'Пуссеты' => '43',
                // 'Браслеты' => '28',
                // 'Запонки' => '41',
            ];

            $cat_fx_1 = [
                'Кольцо' => 'Кольцо',
                'Пирсинг' => 'Пирсинг',
                'Подвеска' => 'Подвеска',
                'Крест' => 'Крест',
                'Серьги' => 'Серьги',
                'Колье' => 'Колье',
                'Пуссеты' => 'Пуссет',
                'Браслеты' => 'Браслет',
                'Браслет' => 'Браслет',
                'Запонки' => 'Запонка',
                'Брошь' => 'Брошь'
            ];

            // $mett_fx_1 = [
            //     'Красное' => ' из красного золота',
            //     'Радаж (красное)' => ' из красного золота',
            //     'Белое' => ' из белого золота',
            //     'Желтое' => ' из желтого золота',
            //     'Красное+белое+желтое' => ' из комбинированного золота',
            //     'Бел. мат.' => ' из белого, матового золота',
            //     'Красн. мат.' => ' из красного, матового золота',
            //     'Желт. мат.' => ' из желтого, матового золота'
            // ];
            //
            // $mett_fx_2 = [
            //     'Красное' => 'krasnZoloto',
            //     'Радаж (красное)' => 'krasnZoloto',
            //     'Белое' => 'belZoloto',
            //     'Желтое' => 'kombinZoloto',
            //     'Красное+белое+желтое' => 'kombinZoloto',
            //     'Бел. мат.' => 'belZoloto',
            //     'Красн. мат.' => 'krasnZoloto',
            //     'Желт. мат.' => 'kombinZoloto'
            // ];

            $title = $item['vid_izdelia'];
            if( !empty( $item['dlaKogo'] ) && $item['dlaKogo'] === 'Женщине,Мужчине' )
            {
                $title .= ' унисекс';
            }

            if( !empty( $item['dlaKogo'] ) && $item['dlaKogo'] === 'Мужчине' )
            {
                $title .= ' мужское';
            }

            if( !empty( $item['dlaKogo'] ) && $item['dlaKogo'] === 'Детям' )
            {
                $title .= ' детское';
            }

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

            if( $item['dlaKogo'] === 'Женщине,Мужчине' )
            {
                $filterData[3]['values'][] = 'woman';
                $filterData[3]['values'][] = 'men';
            }

            if( $item['dlaKogo'] === 'Женщине' )
            {
                $filterData[3]['values'][] = 'woman';
            }

            if( $item['dlaKogo'] === 'Мужчине' )
            {
                $filterData[3]['values'][] = 'men';
            }

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
                'value' => $item['dlaKogo']
            ],[
                'variabled' => 'technologiya',
                'value' => '-'
            ]];

            $paramItem[0]['value'] = $item['cvetMett'] .' золото';
            $paramItem[1]['value'] = 'Золото';

            $kamenList = ['Без камня','С камнем','Кристалл Swarovski','Swarovski Zirconia','Бриллиант','Сапфир','Изумруд','Рубин','Жемчуг','Топаз','Аметист','Гранат','Хризолит','Цитрин','Агат','Кварц','Янтарь','Опал','Фианит',
            'Родолит', 'Ситалл', 'Эмаль', 'Оникс', 'Корунд', 'Коралл прессованный'];

            $kamenListVals = ['empty','no_empty','swarovski','swarovski','brilliant','sapfir','izumrud','rubin','jemchug','topaz','ametist','granat','hrizolit','citrin','agat','kvarc','jantar','opal','fianit',
            'Rodolit', 'Sitall', 'Emal', 'Oniks', 'Korund', 'Corall_pressovannyi'];

            $kamenList2 = ['Без камня','С камнем','Кристаллом Swarovski','Swarovski Zirconia','Бриллиантом','Сапфиром','Изумрудом','Рубином','Жемчугом','Топазом','Аметистом','Гранатом','Хризолитом','Цитрином','Агатом','Кварцом','Янтарем','Опалом','Фианитом',
            'Родолитом', 'Ситаллом', 'Эмалью', 'Ониксом', 'Корундом', 'Кораллом прессованным'];

            $text = $item['optionLabel'];
            $kamen_list = [];
            $param_kamen_list = [];

            foreach( $kamenList as $pk => $pv )
            {
                $str_text = mb_strtolower( $text );
                $str_find = '/' . mb_strtolower( $pv ) . '/iU';
                if ( preg_match($str_find, $str_text)) {
                    $filterData[1]['values'][] = $kamenListVals[$pk];
                    $kamen_list[] = $kamenList2[$pk];
                    $param_kamen_list[] = $kamenList[$pk];
                }
            }

            if( count( $kamen_list ) > 0 )
            {

                if( count( $kamen_list ) == 1 )
                {
                    $title = $title . ' с ' . $kamen_list[0];
                    $paramItem[2]['value'] = $param_kamen_list[0];
                }

                if( count( $kamen_list ) == 2 )
                {
                    $title = $title . ' с ' . $kamen_list[0] . ' и ' . $kamen_list[1] ;
                    $paramItem[2]['value'] = $param_kamen_list[0] . ', ' . $param_kamen_list[1];
                }

                if( count( $kamen_list ) > 2 )
                {
                    $paramItem[2]['value'] = $param_kamen_list;
                    $__i = count($kamen_list)-1;
                    $last = $kamen_list[$__i];
                    array_splice($kamen_list, -1);
                    $title = $title . ' с ' . implode( ',', $kamen_list ) . ' и ' . $last;
                }

            }

            // 'empty','no_empty'
            if( count( $filterData[1]['values'] ) > 0 )
            {
                $filterData[1]['values'][] = 'no_empty';
            }
            else
            {
                $filterData[1]['values'][] = 'empty';
            }

            $filterData[0]['values'][] = $mett_fx_2[$item['cvetMett']];

            $razmerList = ['2.0','12.0','13.0','13.5','14.0','14.5','15.0','15.5','16.0','16.5','17.0','17.5','18.0','18.5','19.0','19.5','20.0','20.5','21.0','21.5','22.0','22.5','23.0','23.5','24.0','24.5','25.0'];
            $razmerListVals = ['2_0','12_0','13_0','13_5','14_0','14_5','15_0','15_5','16_0','16_5','17_0','17_5','18_0','18_5','19_0','19_5','20_0','20_5','21_0','21_5','22_0','22_5','23_0','23_5','24_0','24_5','25_0'];

            /*В вес pfgbcfk размер */
            if( $item['size'] ){
                $sz = str_replace( ",", ".", $item['size'] );
                $paramItem[4]['value'] = $sz;
                foreach( $razmerList as $rk => $rv ){
                    if( $rv === $sz ){
                        $filterData[4]['values'][] = $razmerListVals[$rk];
                    }
                }
            }

            $sex = null;
            if( $item['dlaKogo'] === 'Женщине' )
            {
                $sex = 'woman';
            }

            if( $item['dlaKogo'] === 'Мужчине' )
            {
                $sex = 'men';
            }

            $r['product'] = [
                'title' => $title,
                'articul' => $item['articul'],
                'cat' => $cat_ids[$item['title']],
                'params' => json_encode( $paramItem ),
                'size' => str_replace( ",", ".", trim($item['size'])),
                'filters' => json_encode( $filterData ),
                'proba' => $item['proba'],
                'postavchik' => 'alcor',
                'parser' => 'alcor',
                'weight' => str_replace( ",", ".", $item['weight'] ),
                'qty_empty' => '1',
                'prices_empty' => '1',
                'price_zac' => ( isset( $item['price'] ) ) ? intval( $item['price'] * 100 ) : 0,
                'qty' => '1',
                'sex' => $sex,//woman/men
                'view' => '1',
                'current' => 'RUR',
                'moderate' => '2',
                'lastUpdate' => time(),
                'optionLabel' => json_encode([
                    'collections' => $item['collection'],
                    'options' => $item['Komplect'],
                    'seria' => $item['optionLabel']
                ])
            ];

            $r['photos'] = [
                'product_id' => 0,
                'photo_name' => ( !isset($item['photo']) ) ? $item['articul'].'.jpg' : $item['photo'],
                'define' => '1'
            ];

            return $r;

       }
    }

    // Извлечение параметров ДРАГ камней
    public function getDragValues ()
    {

        $Alcor = $this->mdl_product->queryData([
            'return_type' => 'ARR2',
            'where' => [
                'method' => 'AND',
                'set' => [[
                    'item' => 'postavchik',
                    'value' => 'alcor'
                ]]
            ],
            //'limit' => 100,
            // 'order_by' => [
            //     'item' => 'id',
            //     'value' => 'DESC'
            // ],
            'labels' => ['id', 'title', 'optionLabel']
        ]);

        foreach( $Alcor as $key_prod => $value_prod )
        {

            $_1 = json_decode( $value_prod['optionLabel'], true );
            $_2 = explode( ',', $_1['seria']);
            foreach( $_2 as $_2k => $_2v )
            {
                $_2[$_2k] = trim( $_2v );
            }
            $_2 = array_diff( $_2, [''] );

            foreach( $_2 as $_2k => $_2v )
            {
                $__a = explode( ' ', $_2v );
                $_2[$_2k] = $__a;
                //$_2[$_2k] = array_diff( $__a, [''] );
            }

            $kl = ['сапфир','изумруд','бриллиант','рубин'];
            $kl_index = [0,0,0,0];
            $_for = [
                'Бриллиант' => [ 'Камень', 'Кол-во камней', 'Вес, Ct.', 'Чистота/Цвет', 'Форма огранки', '-', 'Кол-во граней', 'Цвет'],
                'Сапфир' => [ 'Камень', 'Кол-во камней', 'Вес, Ct.', '-', 'Форма огранки', '-',  'Кол-во граней', 'Цвет'],
                'Рубин' => [ 'Камень', 'Кол-во камней', 'Вес, Ct.', '-', 'Форма огранки', '-',  'Кол-во граней', 'Цвет'],
                'Изумруд' => [ 'Камень', 'Кол-во камней', 'Вес, Ct.', '-', 'Форма огранки', '-', 'Кол-во граней', 'Цвет']
            ];

            $_3 = [];
            foreach( $_2 as $_2k => $_2v )
            {
                foreach( $_2v as $_2v_v )
                {
                    $__v = mb_strtolower( $_2v_v );
                    if( in_array( $__v, $kl ) )
                    {
                        $__k = array_search( $__v, $kl );
                        $__r = []; $__rk = 0;
                        foreach( $_2v as $_2v_v_v )
                        {
                            $__r[$__rk] = $_2v_v_v;
                            $__rk++;
                        }
                        $_2v = $__r;

                        $___e = [
                            'kamen' => $_2v[$kl_index[$__k]],
                            'data' => []
                        ];

                        $__1 = $_for[$_2v[$kl_index[$__k]]];
                        foreach( $__1 as $__1k => $__1v )
                        {
                            if( !isset( $_2v[$__1k] ) ) echo $value_prod['id'];
                            $___e['data'][] = [
                                'name' => $__1v,
                                'value' => ( !isset( $_2v[$__1k] ) ) ? '-' : $_2v[$__1k]
                            ];
                        }
                        $_3[] = $___e;
                    }
                }
            }

            $Alcor[$key_prod]['drag'] = $_3;

        }


       /*
        echo "<pre>";
        print_r( $Alcor );
        echo "</pre>";
         */

        foreach( $Alcor as $v )
        {

            $upd = [
                'drag' => json_encode( $v['drag'] )
            ];

             echo   $v['id'];
            echo "<pre>";
            print_r( $upd );
            echo "</pre>";

            $this->mdl_db->_update_db( "products", "id", $v['id'], $upd );
        }

    }

}

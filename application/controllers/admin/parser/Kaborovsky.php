<?php


defined('BASEPATH') OR exit('No direct script access allowed');

class Kaborovsky extends CI_Controller {

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

        if( $this->mdl_helper->get_cookie('HASH') !== $this->mdl_users->userHach() ){
            $this->user_info['admin_access'] = 0;
        }

    }

    // Защита прямых соединений
	public function access_static()
    {
        if( $this->user_info !== false ){
            if( $this->user_info['admin_access'] < 1 ){
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

		$title = 'Парсинг с сайта коробовских';
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

            'content' => $this->mdl_tpl->view('pages/admin/parser/kaborovsky/kaborovsky.html', array(), true),

            'load' => $this->mdl_tpl->view('snipets/load.html',array(
                'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder')
            ),true),

            'resorses' => $this->mdl_tpl->view( 'resorses/admin/parser/kaborovsky.html', array(
                'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path')
			), true )

        ), false);

    }

    // Обработка пакета - заливка его в БД
    public function parseKaborovsky ()
    {

        error_reporting(-1);
		ini_set('display_errors', 1);

        $packs = ( isset( $this->post['pack'] ) ) ? $this->post['pack'] : [];

        // Получить список товаров со схожими сериями в поступлении
        $listArts = []; $err = 0;
        foreach( $packs as $v ){
            if( $v['cat'] )
            {
                $art = trim(mb_strtolower($v['cat']));
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
                        'value' => 'Kaborovsky'
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
                $art = trim(mb_strtolower($v['cat']));
                if( !isset( $listArtsIsset[$art] ) )
                {
                    $INSERT[] = $this->getRenderKaborovsky( $v );
                }
                else
                {
                    $UPDATE[] = [
                        'ID' => $listArtsIsset[$art],
                        'data' => $this->getRenderKaborovsky( $v )
                    ];
                }
            }
        }

        $IDS = [];

        if( count( $UPDATE ) > 0 )
        {

            $upd_bh = [];
            foreach( $UPDATE as $k => $v )
            {

                // $PID = false;
                // foreach( $issetProducts as $ipv ){
                //     if( $ipv['articul'] === $v['ARTICUL'] ){
                //         $PID = $ipv['id'];
                //     }
                // }

                //if( $PID !== false ){

                    /* $this->mdl_db->_update_db( "products", "id", $PID, [
                        'qty' => $v['data']['product']['qty']
                    ]);

                    $this->mdl_db->_update_db( "products", "id", $PID, [
                        'price_item' => $v['data']['price']['price_item']
                    ]);     */

                    $upd_bh[] = [
                        'id' => $v['ID'],
                        'qty' => '1',
                        'price_zac' => $v['data']['price']['price_item']
                    ];

                    if( !in_array( $v['ID'], $IDS ))
                    {
                        $IDS[] = $v['ID'];
                    }
                    // $ret = $this->mdl_product->getRozCena( $PID );
                    // if( $ret['price_r'] !== 'МИНУС' ){
                    //     $end = $ret['price_r'] * 100;
                    //     $updProd['price_roz'] = $end;
                    //     $updProd['salle_procent'] = $ret['procSkidca'];
                    // }
                    //
                    // $this->mdl_db->_update_db( "products", "id", $PID, $updProd );

                //}

            }

            if( count( $upd_bh ) > 0 )
            {
                $this->db->update_batch( 'products', $upd_bh, 'id' );
            }

        }

        if( count( $INSERT ) > 0 )
        {
            $insert_ok = [];
            foreach( $INSERT as $k => $v )
            {

                $art = $v['product']['seria'];
                if( in_array( $art, $insert_ok ) )
                {
                    continue;
                }

                $this->db->insert('products', $v['product'] );
                $insID = $this->db->insert_id();

                if( !in_array( $insID, $IDS ))
                {
                    $IDS[] = $insID;
                }

                $aliase = $this->mdl_product->aliase_translite( $v['product']['title'] ) . '_' . trim( $v['product']['articul'] ) . '_' . $insID;
                $updProd = [
                    'aliase' => $aliase,
                    'moderate' => 0
                ];

                if( isset( $v['photos'] ) )
                {
                    $r = $this->saveImages( $v['photos']['photo_name'], $aliase );
                }
                else
                {
                    $r = false;
                }

                if( $r !== false )
                {
                    $v['photos']['product_id'] = $insID;
                    $v['photos']['photo_name'] = $aliase.'.jpg';
                    $this->db->insert( 'products_photos', $v['photos'] );
                    $updProd['moderate'] = 2;
                }

                if( !empty( $v['price']['price_item'] ))
                {
                    $updProd['price_zac'] = $v['price']['price_item'];
                }

                $this->mdl_db->_update_db( "products", "id", $insID, $updProd );

            }
        }

        if( count( $IDS ) > 0 )
        {
            // Обновление цен
            $this->prices_update( $IDS );

            // Обновление ДРАГ-Камней
            $this->getDragValues( $IDS );
        }

        echo json_encode([
            'err' => 0,
            'mess' => 'success',
            'i' => count( $INSERT ),
            'u' => count( $UPDATE ),
        ]);

    }

    // Обновить стоимость по всей БД
    public function prices_update( $pids = [] )
    {
        $r = [];
        if( count( $pids ) > 0 )
        {
            $this->db->where_in( 'id', $pids );
            $r = $this->db->get( 'products' )->result_array();
        }
        if( count( $r ) > 0 )
        {
            $upd = [];
            foreach ( $r as $v )
            {
                $res = $this->mdl_product->getProductPrice(array(
                    'id' => $v['id'],
                    'title' => $v['title'],
                    'price_zac' => $v['price_zac']
                ));
                if( isset( $res['price_r'] ) > 0 && (int)$res['price_r'] > 0 && $res['price_r'] !== 'МИНУС' )
                {
                    $end = intval( $res['price_r'] ) * 100;
                    $upd[] = [
                        'id' => $v['id'],
                        'price_roz' => $end,
                        'salle_procent' => $res['procSkidca']
                    ];
                }
            }
            if( count( $upd ) > 0 )
            {
                $this->db->update_batch( 'products', $upd, 'id' );
            }
        }
    }

    // Сохранение картинок
    public function saveImages ( $image = false, $nameProduct = false )
    {
        $r = false;

        if( $image !== false && $nameProduct !== false )
        {

            $this->load->library('images');
            $path = "./uploads/products/kaborovsky/";

            if ( file_exists( $path.$image ) )
            {

                $itemFile = $this->images->file_item( $path . $image, $nameProduct.'.jpg' );

                $prew = "./uploads/products/100/";
                $this->images->imageresize( $prew.$nameProduct.'.jpg', $path.$image, 100, 100, 100 );

                $prew2 = "./uploads/products/250/";
                $this->images->imageresize( $prew2.$nameProduct.'.jpg', $path.$image, 250, 250, 100 );

                $grozz = "./uploads/products/500/";
                $this->getImage( $path.$image, $grozz, $nameProduct.".jpg" );

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
    public function getRenderKaborovsky( $item = false )
    {
       if( $item !== false ){

            $cat_ids = [
                'Кольца' => '1',
                'Кольцо' => '1',
                'Обручальные кольца' => '1',
                'Подвеска' => '19',
                'Крест' => '37',
                'Серьги' => '10',
                'Колье' => '36',
                'Пирсинг' => '38',
                'Пуссеты' => '43',
                'Пусеты' => '43',
                'Браслеты' => '28',
                'Запонки' => '41',
                'Броши' => '35',
                'Зажимы' => '42'
            ];

            $cat_fx_1 = [
                'Кольца' => 'Кольцо',
                'Кольцо' => 'Кольцо',
                'Пирсинг' => 'Пирсинг',
                'Пусеты' => 'Пусет',
                'Обручальные кольца' => 'Обручальное кольцо',
                'Подвеска' => 'Подвеска',
                'Крест' => 'Крест',
                'Серьги' => 'Серьги',
                'Колье' => 'Колье',
                'Пуссеты' => 'Пуссет',
                'Браслеты' => 'Браслет',
                'Запонки' => 'Запонка',
                'Броши' => 'Брошь',
                'Зажимы' => 'Зажим'
            ];

            $mett_fx_1 = [
                'Красное' => ' из красного золота',
                'Белое' => ' из белого золота',
                'Желтое' => ' из жёлтого золота'
            ];

            $mett_fx_2 = [
                'Красное' => 'krasnZoloto',
                'Белое' => 'belZoloto',
                'Желтое' => 'JoltZoloto'
            ];

            $title = $item['title'];
            if(isset( $cat_fx_1[$item['title']] ))
            {
                $title = $cat_fx_1[$item['title']];
            }
            if(isset( $mett_fx_2[$item['seo_desc']] ))
            {
                $title .= $mett_fx_1[$item['seo_desc']];
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

            $paramItem[0]['value'] = $item['seo_desc'] .' золото';
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

            foreach( $kamenList as $pk => $pv ){
                $str_text = mb_strtolower( $text );
                $str_find = '/' . mb_strtolower( $pv ) . '/iU';
                if ( preg_match($str_find, $str_text)) {
                    $filterData[1]['values'][] = $kamenListVals[$pk];
                    $kamen_list[] = $kamenList2[$pk];
                    $param_kamen_list[] = $kamenList[$pk];
                }
            }

            if( count( $kamen_list ) > 0 ){

                if( count( $kamen_list ) == 1 ){
                    $title = $title . ' с ' . $kamen_list[0];
                    $paramItem[2]['value'] = $param_kamen_list[0];
                }

                if( count( $kamen_list ) == 2 ){
                    $title = $title . ' с ' . $kamen_list[0] . ' и ' . $kamen_list[1] ;
                    $paramItem[2]['value'] = $param_kamen_list[0] . ', ' . $param_kamen_list[1];
                }

                if( count( $kamen_list ) > 2 ){
                    $paramItem[2]['value'] = implode( ',', $param_kamen_list );
                    $__i = count($kamen_list)-1;
                    $last = $kamen_list[$__i];
                    array_splice($kamen_list, -1);
                    $title = $title . ' с ' . implode( ',', $kamen_list ) . ' и ' . $last;
                }

            }

            // 'empty','no_empty'
            if( count( $filterData[1]['values'] ) > 0 ){
                $filterData[1]['values'][] = 'no_empty';
            }else{
                $filterData[1]['values'][] = 'empty';
            }

            if(isset( $mett_fx_2[$item['seo_desc']] ))
            {
                $filterData[0]['values'][] = $mett_fx_2[$item['seo_desc']];
            }

            $razmerList = ['2.0','12.0','13.0','13.5','14.0','14.5','15.0','15.5','16.0','16.5','17.0','17.5','18.0','18.5','19.0','19.5','20.0','20.5','21.0','21.5','22.0','22.5','23.0','23.5','24.0','24.5','25.0'];
            $razmerListVals = ['2_0','12_0','13_0','13_5','14_0','14_5','15_0','15_5','16_0','16_5','17_0','17_5','18_0','18_5','19_0','19_5','20_0','20_5','21_0','21_5','22_0','22_5','23_0','23_5','24_0','24_5','25_0'];


            /*

            В вес pfgbcfk размер */
            if( $item['size'] ){
                $sz = str_replace( ",", ".", $item['size'] );
                $paramItem[4]['value'] = $sz;
                foreach( $razmerList as $rk => $rv ){
                    if( $rv === $sz ){
                        $filterData[4]['values'][] = $razmerListVals[$rk];
                    }
                }
            }

            $filterData[3]['values'][] = 'woman';

            $r['product'] = [
                'title' => $title,
                'articul' => $item['articul'],
                'cat' => $cat_ids[$item['title']],
                'params' => json_encode( $paramItem ),
                'size' => $item['size'],
                'filters' => json_encode( $filterData ),
                'proba' => $item['proba'],
                'postavchik' => 'Kaborovsky',
                'parser' => 'Kaborovsky',
                'weight' => str_replace( ",", ".", $item['weight'] ),
                'qty_empty' => '1',
                'prices_empty' => '1',
                'qty' => $item['qty'],
                'view' => '1',
                'price_zac' => intval( $item['price'] * 100 ),
                'sex' => 'woman',
                'seria' => trim(mb_strtolower($item['cat'])),
                'current' => 'RUR',
                'moderate' => '2',
                'lastUpdate' => time(),
                'optionLabel' => json_encode([
                    'collections' => $item['seo_keys'],
                    'options' => $item['optionLabel'],
                    'seria' => $item['cat']
                ])
            ];

            $r['price'] = [
                'product_id' => 0,
                'price_id' => 1,
                'current_id' => 1,
                'price_item' => intval( $item['price'] * 100 )
            ];


            if( isset( $item['photo'] ) ){
                $r['photos'] = [
                    'product_id' => 0,
                    'photo_name' => $item['photo'],
                    'define' => '1'
                ];
            }

            return $r;

       }
    }

    // Извлечение драг камней
    public function getDragValues ( $pids = [] )
    {
        //$pids = [13456, 13699, 13457];
        $r = [];
        if( count( $pids ) > 0 )
        {
            $this->db->where_in( 'id', $pids );
            $r = $this->db->get( 'products' )->result_array();
        }

        if( count( $r ) > 0 )
        {
            $upd = [];

            foreach( $r as $key_prod => $value_prod )
            {

                $_1 = json_decode( $value_prod['optionLabel'], true );
                $_2 = explode( '^', $_1['options']);
                foreach( $_2 as $_2k => $_2v )
                {
                    $_2[$_2k] = trim( $_2v );
                }
                $_2 = array_diff( $_2, [''] );

                foreach( $_2 as $_2k => $_2v )
                {
                    $__a = explode( ' ', $_2v );
                    $_2[$_2k] = array_diff( $__a, [''] );
                }

                $kl = ['сапфир','изумруд','бриллиант','рубин'];
                $kl_index = [2,2,1,2];
                $_for = [
                    'Бриллиант' => [    'Кол-во камней', 'Камень',      'Форма огранки',    'Кол-во граней',    '-', 'Вес, Ct.'],
                    'Сапфир' => [       'Кол-во камней', 'Вес, Ct.',    'Камень',           '-',                '-', '-'],
                    'Рубин' => [        'Кол-во камней', 'Вес, Ct.',    'Камень',           '-',                '-', '-'],
                    'Изумруд' => [      'Кол-во камней', 'Вес, Ct.',    'Камень',           '-',                '-', '-']
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

                $upd[] = [
                    'id' => $value_prod['id'],
                    'drag' => json_encode( $_3 )
                ];

            }

            if( count( $upd ) > 0 )
            {
                $this->db->update_batch( 'products', $upd, 'id' );
            }
        }

    }


    public function path_1()
    {

        $this->db->like( 'title', 'Брош ' );
        $r = $this->db->get( 'products')->result_array();

        echo count( $r );

        foreach ($r as $key => $value)
        {
            $title = str_replace("Брош ", "Брошь", $value['title']);
            $this->db->where('id', $value['id']);
            $this->db->update('products', [
                'title' => $title
            ]);
        }

    }


}

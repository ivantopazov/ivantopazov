<?php


defined('BASEPATH') OR exit('No direct script access allowed');

class Karatov extends CI_Controller {
        
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
        
    }
    
    
    // Парсинг сайтмапы каратов
    public function parseSitemap(){
         
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://karatov.com/sitemap1.xml');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec ($ch);
        curl_close ($ch);
        
        $r = [];
        $xml = new SimpleXMLElement($data);        
        foreach ($xml->url as $url_list) {
            $url = $url_list->loc;
            $math = '\/catalog\/item';
            $str_text = $url;
            $str_find = '/' . $math  . '/iU';
            if ( preg_match( $str_find, $str_text ) ) {
                $r[] = (string)$url;
            }
        }
        
        echo "<pre>";
        print_r( $r );
        echo "</pre>";
        
    }
    
    
    function str_split_unicode($str, $l = 0) {
        if ($l > 0) {
            $ret = array();
            $len = mb_strlen($str, "UTF-8");
            for ($i = 0; $i < $len; $i += $l) {
                $ret[] = mb_substr($str, $i, $l, "UTF-8");
            }
            return $ret;
        }
        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    }
    
    
    
    public function renderWebKaratov (){
         
        $get_articul = '7263318';
        $link = 'https://karatov.com/catalog/item/'.$get_articul.'/';
        
        $item = $this->parseProductKaratov( $link );
        
        
        $LIST = [];
        if( count( $item['sized'] ) > 0 ){
            foreach( $item['sized'] as $sv ){
                $newItem = $item;
                $newItem['size'] = $sv['t'];
                $newItem['filters'][4]['values'][] = $sv['v'];
                
                $newItem['sex'] = 'woman';
                $newItem['postavchik'] = 'karatov';
                $newItem['parser'] = 'karatov';
                $newItem['current'] = 'RUR';
                $newItem['view'] = '1';
                $newItem['qty'] = '1';
                $newItem['qty_empty'] = '1';
                $newItem['prices_empty'] = '1';
                
                $LIST[] = $newItem;
            }
        }else{
            $newItem = $item;
            //$newItem['filters'][4]['values'][] = $sv;
            
            $newItem['sex'] = 'woman';
            $newItem['postavchik'] = 'karatov';
            $newItem['parser'] = 'karatov';
            $newItem['current'] = 'RUR';
            $newItem['view'] = '1';
            $newItem['qty'] = '1';
            $newItem['qty_empty'] = '1';
            $newItem['prices_empty'] = '1';
            
            $LIST[] = $newItem;
        }

        

        echo "<pre>";
        print_r( $LIST );
        echo "</pre>";
        
       /*
        foreach( $item['sized'] as $sv ){
            foreach( $razmerList as $pk => $pv ){
                $str_text = str_replace( ",", ".", trim( $sv ));
                if ( $pv === $str_text ) {
                    $filterData[4]['values'][] = $razmerListVals[$pk];
                }
            }
        }
        
        */
        
        
    }
    
    
    
    
    // Парсер товарной позиции в каратове
    public function parseProductKaratov( $link = false ){
        
        $item = [];
        
        if( $link !== false ){
            
            //$link = 'https://karatov.com/catalog/item/3258761/';
            //$link = 'https://karatov.com/catalog/item/'.$get_articul.'/';
            
            $this->load->library('simple_html_dom');
            $html = file_get_html( $link  );
               
            if( $html ) {
                
                if( !$html->find('.notetext', 0) ){
                    
                    $cat_ids = [
                        'Кольцо' => '1',
                        'Подвеска' => '19',
                        'Цепочка' => '19',
                        'Крест' => '37',
                        'Цепь' => '40',
                        'Цепочка' => '40',
                        'Серьги' => '10',
                        'Серьга' => '10',
                        'Колье' => '36',
                        'Пуссеты' => '43',
                        'Браслет' => '28',
                        'Браслеты' => '28',
                        'Запонки' => '41',
                        'Брошь' => '35',
                        'Пирсинг' => '38',
                        'Часы' => '39'
                    ];
                    
                    $art = $this->str_split_unicode( $html->find('h1', 0)->plaintext );
                    
                    $articul = []; 
                    $st = count($art);
                    $en = count($art);
                    foreach( $art as $ak => $av ){
                        if( $av === '(' ){
                            $st = $ak;
                        }
                        if( $av === ')' ){
                            $en = $ak;
                        }
                        if( $ak > $st + 5 && $ak < $en ){
                            $articul[] = $av;
                        }
                    }
                    $item['articul'] = ( count( $articul ) > 0 ) ? implode ( $articul ) : '';
                    
                    $_getH1 = $html->find('h1', 0)->plaintext;
                    
                    $item['h1'] = str_replace( "( арт. " . $item['articul'] . ")", "", $_getH1 );
                    $item['h1'] = str_replace( "(арт. " . $item['articul'] . ")", "", $_getH1 );
                    
                    foreach( $cat_ids as $cik => $civ ){
                        $_cat_str_text = mb_strtolower( $item['h1'] );
                        $_cat_str_find = '/' . mb_strtolower( $cik ) . '/iU';
                        if ( preg_match( $_cat_str_find, $_cat_str_text )) {
                            $item['cat'] = $cat_ids[$cik];
                        }
                    }
                                        
                    $item['photos'] = [];
                    $item['sized'] = [];
                    $item['price'] = 0;
                    $item['desc'] = '';
                    $item['proba'] = '';
                    
                    $item['weight'] = 0;
                    $item['params'] = [];
                    $item['filters'] = [];
                    
                    foreach( $html->find('.carousel_inner img') as $element ){   
                        $item['photos'][] = 'https://karatov.com' . $element->src . '<br />';
                    }    
                    
                    foreach( $html->find('.select-size_item') as $element ){
                        $num = str_replace( ",", ".", trim( $element->plaintext ));
                        if( is_numeric( $num ) || is_float( $num ) ){
                            $item['sized'][] = $num;
                        }
                    }  
                    
                    $blockPrice = $html->find('.product-page_price', 0 );
                    $item['price'] = str_replace(array('Р', ' '), '', $blockPrice->find('.price', 0)->plaintext);
                    
                    $block = $html->find('#productFeatures', 0 );
                    
                    $item['desc'] = trim( $block->find('div.text', 0)->plaintext );
                    
                    foreach( $block->find('div.list_item') as $element ){   
                        $item['params'][] = [
                            'item' => $element->find('.title', 0 )->plaintext,
                            'value' => $element->find('.feature', 0 )->plaintext
                        ];
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
                        'value' => '-'
                    ],[
                        'variabled' => 'technologiya',
                        'value' => '-'
                    ]];
                    
                    /// Установки фильтрации
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
                    
                    $razmerList = [
                        '2.0','12','13','13.5','14','14.5','15',
                        '15.5','16','16.5','17','17.5','18','18.5','19',
                        '19.5','20','20.5','21','21.5','22','22.5','23','23.5','24','24.5','25'];
                    $razmerListVals = [
                        '2_0','12_0','13_0','13_5','14_0','14_5','15_0',
                        '15_5','16_0','16_5','17_0','17_5','18_0','18_5','19_0',
                        '19_5','20_0','20_5','21_0','21_5','22_0','22_5','23_0','23_5','24_0','24_5','25_0'];
                    
                    $metallList = ['Комбинированное золото','Золото (Красное)','Золото (Белое)','Серебро'];
                    $metallListVals = ['kombinZoloto','krasnZoloto','belZoloto','serebro'];
                    
                    $formaList = ['Кабошон','Круг','Овал','Груша','Маркиз', 'Багет', 'Квадрат', 'Октагон', 'Триллион', 'Сердце', 'Кушон', 'Пятигранник', 'Шестигранник', 'Восьмигранник'];
                    $formaListVals = ['Kaboshon','Krug','Oval','Grusha','Markiz', 'Baget', 'Kvadrat', 'Oktagon', 'Trillion', 'Serdtce', 'Kushon', 'Piatigranniq', 'Shestigranniq', 'Vosmigrannic'];
                    
                    $dlaKogo = ['Для женщин','Для мужчин', 'Для женщин, Для мужчин'];
                    $dlaKogoVals = ['woman','men', 'unisex'];
                    
                    
                    foreach( $item['params'] as $element ){
                        
                        $_item = $element['item'];
                        $_value = $element['value'];
                        
                        if( $_item == 'Приблизительный вес' ){
                            $paramItem[4]['value'] = trim( $_value );
                            $item['weight'] = str_replace( array('г', ' '), '', $_value );
                        }
                        
                        if( $_item == 'Проба' ){
                            $item['proba'] = $_value;
                        }
                        
                        if( $_item == 'Вид обработки' ){
                            $paramItem[6]['value'] = trim( $_value );
                        }
                        if( $_item == 'Металл' ){
                            $paramItem[0]['value'] = trim( $_value );
                            $paramItem[1]['value'] = trim( $_value );
                            foreach( $metallList as $pk => $pv ){
                                if ( trim( $pv ) === trim( $_value ) ) {
                                    $filterData[0]['values'][] = $metallListVals[$pk];
                                    ///$paramsValues[]
                                }
                            }
                        }
                        
                        if( $_item == 'Вставка' ){
                            $paramItem[2]['value'] = trim( $_value );
                            foreach( $kamenList as $pk => $pv ){
                                $str_text = mb_strtolower( $_value );
                                $str_find = '/' . mb_strtolower( $pv ) . '/iU';
                                if ( preg_match($str_find, $str_text)) {
                                    $filterData[1]['values'][] = $kamenListVals[$pk];
                                }
                            }
                        }
                        
                        if( $_item == 'Форма основной вставки' ){
                            $paramItem[3]['value'] = trim( $_value );
                            foreach( $formaList as $pk => $pv ){
                                $str_text = mb_strtolower( $_value );
                                $str_find = '/' . mb_strtolower( $pv ) . '/iU';
                                if ( preg_match($str_find, $str_text)) {
                                    $filterData[2]['values'][] = $formaListVals[$pk];
                                }
                            }
                        }
                        
                    } 
                    
                    if( count($filterData[1]['values']) > 0 ){
                        $filterData[1]['values'][] = 'no_empty';
                    }
                    
                    if( count($filterData[1]['values']) < 1 ){
                        $filterData[1]['values'][] = 'empty';
                    }
                    
                    foreach( $item['sized'] as $ks => $sv ){
                        foreach( $razmerList as $pk => $pv ){
                            if ( $pv === $sv ) {
                                $item['sized'][$ks] = [
                                    't' => $razmerList[$pk],
                                    'v' => $razmerListVals[$pk]
                                ];
                            }
                        }
                    }
                    
                    $item['params'] = $paramItem;
                    $item['filters'] = $filterData;
                    
                }
            }
        }
        
        return $item;
        
    }
    
    
    
    
    
    
    
    
}
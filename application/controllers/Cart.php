<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cart extends CI_Controller
{

	protected $user_info = array();
	protected $store_info = array();

	protected $post = array();
	protected $get = array();

	public function __construct()
	{

		parent::__construct();
		$this->user_info = ($this->mdl_users->user_data()) ? $this->mdl_users->user_data() : false;
		$this->store_info = $this->mdl_stores->allConfigs();

		$this->post = $this->security->xss_clean($_POST);
		$this->get = $this->security->xss_clean($_GET);

	}

	public function index()
	{

		$start = microtime(true);

		$title = (!empty($this->store_info['seo_title'])) ? $this->store_info['seo_title'] : $this->store_info['header'];
		$page_var = 'cart';

		$this->mdl_tpl->view('templates/doctype_home.html', array(

			'title' => $title,
			'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'),
			'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),

			'seo' => $this->mdl_tpl->view('snipets/seo_tools.html', array(
				'mk' => (!empty($this->store_info['seo_keys'])) ? $this->store_info['seo_keys'] : '',
				'md' => (!empty($this->store_info['seo_desc'])) ? $this->store_info['seo_desc'] : '',
			), true),

			'navTop' => $this->mdl_tpl->view('snipets/navTop.html', array(
				'store' => $this->store_info,
				'active' => 'cart',
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			), true),

			'header' => $this->mdl_tpl->view('snipets/header.html', array(
				'store' => $this->store_info,
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			), true),

			'navMenu' => $this->mdl_tpl->view('snipets/navMenu.html', array(
				'store' => $this->store_info,
				'active' => 'home',
				'itemsTree' => $this->mdl_category->getTreeMenu(),
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			), true),

			'content' => $this->mdl_tpl->view('pages/cart/basic.html', array(
				'utmLabels' => $this->mdl_tpl->view('snipets/utmLabels.html', $this->mdl_seo->utmLabels($this->get), true),
			), true),

			'footer' => $this->mdl_tpl->view('snipets/footer.html', array(
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			), true),

			'load' => $this->mdl_tpl->view('snipets/load.html', array(
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'utmLabels' => $this->mdl_tpl->view('snipets/utmLabels.html', $this->mdl_seo->utmLabels($this->get), true),
			), true),

			'resorses' => $this->mdl_tpl->view('resorses/home/head.html', array(
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path'),
			), true),

		), false);

		//echo '<p style="background: yellow none repeat scroll 0% 0%; margin: 20px 0px 0px; position: fixed; bottom: 0px;">Время выполнения скрипта: '.(microtime(true) - $start).' сек.</p>';

	}

	// Страница успешной отправки заказа
	public function thanks()
	{

		$start = microtime(true);

		$title = (!empty($this->store_info['seo_title'])) ? $this->store_info['seo_title'] : $this->store_info['header'];
		$page_var = 'cart';

		$this->mdl_tpl->view('templates/doctype_home.html', array(

			'title' => $title,
			'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
			'config_styles_path' => $this->mdl_stores->getСonfigFile('config_styles_path'),
			'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),

			'seo' => $this->mdl_tpl->view('snipets/seo_tools.html', array(
				'mk' => (!empty($this->store_info['seo_keys'])) ? $this->store_info['seo_keys'] : '',
				'md' => (!empty($this->store_info['seo_desc'])) ? $this->store_info['seo_desc'] : '',
			), true),

			'navTop' => $this->mdl_tpl->view('snipets/navTop.html', array(
				'store' => $this->store_info,
				'active' => 'cart',
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			), true),

			'header' => $this->mdl_tpl->view('snipets/header.html', array(
				'store' => $this->store_info,
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			), true),

			'navMenu' => $this->mdl_tpl->view('snipets/navMenu.html', array(
				'store' => $this->store_info,
				'active' => 'home',
				'itemsTree' => $this->mdl_category->getTreeMenu(),
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			), true),

			'content' => $this->mdl_tpl->view('pages/cart/thanks.html', array(), true),

			'footer' => $this->mdl_tpl->view('snipets/footer.html', array(
				'config_images_path' => $this->mdl_stores->getСonfigFile('config_images_path'),
			), true),

			'load' => $this->mdl_tpl->view('snipets/load.html', array(
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'utmLabels' => $this->mdl_tpl->view('snipets/utmLabels.html', $this->mdl_seo->utmLabels($this->get), true),
			), true),

			'resorses' => $this->mdl_tpl->view('resorses/home/head.html', array(
				'addons_folder' => $this->mdl_stores->getСonfigFile('addons_folder'),
				'config_scripts_path' => $this->mdl_stores->getСonfigFile('config_scripts_path'),
			), true),

		), false);

		//echo '<p style="background: yellow none repeat scroll 0% 0%; margin: 20px 0px 0px; position: fixed; bottom: 0px;">Время выполнения скрипта: '.(microtime(true) - $start).' сек.</p>';

	}

	// Отправка заказа
	public function order()
	{

		if (!isset($this->post)) exit();
		if (!isset($this->post['info'])) exit();
		if (!isset($this->post['cart'])) exit();
		if (!isset($this->post['type'])) exit();

		$promocode = isset($this->post['promocode']) ? $this->post['promocode'] : [];

		$user = array();
		foreach ($this->post['info'] as $v) {
			if ($v['name'] === 'fio') {
				$user['fio'] = $v['value'];
			}
			if ($v['name'] === 'phone') {
				$user['phone'] = $v['value'];
			}            
            if( $v['name'] === 'email' ){
                $user['email'] = $v['value'];
            }            
			if ($v['name'] === 'address') {
				$user['address'] = $v['value'];
			}
		}

		$name = $user['fio'];
		$phone = $user['phone'];
		$email = $user['email'];
		$address = $user['address'];

		$tovary = array();
		$summa = 0;

		$IDsTov = [];
		foreach ($this->post['cart'] as $cv) {
			$price = ($cv['price'] / 100);
			$summaItem = ($price * $cv['qty']);
			$summa += $summaItem;
			$tovary[] = array(
				'id' => $cv['id'],
				'title' => $cv['title'],
				'qty' => $cv['qty'],
				'articul' => $cv['orig']['articul'],
				'image' => $cv['orig']['image'],
				'link' => $cv['orig']['link'],
				'price' => $price,
				'summa' => $summaItem,
			);

			if (!in_array($cv['id'], $IDsTov)) {
				$IDsTov[] = $cv['id'];
			}
		}

		$summaWithPromocode = $summa;
		if (!empty($promocode)) {
			$summaWithPromocode = (int)$promocode['percent'] ? round($summa * (100 - (int)$promocode['percent']) / 100) :
				$summa - $promocode['amount'];
		}

		if (count($IDsTov) > 0) {

			$tov = $this->mdl_product->queryData([
				'return_type' => 'ARR2',
				'in' => [
					'method' => 'AND',
					'set' => [[
						'item' => 'id',
						'values' => $IDsTov,
					]],
				],
				'labels' => ['id', 'articul', 'title', 'size', 'shoop', 'postavchik'],
			]);

			if (count($tov) > 0) {

				foreach ($tovary as $ktv => $ctv) {
					foreach ($tov as $tv) {
						if ($tv['id'] == $ctv['id']) {
							$tovary[$ktv]['data'] = $tv;
						}
					}
				}

			}

		}

		$user = $this->mdl_users->queryData([
			'return_type' => 'ARR1',
			'where' => [
				'method' => 'AND',
				'set' => [[
					'item' => 'phone',
					'value' => $phone,
				]],
			],
		]);

		if (!$user) {
			$user = array(
				'fio' => $name,
				'phone' => $phone,
			);
			$this->db->insert('users', array(
				'fio' => $name,
				'phone' => $phone,
			));
			$user['id'] = $this->db->insert_id();
		}

		$traker = time();

		$cart = array(
			'user_id' => $user['id'],
			'promocode_id' => !empty($promocode) ? (int)$promocode['id'] : 0,
			'traker' => $traker,
			'products_list' => json_encode($tovary),
			'summa' => $summa,
			'summa_with_promocode' => $summaWithPromocode,
			'adress' => $address,
			'time' => time(),
			'date' => date("Y-m-d H:i:s"),
			'status' => 0,
		);
		$this->db->insert('orders', $cart);

		/*
			$t = urlencode( 'Вы можете отслеживать состояние заказа используя код отслеживания: ' . $traker );
			file_get_contents('http://sms.ru/sms/send?api_id=9EB00FFE-FAA5-7459-C608-CEC685B49F6C&to='. $phone .'&text=' . $t );
			*/
		$promocodeValue = !empty($promocode) ? ((int)$promocode['percent'] ? ($promocode['percent'] . '%') :
			($promocode['amount'] . +' р.')) : '';

		$html_content = $this->mdl_tpl->view('email/cart/newOrder.html', array(
			'domen' => 'IVAN TOPAZOV',
			'http' => 'http://' . $_SERVER['HTTP_HOST'],
			'tovary' => $tovary,
			'promocode' => !empty($promocode) ? "{$promocode['code']} (-{$promocodeValue})" : '',
			'summa' => $summa,
			'summaWithPromocode' => $summaWithPromocode,
			'type' => $this->post['type'],
			'name' => $name,
			'phone' => $phone,
			'traker' => $traker,
			'email' => $email,
			'ulmLabels' => $this->mdl_tpl->view('email/ulmLabels/labelItems.html', $this->mdl_seo->getUtmData(), true),
			'adress' => $address,
			'date' => date('d.m.Y в H.i'),
		), true);
		

		$html_content_user = $this->mdl_tpl->view('email/cart/newOrderUser.html', array(
			'domen' => 'IVAN TOPAZOV',
			'http' => 'http://' . $_SERVER['HTTP_HOST'],
			'tovary' => $tovary,
			'promocode' => !empty($promocode) ? "{$promocode['code']} (-{$promocodeValue})" : '',
			'summa' => $summa,
			'summaWithPromocode' => $summaWithPromocode,
			'type' => $this->post['type'],
			'name' => $name,
			'phone' => $phone,
			'traker' => $traker,
			'email' => $email,
			'ulmLabels' => $this->mdl_tpl->view('email/ulmLabels/labelItems.html', $this->mdl_seo->getUtmData(), true),
			'adress' => $address,
			'date' => date('d.m.Y в H.i'),
		), true);

		/********** Telegram Bot **********/

		// Шаблон сообщения Телеграм
		// $telegram_messages = $this->mdl_tpl->view( 'email/cart/newOrdertelegram.html', array(
		//     'domen' => 'IVAN TOPAZOV',
		//     'http' => 'http://' . $_SERVER['HTTP_HOST'],
		//     'tovary' => $tovary,
		//     'summa' => $summa,
		//     'type' => $this->post['type'],
		//     'name' => $name,
		//     'phone' => $phone,
		//     'traker' => $traker,
		//     'adress' => $adress,
		//     'date' => date('d.m.Y в H.i')
		// ), true );
		//
		// // Токен бота
		// $token = '388785151:AAEAMTIS6CuxbTyS9fbNte6V8yBKGf_9igI';
		//
		// // *** Я
		// $chatId = 183389531;
		// $parameters = array(
		// 	'chat_id' => $chatId,
		// 	'text' => $telegram_messages
		// );
		// $url = 'https://api.telegram.org/bot'.$token.'/sendMessage?'.http_build_query($parameters);
		// file_get_contents($url);
		//
		// // *** Сергей
		// $chatIdsq = 289668518;
		// $parameterssq = array(
		// 	'chat_id' => $chatIdsq,
		// 	'text' => $telegram_messages
		// );
		// $urlsq = 'https://api.telegram.org/bot'.$token.'/sendMessage?'.http_build_query($parameterssq);
		// file_get_contents($urlsq);
		//
		//
		// // *** Леха
		// $chatIds = 367450584;
		// $parameterss = array(
		// 	'chat_id' => $chatIds,
		// 	'text' => $telegram_messages
		// );
		// $urls = 'https://api.telegram.org/bot'.$token.'/sendMessage?'.http_build_query($parameterss);
		// file_get_contents($urls);
		//
		// // *** Елена
		// $chatIdss = 234773196;
		// $parametersss = array(
		// 	'chat_id' => $chatIdss,
		// 	'text' => $telegram_messages
		// );
		//
		// $urlss = 'https://api.telegram.org/bot'.$token.'/sendMessage?'.http_build_query($parametersss);
		// file_get_contents($urlss);
		//
		// // *** Николай
		// $chatIdssw = 220809540;
		// $parametersssr = array(
		// 	'chat_id' => $chatIdssw,
		// 	'text' => $telegram_messages
		// );
		//
		// $urlsst = 'https://api.telegram.org/bot'.$token.'/sendMessage?'.http_build_query($parametersssr);
		// file_get_contents($urlsst);
		/********** Telegram Bot **********/


		$this->load->model('mdl_mail');
		$this->mdl_mail->set_ot_kogo_from('sale@ivantopazov.ru', 'IVAN TOPAZOV');
		$this->mdl_mail->set_komu_to($email, $name);
		$this->mdl_mail->set_tema_subject('Содержание Вашего заказа');
		$this->mdl_mail->set_tema_message($html_content_user);
		$this->mdl_mail->send();


		$this->mdl_mail->set_ot_kogo_from('sale@ivantopazov.ru', 'IVAN TOPAZOV');
		$this->mdl_mail->set_tema_subject('Заказ на сумму ' . $summa . ' рублей - ' . date('d.m.Y H:i:s'));
		$this->mdl_mail->set_tema_message($html_content);
		$this->mdl_mail->set_komu_to('sale@ivantopazov.ru', 'Покупатель');
		$this->mdl_mail->send();

		echo json_encode(array('err' => 0));
		exit();
	}

	/**
	 * Обновление цен заказа
	 */
	public function refresh()
	{
		$cart = isset($this->post['cart']) ? $this->post['cart'] : [];
		if (empty($cart) || !array($cart)) {
			echo json_encode(['success' => false]);
			exit();
		}
		$ids = array_map(function ($cartItem) {
			return $cartItem['id'];
		}, $cart);

		$cart = array_combine($ids, $cart);

		$promocodeCode = isset($this->post['promocodeCode']) ? $this->post['promocodeCode'] : '';

		$products = $this->mdl_product->queryData([
			'return_type' => 'ARR2',
			'in' => [
				'method' => 'AND',
				'set' => [[
					'item' => 'id',
					'values' => $ids,
				]],
			],
			'labels' => ['id', 'qty', 'price_real'],
		]);

		$prices = [];

		$cartTotal = 0;
		if (count($products) > 0) {

			foreach ($ids as $id) {
				$prices[$id] = 0;
				foreach ($products as $product) {
					if ($product['id'] == $id) {
						if ((int)$product['qty']) {
							$prices[$id] = $product['price_real'];
							$cartItem = $cart[$id];
							$cartTotal += (int)$cartItem['qty'] * $product['price_real'] / 100;
						}
						break;
					}
				}
			}
		}

		$promocode = false;
		if ($cartTotal) {
			$today = strtotime('today');
			$promocode = $this->mdl_promocode->queryData([
				'return_type' => 'ARR1',
				'where' => [
					'method' => 'AND',
					'set' => [[
						'item' => 'code',
						'value' => $promocodeCode,
					], [
						'item' => "(date_start <= $today)",
					], [
						'item' => "(date_end >= $today)",
					], [
						'item' => "min_order <= $cartTotal",
					],],
				],
				'labels' => ['id', 'code', 'amount', 'percent', 'min_order', 'date_start', 'date_end'],
			]);
		}
		echo json_encode(['success' => true, 'prices' => $prices, 'promocode' => $promocode]);
		exit();
	}

	/**
	 * Применение промокода
	 */
	public function use_promocode()
	{
		$code = isset($this->post['code']) ? trim($this->post['code']) : '';
		if (!$code) {
			echo json_encode(['success' => false, 'error' => 'Промокод не указан.'], JSON_UNESCAPED_UNICODE);
			exit();
		}
		$cartTotal = isset($this->post['cartTotal']) ? (int)$this->post['cartTotal'] : 0;
		if (!$cartTotal) {
			echo json_encode(['success' => false, 'error' => 'Корзина пуста.'], JSON_UNESCAPED_UNICODE);
			exit();
		}
		$today = strtotime('today');
		$promocode = $this->mdl_promocode->queryData([
			'return_type' => 'ARR1',
			'where' => [
				'method' => 'AND',
				'set' => [[
					'item' => 'code',
					'value' => $code,
				]],
			],
			'labels' => ['id', 'code', 'amount', 'percent', 'min_order', 'date_start', 'date_end'],
		]);

		$error = false;
		if (empty($promocode)) {
			$error = 'Промокод не найден.';
		} elseif ($promocode['date_start'] && $promocode['date_start'] > $today) {
			$error = 'Промокод еще неактивен.';
		} elseif ($promocode['date_end'] && $promocode['date_end'] < $today) {
			$error = 'Промокод уже неактивен.';
		} elseif ($promocode['min_order'] > $cartTotal) {
			$error = "Минимальная сумма заказа для применения промокода {$promocode['min_order']} р.";
		}

		if ($error) {
			echo json_encode(['success' => false, 'error' => $error], JSON_UNESCAPED_UNICODE);
			exit();
		}

		echo json_encode(['success' => true, 'promocode' => $promocode], JSON_UNESCAPED_UNICODE);
		exit();
	}

	// public function test()
	// {
	//     // Токен бота
	// 	$token = '388785151:AAEAMTIS6CuxbTyS9fbNte6V8yBKGf_9igI';
	//     $telegram_messages = '---';
	//     // $urlss = 'https://api.telegram.org/bot'.$token.'/sendMessage?'.http_build_query($parametersss);
	// 	// file_get_contents($urlss);
	//     //
	// 	// // *** Николай
	// 	$chatIdssw = 220809540;
	// 	$parametersssr = array(
	// 		'chat_id' => $chatIdssw,
	// 		'text' => $telegram_messages
	// 	);
	//     //
	// 	$urlsst = 'https://api.telegram.org/bot'.$token.'/sendMessage?'.http_build_query($parametersssr);
	// 	echo file_get_contents($urlsst);
	//
	//
	//
	//
	// }

}

<?php

require_once APPPATH . 'controllers/admin/parser/BaseParser.php';

defined('BASEPATH') OR exit('No direct script access allowed');

class Master extends BaseParser
{

	protected $parserCode = 'master';
	protected $parserTitle = 'Выгрузка Мастер Бриллиант';

	protected $uploadPath = './uploads/products/master/';
	protected $fileName = 'master.csv';

	protected $postavchik = 'master-brilliant';


	protected function getDataFromFile($currentLine)
	{
		$file = file("{$this->uploadPath}{$this->fileName}");
		$data = [];

		$file[$currentLine] = mb_convert_encoding($file[$currentLine], "utf8", "cp1251");
		$val = str_getcsv($file[$currentLine], ';');

		$data["article"] = trim($val[0]);
		$data["seria"] = trim($val[1]);
		$data["size"] = str_replace('.0', '', str_replace(',', '.', trim($val[2])));
		$data["metal"] = trim($val[3]);
		$data["metal_color"] = trim($val[4]);
		$data["proba"] = trim($val[5]);
		$data["type"] = trim($val[6]);
		$data["vstavki"] = trim($val[7]);
		$data["weight"] = str_replace(",", ".", trim($val[8]));
		$data["price"] = trim($val[9]);
		$data["qty"] = 1;
//		$data["country"] = trim($val[10]);
		$data["garniture"] = trim($val[11]);
		$data["collection"] = trim($val[12]);

		return $data;
	}

	protected function drag($vstavki)
	{
//		 все камни из базы
//
//		"Авантюрин синт"
//		"Агат"
//		"Аквамарин"
//		"Аквамарин синтетический"
//		"Александрит синтетический"
//		"Аметист"
//		"Аметист синтетический"
//		"Аметрин синт"
//		"Аметрин синтетический"
//		"Апатит"
//		"Бирюза н."
//		"Бриллиант"
//		"Бриллиант цв."
//		"Бриллиант черный"
//		"Голубой топаз С2"
//		"Горный хрусталь"
//		"Горный хрусталь concave"
//		"Гранат"
//		"Гранат ситал"
//		"Жемчуг"
//		"Жемчуг морской белый"
//		"Жемчуг морской золотой"
//		"Жемчуг морской черный"
//		"Жемчуг пресн. золотой"
//		"Жемчуг пресн. красный"
//		"Жемчуг пресн. лаванда"
//		"Жемчуг пресн. мятный"
//		"Жемчуг пресн. розовый"
//		"Жемчуг пресн. черный"
//		"Жемчуг пресноводный"
//		"Жемчуг просверленный"
//		"Изумруд г/т"
//		"Изумруд н."
//		"Изумруд ситал"
//		"Иолит"
//		"Кварц"
//		"Кианит"
//		"Коралл нат."
//		"Корунд синт рубиновый"
//		"Корунд синт сапфировый"
//		"Корунд синт сапфировый бц"
//		"Лента текстильная"
//		"Лунный камень"
//		"Морганит"
//		"Морганит синтетический"
//		"Нанокерамика"
//		"Оникс"
//		"Празиолит"
//		"Раух-топаз"
//		"Родолит"
//		"Родолит синтетический"
//		"Рубин н."
//		"Рубин облагороженный"
//		"Сапфир н."
//		"Сапфир н. зеленый"
//		"Сапфир н. розовый"
//		"Сапфир н.желтый"
//		"Сапфир облагороженный"
//		"Сапфир синтетический"
//		"Сердолик"
//		"Спессартин"
//		"Султанит синтетический"
//		"Танзанит"
//		"Топаз"
//		"Топаз London"
//		"Топаз London синт"
//		"Топаз London ситал"
//		"Топаз swiss"
//		"Топаз б/ц"
//		"Топаз свис"
//		"Турмалин"
//		"Турмалин мультиколор"
//		"Турмалин синтетический"
//		"Фианит"
//		"Фианит SWAROVSKI"
//		"Фианит цветной"
//		"Халцедон"
//		"Хризолит"
//		"Хризопраз"
//		"Хромдиопсид"
//		"Циркон н."
//		"Цитрин"
//		"Цитрин синтетический"
//		"Шпинель"
//		"Шпинель  синтетическая"
//		"Эмаль"
//		"Яшма"

		$drag = [];

		$kamen = explode("#", $vstavki);
		foreach ($kamen as $key => $value) {
			$val = explode(";", $value);
			$name = $this->getFullStoneName($val[1]);
			$drag[$key] = [
				'kamen' => $name,
				'kamenCode' => $this->getStoneCode($name),
				'data' => [
					["name" => "Кол-во камней", "value" => $val[0]],
					["name" => "Камень", "value" => $name],
					["name" => "Форма огранки", "value" => $val[2]],
					["name" => "Вес, Ct.", "value" => $val[3]],
				],
			];
		}

		return $drag;
	}

}

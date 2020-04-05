<?php

require_once APPPATH . 'controllers/admin/parser/BaseParser.php';

defined('BASEPATH') OR exit('No direct script access allowed');

class Own extends BaseParser
{

	protected $parserCode = 'own';
	protected $parserTitle = 'Выгрузка собственных товаров';

	protected $uploadPath = './uploads/products/own/';
	protected $fileName = 'own.csv';

	// потом видимо добавятся и другие поставщики, нужно будет брать их из csv
	protected $postavchik = 'sokolov';

	protected $multipleImages = true;
	protected $imageFileNameTemplate = '{article}-{index}.jpg';

	protected function getDataFromFile($currentLine)
	{
		$file = file("{$this->uploadPath}{$this->fileName}");
		$data = [];

		// выгрузка в utf8
//		$file[$currentLine] = mb_convert_encoding($file[$currentLine], "utf8", "cp1251");
		$val = str_getcsv($file[$currentLine], ';');

		$data["type"] = trim($val[0]);
		$data["article"] = trim($val[1]);
		$data["weight"] = str_replace(",", ".", trim($val[2]));
		$data["seria"] = trim($val[3]);
		$data["size"] = str_replace('.0', '', str_replace(',', '.', trim($val[4])));
		$data["metal"] = 'Золото';
		$data["metal_color"] = trim($val[5]);
		$data["vstavki"] = trim($val[6]);
		$data["garniture"] = trim($val[7]);
		$data["proba"] = trim($val[8]);
		$data["gender"] = trim($val[9]) ?: 'Унисекс';
		$data["price"] = trim($val[10]);
		$data["price_roz"] = trim($val[11]);
		$data["qty"] = 1;

		if ($data["metal_color"] == 'Белое золото') {
			$data["metal_color"] = 'Белый';
//		} elseif ($data["metal_color"] == 'Желтое золото') {
//			$data["metal_color"] = 'Желтый';
		} else {
			$data["metal_color"] = 'Красный';
		}

		return $data;
	}

	protected function drag($vstavka)
	{
		//все камни из выгрузки
		/*
		Авантюрин 
		Агат 
		Аметист 
		Бирюза синтетическая 
		Бриллиант 
		Гранат 
		Жемчуг 
		Изумруд 
		Кварц 
		Керамика 
		Коралл прессованный 
		Корунд 
		Наношпинель 
		Раухтопаз 
		Родолит 
		Рубин 
		Сапфир 
		Ситалл 
		Стекло минеральное 
		Стекло сапфировое 
		Танзанит 
		Топаз 
		Топаз Swarovski 
		Фианит 
		Хризолит 
		Цитрин 
		Эмаль 
		Янтарь прессованный 
		Swarovski Zirconia 
		*/

		//все параметры камней из выгрузки
		/*
		Наименование
		Тип вставки
		Количество
		Форма вставки
		Цвет
		Чистота
		Огранка
		Вес
		*/

		$vstavka = trim($vstavka);
		$drag = [];

		if (!$vstavka) {
			return $drag;
		}

		$stones = explode('#', $vstavka);
		foreach ($stones as $stone) {
			$stone = trim($stone);
			if (!$stone) {
				continue;
			}
			$stoneParms = explode("|", $stone);

			$name = '';
			$quantity = '';
			$form = '';
			$color = '';
			$clarity = '';
			$cut = '';
			$carats = '';

			foreach($stoneParms as $stoneParm) {
				$stoneParm = trim($stoneParm);
				if (!$stoneParm) {
					continue;
				}
				list($stoneParmKey, $stoneParmValue) = explode('=', $stoneParm);
				switch ($stoneParmKey) {
					case 'Тип вставки':
						$name = $stoneParmValue;
						break;
					case 'Количество':
						$quantity = $stoneParmValue;
						break;
					case 'Форма вставки':
						$form = $stoneParmValue;
						break;
					case 'Цвет':
						$color = $stoneParmValue;
						break;
					case 'Чистота':
						$clarity = $stoneParmValue;
						break;
					case 'Огранка':
						$cut = $stoneParmValue;
						break;
					case 'Вес':
						$carats = preg_replace('/[^0-9.,]/', '', $stoneParmValue);;
						break;
				}
			}
			if (!$name) {
				continue;
			}

			$name = $this->getFullStoneName($name);
			$code = $this->getStoneCode($name);


			$data = [
				["name" => "Камень", "value" => $name],
			];
			if ($quantity) {
				$data[] = ["name" => "Кол-во камней", "value" => $quantity];
			}
			if ($form) {
				$data[] = ["name" => "Форма огранки", "value" => $form];
			}
			if ($color) {
				$data[] = ["name" => "Цвет камня", "value" => $color];
			}
			if ($clarity) {
				$data[] = ["name" => "Чистота", "value" => $clarity];
			}
			if ($cut) {
				$data[] = ["name" => "Огранка", "value" => $cut];
			}
			if ($carats) {
				$data[] = ["name" => "Вес, Ct.", "value" => $carats];
			}
			$drag[] = [
				'kamen' => $name,
				'kamenCode' => $code,
				'data' => $data,
			];
		}

		return $drag;
	}

	protected function getPriceRoz($price_zac, $data)
	{
		$price = str_replace(',', '.', trim($data['price_roz']));
		$price = (float)preg_replace('{[^\d.]+}', '', $price);

		return (int)($price * 100);
	}
}

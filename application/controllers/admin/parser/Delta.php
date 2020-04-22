<?php

require_once APPPATH.'controllers/admin/parser/BaseParser.php';

defined('BASEPATH') OR exit('No direct script access allowed');

class Delta extends BaseParser
{
	protected $parserCode = 'delta';
	protected $parserTitle = 'Выгрузка Дельта';

	protected $uploadPath = './uploads/products/delta/';
	protected $fileName = 'delta.csv';

	protected $postavchik = 'delta';

	protected $priceType = 'alternative'; // 'common'

	protected $downloadImages = true;

	protected function getDataFromFile($currentLine)
	{
		$file = file("{$this->uploadPath}{$this->fileName}");
		$data = [];

//		$file[$currentLine] = mb_convert_encoding($file[$currentLine], 'utf8', 'cp1251'); // сейчас в utf8
//		$val = explode(';', $file[$currentLine]); // Делим данные
		$val = str_getcsv($file[$currentLine], ';');

		$data['article'] = trim($val[0]);
		$data['metal_color'] = trim($val[1]);
		$data['vstavki'] = trim($val[2]);
		$data['brandCode'] = trim($val[3]);
		$data['type'] = trim($val[4]);
		$data['size'] = str_replace('.0', '', str_replace(',', '.', trim($val[5])));
		$data['qty'] = trim($val[6]);
		$data['weight'] = str_replace(',', '.', trim($val[7]));
		$data['price'] = str_replace(',', '.', trim($val[8]));
		$data['proba'] = preg_replace('{[^\d]+}', '', trim($val[9]));
		$data['imageUrl'] = trim($val[11]);
		$data['metal'] = 'Золото';
		$data['seria'] = '';
		$data['collection'] = '';
		$data['garniture'] = '';

		// артикул вида "б118460/16,5" содержит размер после символа "/", убираем
		if (preg_match('{(.+?)/(\d+,\d+)}', $data['article'], $m)) {
			$data['article'] = $m[1];
			if (!$data['size']) {
				$data['size'] = str_replace('.0', '', str_replace(',', '.', trim($m[2])));
			}
		}
		if ($data['brandCode'] == 'КЮЗ555368' || $data['brandCode'] == 'КЮЗ556260') {
			$data['article'] = str_replace('Г8', '', $data['article']);
			$sizeFromArticle = substr($data['article'], -2);
			$data['article'] = substr($data['article'], 0, -2);
			if (!$data['size']) {
				$data['size'] = $sizeFromArticle;
			}
		}

		return $data;
	}

	protected function drag($vstavka)
	{
		//все камни из выгрузки
		/*
		Фианит
		Топаз swiss
		Раух-топаз
		Гранат
		Топаз
		Жемчуг полупросверленный
		Кварц зеленый иск.
		Ситалл сапфир
		Корунд синт.
		Ситалл рубин
		Кварц синий иск.
		Жемчуг пресноводный
		Аметист
		Ситалл топаз
		Ситалл изумруд
		Кварц голубой иск.
		Ситалл топаз swiss
		Ситалл гранат
		Ситалл цитрин
		Сапфироваый Корунд
		Жемчуг синтетический п/пр
		Ситалл аметист
		Nano crystal
		Изумрудный агат
		Лондон ситалл
		Ситалл лондон
		Ситалл кварц зеленый
		Ситалл хризолит
		Кварц рубиновый иск.
		Ситалл свисс топаз
		Рубиновый Корунд
		Жемчуг просверленный для бус
		Янтарь пресс.
		Агат
		Жемчуг синтетический пр
		Кварц розовый иск.
		Муранское Стекло
		Цитрин
		Ситалл аквамарин
		Хризолит
		Жемчуг устричный пресноводный
		*/

		//все параметры камней из выгрузки
		/*
		Камень
		ФормаОгранки
		Размер
		ЦветКамня
		ПрВес
		*/

		$vstavka = trim($vstavka);
		$vstavka = str_replace(['Rose 3#', 'Ruby 5#', '#0/1'], ['Rose 3', 'Ruby 5', '0/1'], $vstavka);
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

//			$quantity = ''; // количества пока нет
			$name = '';
			$form = '';
			$size = '';
			$color = '';
			$carats = '';

			foreach($stoneParms as $stoneParm) {
				$stoneParm = trim($stoneParm);
				if (!$stoneParm) {
					continue;
				}
				list($stoneParmKey, $stoneParmValue) = explode('=', $stoneParm);
				switch ($stoneParmKey) {
					case 'Камень':
						$name = $stoneParmValue;
						break;
					case 'ФормаОгранки':
						$form = $stoneParmValue;
						break;
					case 'Размер':
						$size = $stoneParmValue;
						break;
					case 'ЦветКамня':
						$color = $stoneParmValue;
						break;
					case 'ПрВес':
						$carats = $stoneParmValue;
						break;
				}
			}
			if (!$name) {
				continue;
			}

			$name = $this->getFullStoneName($name);
			$code = $this->getStoneCode($name);


			$data = [
//				["name" => "Кол-во камней", "value" => $quantity],
				["name" => "Камень", "value" => $name],
			];
			if ($form) {
				$data[] = ["name" => "Форма огранки", "value" => $form];
			}
			if ($size) {
				$data[] = ["name" => "Размер камня, мм", "value" => $size];
			}
			if ($color) {
				$data[] = ["name" => "Цвет камня", "value" => $color];
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

	protected function getPriceRoz($price_zac)
	{
		return (int)($price_zac * 2);
	}

	/*protected function getSalePercent()
	{
		return 20;
	}*/

}

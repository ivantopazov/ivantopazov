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

		$file[$currentLine] = mb_convert_encoding($file[$currentLine], 'utf8', 'cp1251');
//		$val = explode(';', $file[$currentLine]); // Делим данные
		$val = str_getcsv($file[$currentLine], ';');

		$data['article'] = trim($val[0]);
		$data['brandCode'] = trim($val[1]);
		$data['type'] = trim($val[2]);
		$data['size'] = str_replace('.0', '', str_replace(',', '.', trim($val[3])));
		$data['qty'] = trim($val[4]);
		$data['weight'] = str_replace(',', '.', trim($val[5]));
		$data['price'] = str_replace(',', '.', trim($val[6]));
		$data['proba'] = preg_replace('{[^\d]+}', '', trim($val[7]));
		$data['imageUrl'] = trim($val[9]);
		$data['metal'] = 'Золото';
		$data['metal_color'] = '';
		$data['seria'] = '';
		$data['vstavki'] = '';
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

		if (strpos($data["article"], 'б') === 0) {
			$data["metal_color"] = 'Белый';
		} elseif (strpos($data["article"], 'л') === 0) {
			$data["metal_color"] = 'Желтый';
		} else {
			$data["metal_color"] = 'Красный';
		}

		return $data;
	}

/*	protected function drag($vstavka)
	{
		$vstavka = trim($vstavka);
		$drag = [];

		if (!$vstavka || $vstavka == '<без вставок>') {
			return $drag;
		}

		$stone = explode(', ', str_replace(' ,', ', ', $vstavka));
		foreach ($stone as $value) {
			$stoneParms = explode(" ", $value);
			$quantity = array_shift($stoneParms);
			$name = array_shift($stoneParms);

			if (
				$name == 'Топаз' && $stoneParms[0] == 'Sky' ||
				$name == 'Ювелирный' && $stoneParms[0] == 'кристалл' ||
				$name == 'Шпинель' ||
				$stoneParms[0] == 'синт.' ||
				$stoneParms[0] == 'культ' ||
				$stoneParms[0] == 'г/т'
			) {
				$name .= ' ' . array_shift($stoneParms);
			}

			$name = $this->getFullStoneName($name);
			$code = $this->getStoneCode($name);

			if ($name == 'Лента' || $name == 'Леска') {
				continue;
			}

			$form = array_shift($stoneParms);
			if ($form == 'Кр') {
				$form = 'круг';
			}
			if ($form == 'нити') { // нити 30мм круг
				$name .= ' ' . array_shift($stoneParms) . ' ' . array_shift($stoneParms);

			}

			if ($name == 'Металл') {
				$name = 'Серебро';
				$data = [
					["name" => "Кол-во вставок", "value" => $quantity],
					["name" => "Вставка", "value" => $name],
					["name" => "Проба", "value" => '925'],
				];
			} else {
				$data = [
					["name" => "Кол-во камней", "value" => $quantity],
					["name" => "Камень", "value" => $name],
					["name" => "Форма огранки", "value" => $form],
				];

				if ($name == 'Бриллиант') {
					list($facets, $size, $carats, $sifting, $colorAndPurity) = $stoneParms;
					$data = array_merge($data, [
						["name" => "Кол-во граней", "value" => $facets],
						["name" => "Размер камня, мм", "value" => str_replace('d=', '', $size)],
						["name" => "Вес, Ct.", "value" => str_replace('Ct', '', $carats)],
						["name" => "Рассев", "value" => $sifting],
						["name" => "Цвет/Чистота", "value" => $colorAndPurity],
					]);
				} else {
					list($size, $carats, $color, $color2, $color3) = $stoneParms;
					$data = array_merge($data, [
						["name" => "Размер камня, мм", "value" => $size],
						["name" => "Вес, Ct.", "value" => $carats],
						["name" => "Цвет", "value" => $color . ($color2 ? ' ' . $color2 : '') . ($color3 ? ' ' . $color3 : '')],
					]);
				}
			}
			$drag[] = [
				'kamen' => $name,
				'kamenCode' => $code,
				'data' => $data,
			];
		}

		return $drag;
	}*/

	protected function getPriceRoz($price_zac)
	{
		return (int)($price_zac * 2);
	}

	protected function getSalePercent()
	{
		return 20;
	}

}

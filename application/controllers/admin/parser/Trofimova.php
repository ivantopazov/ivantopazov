<?php

require_once APPPATH . 'controllers/admin/parser/BaseParser.php';

defined('BASEPATH') OR exit('No direct script access allowed');

class Trofimova extends BaseParser
{

	protected $parserCode = 'trofimova';
	protected $parserTitle = 'Выгрузка TROFIMOVA jewellery';

	protected $uploadPath = './uploads/products/trofimova/';
	protected $fileName = 'trofimova.csv';

	protected $postavchik = 'trofimova-jewellery';


	protected function getDataFromFile($currentLine)
	{
		$file = file("{$this->uploadPath}{$this->fileName}");
		$data = [];

		$file[$currentLine] = mb_convert_encoding($file[$currentLine], "utf8", "cp1251");
		$val = str_getcsv($file[$currentLine], ';');

		// Фильтр пустых строк
		if (strlen($val[1]) < 2) {
			return $data;
		}

		$data["article"] = trim($val[1]);
		$data["weight"] = str_replace(",", ".", trim($val[2]));
		$data["vstavki"] = trim($val[3]);
		$data["price"] = trim($val[4]);
		$data["proba"] = trim($val[5]);
		$data["size"] = str_replace('.0', '', str_replace(',', '.', trim($val[6])));
		$data["type"] = trim($val[7]);
		$data["metal"] = 'Золото';
		$data["metal_color"] = trim($val[8]);
		$data["qty"] = 1;
		$data["seria"] = '';
		$data["garniture"] = '';
		$data["collection"] = '';

		if ($data["metal_color"] == 'Белое золото') {
			$data["metal_color"] = 'Белый';
		} elseif ($data["metal_color"] == 'Желтое золото') {
			$data["metal_color"] = 'Желтый';
		} else {
			$data["metal_color"] = 'Красный';
		}

		return $data;
	}

	protected function drag($vstavki)
	{

		$drag = [];

		$kamen = explode(",", $vstavki);
		foreach ($kamen as $key => $value) {
			$value = preg_replace('/(\d+) (\d+Ct)/', '$1.$2', trim($value));
			$val = explode(" ", $value);
			$drag[$key] = [
				'kamen' => $this->getFullStoneName($val[1]),
				'kamenCode' => $this->getStoneCode($val[1]),
				'data' => [
					["name" => "Кол-во камней", "value" => $val[0]],
					["name" => "Камень", "value" => $val[1]],
					["name" => "Форма огранки", "value" => $val[2]],
					["name" => "Вес, Ct.", "value" => $val[4]],
				],
			];
		}

		return $drag;
	}

}

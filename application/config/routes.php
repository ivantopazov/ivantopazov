<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'home';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
  
$route['wiki/(:any)'] = 'wiki/get_item/$1';


$route['robots.txt'] = "seo/robots";
$route['sitemap.xml'] = "seo/sitemap";
$route['sitemapProductsCategory_(:num).xml'] = "seo/sitemapProducts/$1";

//$route['catalog/podveski?f[metall]=serebro&f[Cena]=0|90000&s=new&l=30&t='] = 'catalog/podveski_iz_serebra'; 

//catalog/podveski?f[metall]=serebro&f[Cena]=0|90000&s=new&l=30&t=
//$route['catalog/(:any)'] = 'catalog/index/$1/1'; 
<?php

defined('BASEPATH') OR exit('No direct script access allowed');

$autoload['packages'] = array();
$autoload['libraries'] = array('database');
$autoload['drivers'] = array();
$autoload['helper'] = array('url', 'cookie');
$autoload['config'] = array();
$autoload['language'] = array();
$autoload['model'] = array(
    'mdl_db',
    'mdl_stores',
    'mdl_tpl',
    'mdl_helper',
    'mdl_users',
    'mdl_collections',
    'mdl_baners',
    'mdl_category',
    'mdl_product',
    'mdl_orders',
    'mdl_seo'
);

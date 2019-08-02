<?php

defined('BASEPATH') OR exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;

if( $_SERVER['SERVER_NAME'] === 'direligt.beget.tech' ){
    $db['default'] = array(
        'dsn'	=> '',
        'hostname' => 'localhost',
        'username' => 'direligt_test',
        'password' => '123456',
        'database' => 'direligt_test',
        'dbdriver' => 'mysqli',
        'dbprefix' => '',
        'pconnect' => TRUE,
        'db_debug' => (ENVIRONMENT !== 'production'),
        'cache_on' => TRUE,
        'cachedir' => '',
        'char_set' => 'utf8',
        'dbcollat' => 'utf8_general_ci',
        'swap_pre' => '',
        'encrypt' => FALSE,
        'compress' => FALSE,
        'stricton' => FALSE,
        'failover' => array(),
        'save_queries' => FALSE
    );
}else{
    $db['default'] = array(
        'dsn'	=> '',
        'hostname' => 'localhost',
        'username' => 'direligt_yuveli',
        'password' => '7*SP2E5#',
        'database' => 'direligt_yuveli',
        'dbdriver' => 'mysqli',
        'dbprefix' => '',
        'pconnect' => TRUE,
        'db_debug' => (ENVIRONMENT !== 'production'),
        'cache_on' => TRUE,
        'cachedir' => '',
        'char_set' => 'utf8',
        'dbcollat' => 'utf8_general_ci',
        'swap_pre' => '',
        'encrypt' => FALSE,
        'compress' => FALSE,
        'stricton' => FALSE,
        'failover' => array(),
        'save_queries' => FALSE
    );
}





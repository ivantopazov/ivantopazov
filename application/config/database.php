<?php

defined('BASEPATH') OR exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;

switch ( $_SERVER['REMOTE_ADDR'] ) {

    case '127.0.0.1':
            $db['default'] = array(
                'dsn'	=> '',
                'hostname' => 'localhost',
                'username' => 'root',
                'password' => '',
                'database' => 'ivantopazov',
                'dbdriver' => 'mysqli',
                'dbprefix' => '',
                'pconnect' => FALSE,
                'db_debug' => (ENVIRONMENT !== 'production'),
                'cache_on' => FALSE,
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
        break;

    case 'direligt.beget.tech':
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
        break;

    default:
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
        break;
}

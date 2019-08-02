<?php

defined('BASEPATH') OR exit('No direct script access allowed');

$config['base_url'] = 'http://' . $_SERVER['HTTP_HOST'] .'/';
$config['index_page'] = '';
$config['uri_protocol'] = 'REQUEST_URI';
$config['url_suffix'] = '';
$config['language'] = 'russian';
$config['charset'] = 'UTF-8';
$config['enable_hooks'] = FALSE;
$config['subclass_prefix'] = 'MY_';
$config['composer_autoload'] = FALSE;
$config['permitted_uri_chars'] = 'a-z 0-9~%.:_\-';
$config['allow_get_array'] = TRUE;
$config['enable_query_strings'] = FALSE;
$config['controller_trigger'] = 'c';
$config['function_trigger'] = 'm';
$config['directory_trigger'] = 'd';
$config['log_threshold'] = 0;
$config['log_path'] = '';
$config['log_file_extension'] = 'log.txt';
$config['log_file_permissions'] = 0644;
$config['log_date_format'] = 'Y-m-d H:i:s';
$config['error_views_path'] = '';
$config['cache_path'] = '';
$config['cache_query_string'] = FALSE;
$config['encryption_key'] = '';
$config['sess_driver'] = 'files';
$config['sess_cookie_name'] = 'ci_session';
$config['sess_expiration'] = 7200;
$config['sess_save_path'] = NULL;
$config['sess_match_ip'] = FALSE;
$config['sess_time_to_update'] = 300;
$config['sess_regenerate_destroy'] = FALSE;
$config['cookie_prefix'] = '';
$config['cookie_domain'] = '';
$config['cookie_path'] = '/';
$config['cookie_secure'] = FALSE;
$config['cookie_httponly'] 	= FALSE;
$config['standardize_newlines'] = FALSE;
$config['global_xss_filtering'] = FALSE;
$config['csrf_protection'] = FALSE;
$config['csrf_token_name'] = 'csrf_test_name';
$config['csrf_cookie_name'] = 'csrf_cookie_name';
$config['csrf_expire'] = 7200;
$config['csrf_regenerate'] = TRUE;
$config['csrf_exclude_uris'] = array();
$config['compress_output'] = TRUE;
$config['time_reference'] = 'local';
$config['rewrite_short_tags'] = FALSE;
$config['proxy_ips'] = '';


$config['addons_folder'] = 'addons';
$config['tpl_eninge'] = 'addons';
$config['templates_folder'] = 'templates';
$config['templates'] = ''; // Ставится в БД
$config['tpl_folder'] = 'views';
$config['images_folder_name'] = 'images';
$config['scripts_folder_name'] = 'scripts';
$config['styles_folder_name'] = 'styles';

$config['threme_folders'] = $config['templates_folder'].'/'.$config['templates'];
$config['config_tpl_path'] = $config['threme_folders'].'/'. $config['tpl_folder'];
$config['config_styles_path'] = $config['threme_folders'].'/'. $config['styles_folder_name'];
$config['config_scripts_path'] = $config['threme_folders'].'/'. $config['scripts_folder_name'];
$config['config_images_path'] = $config['threme_folders'].'/'. $config['images_folder_name'];

   
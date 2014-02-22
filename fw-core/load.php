<?php
/*** CHECK IF IN DEVELOPMENT ENVIROMENT */
if (get_config("APP.DEVELOPMENT_ENVIRONMENT") == true) {
	error_reporting(E_ALL);
	@ini_set('display_errors','On');
} else {
	error_reporting(E_ALL);
	@ini_set('display_errors','Off');
	@ini_set('log_errors', 'On');
}
ini_set('error_log', LOGS_DIR.'error.log');

$hooks 		= new Hook();
$router 	= new Router();
$cache 		= new Cache();
$i18n 		= new i18n();

$i18n->import_folder(LOCALE_DIR);
Plugin::init();
trigger("fw.init");

/* URI REQUEST ROUTING */
$requested_uri = (isset($_GET['fw-url']))?$_GET['fw-url']:(isset($_SERVER['PATH_INFO'])?substr($_SERVER['PATH_INFO'],1):"");
$requested_uri = apply_filters('fw.requested_uri',$requested_uri);
set_info("url",$requested_uri);

$request_file = $router->route_request($requested_uri);

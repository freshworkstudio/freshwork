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
$fw_blocks	= new View_Block();

$i18n->import_folder(LOCALE_DIR);

//Import Routing Files of Core and App
require_once (CORE_DIR . 'routing.php');
require_once (CONFIG_DIR . 'routing.php');

require_once("hooks.php");
Plugin::init();
trigger("fw_init");

/* URI REQUEST ROUTING */
$requested_uri = (isset($_GET['fw-url']))?$_GET['fw-url']:(isset($_SERVER['PATH_INFO'])?substr($_SERVER['PATH_INFO'],1):"");
$requested_uri = apply_filters('fw_requested_uri',$requested_uri);
set_info("url",$requested_uri);

//We have the file of the view.
$view_file = $router->route_request($requested_uri);

//Here we going to choose a templete to load the view on it
$template_file = apply_filters('default_template',false);
if(!(bool)$template_file){
	$tpl_file = WWW_DIR."templates".DS."default.php";
	if(file_exists($tpl_file))$template_file=$tpl_file;
}
set_info("template",$template_file);


start_block("content");
fw_include($view_file);
end_block();

if(get_info("template")){
	start_block("layout");
	fw_include(get_info("template"));
	end_block();
	echo get_block("layout");
}else{
	echo get_block("content");		
}





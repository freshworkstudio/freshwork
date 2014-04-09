<?php
$t = microtime(true);

/*** CHECK IF WE'RE IN DEVELOPMENT ENVIROMENT */
if (get_config("APP.DEVELOPMENT_ENVIRONMENT") == true) {
	error_reporting(E_ALL);
	@ini_set('display_errors','On');
} else {
	error_reporting(E_ALL);
	@ini_set('display_errors','Off');
	@ini_set('log_errors', 'On');
}
ini_set('error_log', LOGS_DIR.'error.log');


//INIT APP. Start Global Objects
$app 	= new Freshwork\App();
$app->init();
$app->plugins->init();


$app->i18n->import_folder(LOCALE_DIR);

//Import Routing Files of Core and App
require_once (CORE_DIR . 'routing.php');
require_once (CONFIG_DIR . 'routing.php');

require_once("hooks.php");
$app->plugins->boot();

trigger("fw_init");

/* URI REQUEST ROUTING */
$requested_uri = (isset($_GET['fw_url']))?$_GET['fw_url']:(isset($_SERVER['PATH_INFO'])?substr($_SERVER['PATH_INFO'],1):"");
$requested_uri = apply_filters('fw_requested_uri',$requested_uri);
set_info("url",$requested_uri);

//Execute callbacks based on requested uri
$app->router->execute_callbacks($requested_uri);

//Based on request_uri, we check which view file we're going to load. Filtered via @file_to_load hook.
$view_file = apply_filters("file_to_load",$app->router->route_request($requested_uri));

//Get the default tempalte
$app->view->init();


//Define the View File to Load
$app->view->set_view($view_file);



//Load view and put the output in 'content' view block
$view_vars = apply_filters("view_vars",array());
$app->view->load_view($view_vars);

echo apply_filters("app_html",$app->view->render());

//Here we going to choose a templete to load the view on it



$t = microtime(true)-$t;
echo "T: $t";





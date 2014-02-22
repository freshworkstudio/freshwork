<?php
//Disable Magic Quotes. Another proceso will add quotes later.
@ini_set('magic_quotes_runtime',0);

/** Autoload any classes that are required **/
function __autoload($className) {
	if (file_exists(CORE_LIBS_DIR. strtolower($className) . '.php')) {
		require_once(CORE_LIBS_DIR. strtolower($className) . '.php');
	}
}

//Include common functions of the framework
require_once(CORE_DIR.'functions.php');

$config = new Configuration(); //Configs of configurations files
$fw_info = new Configuration(); //Runtime variables 
load_config_file(CORE_DIR.'base-config.php'); //Load base config

if(!file_exists(APP_DIR.'config.php')){
	require_once(CORE_DIR.'install.php');	
}else{
	load_config_file(APP_DIR.'config.php');
	require_once('load.php');
}

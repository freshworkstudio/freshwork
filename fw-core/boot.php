<?php

//Disable Magic Quotes. Another proceso will add quotes later.
@ini_set('magic_quotes_runtime',0);

/** Autoload any classes that are required **/
function __autoload($className) {
	$className = strtolower(str_replace('\\',DS,$className));
	
	$file = CORE_LIBS_DIR.$className. '.php';
	if (file_exists( $file )) {
		require_once($file);
	}
}

//Include common functions of the framework
require_once(CORE_DIR.'functions.php');

$config = new Freshwork\Configuration(); //Configs of configurations files
$fw_info = new Freshwork\Configuration(); //Runtime variables 
load_config_file(CORE_DIR.'base-config.php'); //Load base config

if(!file_exists(CONFIG_DIR.'config.php')){
	require_once(CORE_DIR.'install.php');	
}else{
	load_config_file(CONFIG_DIR.'config.php');
	require_once('load.php');
}
//Gj2HMaNsXg9s

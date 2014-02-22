<?php
//Thanks for using Freshwork CMS :). Hope you like it.
//CONS DEFINITIONS
define('DS', DIRECTORY_SEPARATOR);
define('ABS_DIR', dirname((__FILE__)).DS);
define('APP_DIR',ABS_DIR.'fw-app'.DS);
define('UPLOADS_DIR',APP_DIR.'uploads'.DS);
define('TMP_DIR',APP_DIR.'tmp'.DS);
define('MODELS_DIR',APP_DIR.'models'.DS);
define('PLUGINS_DIR',ABS_DIR.'fw-plugins'.DS);
define('CONFIG_DIR',APP_DIR.'config'.DS);
define('WWW_DIR',APP_DIR.'www'.DS);
define('LOGS_DIR',TMP_DIR.'logs'.DS);
define('CORE_DIR',ABS_DIR.'fw-core'.DS);
define('CORE_LIBS_DIR',CORE_DIR.'libs'.DS);
define('CACHE_DIR',TMP_DIR.'cache'.DS);
define('LOCALE_DIR',APP_DIR.'locale'.DS);

define('DOMAIN',$_SERVER['HTTP_HOST']);
define('ABS_URL',str_replace($_SERVER['DOCUMENT_ROOT'],'',ABS_DIR));
define('APP_URL',ABS_URL.'fw-app/');
define('PLUGINS_URL',APP_URL.'plugins/');
define('WWW_URL',APP_URL.'www/');
define('UPLOADS_URL',APP_URL.'uploads/');



require_once (CORE_DIR. 'boot.php');
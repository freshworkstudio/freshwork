<?php
//Get the APP object from anywhere. 
function APP(){
	global $app;
	return 	$app;
}


/************************************
OPTIONS
************************************/


/************************************
INFO VALUES OF THE APP
************************************/
function get_info(){
	global $fw_info;
	return apply_filters("get_info",call_user_func_array(array($fw_info,"get"),func_get_args()));		
}
function get_all_info(){
	global $fw_info;
	return call_user_func_array(array($fw_info,"get_all"),func_get_args());		
}
function set_info(){
	global $fw_info;
	return apply_filters("set_info",call_user_func_array(array($fw_info,"set"),func_get_args()));	
}

/************************************
APP CONFIGURATION
************************************/
function get_config(){
	global $config;
	return call_user_func_array(array($config,"get"),func_get_args());		
}
function get_all_configs(){
	global $config;
	return call_user_func_array(array($config,"get_all"),func_get_args());		
}
function set_config(){
	global $config;
	return call_user_func_array(array($config,"set"),func_get_args());	
}
function load_config_file($file,$replace=true){
	$conf = array();
	require_once($file);
	set_config($conf,$replace);	 //Add the configs to the main array
}


/**
 * Retrieve metadata from a file.
 *
 * Searches for metadata in the first 8kiB of a file, such as a plugin or theme.
 * Each piece of metadata must be on its own line. Fields can not span multiple
 * lines, the value will get cut at the end of the first line.
 *
 * If the file data is not within that first 8kiB, then the author should correct
 * their plugin file and move the data headers to the top.
 *
 * @see http://codex.wordpress.org/File_Header
 *
 * @since 2.9.0
 * @param string $file Path to the file
 * @param array $default_headers List of headers, in the format array('HeaderKey' => 'Header Name')
 * @param string $context If specified adds filter hook "extra_{$context}_headers"
 */
function get_file_data( $file, $default_headers, $context = '' ) {
	// We don't need to write to the file, so just open for reading.
	$fp = fopen( $file, 'r' );

	// Pull only the first 8kiB of the file in.
	$file_data = fread( $fp, 8192 );

	// PHP will close file handle, but we are good citizens.
	fclose( $fp );

	// Make sure we catch CR-only line endings.
	$file_data = str_replace( "\r", "\n", $file_data );

	if ( $context && $extra_headers = apply_filters( "extra_{$context}_headers", array() ) ) {
		$extra_headers = array_combine( $extra_headers, $extra_headers ); // keys equal values
		$all_headers = array_merge( $extra_headers, (array) $default_headers );
	} else {
		$all_headers = $default_headers;
	}

	foreach ( $all_headers as $field => $regex ) {
		if ( preg_match( '/^[ \t\/*#@]*' . preg_quote( $regex, '/' ) . ':(.*)$/mi', $file_data, $match ) && $match[1] )
			$all_headers[ $field ] = _cleanup_header_comment( $match[1] );
		else
			$all_headers[ $field ] = '';
	}

	return $all_headers;
}

/**
 * Strip close comment and close php tags from file headers used by WP.
 * See http://core.trac.wordpress.org/ticket/8497
 *
 * @since 2.8.0
 *
 * @param string $str
 * @return string
 */
function _cleanup_header_comment($str) {
	return trim(preg_replace("/\s*(?:\*\/|\?>).*/", '', $str));
}


/************************************
FILES FUNCTIONS
************************************/
function get_dirs($directory){
	//Get dirs excludin '.','..',dirs that start with "_" and ".".
	$dirs = array();
	if(!file_exists($directory))return false;
	if ($gestor = opendir($directory)) {
		/* Esta es la forma correcta de iterar sobre el directorio. */
		while (false !== ($dir = readdir($gestor))) {
			if($dir != ".." && $dir != "." && substr($dir,0,1) != "_" && substr($dir,0,1) != ".")
				$dirs[]=$dir;
		}
		closedir($gestor);
	}	
	return $dirs;
}

/************************************
ENVIROMENT FUNCTIONS
************************************/

function is_ajax(){
	return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
}

function is_404(){
	global $app;
	return $app->is_404();	
}
/************************************
MISC
************************************/

function fw_error($msg,$error_type=E_USER_NOTICE){
	trigger("trigger_error",$error_type);
	trigger_error($msg,$error_type);	
}

function fw_include($file){
	trigger("include",$file);
	include_once($file);
}

function get_plugin_info($code,$field=false){
	$plugins = APP()->plugins->get_all();
	if(!isset($plugins[$code]))fw_error(__("The plugin '$code' doesn't exists"));
	$plugin = $plugins[$code];
	if(!$field)return $plugin;
	return $plugin[$field];
}

function importBootFiles($view_filename){
	if(strpos($view_filename,WWW_DIR) === false)return;
	$rel = explode(DS,str_replace(WWW_DIR,"",$view_filename));
	
	
	unset($rel[count($rel)-1]);
	array_unshift($rel,"");
	$tmp = "";
	foreach($rel as $folder){
		$tmp .= $folder.DS;
		$f = substr(WWW_DIR,0,-1).$tmp."boot.php";
		if(file_exists($f)){
			include_once($f);	
		}
	}
}

function wtf($var, $arrayOfObjectsToHide=array(), $fontSize=11)
{
    $text = print_r($var, true);
    $text = str_replace('<', '&lt;', $text);
    $text = str_replace('>', '&gt;', $text);

    foreach ($arrayOfObjectsToHide as $objectName) {
        $searchPattern = '#(\W'.$objectName.' Object\n(\s+)\().*?\n\2\)\n#s';
        $replace = "$1<span style=\"color: #FF9900;\">";
        $replace .= "--&gt; HIDDEN - courtesy of wtf() &lt;--</span>)";
        $text = preg_replace($searchPattern, $replace, $text);
    }

    // color code objects
    $text = preg_replace(
        '#(\w+)(\s+Object\s+\()#s',
        '<span style="color: #079700;">$1</span>$2',
        $text
    );
    // color code object properties
    $pattern = '#\[(\w+)\:(public|private|protected)\]#';
    $replace = '[<span style="color: #000099;">$1</span>:';
    $replace .= '<span style="color: #009999;">$2</span>]';
    $text = preg_replace($pattern, $replace, $text);

    echo '<pre style="
        font-size: '.$fontSize.'px;
        line-height: '.$fontSize.'px;
        background-color: #fff; padding: 10px;
        ">'.$text.'</pre>
    ';
}

/************************************
VIEW BLOCKS FUNCTIONS 
************************************/
function start_block(){
	$args = func_get_args(); 
	return call_user_func_array(array(APP()->blocks,'start'), $args);	
}
function end_block(){
	$args = func_get_args(); 
	return call_user_func_array(array(APP()->blocks,'end'), $args);	
}
function start_block_if_empty(){
	$args = func_get_args(); 
	return call_user_func_array(array(APP()->blocks,'start_if_empty'), $args);	
}
function get_block(){
	$args = func_get_args(); 
	return call_user_func_array(array(APP()->blocks,'get'), $args);	
}

function set_block(){
	$args = func_get_args(); 
	return call_user_func_array(array(APP()->blocks,'set'), $args);	
}

function load_block(){
	$args = func_get_args(); 
	return call_user_func_array(array(APP()->blocks,'load'), $args);	
}

/*************************
HOOOKS
****************************/

/*** GLOBAL FUNCTIONS ****/
function add_filter($filter,$function,$priority = 10,$accepted_arguments=1){
	$args = func_get_args();
	return call_user_func_array(array(APP()->hooks,"add_filter"),$args);
}
function apply_filters($filter,$value){
	$args = func_get_args();
	return call_user_func_array(array(APP()->hooks,"apply_filters"),$args);
}
function add_listener($filter,$function,$priority = 10,$accepted_arguments=1){
	$args = func_get_args();
	return call_user_func_array(array(APP()->hooks,"add_listener"),$args);
}
function trigger($filter){
	$args = func_get_args();
	return call_user_func_array(array(APP()->hooks,"trigger"),$args);
}


/**************************
i18n
****************************/

//HERRAMIENTAS DE IDIOMA
function ___($str){
	$args = (is_array($str))?$str:func_get_args();
	echo __($args); 
}
function __($str){
	global $app;
	$args = (is_array($str))?$str:func_get_args();
	return ($app->i18n->translate($args));
}





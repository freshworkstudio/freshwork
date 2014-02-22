<?php
function get_option($key){
	return false;	
}

/* Configuration Shrotcuts */
function get_info(){
	global $fw_info;
	return call_user_func_array(array($fw_info,"get"),func_get_args());		
}
function get_all_info(){
	global $fw_info;
	return call_user_func_array(array($fw_info,"get_all"),func_get_args());		
}
function set_info(){
	global $fw_info;
	return call_user_func_array(array($fw_info,"set"),func_get_args());	
}

/* Configuration Shrotcuts */
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
	set_config($conf,$replace);	 //Add the configs to the  ain array
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
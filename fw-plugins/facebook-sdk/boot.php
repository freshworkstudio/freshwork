<?php
/*
Plugin Name: Facebook Developer 
Plugin URL: http://www.freshworkcms.com/plugins/facebook-sdk
Version: 0.1
Author: Freshwork Studio
Author URL: http://www.freshworkstudio.com
License: GPLv2
*/
load_config_file(PLUGINS_DIR."facebook-sdk/config.php");

if(get_config("FB.APPID") != ""){
	include("facebook.php");
	$config = array();
	$config["appId"] = get_config("FB.APPID");
	$config["secret"] = get_config("FB.APPSECRET");
	$config["fileUpload"] = true; // optional
	
	global $facebook; //Hacer la variable pública
	$facebook = new Facebook($config);
	
	add_filter("after_render_content","facebook_body_loads");
	add_filter("url_routes",function($routes){
		$routes["channel.html"] = PLUGINS_DIR."facebook-sdk/channel.html.php";
		$routes["add-to-tab-facebook"]=PLUGINS_DIR."facebook-sdk/add_to_tab.php";
		return $routes;
	});
	
	function facebook_body_loads($site_html){
		global $conf,$router,$page;
		if(!$page->parse_html)return $site_html;
		$html = get_facebook_script();//(PLUGINS_DIR."facebook-sdk/init.html");
		
		$site_html = phpQuery::newDocumentHTML($site_html);
		pq("body")->prepend($html);
		return $site_html;
	}
	
	function get_facebook_script(){
		global $conf,$router,$page;
		$html = file_get_contents(PLUGINS_DIR."facebook-sdk/init.html");
		$html = str_replace("{APP_ID}",get_config("FB.APPID"),$html);
		$html = str_replace("{BASE_URL}",DOMAIN.ABS_URL,$html);
		$html = str_replace("{LANG}",get_config("FB.LANG"),$html);
		$html = str_replace("{PERMISSIONS}",get_config("FB.PERMISSIONS"),$html);
		return $html;
	}
}
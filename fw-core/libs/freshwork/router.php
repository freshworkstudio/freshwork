<?php
namespace Freshwork;
class Router{
	var $routes = array();
	var $callbacks = array();
	var $valid_extensions = array("php","php5","html","htm");
	
	function redirect($pattern,$result){
		$this->routes[$pattern] = $result;
	}
	function map($pattern,$callback){
		$this->callbacks[$pattern] = $callback;
	}
	
	function match_url($url,$pattern){
		if ($pattern === '*' || $pattern === $url)
			return true;
		
		$ids = array();
		$char = substr($pattern, -1);
		$regex = preg_replace_callback(
			'#@([\w]+)(:([^/\(\)]*))?#',
			function($matches) use (&$ids) {
				$ids[$matches[1]] = null;
				if (isset($matches[3])) {
					return '(?P<'.$matches[1].'>'.$matches[3].')';
				}
				return '(?P<'.$matches[1].'>[^/\?]+)';
			},
			$pattern
		);
		if ($char === '/')$regex .= '?';
		else $regex .= '/?'; // Allow trailing slash
		
		$params = array();
		if (preg_match('#^'.$regex.'(?:\?.*)?$#i', $url, $matches)) {
			foreach ($ids as $k => $v) {
				$params[$k] = (array_key_exists($k, $matches)) ? urldecode($matches[$k]) : null;
			}
			return $params;
		}
		return false;

	}
	function match_method($methods){
		global $request;		
		return (in_array("*",$methods) || in_array($request->getMethod(),$methods));
	}
	function execute_callbacks($url){
		$maps = apply_filters("map_callbacks",$this->callbacks,$url);
		
		$ids = array();
		foreach($maps as $pattern => $callback){
			//Check all the connected callbacks
			
			$methods = array("*");
			if (strpos($pattern, ' ') !== false) {
				list($method, $url) = explode(' ', trim($pattern), 2);
				$methods = explode('|', $method);
			}
			
			
			if($this->match_method($methods) && $match = $this->match_url($url,$pattern) !== false){
				if(!is_array($match))$match = array();
				call_user_func_array($callback,$match);
			}
		}
		return false;
	}
	function get_redirection($url){
		$routes = apply_filters("url_routes",$this->routes,$url);
		foreach($routes as $origen => $destino){
			$replac = "/^".str_replace("/","\/",$origen)."$/";
			if(preg_match($replac,$url)){
				$file = preg_replace("/".str_replace("/","\/",$origen)."/",$destino,$url);
				$file = $this->retrieve_get_params($file);
				return str_replace("//","/",$file);
			}
		}
		return false;
	}
	/*
	Take a file path. If has GET parameters, removes them of the filename and put them in $_GET var
	*/
	function retrieve_get_params($filename){
		if(strpos($filename,"?") !== false){
			$parts = explode("?",$filename);
			$query_params = $parts[1];
			parse_str($q,$_GET);
			return $parts[0];
		}
		return $filename;
	}
	function route_request($requested_uri){
		//Get the file name of this request uri
		//Check redirections API 
		$pseudo_file = $this->get_redirection($requested_uri);
		
		
		//If there isn't rediretions in the API, get the default filename
		if(!$pseudo_file)$pseudo_file = WWW_DIR.$requested_uri;
		
		//Based on the current filename, normalize it, and search the exact filename. If can't find, returns false.
		$file = $this->get_final_filename($pseudo_file);
		
		//If $filename doesn't exists, try to get 404 page.
		if(!$file){
			if($requested_uri != "404"){
				global $app;
				$app->set_404();
				$file = $this->route_request("404");
			}else{
				header("HTTP/1.0 404 Not Found");
				exit;	
			}
		}
		return $file;
	}
	
	function get_default_page_name(){
		return apply_filters("default_page_name","index");	
	}
	function normalize_filename($filename){
		if($filename == "")$filename = $this->get_default_page_name();
		if(strrchr($filename,"/")== "/")$filename .= $this->get_default_page_name();
		return $filename;
	}
	function get_relative_url($filename){
		return str_replace(ABS_DIR,"",$filename);
			
	}
	function get_final_filename($file){
		//normalize file. If ends in /, remplace with /index
		
		$file = $this->normalize_filename($file);
		
		//Get the file extension if is set
		$path = pathinfo($file);
		$extension = (isset($path["extension"]))?$path["extension"]:false;
		
		if(!in_array($extension,$this->valid_extensions)){
			if(is_dir($file)){
				//If the request URI havn't and extension because is a directory, 
				//we add the final "/" to the uri and make a 301  redirect
				header("Location: ".ABS_URL.get_info("url")."/",301);
				exit;	
			}
			//If the requested file has not a valida extension, its probably beacuse its an asset, like css, js. In that case, redirect to the direct url of the file.
			if(isset($path["extension"]) && !in_array($path["extension"],$this->valid_extensions) && file_exists($file)){
				header("Location: ".ABS_URL.$this->get_relative_url($file));
				exit;		
			}
			
			//Request URI hasn't a valid extension, so we test with every combination of valid extenions to check if file exists		
			foreach($this->valid_extensions as $ext_valida){
				$tmp = "$file.{$ext_valida}";
				if(file_exists($tmp)){
					$file = $tmp;
					break;	
				}
			}
			
		}
		
		if(!is_file($file))return false;
		return $file;
	}
}?>
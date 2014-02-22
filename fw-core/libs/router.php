<?php

class Router{
	var $routing = array();
	var $valid_extensions = array("php","php5","html","htm");
	
	function redirect($pattern,$result,$template = null){
		$this->routing[$pattern] = $result;
	}
	function get_redirection($url){
		
		$preg = false;
		foreach($this->routing as $origen => $destino){
			$replac = "/^".str_replace("/","\/",$origen)."$/";
			if(preg_match($replac,$url)){
				$preg = true;
				$file = preg_replace("/".str_replace("/","\/",$origen)."/",$destino,$url);
				//if($file{0} != "/")$file = WWW_DIR.$file;
				//if(!is_file($file))return false;
				return $file;
			}
		}
		return false;
	}
	function route_request($requested_uri){
		//Get the file name of this request uri
		//Check redirections API 
		$file = $this->get_redirection($requested_uri);
		
		//If there isn't rediretions in the API, get the default filename
		if(!$file)$file = $this->get_filename($requested_uri);
		
		//If $filename doesn't exists, try to get 404 page.
		if(!$file){
			if($requested_uri != "404"){
				$file = $this->route_request("404");
			}else{
				header("HTTP/1.0 404 Not Found");
				exit;	
			}
		}
		set_info("request_filename",$file);
		//parse		
		if(strpos($destination,"?") !== false){
			$redirect = explode("?",$destination);
			$q = $redirect[1];
			$redirect = $redirect[0];
			parse_str($q,$_GET);
		}
		
		if(!is_file($final_request_uri)){
			
			//header("Location: ".ABS_URL."404?$url"); 
			//exit;	
		}
		/*if(!is_file($destination)){
			trigger_error("Can't find the controller file for this request: $destination");	
			return false;
		}*/
		return $destination;
	}
	
	function get_default_page_name(){
		return apply_filter("default_page_name","index");	
	}
	function get_filename($requested_uri){
		//Retrive file name for this request uri
		/*
		@TODO: Remove WWW_DIR prepend when 
		*/
		
		//Normalize Request URI
		if($requested_uri == "")$requested_uri = $this->get_default_page_name();
		if(strrchr($requested_uri,"/")== "/")$requested_uri .= $this->get_default_page_name();
		
		//Get the file extension if is set
		$path = pathinfo($requested_uri);
		$extension = (isset($path["extension"]))?$path["extension"]:false;
		
		if(in_array($extension,$this->valid_extensions)){
			//Request uri has an extension and it's valid, so the filename have to be that name
			$file = WWW_DIR.$requested_uri;
		}else{
			$file = WWW_DIR.$requested_uri;
			if(is_dir($file)){
				//If the request URI havn't and extension because is a directory, 
				//we add the final "/" to the uri and make a 301  redirect
				header("Location: ".$requested_uri."/",301);
				exit;	
			}
			//Request URI hasn't a valid extension, so we test with every combination of valid extenions to check if file exists
			foreach($this->valid_extensions as $ext_valida){
				$request_file =  WWW_DIR.$requested_uri.".$ext_valida";
				if(file_exists($request_file)){
					$file = $request_file;
					break;	
				}
			}
		}
		//If the filename of the 
		if(!is_file($file))return false;
		return $file;
	}
}?>
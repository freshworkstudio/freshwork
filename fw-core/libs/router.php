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
	function get_default_redirection($url){
		return $url;	
	}
	function route_request($url){
		$final_url = $this->get_redirection($url);
		if(!$final_url)$final_url = $this->get_default_redirection($url);
		
		$destination = $this->getFile($final_url);
		var_dump( $destination);
		//parse		
		if(strpos($destination,"?") !== false){
			$redirect = explode("?",$destination);
			$q = $redirect[1];
			$redirect = $redirect[0];
			parse_str($q,$_GET);
		}
		
		/*if(!is_file($destination)){
			trigger_error("Can't find the controller file for this request: $destination");	
			return false;
		}*/
		return $destination;
	}
	
	
	function getFile($url){
		if($url == "")$url = "index";
		if(strrchr($url,"/")== "/")$url .= "index";
		$path = pathinfo($url);
		$ext = (isset($path["extension"]))?$path["extension"]:"";	
		if(in_array($ext,$this->valid_extensions)){
			$file = WWW_DIR.$url;
			if(!is_file($file))return false;
		}else{
			$file = WWW_DIR.$url;
			foreach($this->valid_extensions as $ext_valida){
				$request_file =  WWW_DIR.$url.".$ext_valida";
				if(file_exists($request_file)){
					$file = $request_file;
					break;	
				}
			}
			if(is_dir($file)){
				$file = $this->routeURL($url."/");
				if(is_file($file)){
					header("Location: ".$url."/");
					exit;	
				}
			}
			if(!is_file($file))return false;
			
		}
		return $file;
	}
}?>
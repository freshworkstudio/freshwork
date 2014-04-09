<?php
namespace Freshwork;

class i18n{
	var $get = "lang";
	var $ses = "i18n";
	var $importado = false;
	var $default_lang = "en_US";
	var $lang_array = array();
	var $cur_lang;
	var $lang_name;
	var $disponibles = array();
	static private $instance = null;
	
	function i18n(){
		$this->determine_lang();
	}
	public static function instance(){
		if(self::$instance == null)self::$instance = new self();
		return self::$instance;
	}
	function import_folder($dir){
		$current_lang =$this->get_lang();
		if(file_exists($dir)){
			$finds = array();
			$directory= dir($dir);
			while ($file = $directory->read()){
				if($file != "." && $file != ".."){
					$l = pathinfo($file);
					if(0 !== $level = $this->get_coincidence_level($current_lang,$l["filename"]))$finds[$level]=$dir.$file;
				}
			}
			$directory->close();
		}
		ksort($finds);
		
		//Import language arrays
		foreach($finds as $file)$this->import_locale_file($file);
	}
	function get_coincidence_level($lang1,$lang2){
		$lang_parts = explode("_",str_replace("-","_",strtolower($lang1)));
		$maxlevel = count($lang_parts);
		if($lang2==$lang1)return $maxlevel;
		if($lang2==$lang_parts[0])return 1;
		return 0;
	}
	function add_lang_array($arr){
		$this->lang_array = array_merge($this->lang_array,$arr);
	}
	function set_lang($lang){
		$this->cur_lang = $lang;
		$_SESSION[$this->ses] = $lang;
	}
	
	function get_lang(){
		return apply_filters("get_language",$this->cur_lang);	
	}
	function determine_lang($set = true){
		$lang = apply_filters('get_default_lang',$this->default_lang);
		$lang = $this->default_lang;
		if(isset($_GET[$this->get])){
			$lang = ($_GET[$this->get]);
		}else{
			if(isset($_SESSION[$this->ses])){
				$lang = $_SESSION[$this->ses];	
			}else{
				$lang = $this->getDefaultLanguage();
			}
		}
		if($set)$this->set_lang($lang);
		return $lang;
	}
	function import_locale_file($file){
		if(file_exists($file)){
			include($file);
			$this->lang_name = $lang_name;
			$this->add_lang_array($lang);
		}else{
			trigger_error ( "No se encuentra archivo de idioma para '".$this->get_lang()."'" );
		}
	}
	function translate($str){
		$args = (is_array($str))?$str:func_get_args();
		$args[0] = html_entity_decode($args[0]);
		$args[0] = isset($this->lang_array[$args[0]])?$this->lang_array[$args[0]]:$args[0];
		return (call_user_func_array('sprintf',$args));
	}
	
	function getLink($lang){
		$link = $_SERVER['REQUEST_URI'];
		$part = explode("?",$link);
		if(isset($part[1])){
			$link = $part[0];	
			$gets = explode("=",$part[1]);
			//FALTA POR TERMINAR....
		}
		return $link;
	}
	#########################################################
	# Copyright © 2008 Darrin Yeager                        #
	# http://www.dyeager.org/                               #
	# Licensed under BSD license.                           #
	#   http://www.dyeager.org/downloads/license-bsd.php    #
	#########################################################
	
	function getDefaultLanguage() {
	   if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]))
		  return $this->parseDefaultLanguage($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
	   else
		  return $this->parseDefaultLanguage(NULL);
	   }
	
	function parseDefaultLanguage($http_accept) {
	   	if(isset($http_accept) && strlen($http_accept) > 1)  {
		  # Split possible languages into array
		  $x = explode(",",$http_accept);
		  foreach ($x as $val) {
			 #check for q-value and create associative array. No q-value means 1 by rule
			 if(preg_match("/(.*);q=([0-1]{0,1}\.\d{0,4})/i",$val,$matches))
				$lang[$matches[1]] = (float)$matches[2];
			 else
				$lang[$val] = 1.0;
		  }
		  
		  asort($lang,SORT_NUMERIC );
		  $lang = array_keys(($lang));
		  $lang = str_replace("-","_",array_pop($lang));
		  return $lang;
	   }
	   return ($this->default_lang);
	}
}
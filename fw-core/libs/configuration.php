<?php
/*


@TODO: Do $replace paramenter to start working
*/
class Configuration{
	var $configs;
	function __construct(){
		$this->configs = array();
	}
	function set_all($arr = false,$reemplazar = false){
		global $conf;
		if($arr === false)$arr = $conf;
		if($reemplazar){
			$this->configs = $arr;
		}else{
			foreach($arr as $key => $value){
				$this->configs[$key] = $value;
			}
		}
	}
	//It can receives an array of configurations in the first parameter. If the first parameter isn't and array, it will be the key of the configuration and the second parameter the value. if array: 2nd parameter set if has to @replace configurations. if isn't an array: the third paramenter defines if has to @replace. 
	function set(){
		$args = func_get_args();
		if(count($args) <= 0)trigger_error("The functions expects at least 1 parameter");
		if(is_array($args[0])){
			$replace = $args[1]?$args[1]:true;
			foreach($args[0] as $key => $value){
				$this->configs[$key] = $value;
			}
		}else{
			$replace = isset($args[2])?$args[2]:true;
			$this->configs[$args[0]] = $args[1];
		}
	}
	function get($key){
		if(!isset($this->configs[$key]))return;
		return $this->configs[$key];
	}
	
	function get_all(){
		$arr = array();
		foreach($this->configs as $key => $valor)$arr[$key] = $valor;
		return $arr;
	}
}
?>
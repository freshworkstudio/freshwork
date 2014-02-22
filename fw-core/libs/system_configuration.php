<?php
/*




@TODO: Do $replace paramenter to start working
*/
class System_Configuration{
	private static $instance;
	private static $configs;
	function __contruct(){
		self::$configs = array();
	}
	public static function instance(){
		if(is_null(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}
	public static function set_all($arr = false,$reemplazar = false){
		global $conf;
		if($arr === false)$arr = $conf;
		if($reemplazar){
			self::$configs = $arr;
		}else{
			foreach($arr as $key => $value){
				self::$configs[$key] = $value;
			}
		}
	}
	//It can receives an array of configurations in the first parameter. If the first parameter isn't and array, it will be the key of the configuration and the second parameter the value. if array: 2nd parameter set if has to @replace configurations. if isn't an array: the third paramenter defines if has to @replace. 
	public static function set(){
		$args = func_get_args();
		if(count($args) <= 0)trigger_error("The functions expects at least 1 parameter");
		if(is_array($args[0])){
			$replace = $args[1]?$args[1]:true;
			foreach($args[0] as $key => $value){
				self::$configs[$key] = $value;
			}
		}else{
			$replace = $args[2]?$args[2]:true;
			self::$configs[$args[0]] = $args[1];
		}
	}
	public static function get($key){
		if(!isset(self::$configs[$key]))return;
		return self::$configs[$key];
	}
	
	public static function get_all(){
		$arr = array();
		foreach(self::$configs as $key => $valor)$arr[$key] = $valor;
		return $arr;
	}
}
?>
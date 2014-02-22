<?php
class Model extends DB {
	protected $_model;
	function __construct() {
		global $conf,$inflect;
		$this->connect(get_config("DB.HOST"),get_config("DB.USER"),get_config("DB.PASSWORD"),get_config("DB.NAME"));
		$this->_development_env = get_config("APP.DEVELOPMENT_ENVIRONMENT");
		if(!isset($this->_model))$this->_model = apply_filters("model.default_model_name",get_class($this),$this);
		if(!isset($this->_table))$this->_table = strtolower($inflect->pluralize($this->_model));
		if (!isset($this->abstract)) {
			$this->_describe();
		}
		$this->clear();
	}
	function clear(){
		global $inflect,$conf;
		parent::clear();
		$this->_limit = apply_filters("model.paginate_limit",get_config("APP.PAGINATE_LIMIT"));
	}

	function __destruct() {
	}
	
	function paginador(){
			
	}
}

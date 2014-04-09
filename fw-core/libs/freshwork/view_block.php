<?php
namespace Freshwork;

/**
Completely based on ViewBlock.php file of CakePHP framework
https://github.com/cakephp/cakephp/blob/master/lib/Cake/View/ViewBlock.php
**/
class View_Block{
	protected $_blocks;
	protected $_active_blocks;
	protected $_discard_next;
	function __construct(){
		$this->_blocks = array();	
		$this->_active_blocks = array();
		$this->_discardNext = false;	
	}
	function start($code){
		trigger("before_start_block",$code);
		if (in_array($code, $this->_active_blocks)) {
			fw_error(__("A view block with the name '%s' is already/still open.", $code));
		}
		$this->_active_blocks[]=$code;
		ob_start();
		trigger("after_start_block",$code);
	}
	function start_if_empty($code) {
		trigger("before_start_if_empty_block",$code);
		if (empty($this->_blocks[$code])) {
			return $this->start($code);
		}
		$this->_discard_next = true;
		ob_start();
		trigger("after_start_if_empty_block",$code);
	}
	function end(){
		trigger("before_end_block");
		if ($this->_discard_next) {
			$this->_discard_next = false;
			ob_end_clean();
			return;
		}
		$active = end($this->_active_blocks);
		$content = ob_get_clean();
		if (!isset($this->_blocks[$active]))$this->_blocks[$active] = '';
		$this->_blocks[$active] .= $content;
		array_pop($this->_active_blocks);
		trigger("after_end_block",$active);
	}
	function concat($name, $value = null, $mode = 'append') {
		if (isset($value)) {
			if (!isset($this->_blocks[$name]))$this->_blocks[$name] = '';
			
			if ($mode === 'prepend') {
				$this->_blocks[$name] = $value . $this->_blocks[$name];
			} else {
				$this->_blocks[$name] .= $value;
			}
		} else {
			$this->start($name);
		}
	}
	function append($name, $value = null) {
		$this->concat($name, $value);
	}
	function set($code,$value){
		$this->_blocks[$code] = (string)$value;	
	}
	function get($code,$default='') {
		if (!isset($this->_blocks[$code]))return apply_filters('block_default',$default,$code);
		return apply_filters("get_{$code}_block",$this->_blocks[$code]);
	}
	function get_keys() {
		return array_keys($this->_blocks);
	}
	function get_active() {
		return end($this->_active_blocks);
	}
	function get_uncloseds() {
		return $this->_active_blocks;
	}
	
	function load($code,$file,$vars = array()){
		if(!is_file($file))
			fw_error("The view file '$file' doesn't exists");
		$this->start($code);
		extract($vars);
		include($file);
		$this->end();
	}
}
<?php
/**
Completely based on ViewBlock.php file of CakePHP framework
https://github.com/cakephp/cakephp/blob/master/lib/Cake/View/ViewBlock.php
**/
class ViewsHandler{
	protected $_blocks;
	protected $_active_blocks;
	protected $_discard_next;
	function __construct(){
		$this->_blocks = array();	
		$this->_active_blocks = array();
		$this->_discardNext = false;	
	}
	function start($code){
		if (in_array($name, $this->_active)) {
			fw_error(__("A view block with the name '%s' is already/still open.", $name));
		}
		$this->_active_blocks[]=$code;
		ob_start();
	}
	function start_if_empty($name) {
		if (empty($this->_blocks[$name])) {
			return $this->start($name);
		}
		$this->_discard_next = true;
		ob_start();
	}
	function end(){
		if ($this->_discard_next) {
			$this->_discardNext = false;
			ob_end_clean();
			return;
		}
		$active = end($this->_active_blocks);
		$content = ob_get_clean();
		if (!isset($this->_blocks[$active]))$this->_blocks[$active] = '';
		$this->_blocks[$active] .= $content;
		array_pop($this->_active_blocks);
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
	public function append($name, $value = null) {
		$this->concat($name, $value);
	}
	function set($code,$value){
		$this->_blocks[$code] = (string)$value;	
	}
	function get($code,$default='') {
		if (!isset($this->_blocks[$code]))return $default;
		return $this->_blocks[$code];
	}
	function get_keys() {
		return array_keys($this->_blocks);
	}
	function get_actives() {
		return end($this->_active_blocks);
	}
	function get_uncloseds() {
		return $this->_active_blocks;
	}
}
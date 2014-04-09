<?php
namespace Freshwork;

//This manages the main view of the app. 
class View{
	var $layout = false;
	var $view_file = false;
	var $content = "";
	var $layouts = array();
	
	function __construct(){
		
	}
	
	public function init(){
		$this->locate_default_layout();	
	}
	private function locate_default_layout(){
		if(is_ajax()){
			//is Ajax request
			 $this->set_layout( apply_filters("set_view_template_ajax",false)  );
		}else{
			//If it's a normal (not ajax) request...
			$template_file = apply_filters('default_view_template',false);
			if(!(bool)$template_file){
				$tpl_file = WWW_DIR."templates".DS."default.php";
				if(file_exists($tpl_file))$template_file=$tpl_file;
			}	
			$this->set_layout( apply_filters("set_view_template",$template_file) );
		}	
	}
	//View
	function set_view($file){
		$this->view_file = $file;
	}
	function get_view($file){
		return $this->view_file;	
	}
	//Layout
	public function set_layout($file){
		$this->layout = $file;
	}
	public function get_layout(){
		return $this->layout;	
	}
	

	public function load_view($vars = array()){
		trigger("before_load_content");
		load_block("content",$this->view_file,$vars);
		trigger("after_load_content");
	}
	
	public function render(){
		if($this->layout){
			load_block("layout",$this->layout);
			return get_block("layout");
		}else{
			return get_block("content");		
		}
	}
}
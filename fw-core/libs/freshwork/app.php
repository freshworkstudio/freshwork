<?php
namespace Freshwork;
class App{
	protected $is_404;
	public $hooks,$request,$router,$cache,$i18n,$blocks,$view;
	
	function __construct(){
		
	}
	function init(){
		$this->hooks 		= new Hook();
		$this->request 	= new Request();
		$this->router 		= new Router();
		$this->cache 		= new Cache();
		$this->i18n 		= new i18n();
		$this->blocks		= new View_Block();
		$this->view 		= new View();	
		$this->plugins 	= new Plugins();	
	}
	
	function set_404($value = true){
		$this->	is_404 = $value;
	}
	function is_404(){
		return $this->is_404;
	}	
	
}
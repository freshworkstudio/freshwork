<?php
namespace Freshwork;
//The plugins controller
class Plugins{	
	protected $plugins;
	
	function init(){
		$this->plugins = apply_filters('fw_plugins_list',$this->get_all());
	}
	public function get_all(){
		global $app;
		$plugins = array();
		if(get_config("APP.DEVELOPMENT_ENVIRONMENT") || !$app->cache->has('fw-plugins')){
			$plugins = $this->refresh_plugin_list();
			$app->cache->set('fw-plugins',$plugins);
		}else{
			$plugins = $app->cache->get('fw-plugins');
		}
		
		return $plugins;
	}
	static function refresh_plugin_list(){
		$dirs = get_dirs(PLUGINS_DIR);
		foreach($dirs as $plugin){
			$plugins[$plugin] = new Plugin($plugin,PLUGINS_DIR);
		}
		return $plugins;
	}
	public function boot(){
		foreach($this->plugins as $plugin){
			if($plugin->is_enabled()){
				$plugin->boot();
			}
		}
	}
}


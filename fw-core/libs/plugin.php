<?php
namespace Freshwork;
class Plugins{	
	static function get_list(){
		global $app;
		$plugins = array();
		if(get_config("APP.DEVELOPMENT_ENVIRONMENT") || $app->cache->get('fw-plugins') == NULL){
			$plugins = self::refresh_plugin_list();
			$app->cache->set('fw-plugins',$plugins);
		}else{
			$plugins = $app->cache->get('fw-plugins');
		}
		
		return $plugins;
	}
	static function refresh_plugin_list(){
		$plugins = array();
		if(file_exists(PLUGINS_DIR)){
			if ($gestor = opendir(PLUGINS_DIR)) {
				/* Esta es la forma correcta de iterar sobre el directorio. */
				while (false !== ($entrada = readdir($gestor))) {
					if($entrada != ".." && $entrada != "." && substr($entrada,0,1) != "_" && substr($entrada,0,1) != "."){
						$pfile = PLUGINS_DIR.$entrada.DS."boot.php";
						$plugins[$entrada] = get_plugin_data($pfile);
						$plugins[$entrada] = array(
							"name" 			=> $entrada,
							"enabled" 		=> true,
							"directory" 	=> PLUGINS_DIR.$entrada.DS,
							"url"			=> PLUGINS_URL.$entrada."/",
							"boot_file"	=> $pfile);
					}
				}
			 
				closedir($gestor);
			}
		}
		return $plugins;
	}
	static function init(){
		global $the_plugin;
		$plugins = apply_filters('fw_plugins_list',self::get_list());
		set_info("plugins_list",$plugins);
		foreach($plugins as $plugin){
			if($plugin["enabled"] == true){
				$the_plugin = $plugin; 
				include($plugin["boot_file"]);
			}
		}
	}
}

/**
 * Parse the plugin contents to retrieve plugin's metadata.
 *
 * The metadata of the plugin's data searches for the following in the plugin's
 * header. All plugin data must be on its own line. For plugin description, it
 * must not have any newlines or only parts of the description will be displayed
 * and the same goes for the plugin data. The below is formatted for printing.
 *
 * <code>
 * /*
 * Plugin Name: Name of Plugin
 * Plugin URI: Link to plugin information
 * Description: Plugin Description
 * Author: Plugin author's name
 * Author URI: Link to the author's web site
 * Version: Must be set in the plugin for WordPress 2.3+
 * Text Domain: Optional. Unique identifier, should be same as the one used in
 *		plugin_text_domain()
 * Domain Path: Optional. Only useful if the translations are located in a
 *		folder above the plugin's base path. For example, if .mo files are
 *		located in the locale folder then Domain Path will be "/locale/" and
 *		must have the first slash. Defaults to the base folder the plugin is
 *		located in.
 * Network: Optional. Specify "Network: true" to require that a plugin is activated
 *		across all sites in an installation. This will prevent a plugin from being
 *		activated on a single site when Multisite is enabled.
 *  * / # Remove the space to close comment
 * </code>
 *
 * Plugin data returned array contains the following:
 *		'Name' - Name of the plugin, must be unique.
 *		'Title' - Title of the plugin and the link to the plugin's web site.
 *		'Description' - Description of what the plugin does and/or notes
 *		from the author.
 *		'Author' - The author's name
 *		'AuthorURI' - The authors web site address.
 *		'Version' - The plugin version number.
 *		'PluginURI' - Plugin web site address.
 *
 * Some users have issues with opening large files and manipulating the contents
 * for want is usually the first 1kiB or 2kiB. This function stops pulling in
 * the plugin contents when it has all of the required plugin data.
 *
 * The first 8kiB of the file will be pulled in and if the plugin data is not
 * within that first 8kiB, then the plugin author should correct their plugin
 * and move the plugin data headers to the top.
 *
 * The plugin file is assumed to have permissions to allow for scripts to read
 * the file. This is not checked however and the file is only opened for
 * reading.
 *
 * @link http://trac.wordpress.org/ticket/5651 Previous Optimizations.
 * @link http://trac.wordpress.org/ticket/7372 Further and better Optimizations.
 * @since 1.5.0
 *
 * @param string $plugin_file Path to the plugin file
 * @param bool $markup Optional. If the returned data should have HTML markup applied. Defaults to true.
 * @param bool $translate Optional. If the returned data should be translated. Defaults to true.
 * @return array See above for description.
 */
function get_plugin_data( $plugin_file, $markup = true, $translate = true ) {

	$default_headers = array(
		'name' 			=> 'Plugin Name',
		'plugin_url' 	=> 'Plugin URL',
		'version' 		=> 'Version',
		'description' 	=> 'Description',
		'author' 		=> 'Author',
		'author_url' 	=> 'Author URL',
		'license'		=> 'License'
	);

	$plugin_data = get_file_data( $plugin_file, $default_headers, 'plugin' );
	return $plugin_data;
}
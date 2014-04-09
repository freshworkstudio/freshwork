<?php
/*
@TODO: Make $this available. (Plugin Object)
*/
namespace fw_panel;
//ADD NEW ROUTES
add_filter("url_routes",function($routes){
	$routes["fw-panel(.*)"] = $this->get_directory()."www$1";
	
	return $routes;
});

//ADD NEW CALLBACKS
add_filter("map_callbacks",function ($callbacks){
	$callbacks["fw-panel(.*)"] = function(){
		//SET ADMIN TEMPLATE
		add_filter("set_view_template",function($routes){
			global $fw_panel_plugin_data;
			if(!is_404()){
				return $this->get_directory()."templates/admin.php";
			}
		});
	};
	return $callbacks;
});


include("functions.php");

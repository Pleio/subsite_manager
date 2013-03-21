<?php

	/**
	* Elgg administration plugin screen
	*
	* Shows a list of plugins that can be sorted and filtered.
	*
	* @package Elgg.Core
	* @subpackage Admin.Plugins
	*/
	
	elgg_load_js('lightbox');
	elgg_load_css('lightbox');
	
	if(!subsite_manager_on_subsite()){
		elgg_generate_plugin_entities();
	}
	$installed_plugins = elgg_get_plugins('any');
	
	if(subsite_manager_is_superadmin_logged_in() && (get_input("advanced") == "yes")){
		echo elgg_view("admin/plugins/advanced", array("installed_plugins" => $installed_plugins));
	} else {
		echo elgg_view("admin/plugins/simple", array("installed_plugins" => $installed_plugins));
	}

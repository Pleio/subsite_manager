<?php 

	// can only be viewed on main site
	if(subsite_manager_on_subsite()){
		forward("admin");
	}
	
	echo elgg_view_module("info", "", elgg_echo("subsite_manager:subsite:plugins:description", array("<a class='elgg-button elgg-button-action' href='javascript:void(0);' onclick='$(\"#subsite-manager-plugins-list\").show();'>", "</a>")));
	echo "<br />";
	
	// regenerate plugin list
	elgg_generate_plugin_entities();
	if($all_plugins = elgg_get_plugins("all")){
		$plugins = array();
		
		foreach($all_plugins as $plugin){
			$plugins[$plugin->getID()] = $plugin;
		}
		
		unset($all_plugins);
		ksort($plugins);
	} else {
		$plugins = false;
	}
	
	
	$form_vars = array(
		"id" => "subsite-manager-plugins-list",
		"class" => "hidden"
	);
	$body_vars = array(
		"plugins" => $plugins,
		"subsites_count" => subsite_manager_get_subsites(0, 0, true),
		"counters" => subsite_manager_count_enabled_plugins($plugins),
		"required_plugins" => subsite_manager_get_required_plugins()
	);
	
	echo elgg_view_form("subsites/plugins", $form_vars, $body_vars);

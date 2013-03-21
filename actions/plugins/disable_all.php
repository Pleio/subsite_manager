<?php 

	// this could take a while
	set_time_limit(0);

	// get the plugin name
	$plugin = get_input("plugin");
	
	// only on main site
	if(!subsite_manager_on_subsite()){
		// is this a valid plugin
		if(!empty($plugin) && elgg_get_plugin_from_id($plugin)){
			// are there any active plugins
			if($relationships = subsite_manager_get_active_plugin_relationships($plugin)){
				$error_count = 0;
				
				// remove relationships
				foreach($relationships as $rel){
					if(!delete_relationship($rel->getSystemLogID())){
						$error_count++;
					}
				}
				
				if($error_count == 0){
					system_message(elgg_echo("subsite_manager:actions:plugins:disable_all:success"));
				} else {
					register_error(elgg_echo("subsite_manager:actions:plugins:disable_all:error:some_errors"));
				}
			} else {
				register_error(elgg_echo("subsite_manager:actions:plugins:disable_all:error:no_active"));
			}
		} else {
			register_error(elgg_echo("PluginException:InvalidID"), array($plugin));
		}
	} else {
		register_error(elgg_echo("subsite_manager:action:error:on_subsite"));
	}
	
	forward(REFERER);
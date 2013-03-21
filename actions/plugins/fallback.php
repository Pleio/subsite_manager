<?php 

	$guid = (int) get_input("guid");
	
	if(subsite_manager_on_subsite()){
		if(!empty($guid)){
			if(($plugin = get_entity($guid)) && elgg_instanceof($plugin, "object", "plugin", "ElggPlugin")){
				if($plugin->unsetAllSettings()){
					system_message(elgg_echo("subsite_manager:actions:plugins:fallback:success"));
				} else {
					register_error(elgg_echo("subsite_manager:actions:plugins:fallback:error:unset"));
				}
			} else {
				register_error(elgg_echo("InvalidClassException:NotValidElggStar", array($guid, "ElggPlugin")));
			}
		} else {
			register_error(elgg_echo("InvalidParameterException:MissingParameter"));
		}
	} else {
		register_error(elgg_echo("subsite_manager:actions:plugins:fallback:error:subsite"));
	}
	
	forward(REFERER);
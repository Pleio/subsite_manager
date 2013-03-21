<?php 

	if(subsite_manager_on_subsite()){
		$subsite = elgg_get_site_entity();
		
		$params = get_input("params");
		
		if($subsite->setPrivateSetting("main_profile_fields_configuration", json_encode($params))){
			system_message(elgg_echo("subsite_manager:action:main_profile_fields:success"));
		} else {
			register_error(elgg_echo("subsite_manager:action:main_profile_fields:error:save"));
		}
	} else {
		register_error(elgg_echo("subsite_manager:action:error:subsite_only"));
	}
	
	forward(REFERER);
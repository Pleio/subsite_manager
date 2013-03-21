<?php 

	if(!subsite_manager_on_subsite()){
		
		// get settings
		$enabled_everywhere = get_input("enabled_everywhere");
		$subsite_default_manageable = get_input("subsite_default_manageable");
		$fallback_to_main_settings = get_input("fallback_to_main_settings");
		$use_global_usersettings = get_input("use_global_usersettings");
		$enabled_for_new_subsites = get_input("enabled_for_new_subsites");
		
		// get site
		$site = elgg_get_site_entity();
		
		if(!empty($enabled_everywhere)){
			$enabled_everywhere = implode($enabled_everywhere, ",");
		}
		
		if(!empty($subsite_default_manageable)){
			$subsite_default_manageable = implode($subsite_default_manageable, ",");
		}
		
		if(!empty($fallback_to_main_settings)){
			$fallback_to_main_settings = implode($fallback_to_main_settings, ",");
		}
		
		if(!empty($use_global_usersettings)){
			$use_global_usersettings = implode($use_global_usersettings, ",");
		}
		
		if(!empty($enabled_for_new_subsites)){
			$enabled_for_new_subsites = implode($enabled_for_new_subsites, ",");
		}
		
		$site->setPrivateSetting("enabled_everywhere", $enabled_everywhere);
		$site->setPrivateSetting("subsite_default_manageable", $subsite_default_manageable);
		$site->setPrivateSetting("fallback_to_main_settings", $fallback_to_main_settings);
		$site->setPrivateSetting("use_global_usersettings", $use_global_usersettings);
		$site->setPrivateSetting("enabled_for_new_subsites", $enabled_for_new_subsites);
		
		system_message(elgg_echo("subsite_manager:action:plugins:manage:success"));
	} else {
		register_error(elgg_echo("subsite_manager:action:error:on_subsite"));
	}
	
	forward(REFERER);
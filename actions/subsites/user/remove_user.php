<?php 

	$site_guid = (int) get_input("site_guid", elgg_get_site_entity()->getGUID());
	$user_guid = (int) get_input("user_guid", elgg_get_logged_in_user_guid());
	
	if(($subsite = get_entity($site_guid)) && ($user = get_user($user_guid))){
		if(elgg_instanceof($subsite, "site", Subsite::SUBTYPE, "Subsite")){
			if($subsite->removeUser($user->getGUID())){
				system_message(elgg_echo("subsite_manager:action:subsites:remove_user:success", array($user->name, $subsite->name)));
			} else {
				register_error(elgg_echo("subsite_manager:action:subsites:remove_user:error:remove", array($user->name, $subsite->name)));
			}
		} else {
			register_error(elgg_echo("InvalidClassException:NotValidElggStar", array($site_guid, "Subsite")));
		}
	} else {
		register_error(elgg_echo("InvalidParameterException:MissingParameter"));
	}
	
	forward(REFERER);
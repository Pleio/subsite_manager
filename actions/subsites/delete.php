<?php

	$guid = (int) get_input("guid");
	$user = elgg_get_logged_in_user_entity();

	// delete subsite only some admins
	$allowed_admins = array(
		"ziemerink",
		"bartjeu",
// 		"jdalsem",
// 		"jeabakker"
	);

	if(!in_array($user->username, $allowed_admins)){
		forward(REFERER);
	}
	set_time_limit(0);
	if(!subsite_manager_on_subsite()){
		if(!empty($guid) && ($site = get_entity($guid))){
			if(elgg_instanceof($site, "site", Subsite::SUBTYPE, "Subsite")){
				if($site->delete()){
					system_message(elgg_echo("entity:delete:success", array($site->name)));
				} else {
					register_error(elgg_echo("entity:delete:fail", array($site->name)));
				}
			} else {
				register_error(elgg_echo("InvalidClassException:NotValidElggStar", array("Subsite")));
			}
		} else {
			register_error(elgg_echo("InvalidParameterException:GUIDNotFound", array($guid)));
		}
	} else {
		register_error(elgg_echo("subsite_manager:action:error:on_subsite"));
	}

	forward(REFERER);

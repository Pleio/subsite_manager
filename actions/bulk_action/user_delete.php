<?php

	$user_guids = get_input("user_guids");
	
	if (!subsite_manager_on_subsite() && subsite_manager_is_superadmin_logged_in()) {
		if (!empty($user_guids)) {
			// this could take a while
			set_time_limit(0);
			
			if (!is_array($user_guids)) {
				$user_guids = array($user_guids);
			}
			
			foreach ($user_guids as $user_guid) {
				if ($user = get_user($user_guid)) {
					$name = $user->name;
					
					if ($user->delete()) {
						system_message(elgg_echo("admin:user:delete:yes", array($name)));
					}
				}
			}
		} else {
			register_error(elgg_echo("InvalidParameterException:MissingParameter"));
		}
	} else {
		register_error(elgg_echo("subsite_manager:action:error:on_subsite"));
	}
	
	forward(REFERER);
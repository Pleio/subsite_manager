<?php 

	$site = elgg_get_site_entity();
	$user_guid = (int) get_input("user_guid");
	
	if(!empty($user_guid) && elgg_is_admin_logged_in() && subsite_manager_on_subsite()){
		if($site->isAdmin($user_guid)){
			$site->removeAdmin($user_guid);
			system_message(elgg_echo("admin:user:removeadmin:yes"));
		} else {
			$site->makeAdmin($user_guid);
			system_message(elgg_echo("admin:user:makeadmin:yes"));
		}
		
	} else {
		register_error(elgg_echo("InvalidParameterException:NoEntityFound"));
	}
	
	forward(REFERER);
<?php 

	$user_guid = (int) get_input("user_guid");
	$email = get_input("email");
	
	if(subsite_manager_on_subsite()){
		if(!empty($user_guid) || !empty($email)){
			$site = elgg_get_site_entity();
			
			if($site->removeInvitation($user_guid, $email)){
				system_message(elgg_echo("subsite_manager:action:invite:revoke:success"));
			} else {
				register_error(elgg_echo("subsite_manager:action:invite:revoke:error"));
			}
		} else {
			register_error(elgg_echo("subsite_manager:action:error:input"));
		}
	} else {
		register_error(elgg_echo("subsite_manager:action:error:subsite_only"));
	}
	
	forward(REFERER);
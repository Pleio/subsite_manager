<?php 

	$site = elgg_get_site_entity();
	$annotation_id = (int) get_input("annotation_id");
	
	if(subsite_manager_on_subsite() && !empty($annotation_id)){
		if($site->declineMembershipRequest($annotation_id)){
			system_message(elgg_echo("subsite_manager:action:subsite:membership:decline:success"));
		} else {
			register_error(elgg_echo("subsite_manager:action:subsite:membership:decline:error"));
		}
	} else {
		register_error(elgg_echo("subsite_manager:action:error:subsite_only"));
	}
	
	forward(REFERER);
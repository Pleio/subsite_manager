<?php 

	$guid = get_input("guid");
	
	if(!empty($guid)){
		if(($subsite = elgg_get_site_entity($guid)) && elgg_instanceof($subsite, "site", Subsite::SUBTYPE, "Subsite")){
			if($subsite->canEdit()){
				if($subsite->featured){
					unset($subsite->featured);
					$msg = elgg_echo("subsite_manager:actions:subsites:toggle_featured:success:unfeatured");
				} else {
					$subsite->featured = time();
					$msg = elgg_echo("subsite_manager:actions:subsites:toggle_featured:success:featured");
				}
				
				system_message($msg);
			} else {
				register_error(elgg_echo("InvalidParameterException:GUIDNotFound", array($guid)));
			}
		} else {
			register_error(elgg_echo("InvalidClassException:NotValidElggStar", array($guid, "Subsite")));
		}
	} else {
		register_error(elgg_echo("InvalidParameterException:MissingParameter"));
	}
	
	forward(REFERER);
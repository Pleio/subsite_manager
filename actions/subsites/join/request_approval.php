<?php 

	$subsite_guid = (int) get_input("subsite_guid");
	$reason = get_input("reason");
	
	$forward_url = REFERER;
	
	if(!empty($subsite_guid)){
		if(($subsite = elgg_get_site_entity($subsite_guid)) && elgg_instanceof($subsite, "site", Subsite::SUBTYPE, "Subsite")){
			// handle missing profile fields here
			if(subsite_manager_set_missing_subsite_profile_fields()){
				if($subsite->requestMembership($reason)){
					// cleanup some stuff
					elgg_clear_sticky_form("subsite_missing_profile_fields");
					
					if(isset($_SESSION["last_forward_from"])){
						unset($_SESSION["last_forward_from"]);
					}
					
					if(isset($_SESSION["no_access_forward_from"])){
						unset($_SESSION["no_access_forward_from"]);
					}
					
					// forward to the main site
					$forward_url = $subsite->getOwnerEntity()->url . "subsites";
					
					var_dump(get_input('action'));
					exit();
 					system_message(elgg_echo("subsite_manager:actions:subsites:join:request_approval:success"));
				} else {
					register_error(elgg_echo("subsite_manager:actions:subsites:join:request_approval:error:request"));
				}
			} else {
				register_error(elgg_echo("subsite_manager:action:subsites:join:error:missing_fields"));
			}
		} else {
			register_error(elgg_echo("InvalidClassException:NotValidElggStar", array($subsite_guid, "Subsite")));
		}
	} else {
		register_error(elgg_echo("InvalidParameterException:MissingParameter"));
	}
	
	forward($forward_url);
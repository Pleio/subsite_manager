<?php 

	gatekeeper();
	
	$site_guid = (int) get_input("site_guid", elgg_get_site_entity()->getGUID());
	$user_guid = (int) get_input("user_guid", elgg_get_logged_in_user_guid());
	
	$forward_url = REFERER;
	
	if(($subsite = get_entity($site_guid)) && ($user = get_user($user_guid))){
		if(elgg_instanceof($subsite, "site", Subsite::SUBTYPE, "Subsite")){
			if($subsite->canJoin()){
				if(subsite_manager_set_missing_subsite_profile_fields($user_guid)){
					if($subsite->addUser($user->getGUID())){
						elgg_clear_sticky_form("subsite_missing_profile_fields");
						
						// where should we forward the user to
						if(isset($_SESSION["no_access_forward_from"])){
							// did we get here by access validation
							$forward_url = $_SESSION["no_access_forward_from"];
							unset($_SESSION["no_access_forward_from"]);
						} elseif(isset($_SESSION["last_forward_from"])){
							// did we get here by a gatekeeper
							$forward_url = $_SESSION["last_forward_from"];
							unset($_SESSION["last_forward_from"]);
						} else {
							$forward_url = $subsite->getURL();
						}
						
						system_message(elgg_echo("subsite_manager:action:subsites:add_user:success", array($user->name, $subsite->name)));
					} else {
						register_error(elgg_echo("subsite_manager:action:subsites:add_user:error:add", array($user->name, $subsite->name)));
					}
				} else {
					register_error(elgg_echo("subsite_manager:action:subsites:join:error:missing_fields"));
				}
			} else {
				register_error(elgg_echo("subsite_manager:action:subsites:add_user:error:join", array($user->name, $subsite->name)));
			}
		} else {
			register_error(elgg_echo("InvalidClassException:NotValidElggStar", array($site_guid, "Subsite")));
		}
	} else {
		register_error(elgg_echo("InvalidParameterException:MissingParameter"));
	}
	
	forward($forward_url);
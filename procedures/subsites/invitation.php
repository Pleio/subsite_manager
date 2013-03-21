<?php 

	$user_guid = (int) get_input("user");
	$site_guid = (int) get_input("site");
	$code = get_input("code");
	
	$forward_url = "subsites";
	
	if(!empty($user_guid) && !empty($site_guid) && !empty($code)){
		if(($user = get_user($user_guid)) && ($site = elgg_get_site_entity($site_guid))){
			if(elgg_instanceof($site, "site", Subsite::SUBTYPE, "Subsite")){
				$secret = get_site_secret();
				$s_code = md5($user_guid . $secret . $site_guid);
	
				if($code == $s_code){
					if($site->hasInvitation($user_guid)){
						if($site->addUser($user_guid)){
							$forward_url = $site->url;
								
							system_message(elgg_echo("subsite_manager:procedures:subsites:invitation:success", array($site->name)));
						} else {
							register_error(elgg_echo("subsite_manager:procedures:subsites:invitation:error:add_user"));
						}
					} else {
						register_error(elgg_echo("subsite_manager:procedures:subsites:invitation:error:invitation"));
					}
				} else {
					register_error(elgg_echo("subsite_manager:procedures:subsites:invitation:error:code"));
				}
			} else {
				register_error(elgg_echo("InvalidParameterException:GUIDNotFound", array($site_guid)));
			}
		} else {
			register_error(elgg_echo("InvalidParameterException:NoEntityFound"));
		}
	} else {
		register_error(elgg_echo("InvalidParameterException:MissingParameter"));
	}
	
	forward($forward_url);
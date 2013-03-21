<?php 

	$site_guid = (int) get_input("site");
	$user_guid = (int) get_input("user");
	$code = get_input("code");
	
	$forward_url = "subsites";
	
	if(!empty($user_guid) && !empty($site_guid) && !empty($code)){
		if(($site = elgg_get_site_entity($site_guid)) && ($user = get_user($user_guid))){
			if(elgg_instanceof($site, "site", Subsite::SUBTYPE, "Subsite")){
				$secret = get_site_secret();
				$s_code = md5($user->getGUID() . $secret . $site->getGUID());
	
				if($code == $s_code){
					if($site->addUser($user->getGUID())) {
						
						if(isset($_SESSION["no_access_forward_from"])){
							// did we get here by access validation
							$forward_url = $_SESSION["no_access_forward_from"];
							unset($_SESSION["no_access_forward_from"]);
						} elseif(isset($_SESSION["last_forward_from"])){
							// check last forward from
							$forward_url = $_SESSION["last_forward_from"];
							unset($_SESSION["last_forward_from"]);
						} else {
							$forward_url = $site->getURL();
						}
						
						system_message(elgg_echo("subsite_manager:subsites:join_domain:success", array($site->name)));
					} else {
						register_error(elgg_echo("subsite_manager:subsites:join_domain:error:add_user"));
					}
				} else {
					register_error(elgg_echo("subsite_manager:subsites:join_domain:error:code"));
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
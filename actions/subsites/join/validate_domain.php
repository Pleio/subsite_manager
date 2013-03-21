<?php 

	$subsite_guid = (int) get_input("subsite_guid");
	$email = get_input("email");
	
	$user = elgg_get_logged_in_user_entity();
	$site_secret = get_site_secret();
	
	$forward_url = REFERER;
	
	if(!empty($subsite_guid)){
		if(($subsite = elgg_get_site_entity($subsite_guid)) && elgg_instanceof($subsite, "site", Subsite::SUBTYPE, "Subsite")){
			try {
				if(validate_email_address($email)){
					// handle missing profile fields here
					if(subsite_manager_set_missing_subsite_profile_fields()){
						if($subsite->validateEmailDomain($user->getGUID(), $email)){
							$code = md5($user->getGUID() . $site_secret . $subsite_guid);
							
							$link = $subsite->getOwnerEntity()->url . "subsites/join_domain?user=" . $user->getGUID() . "&site=" . $subsite_guid . "&code=" . $code;
							
							$subject = elgg_echo("subsite_manager:subsites:join:validate_domain:subject", array($subsite->name));
							$message = elgg_echo("subsite_manager:subsites:join:validate_domain:message", array($user->name, $subsite->name, $link));
							
							$old_email = $user->email;
							$user->email = $email;
							$user->save();
							
							notify_user($user->getGUID(), $subsite->getGUID(), $subject, $message, null, "email");
							
							$user->email = $old_email;
							$user->save();
							
							// cleanup some stuff
							elgg_clear_sticky_form("subsite_missing_profile_fields");
							
							// forward to the main site
							$forward_url = $subsite->getOwnerEntity()->url . "subsites";
							
							system_message(elgg_echo("subsite_manager:actions:subsites:join:validate_domain:success"));
						} else {
							register_error(elgg_echo("subsite_manager:actions:subsites:join:validate_domain:error:domain"));
						}
					} else {
						register_error(elgg_echo("subsite_manager:action:subsites:join:error:missing_fields"));
					}
				} else {
					register_error(elgg_echo("registration:notemail"));
				}
			} catch(RegistrationException $e){
				register_error($e->getMessage());
			}
		} else {
			register_error(elgg_echo("InvalidClassException:NotValidElggStar", array($subsite_guid, "Subsite")));
		}
	} else {
		register_error(elgg_echo("InvalidParameterException:MissingParameter"));
	}
	
	forward($forward_url);
	
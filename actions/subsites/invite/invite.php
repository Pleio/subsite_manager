<?php 

	/**
	 * Invite users
	 * Eighter existing users (by guid) or new users (by email)
	 * 
	 * Or by CSV upload, if so goto step 2
	 */

	elgg_make_sticky_form("subsite_manager_invite");
	
	$forward_url = REFERER;
	
	if(subsite_manager_on_subsite()){
		$user_guids = get_input("user_guids");
		$emails = get_input("user_guids_email");
		$csv = get_uploaded_file("csv");
		$message = get_input("message");
		
		$site = elgg_get_site_entity();
		$parent_site = $site->getOwnerEntity();
		$loggedin_user = elgg_get_logged_in_user_entity();
		$secret = get_site_secret();
		
		if(!empty($user_guids) || !empty($emails) || !empty($csv)){
			$cleanup_stick_form = true;
			$invited_users = 0;
			
			// handle user guids
			if(!empty($user_guids)){
				foreach($user_guids as $user_guid){
					if($user = get_user($user_guid)){
						// check if we have apending membership request
						if($id = $site->pendingMembershipRequest($user->getGUID())){
							$site->approveMembershipRequest($id);
						} elseif($site->createInvitation($user->getGUID())){
							$invited_users++;
				
							// @todo check this code
							$code = md5($user->getGUID() . $secret . $site->getGUID());
							$join_link = $parent_site->url . "subsites/invitation?site=" . $site->getGUID() . "&user=" . $user->getGUID() . "&code=" . $code;
				
							$subject = elgg_echo("subsite_manager:subsite_admin:invite:existing_user:subject", array($site->name));
							$new_message = elgg_echo("subsite_manager:subsite_admin:invite:existing_user:message", array(
								$user->name,
								$loggedin_user->name,
								$site->name,
								$message,
								$join_link
							));
				
							notify_user($user->getGUID(), $site->getGUID(), $subject, $new_message, null, "email");
						}
					}
				}
			}
			
			// handle email addresses
			if(!empty($emails)){
				foreach($emails as $email){
					if(!empty($email) && is_email_address($email)){
						if($users = get_user_by_email($email)){
							// found an existing user
							$user = $users[0];
							$user_guid = $user->getGUID();
								
							if($id = $site->pendingMembershipRequest($user_guid)){
								$site->approveMembershipRequest($id);
							} elseif($site->createInvitation($user_guid)){
								$invited_users++;
									
								// @todo check this code
								$code = md5($user_guid . $secret . $site->getGUID());
								$join_link = $parent_site->url . "subsites/invitation?site=" . $site->getGUID() . "&user=" . $user_guid . "&code=" . $code;
									
								$subject = elgg_echo("subsite_manager:subsite_admin:invite:existing_user:subject", array($site->name));
								$new_message = elgg_echo("subsite_manager:subsite_admin:invite:existing_user:message", array(
									$user->name,
									$loggedin_user->name,
									$site->name,
									$message,
									$join_link
								));
									
								notify_user($user_guid, $site->getGUID(), $subject, $new_message, null, "email");
							}
						} else {
							// invite by email
							if($site->createInvitation(null, $email)){
								// can we create a nice name
								if(($name_column >= 0) && !empty($data[$name_column])){
									$email = $name . "<" . $email . ">";
								}
									
								if(subsite_manager_subsite_invite_email($email, $message)){
									$invited_users++;
								}
							}
						}
					}
				}
			}
			
			// handle csv
			if(!empty($csv)){
				$tmp_location = $_FILES["csv"]["tmp_name"];
				
				if($fh = fopen($tmp_location, "r")){
					if(($data = fgetcsv($fh, 0, ";")) !== false){
						$cleanup_stick_form = false;
						
						$temp_file = tempnam(sys_get_temp_dir(), "subsite_invite_" . $site->getGUID());
						move_uploaded_file($tmp_location, $temp_file);
						
						$_SESSION["subsite_manager_csv"] = array(
							"column" => $data,
							"location" => $temp_file
						);
						
						$forward_url = "admin/users/invite_csv";
					}
				}
			}
			
			// report to user
			if(($invited_users > 0) || !$cleanup_stick_form){
				if($cleanup_stick_form){
					elgg_clear_sticky_form("subsite_manager_invite");
				}
				
				if(($invited_users > 0) && !$cleanup_stick_form){
					// invited users and continue to csv
					system_message(elgg_echo("subsite_manager:action:invite:success:users_csv", array($invited_users)));
				} elseif($invited_users > 0){
					// invited users
					system_message(elgg_echo("subsite_manager:action:invite:success:users", array($invited_users)));
				} elseif(!$cleanup_stick_form){
					// continue to csv
					system_message(elgg_echo("subsite_manager:action:invite:success:csv"));
				}
			} else {
				register_error(elgg_echo("subsite_manager:action:invite:error:users"));
			}
		} else {
			register_error(elgg_echo("subsite_manager:action:error:input"));
		}
	} else {
		register_error(elgg_echo("subsite_manager:action:error:subsite_only"));
	}
	
	forward($forward_url);
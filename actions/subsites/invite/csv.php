<?php 

	/**
	 * Invite users by CSV upload (this is step 2)
	 */

	elgg_make_sticky_form("subsite_manager_invite");
	
	$forward_url = REFERER;
	
	if(subsite_manager_on_subsite()){
		if(!empty($_SESSION["subsite_manager_csv"])){
			$name_column = (int) get_input("name");
			$email_column = (int) get_input("email");
			$message = get_input("message");
			
			if($email_column >= 0){
				$site = elgg_get_site_entity();
				$parent_site = $site->getOwnerEntity();
				$loggedin_user = elgg_get_logged_in_user_entity();
				$secret = get_site_secret();
				
				$sample_data = elgg_extract("column", $_SESSION["subsite_manager_csv"]);
				$tmp_location = elgg_extract("location", $_SESSION["subsite_manager_csv"]);
				
				if($email_column < count($sample_data)){
					if($fh = fopen($tmp_location, "r")){
						$invited = 0;
						
						// show hidden (unvalidated) users
						$hidden = access_get_show_hidden_status();
						access_show_hidden_entities(true);
						
						while(($data = fgetcsv($fh, 0, ";")) !== false){
							$email = trim($data[$email_column]);
							
							if(!empty($email) && is_email_address($email)){
								if($users = get_user_by_email($email)){
									// found an existing user
									$user = $users[0];
									$user_guid = $user->getGUID();
										
									if($id = $site->pendingMembershipRequest($user_guid)){
										$site->approveMembershipRequest($id);
									} elseif($site->createInvitation($user_guid)){
										$invited++;
										
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
											$invited++;
										}
									}
								}
							}
						}
						
						// restore hidden users
						access_show_hidden_entities($hidden);
						
						if($invited > 0){
							unlink($tmp_location);
							elgg_clear_sticky_form("subsite_manager_invite");
							unset($_SESSION["subsite_manager_csv"]);
							
							$forward_url = "admin/users/invite";
							
							system_message(elgg_echo("subsite_manager:action:invite:csv:success", array($invited)));
						} else {
							register_error(elgg_echo("subsite_manager:action:invite:csv:error:users"));
						}
					} else {
						register_error(elgg_echo("subsite_manager:action:invite:csv:error:csv"));
					} 
				} else {
					register_error(elgg_echo("subsite_manager:action:invite:csv:error:email_column:invalid"));
				}
			} else {
				register_error(elgg_echo("subsite_manager:action:invite:csv:error:email_column"));
			}
		} else {
			register_error(elgg_echo("subsite_manager:action:invite:csv:error:content"));
		}
	} else {
		register_error(elgg_echo("subsite_manager:action:error:subsite_only"));
	}
	
	forward($forward_url);
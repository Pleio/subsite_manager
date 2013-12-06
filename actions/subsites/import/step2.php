<?php

	global $SUBSITE_MANAGER_IMPORTING_USERS;

	$columns = get_input("columns");
	$message = get_input("message");
	
	$forward_url = REFERER;
	$site = elgg_get_site_entity();
	$loggedin_user = elgg_get_logged_in_user_entity();
	$profile_fields = get_config("profile_fields");
	$site_guid = $site->getGUID();
	
	set_time_limit(0);
	
	if (!empty($columns)) {
		if (in_array("displayname", $columns) && in_array("email", $columns)) {
			$csv_location = $_SESSION["subsite_manager_import"]["location"];
			
			$name_column = array_search("displayname", $columns);
			$email_column = array_search("email", $columns);
			
			if ($fh = fopen($csv_location, "r")) {
				// set flag to let other parts of the system know what we're doing
				$SUBSITE_MANAGER_IMPORTING_USERS = true;
				
				$users_created = 0;
				$users_already = 0;
				
				// show hidden (unvalidated) users
				$hidden = access_get_show_hidden_status();
				access_show_hidden_entities(true);
				
				while (($data = fgetcsv($fh, 0, ";")) !== false) {
					$email = $data[$email_column];
					$displayname = $data[$name_column];
					
					if (!($users = get_user_by_email($email))) {
						// get username
						if (in_array("username", $columns)) {
							$col = array_search("username", $columns);
							$username = $data[$col];
						} else {
							$username = subsite_manager_create_username_from_email($email);
						}
						
						// get password
						if (in_array("password", $columns)) {
							$col = array_search("password", $columns);
							$password = $data[$col];
						} else {
							$password = generate_random_cleartext_password();
						}
						
						if (!empty($username) && !empty($email) && !empty($displayname) && !empty($password)) {
							try {
								if ($user_guid = register_user($username, $password, $displayname, $email)) {
									$users_created++;
									$user = get_user($user_guid);
									
									// validate user
									elgg_set_user_validation_status($user->getGUID(), true, "email");
									
									// add user to subsite
									if (subsite_manager_on_subsite()) {
										if(!$site->isUser($user->getGUID())){
											$site->addUser($user->getGUID());
										}
									}
									
									// add metadata
									foreach ($columns as $col_id => $metadata_name) {
										if (!in_array($metadata_name, array("username", "displayname", "email", "password"))) {
											if ($profile_fields[$metadata_name] == "tags") {
												$value = string_to_tag_array($data[$col_id]);
											} else {
												$value = $data[$col_id];
											}
											
											if (!empty($value)) {
												if (is_array($value)) {
													foreach ($value as $v) {
														create_metadata($user->getGUID(), $metadata_name, $v, "text", $user->getGUID(), ACCESS_PRIVATE, true, $site_guid);
													}
												} else {
													create_metadata($user->getGUID(), $metadata_name, $value, "text", $user->getGUID(), ACCESS_PRIVATE, false, $site_guid);
												}
											}
										}
									}
									
									// notify user
									$subject = elgg_echo("subsite_manager:import:notify:new:subject", array($site->name));
									$msg = elgg_echo("subsite_manager:import:notify:new:message", array(
										$user->name,
										$site->name,
										$site->url,
										$message,
										$username,
										$password
									));
									
									notify_user($user->getGUID(), $site->getGUID(), $subject, $msg, null, "email");
									
									// cache cleanup
									_elgg_invalidate_cache_for_entity($user->getGUID());
								}
							} catch (Exception $e){}
						}
					} else {
						// existing user
						$user = $users[0];
						$users_already++;
						
						if (subsite_manager_on_subsite()) {
							if (!$site->isUser($user->getGUID())) {
								$site->addUser($user->getGUID());
								
								// notify user
								$subject = elgg_echo("subsite_manager:import:notify:existing:subject", array($site->name));
								$msg = elgg_echo("subsite_manager:import:notify:existing:message", array(
									$user->name,
									$site->name,
									$site->url,
									$message
								));
									
								notify_user($user->getGUID(), $site->getGUID(), $subject, $msg, null, "email");
									
								// cache cleanup
								_elgg_invalidate_cache_for_entity($user->getGUID());
							}
						}
					}
				}
				
				// restore hidden users
				access_show_hidden_entities($hidden);
				
				if (!empty($users_already) || !empty($users_created)) {
					unset($_SESSION["subsite_manager_import"]);
					unlink($csv_location);
					$forward_url = elgg_get_site_url() . "admin/users/import";
					
					system_message(sprintf(elgg_echo("subsite_manager:action:import:step2:success"), $users_created, $users_already));
				} else {
					register_error(elgg_echo("subsite_manager:action:import:step2:error:unknown"));
				}
			} else {
				register_error(elgg_echo("subsite_manager:action:import:step2:error:csv_file"));
			}
		} else {
			register_error(elgg_echo("subsite_manager:action:import:step2:error:required_fields"));
		}
	} else {
		register_error(elgg_echo("subsite_manager:action:import:step2:error:columns"));
	}
	
	forward($forward_url);

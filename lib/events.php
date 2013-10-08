<?php

	function subsite_manager_make_admin_handler($event, $type, $entity){
		
		if(!subsite_manager_on_subsite() && elgg_instanceof($entity, "user", "", "ElggUser")){
			subsite_manager_make_superadmin($entity->getGUID());
		}
	}
	
	function subsite_manager_remove_admin_handler($event, $type, $entity){
		
		if(!subsite_manager_on_subsite() && elgg_instanceof($entity, "user", "", "ElggUser")){
			subsite_manager_remove_superadmin($entity->getGUID());
		}
	}
	
	function subsite_manager_upgrade_system_handler($event, $type, $entity){
		
		if(get_input("all") == "true"){
			// update plugin order timestamp
			datalist_set("plugin_order_last_update", time());
				
			// find subsites and do stuff
			$options = array(
					"type" => "site",
					"subtype" => Subsite::SUBTYPE,
					"limit" => false
			);
				
			// this could take a while
			set_time_limit(0);
				
			$batch = new ElggBatch("elgg_get_entities", $options);
			$viewtypes = elgg_get_config("view_types");
			$dataroot = elgg_get_config("dataroot");
				
			foreach($batch as $subsite){
				// clear simplecache
				$dir =  $dataroot . "views_simplecache/" . $subsite->getGUID() . "/";
				if (file_exists($dir) && ($handle = opendir($dir))) {
					// remove files
					while (false !== ($file = readdir($handle))) {
						if (!is_dir($dir . $file)) {
							unlink($dir . $file);
						}
					}
					closedir($handle);
				}
		
				if(!empty($viewtypes) && is_array($viewtypes)){
					foreach ($viewtypes as $viewtype) {
						datalist_set("sc_lastupdate_" . $viewtype . "_" . $subsite->getGUID(), 0);
						datalist_set("sc_lastcached_" . $viewtype . "_" . $subsite->getGUID(), 0);
					}
				}
		
				// clear system cache
				$system_cache = new ElggFileCache($dataroot . "system_cache/" . $subsite->getGUID() . "/");
				$system_cache->clear();
		
				// cleanup cron cache
				$cron_cache = $dataroot . "subsite_manager/" . $subsite->getGUID() . "/cron_cache.json";
				if(file_exists($cron_cache)){
					unlink($cron_cache);
				}
		
				// reset translation editor cache
				// can't use remove_private_setting because of 'name like' not 'name ='
				$sql = "DELETE FROM " . get_config("dbprefix") . "private_settings";
				$sql .= " WHERE name LIKE 'te_last_update_%'";
				$sql .= " AND entity_guid = " . $subsite->getGUID();
		
				delete_data($sql);
		
				// reset plugin order
				remove_private_setting($subsite->getGUID(), "plugin_order_last_update");
		
			}
		}
		
		// force reorder of plugins on subsite
		if (subsite_manager_on_subsite()) {
			$site = elgg_get_site_entity();
		
			remove_private_setting($site->getGUID(), "plugin_order_last_update");
		}
	}
	
	/**
	 * Create a default menu for new Subsites
	 *
	 * @param string $event
	 * @param string $object_type
	 * @param Subsite $object
	 */
	function subsite_manager_create_site_event_handler($event, $object_type, $object){
		
		if($object instanceof Subsite){
			$site = $object;
				
			$i = 0;
	
			$menu_items = array(
				array(
					"title" => "Voorpagina",
					"url" => "[wwwroot]",
					"access_id" => ACCESS_PUBLIC,
					"children" => array(
						array(
							"title" => "Alle blogs",
							"url" => "[wwwroot]blog/all",
							"access_id" => ACCESS_LOGGED_IN
						),
						array(
							"title" => "Alle activiteiten",
							"url" => "[wwwroot]activity",
							"access_id" => ACCESS_LOGGED_IN
						)
					)
				),
				array(
					"title" => "Mijn pagina",
					"url" => "#",
					"access_id" => ACCESS_LOGGED_IN,
					"children" => array(
						array(
							"title" => "Mijn dashboard",
							"url" => "[wwwroot]dashboard",
							"access_id" => ACCESS_LOGGED_IN
						),
						array(
							"title" => "Mijn profielpagina",
							"url" => "[wwwroot]profile/[username]",
							"access_id" => ACCESS_LOGGED_IN
						),
						array(
							"title" => "Mijn instellingen",
							"url" => "[wwwroot]settings",
							"access_id" => ACCESS_LOGGED_IN
						)
					)
				),
				array(
					"title" => "Groepen",
					"url" => "#",
					"access_id" => ACCESS_LOGGED_IN,
					"children" => array(
						array(
							"title" => "Mijn groepen",
							"url" => "[wwwroot]groups/member/[username]",
							"access_id" => ACCESS_LOGGED_IN
						),
						array(
							"title" => "Alle groepen",
							"url" => "[wwwroot]groups/all/?filter=pop",
							"access_id" => ACCESS_LOGGED_IN
						)
					)
				),
				array(
					"title" => "Leden",
					"url" => "#",
					"access_id" => ACCESS_LOGGED_IN,
					"children" => array(
						array(
							"title" => "Zoeken",
							"url" => "[wwwroot]members",
							"access_id" => ACCESS_LOGGED_IN
						),
						array(
							"title" => "Mijn contacten",
							"url" => "[wwwroot]friends/[username]",
							"access_id" => ACCESS_LOGGED_IN
						),
						array(
							"title" => "Contactverzoeken",
							"url" => "[wwwroot]friend_request/",
							"access_id" => ACCESS_LOGGED_IN
						)
					)
				),
				array(
					"title" => "Toevoegen",
					"url" => "#",
					"access_id" => ACCESS_LOGGED_IN,
					"children" => array(
						array(
							"title" => "Content toevoegen",
							"url" => "[wwwroot]add",
							"access_id" => ACCESS_LOGGED_IN
						),
						array(
							"title" => "Nieuwe groep maken",
							"url" => "[wwwroot]groups/add",
							"access_id" => ACCESS_LOGGED_IN
						)
					)
				),
				array(
					"title" => "Beheer",
					"url" => "[wwwroot]admin",
					"access_id" => ACCESS_PRIVATE,
					"children" => array(
						array(
							"title" => "Gebruikersbeheer",
							"url" => "[wwwroot]admin/users/newest",
							"access_id" => ACCESS_PRIVATE
						),
						array(
							"title" => "Nodig leden uit",
							"url" => "[wwwroot]admin/users/invite",
							"access_id" => ACCESS_PRIVATE
						),
						array(
							"title" => "Pluginbeheer",
							"url" => "[wwwroot]admin/plugins",
							"access_id" => ACCESS_PRIVATE
						),
						array(
							"title" => "Beheer template",
							"url" => "[wwwroot]admin/appearance/template",
							"access_id" => ACCESS_PRIVATE
						)
					)
				)
			);
				
			foreach($menu_items as $main_item){
				$item = new ElggObject();
				$item->subtype = "menu_builder_menu_item";
				$item->owner_guid = $site->getGUID();
				$item->container_guid = $site->getGUID();
				$item->site_guid = $site->getGUID();
	
				$item->access_id = $main_item["access_id"];
				$item->parent_guid = 0;
				$item->title = $main_item["title"];
				$item->url = $main_item["url"];
				$item->order = $i;
				$i++;
	
				$item->save();
	
				if(array_key_exists("children", $main_item)){
					foreach($main_item["children"] as $sub_item){
						$submenu_item = new ElggObject();
						$submenu_item->subtype = "menu_builder_menu_item";
						$submenu_item->owner_guid = $site->getGUID();
						$submenu_item->container_guid = $site->getGUID();
						$submenu_item->site_guid = $site->getGUID();
						$submenu_item->access_id = $sub_item["access_id"];
	
						$submenu_item->parent_guid = $item->getGUID();
						$submenu_item->title = $sub_item["title"];
						$submenu_item->url = $sub_item["url"];
						$submenu_item->order = $i;
						$i++;
						$submenu_item->save();
					}
				}
			}
		}
	}
	
	function subsite_manager_create_event_handler($event, $object_type, $object){
		
		if (subsite_manager_on_subsite()) {
			$site = elgg_get_site_entity();
			
			if ($object instanceof ElggUser) {
				// check for invited groups
				global $SUBSITE_MANAGER_INVITED_GROUPS;
				
				if ($site->hasInvitation($object->getGUID(), $object->email)) {
					// you were invited so all is good
					$site->addUser($object->getGUID());
				} else {
					// check if the user is allowed on this site and add the user to main site
					switch ($site->getMembership()) {
						case Subsite::MEMBERSHIP_APPROVAL:
							// user should have requested membership, so remove from subsite and request membership
							$site->removeUser($object->getGUID());
							
							if (empty($SUBSITE_MANAGER_INVITED_GROUPS)) {
								$site->requestMembership(elgg_echo("subsite_manager:create:user:request_membership"), $object->getGUID());
								system_message(elgg_echo("subsite_manager:create:user:message:request_membership"));
							}
							break;
						case Subsite::MEMBERSHIP_DOMAIN:
							// user registration only for allowed domain, so check if correct
							if (!$site->validateEmailDomain($object->getGUID(), $object->email)) {
								// domain of the user was not valid
								$site->removeUser($object->getGUID());
								
								if (empty($SUBSITE_MANAGER_INVITED_GROUPS)) {
									system_message(elgg_echo("subsite_manager:create:user:message:domain"));
								}
							}
							break;
						case Subsite::MEMBERSHIP_DOMAIN_APPROVAL:
							// user registration by email domain or request if not match
							if (!$site->validateEmailDomain($object->getGUID(), $object->email)) {
								$site->removeUser($object->getGUID());
								
								if (empty($SUBSITE_MANAGER_INVITED_GROUPS)) {
									$site->requestMembership(elgg_echo("subsite_manager:create:user:request_membership"), $object->getGUID());
									system_message(elgg_echo("subsite_manager:create:user:message:request_membership"));
								}
							}
							break;
						case Subsite::MEMBERSHIP_INVITATION:
							// user should have been invited, so remove from subsite
							$site->removeUser($object->getGUID());
						
							// make sure the user knows why registration failed
							register_error(elgg_echo("subsite_manager:subsites:no_access:invitation"));
						
							// register a plugin hook to cleanup the user
							elgg_register_plugin_hook_handler("register", "user", "subsite_manager_block_user_registration");
							break;
					}
				}
				
				$object->addToSite($site->getOwnerGUID());
				$sql = "UPDATE " . get_config("dbprefix") . "entities SET site_guid=" . $site->getOwnerGUID() . " WHERE guid=" . $object->getGUID();
				
				return update_data($sql);
			}
		}
	}
	
	/**
	 * A plugin has been activated/deactivated or the settings have changed, reset cron cache file (only on subsite)
	 *
	 * @param string $event
	 * @param string $object_type
	 * @param ElggEntity $object
	 */
	function subsite_manager_plugin_event_handler($event, $object_type, $object){
		static $run_once;
		
		if(subsite_manager_on_subsite() && !isset($run_once)){
			$run_once = true;
			$site = elgg_get_site_entity();
			
			subsite_manager_remove_cron_cache($site->getGUID());
		}
	}
	
	/**
	 * actions related to the login of a user
	 *
	 * @param unknown_type $event
	 * @param unknown_type $object_type
	 * @param unknown_type $object
	 */
	function subsite_manager_login_event_handler($event, $object_type, $user){
		
		if(!empty($user) && elgg_instanceof($user, "user", null, "ElggUser")){
			// if memcache is available, invalidate the memcache content
			if(is_memcache_available()){
				register_shutdown_function("subsite_manager_login_shutdown_hook", $user);
			}
			
			// check if this is the first login, if so check for invited subsites
			if(empty($user->last_login)){
				if($subsites = subsite_manager_get_invited_subsites($user)){
					foreach($subsites as $subsite){
						if($subsite->addUser($user->getGUID())){
							system_message(elgg_echo("subsite_manager:login:subsite:join", array($subsite->name)));
							
							// notify user about add
							$subject = elgg_echo("subsite_manager:login:subsite:join", array($subsite->name));
							$message = elgg_echo("subsite_manager:login:subsite:join:notify:message", array($user->name, $subsite->name, $subsite->url));
							
							notify_user($user->getGUID(), $subsite->getGUID(), $subject, $message);
						}
					}
				}
			}
			
			// simplesaml work around
			if (!subsite_manager_on_subsite()) {
				// we need to rewrite this in order to work around a 'feature'
				if (isset($_SESSION["saml_attributes"]) && isset($_SESSION["saml_source"])) {
					$_SESSION["sm_saml_attributes"] = $_SESSION["saml_attributes"];
					$_SESSION["sm_saml_source"] = $_SESSION["saml_source"];
				}
			}
		}
	}
	
	/**
	 * Add the user to a subsite in case of open subsite or if the admin says so
	 *
	 * @param string $event
	 * @param string $type
	 * @param mixed $params
	 */
	function subsite_manager_group_join_handler($event, $type, $params){
		
		if(!empty($params) && is_array($params)){
			$group = elgg_extract("group", $params);
			$user = elgg_extract("user", $params);
	
			if(elgg_instanceof($user, "user", null, "ElggUser") && elgg_instanceof($group, "group", null, "ElggGroup")) {
				if(($site = get_entity($group->site_guid)) && elgg_instanceof($site, "site", Subsite::SUBTYPE, "Subsite")){
					if(!$site->isUser($user->getGUID())){
						if($site->canJoin($user->getGUID())){
							// the user is not a member of the site, but can join so add user to the subsite
							$site->addUser($user->getGUID());
						} elseif(elgg_is_admin_logged_in() && (get_input("action") == "groups/invite") && (get_input("subsite_manager_add_users", "no") == "yes")){
							// user was added to the group by an admin and tha admin wanted the user to join the site
							$site->addUser($user->getGUID());
						}
					}
				}
			}
		}
	}
	
	/**
	 * This event will be triggered when plugin are reordered in in 'boot' 'system'
	 *
	 * @param string $event
	 * @param string $type
	 * @param mixed $entity
	 */
	function subsite_manager_ready_system_handler($event, $type, $entity) {
		
		elgg_reset_system_cache();
		elgg_invalidate_simplecache();
		
	}
	
	/**
	 * Log the groups a user was invited for
	 *
	 * @param string $event
	 * @param string $type
	 * @param ElggAnnotation $object
	 */
	function subsite_manager_delete_annotation_handler($event, $type, $object) {
		
		if (!empty($object) && ($object instanceof ElggAnnotation)) {
			
			if ($object->name == "email_invitation") {
				global $SUBSITE_MANAGER_INVITED_GROUPS;
				
				if (!isset($SUBSITE_MANAGER_INVITED_GROUPS)) {
					$SUBSITE_MANAGER_INVITED_GROUPS = array();
				}
				
				$SUBSITE_MANAGER_INVITED_GROUPS[] = $object->getOwnerGUID();
			}
		}
	}
	
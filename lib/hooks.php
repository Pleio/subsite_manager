<?php

	global $SUBSITE_MANAGER_MAIN_PROFILE_FIELDS;

	function subsite_manager_entity_menu_handler($hook, $entity, $return_value, $params){
		$result = $return_value;

		if(!elgg_in_context("widgets") && !empty($params) && is_array($params)){
			$entity = elgg_extract("entity", $params);

			if(elgg_instanceof($entity, "site", Subsite::SUBTYPE, "Subsite")){
				// remove access notice
				foreach($result as $index => $item){
					if($item->getName() == "access"){
						unset($result[$index]);
						break;
					}
				}

				// can edit
				if($entity->canEdit()){
					// delete
					$result[] = ElggMenuItem::factory(array(
						"name" => "delete",
						"text" => elgg_view_icon("delete"),
						"title" => elgg_echo("delete"),
						"confirm" => elgg_echo("deleteconfirm"),
						"href" => "action/subsites/delete?guid=" . $entity->getGUID(),
						"priority" => 50
					));

					$result[] = ElggMenuItem::factory(array(
						"name" => "feature",
						"text" => $entity->featured ? elgg_echo("subsite_manager:unfeature") : elgg_echo("subsite_manager:feature"),
						"title" => elgg_echo("feature"),
						"href" => "action/subsites/toggle_featured?guid=" . $entity->getGUID(),
						"is_action" => true,
						"priority" => 40
					));
				}
			}
		}

		return $result;
	}

	function subsite_manager_subsite_menu_handler($hook, $entity, $return_value, $params){
		$result = $return_value;

		if(!elgg_in_context("widgets") && !empty($params) && is_array($params)){
			if(isset($params["entity"])){
				$entity = $params["entity"];

				if(elgg_instanceof($entity, "site", Subsite::SUBTYPE, "Subsite")){
					// Join or leave site
					if($entity->isUser()){
						if(!$entity->isAdmin()){
							$result[] = ElggMenuItem::factory(array(
								"name" => "leave",
								"text" => elgg_echo("subsite_manager:subsite:leave"),
								"href" => "action/subsites/remove_user?site_guid=" . $entity->getGUID(),
								"confirm" => elgg_echo("subsite_manager:subsite:leave:confirm"),
								"class" => "elgg-button elgg-button-submit"
							));
						} else {
							$result[] = ElggMenuItem::factory(array(
								"name" => "leave",
								"text" => elgg_echo("subsite_manager:subsite:admin_leave"),
								"href" => false,
								"class" => "elgg-button elgg-button-submit elgg-state-disabled"
							));
						}
					} elseif($entity->pendingMembershipRequest()) {
						$result[] = ElggMenuItem::factory(array(
							"name" => "pending",
							"text" => elgg_echo("subsite_manager:subsite:pending"),
							"href" => false,
							"class" => "elgg-button elgg-button-submit elgg-state-disabled"
						));
					} elseif($entity->canJoin()){
						$result[] = ElggMenuItem::factory(array(
							"name" => "join",
							"text" => elgg_echo("subsite_manager:subsite:join"),
							"href" => $entity->getURL() . "subsites/join",
							"class" => "elgg-button elgg-button-submit"
						));
					} else {
						switch($entity->getMembership()){
							case Subsite::MEMBERSHIP_APPROVAL:
							case Subsite::MEMBERSHIP_DOMAIN_APPROVAL:
								$result[] = ElggMenuItem::factory(array(
									"name" => "join",
									"text" => elgg_echo("subsite_manager:subsite:request"),
									"href" => $entity->getURL() . "subsites/join/request",
									"class" => "elgg-button elgg-button-submit"
								));
								break;
							case Subsite::MEMBERSHIP_DOMAIN:
								$result[] = ElggMenuItem::factory(array(
									"name" => "join",
									"text" => elgg_echo("subsite_manager:subsite:validate_domain"),
									"href" => $entity->getURL() . "subsites/join/domain",
									"class" => "elgg-button elgg-button-submit"
								));
								break;
							default:
								// join not possible
								break;
						}
					}
				}
			}
		}

		return $result;
	}

	function subsite_manager_user_hover_menu($hook, $type, $return, $params) {
		$user = elgg_extract("entity", $params);
		$site = elgg_get_site_entity();

		if (elgg_is_admin_logged_in() && subsite_manager_on_subsite()) {

			// cleanup most admin menu items
			$allowed_menu_items = array(
				"user_support_staff",
				"entity_tools:admin",
				"makesubscribed"
			);

			if (subsite_manager_is_superadmin()) {
				$allowed_menu_items[] = "resetpassword";
			}

			foreach ($return as $index => $menu_item) {
				// echo $menu_item->getName() . "|";
				if (($menu_item->getSection() == "admin") && !in_array($menu_item->getName(), $allowed_menu_items)) {
					unset($return[$index]);
				}
			}

			if (!$site->isUser($user->guid)) {
				return $return;
			}

			// add options for admins
			if(!$site->isAdmin($user->getGUID())) {
				// make subsite admin
				$menu_options = array(
					"name" => "subsite_manager_toggle_admin",
					"text" => elgg_echo("makeadmin"),
					"href" => "action/subsites/user/toggle_admin?user_guid=" . $user->getGUID(),
					"confirm" => elgg_echo("question:areyousure"),
					"section" => "admin"
				);
				$return[] = ElggMenuItem::factory($menu_options);

				// kick from site
				$menu_options = array(
					"name" => "subsite_manager_remove_user",
					"text" => elgg_echo("subsite_manager:subsites:remove_user"),
					"href" => "action/subsites/remove_user?user_guid=" . $user->getGUID(),
					"rel" => elgg_echo("subsite_manager:subsite:remove_user:confirm"),
					"is_action" => true,
					"section" => "admin"
				);
				$return[] = ElggMenuItem::factory($menu_options);
			} else {
				// unmake subsite admin
				$menu_options = array(
					"name" => "subsite_manager_toggle_admin",
					"text" => elgg_echo("removeadmin"),
					"href" => "action/subsites/user/toggle_admin?user_guid=" . $user->getGUID(),
					"confirm" => elgg_echo("question:areyousure"),
					"section" => "admin"
				);
				$return[] = ElggMenuItem::factory($menu_options);
			}

			return $return;
		}
	}

	function subsite_manager_page_menu_handler($hook, $entity, $return_value, $params){
		$result = $return_value;

		if(elgg_in_context("admin") && (get_input("advanced") != "yes")){
			if(!empty($result) && is_array($result)){
				$active_plugins = elgg_get_plugins("active");

				if(!empty($active_plugins)){
					$plugins_with_settings = array();
					foreach($active_plugins as $plugin){
						$settings_view_old = "settings/" . $plugin->getID() . "/edit";
						$settings_view_new = "plugins/" . $plugin->getID() . "/settings";
						if (elgg_view_exists($settings_view_new) || elgg_view_exists($settings_view_old)) {
							$plugins_with_settings[$plugin->getID()] = $plugin;
						}
					}

					if(!empty($plugins_with_settings)){
						$hiding_plugins = array();

						foreach($plugins_with_settings as $plugin_name => $plugin){
							if(!subsite_manager_show_plugin($plugin)){
								$hiding_plugins[] = $plugin_name;
							}
						}

						if(!empty($hiding_plugins)){
							foreach($result as $index => $item){
								if($item->getSection() == "configure"){
									if(in_array($item->getName(), $hiding_plugins)){
										unset($result[$index]);
									}
								}
							}
						}
					}
				}
			}
		} elseif (elgg_in_context("groups") && ($user = elgg_get_logged_in_user_entity()) && !subsite_manager_on_subsite()) {
			if (!empty($result) && is_array($result)) {

				$options = array(
					"type" => "group",
					"relationship" => "invited",
					"relationship_guid" => $user->getGUID(),
					"inverse_relationship" => TRUE,
					"count" => true,
					"site_guids" => false
				);

				if ($invite_count = elgg_get_entities_from_relationship($options)) {
					// need to adjust the group invite counter
					foreach($result as $section => &$menu_items) {
						if (!empty($menu_items) && is_array($menu_items)) {
							foreach($menu_items as $index => &$menu_item) {
								if ($menu_item->getName() == "groups:user:invites") {
									$menu_item->setText(elgg_echo("groups:invitations:pending", array($invite_count)));
								}
							}
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * This hook allows for editing the page menu just before is is drawn, no more menu items will be added
	 *
	 * @param string $hook
	 * @param string $entity
	 * @param mixed $return_value
	 * @param mixed $params
	 * @return mixed
	 */
	function subsite_manager_page_prepare_menu_handler($hook, $entity, $return_value, $params){
		$result = $return_value;

		if(elgg_in_context("admin") && !empty($result) && is_array($result)){
			$page_menu = $result;

			if(!subsite_manager_is_superadmin_logged_in()){
				$allowed_menu_items = array(
					"dashboard",
					"users",
					"users:online",
					"users:newest",
					"users:admins",
					"users:membership",
					"users:invite",
					"users:invitations",
					"users:export",
					"statistics",
					"statistics:overview",
					"administer_utilities",
					"administer_utilities:reportedcontent",
					"administer_utilities:csv_exporter",
					"administer_utilities:group_bulk_delete",
					"administer_utilities:profile_sync",
					"administer_utilities:rewrite",
					"wizard",
					"profile_sync",
					"site_announcements",
					"appearance",
					"appearance:profile_fields",
					"appearance:template",
					"appearance:group_fields",
					"static",
					"appearance:expages",
					"appearance:theme_oirschot",
					"appearance:theme_eersel",
					"plugins",
					"widgets",
					"widgets:manage",
					"widgets:default", // widget manager
					"appearance:default_widgets", // core
					"settings",
					"settings:basic",
					"settings:advanced",
					"settings:pleio_api",
				);

				// loop through menu sections
				foreach($page_menu as $section => $menu_items){

					if(!empty($menu_items) && is_array($menu_items)){
						// loop through menu items
						foreach($menu_items as $index => $menu_item){

							if(in_array($menu_item->getName(), $allowed_menu_items)){
								//check for submenus
								if($children = $menu_item->getChildren()){
									// loop through submenus
									if($menu_item->getName() != "settings"){
										// 'normal' submenu
										foreach($children as $child_index => $child){
											if(!in_array($child->getName(), $allowed_menu_items)){
												unset($children[$child_index]);
											}
										}
									} else {
										// the setting menu is special
										// most menu items have the name of the plugin, but some not, these should be checked
										foreach($children as $child_index => $child){
											if(stristr($child->getName(), "settings:") && !in_array($child->getName(), $allowed_menu_items)){
												unset($children[$child_index]);
											}
										}
									}

									if(!empty($children)){
										$menu_item->setChildren($children);
									} elseif(!$menu_item->getHref()){
										unset($page_menu[$section][$index]);
									}
								}
							} else {
								unset($page_menu[$section][$index]);
							}
						}

						if(empty($page_menu[$section])){
							unset($page_menu[$section]);
						}
					}
				}

				$result = $page_menu;
			}
		}

		return $result;
	}

	function subsite_manager_annotation_menu_handler($hook, $entity, $return_value, $params){
		$result = $return_value;

		if (subsite_manager_on_subsite() && elgg_in_context("admin")) {
			if ($annotation = elgg_extract("annotation", $params)) {
				if ($annotation->name == "request_membership") {
					if ($user = get_user($annotation->owner_guid)) {
						$result[] = ElggMenuItem::factory(array(
							"name" => "approve",
							"text" => elgg_echo("subsite_manager:approve"),
							"href" => "action/subsites/membership/approve?annotation_id=" . $annotation->id,
							"confirm" => elgg_echo("subsite_manager:request_membership:approve:confirm"),
							"priority" => 10
						));

						$result[] = ElggMenuItem::factory(array(
							"name" => "decline",
							"text" => elgg_echo("subsite_manager:decline"),
							"href" => "action/subsites/membership/decline?annotation_id=" . $annotation->id,
							"rel" => elgg_echo("subsite_manager:request_membership:decline:confirm"),
							"is_action" => true,
							"priority" => 20
						));
					}
				}
			}
		}

		return $result;
	}

	function subsite_manager_walled_garden_handler($hook, $type, $return, $params){
		$result = $return;

		$result[] = "subsite_icon";
		$result[] = "subsites/no_access";

		// need to bypass subsite access rules
		if(elgg_is_logged_in()){
			$result[] = "subsites/join.*";
			$result[] = "groups/invitations.*";
			$result[] = "groups/member.*";
			$result[] = "accept_terms.*";

			// allow some actions
			$result[] = "action/subsites/join.*";
		}

		$result[] = "mod/subsite_manager/procedures/simplesaml/.*";

		return $result;
	}

	/**
	 * merge user profile fields from main site with subsite fields
	 *
	 * @param string $hook
	 * @param string $type
	 * @param mixed $returnvalue
	 * @param mized $params
	 * @return mixed
	 */
	function subsite_manager_profile_fields_hook($hook, $type, $returnvalue, $params){
		global $SUBSITE_MANAGER_MAIN_PROFILE_FIELDS;

		static $running;
		$result = $returnvalue;

		// only on subsites and recursive deadloop protection
		if(subsite_manager_on_subsite() && empty($running)){
			$running = true;

			$subsite = elgg_get_site_entity();
			elgg_set_config("site_guid", $subsite->getOwnerGUID());

			// we need to check the system cache for updates on main site
			$dataroot = elgg_get_config("dataroot");
			$main_system_cache_location = $dataroot . "system_cache/" . $subsite->getOwnerGUID() . "/profile_manager_profile_fields_" . $subsite->getOwnerGUID();
			$local_system_cache_location = $dataroot . "system_cache/" . $subsite->getGUID() . "/profile_manager_profile_fields_" . $subsite->getOwnerGUID();

			if (file_exists($main_system_cache_location) && file_exists($local_system_cache_location)) {
				$main_time = filemtime($main_system_cache_location);
				$local_time = filemtime($local_system_cache_location);

				if ($main_time >= $local_time) {
					unlink($local_system_cache_location);
				}
			}

			// now get the fields
			$main_fields = elgg_trigger_plugin_hook("profile:fields", "profile", null, array());

			// save fields for get/set metadata
			$SUBSITE_MANAGER_MAIN_PROFILE_FIELDS = $main_fields;

			// build new result
			$result = array_merge($result, $main_fields);

			elgg_set_config("site_guid", $subsite->getGUID());
			$running = false;
		}

		return $result;
	}

	/**
	* merge group profile fields from main site with subsite fields
	*
	* @param string $hook
	* @param string $type
	* @param mixed $returnvalue
	* @param mized $params
	* @return mixed
	*/
	function subsite_manager_group_fields_hook($hook, $type, $returnvalue, $params){
		static $running;
		$result = $returnvalue;

		// only on subsites and recursive deadloop protection
		if(subsite_manager_on_subsite() && empty($running)){
			$running = true;

			$subsite = elgg_get_site_entity();
			elgg_set_config("site_guid", $subsite->getOwnerGUID());

			// we need to check the system cache for updates on main site
			$dataroot = elgg_get_config("dataroot");
			$main_system_cache_location = $dataroot . "system_cache/" . $subsite->getOwnerGUID() . "/profile_manager_group_fields_" . $subsite->getOwnerGUID();
			$local_system_cache_location = $dataroot . "system_cache/" . $subsite->getGUID() . "/profile_manager_group_fields_" . $subsite->getOwnerGUID();

			if (file_exists($main_system_cache_location) && file_exists($local_system_cache_location)) {
				$main_time = filemtime($main_system_cache_location);
				$local_time = filemtime($local_system_cache_location);

				if ($main_time >= $local_time) {
					unlink($local_system_cache_location);
				}
			}

			// now get the main fields
			$main_fields = elgg_trigger_plugin_hook("profile:fields", "group", null, array());

			$result = array_merge($result, $main_fields);

			elgg_set_config("site_guid", $subsite->getGUID());
			$running = false;
		}

		return $result;
	}

	function subsite_manager_cron_handler($hook, $type, $returnvalue, $params){
		global $SUBSITE_MANAGER_IGNORE_WRITE_ACCESS;

		if(!subsite_manager_on_subsite()){
			// Update the member count of every subsite (daily)
			switch($type){
				case "daily":
					//update member_count on all subsites
					$options = array(
						"type" => "site",
						"subtype" => Subsite::SUBTYPE,
						"limit" => false
					);

					if($subsites = elgg_get_entities($options)){
						// we need access to update the member count
						$backup_access = $SUBSITE_MANAGER_IGNORE_WRITE_ACCESS;
						$SUBSITE_MANAGER_IGNORE_WRITE_ACCESS = true;

						foreach($subsites as $subsite){
							$subsite->getMembers(array("count" => true, "force_update_member_count" => true));
						}

						// restore access
						$SUBSITE_MANAGER_IGNORE_WRITE_ACCESS = $backup_access;
					}
					break;
				default;
					break;
			}

			// check if we need to run crons on subsites
			$cron_periods = array(
// 				"reboot",
// 				"minute",
				"fiveminute",
// 				"fifteenmin",
// 				"halfhour",
				"hourly",
				"daily",
				"weekly",
				"monthly",
				"yearly"
			);

			if(in_array($type, $cron_periods) && elgg_is_active_plugin("commandline_cron")){
				$subsites = array();

				// find out which subsbites have need for the current cron interval
				$base_cron_cache_path = get_config("dataroot") . "subsite_manager/";

				if($fh = opendir($base_cron_cache_path)){
					while(($filename = readdir($fh)) !== false){
						if(is_numeric($filename) && is_dir($base_cron_cache_path . $filename . "/")){
							if(file_exists($base_cron_cache_path . $filename . "/cron_cache.json")){
								if($contents = file_get_contents($base_cron_cache_path . $filename . "/cron_cache.json")){
									$crons = json_decode($contents, true);

									if(in_array($type, $crons)){
										if(($subsite = elgg_get_site_entity($filename)) && elgg_instanceof($subsite, "site", Subsite::SUBTYPE, "Subsite")){
											$subsites[] = $subsite;
										}
									}
								}
							}
						}
					}
				}

				// we have found some subsites which need this cron interval
				if(!empty($subsites)){
					if (SUBSITE_MANAGER_RUN_CRON_ASYNC) {
						subsite_manager_cron_run_async($subsites, $type);
					} else {
						subsite_manager_cron_run_sync($subsites, $type);
					}
				}
			}
		}
	}

	function subsite_manager_cron_run_async($subsites, $interval) {
		$json = array(
			"interval" => $interval,
			"no_processes" => SUBSITE_MANAGER_SIMULTANEOUS_CRON_PROCESSES,
			"memory_limit" => ini_get("memory_limit"),
			"path" => elgg_get_config("plugins_path") . "commandline_cron/procedures/cli.php",
			"hosts" => array()
		);

		foreach($subsites as $subsite){
			$url = parse_url($subsite->url);

			if(!empty($url) && ($secret = commandline_cron_generate_secret($subsite->getGUID()))){
				$json['hosts'][] = array(
					"host" => $url['host'],
					"secret" => $secret,
					"https" => $url['scheme'] === 'https' ? true : false
				);
			}
		}

		$script = dirname(__FILE__) . "/../procedures/cronscheduler.py";
		$command = "python {$script} " . base64_encode(json_encode($json));
		exec($command);
	}

	function subsite_manager_cron_run_sync($subsites) {
		foreach($subsites as $subsite){
			$https = false;
			$host = "";

			$parts = parse_url($subsite->url);

			$host = elgg_extract("host", $parts);
			if(elgg_extract("scheme", $parts, "") === "https"){
				$https = true;
			}

			if(!empty($host) && ($secret = commandline_cron_generate_secret($subsite->getGUID()))){
				$commandline = $cron_cli_path;
				$commandline .= " secret=" . $secret;
				$commandline .= " host=" . $host;
				$commandline .= " interval=" . $type;
				$commandline .= " memory_limit=" . $memory_limit;

				if(!empty($https)){
					$commandline .= " https=On";
				}

				exec("php " . $commandline . " > /dev/null &");
			}
		}
	}

	function subsite_manager_permissions_check_metadata($hook, $type, $returnvalue, $params){
		global $SUBSITE_MANAGER_IGNORE_WRITE_ACCESS;

		if(isset($SUBSITE_MANAGER_IGNORE_WRITE_ACCESS) && $SUBSITE_MANAGER_IGNORE_WRITE_ACCESS === true){
			$entity = elgg_extract("entity", $params);

			if(elgg_instanceof($entity, "site", Subsite::SUBTYPE)){
				return true;
			}
		}
	}

	/**
	 * Allow subsites to be found when searching
	 *
	 * @param string $hook
	 * @param string $type
	 * @param mixed $returnvalue
	 * @param mixed $params
	 * @return mixed
	 */
	function subsite_manager_search_subsite_hook($hook, $type, $returnvalue, $params){
		$join = "JOIN " . get_config("dbprefix") . "sites_entity se ON e.guid = se.guid";
		$params['joins'] = array($join);
		$fields = array('name', 'description', 'url');

		$where = search_get_where_sql('se', $fields, $params, FALSE);

		$params['wheres'] = array($where);
		$params['count'] = TRUE;
		$count = elgg_get_entities($params);

		// no need to continue if nothing here.
		if (!$count) {
			return array('entities' => array(), 'count' => $count);
		}

		$params['count'] = FALSE;
		$entities = elgg_get_entities($params);

		// add the volatile data for why these entities have been returned.
		foreach ($entities as $entity) {
			$title = search_get_highlighted_relevant_substrings($entity->name, $params['query']);
			$entity->setVolatileData('search_matched_title', $title);

			$desc = search_get_highlighted_relevant_substrings($entity->description, $params['query']);
			$entity->setVolatileData('search_matched_description', $desc);

			$icon = elgg_view_entity_icon($entity, 'tiny');

			$entity->setVolatileData("search_icon", $icon);
		}

		return array(
			'entities' => $entities,
			'count' => $count,
		);
	}

	/**
	 * Allow subsite admins to edit entities on their own site
	 * and reset the password of a user on their site.
	 *
	 * @param string $hook
	 * @param string $type
	 * @param boolean $returnvalue
	 * @param mixed $params
	 * @return boolean
	 */
	function subsite_manager_permissions_check_hook($hook, $type, $returnvalue, $params) {
		$user = elgg_extract("user", $params);
		$entity = elgg_extract("entity", $params);
		$site = elgg_get_site_entity();

		if (!subsite_manager_on_subsite()) {
			return $returnvalue;
		}

		if (!$user || !$entity) {
			return $returnvalue;
		}

		if (!$returnvalue) {
			if ($entity->site_guid == $site->guid || $entity->guid == $site->guid) {
				if ($site->isAdmin($user->guid)) {
					return true;
				}
			}

			// subsite admins are allowed to apply some actions to ElggUser objects
			if ($entity instanceof ElggUser) {
				$allowed_actions = array(
	                "admin/user/resetpassword"
	            );
				$allowed_contexts = array(
	                "entities"
	            );

				if (in_array(get_input("action"), $allowed_actions) || in_array(elgg_get_context(), $allowed_contexts)) {
					// check if the user is an admin of the current site and the entity (user) is a member of this site
					if ($site->isAdmin($user->guid) && $site->isUser($entity->guid)) {
						return true;
					}
				}
			}
		}
	}

	/**
	 * Check if the user can write to the container entity
	 * Restores write access to subsite admins similar to @see elgg_override_permissions()
	 *
	 * @param string $hook
	 * @param string $type
	 * @param boolean $returnvalue
	 * @param mixed $params
	 * @return boolean
	 */
	function subsite_manager_container_permissions_check_hook($hook, $type, $returnvalue, $params) {
		$user = elgg_extract("user", $params);
		$container = elgg_extract("container", $params);
		$type = elgg_extract("type", $params);
		$site = elgg_get_site_entity();

		if (!subsite_manager_on_subsite()) {
			return $returnvalue;
		}

		if (!$user || !$container) {
			return $returnvalue;
		}

		if (subsite_manager_is_superadmin($user->guid)) {
			return $returnvalue;
		}

		if ($site->isAdmin($user->guid)) {
			return $returnvalue;
		}

		if (!$site->isUser($user->guid) && !$container instanceof ElggGroup) {
			return false;
		} else {
			return $returnvalue;
		}
	}

	/**
	 * Add/remove ACL's to the users write access list
	 *
	 * @param string $hook
	 * @param string $type
	 * @param mixed $returnvalue
	 * @param mixed $params
	 * @return mixed
	 */
	function subsite_manager_access_write_hook($hook, $type, $returnvalue, $params) {
		$result = $returnvalue;

		$user_guid = elgg_extract("user_id", $params);
		$site_guid = elgg_extract("site_id", $params);

		if (!$user_guid || !$site_guid) {
			return $returnvalue;
		}

		$site = elgg_get_site_entity($site_guid);
		if (!$site) {
			return $returnvalue;
		}

		// Widgets have a different access level then the rest of the content
		if (elgg_in_context("widgets")) {
			if(elgg_in_context("groups")) {
				$group = elgg_get_page_owner_entity();
				if(!empty($group->group_acl)){
					$result[$group->group_acl] = elgg_echo("groups:group") . ": " . $group->name;

					if(elgg_instanceof($site, "site", Subsite::SUBTYPE, "Subsite")){
						$result[$site->getACL()] = elgg_echo("members") . " " . $site->name;
					}

					$result[ACCESS_LOGGED_IN] = elgg_echo("LOGGED_IN");
					$result[ACCESS_PUBLIC] = elgg_echo("PUBLIC");
				}
			} else {
					if (elgg_is_admin_logged_in()) {
						$result[ACCESS_PRIVATE] = elgg_echo("access:admin_only");
					}

					if(elgg_instanceof($site, "site", Subsite::SUBTYPE, "Subsite")){
						$result[$site->getACL()] = elgg_echo("members") . " " . $site->name;
					}

					$result[ACCESS_LOGGED_IN] = elgg_echo("LOGGED_IN");
					$result[ACCESS_LOGGED_OUT] = elgg_echo("LOGGED_OUT");
					$result[ACCESS_PUBLIC] = elgg_echo("PUBLIC");
			}
		} else {
			// put group access in the right place
			if (($group = elgg_get_page_owner_entity()) && elgg_instanceof($group, "group")) {
				if (isset($result[$group->group_acl])) {
					unset($result[$group->group_acl]);
					$result[$group->group_acl] = elgg_echo("groups:group") . ": " . $group->name;
				}
			}

			if (elgg_instanceof($site, "site", Subsite::SUBTYPE, "Subsite")) {
				// or on a subsite, so add the subsite ACL to the list
				if ($acl = $site->getACL()) {
					$result[$acl] = elgg_echo("members") . " " . $site->name;
				}

				// check if ACCESS_PUBLIC has been disabled
				if(!$site->hasPublicACL()){
					unset($result[ACCESS_PUBLIC]);
					unset($result[ACCESS_LOGGED_IN]);
				}
			}

			// put logged in access in the right place
			if (isset($result[ACCESS_LOGGED_IN])) {
				unset($result[ACCESS_LOGGED_IN]);
				$result[ACCESS_LOGGED_IN] = elgg_echo("LOGGED_IN");
			}

			// put public access in the right place
			if (isset($result[ACCESS_PUBLIC])) {
				unset($result[ACCESS_PUBLIC]);
				$result[ACCESS_PUBLIC] = elgg_echo("PUBLIC");
			}
		}

		return $result;
	}

	/* add the subsite acl to the menu_builder access write list */
	function subsite_manager_access_write_hook_menu_builder($hook, $type, $returnvalue, $params){
		$result = $returnvalue;

		if(elgg_in_context("menu_builder")){
			if(subsite_manager_on_subsite()){
				$site = elgg_get_site_entity();
				if($acl = $site->getACL()){
					$result = array(
						ACCESS_PUBLIC => elgg_echo("PUBLIC"),
						ACCESS_LOGGED_IN => elgg_echo("LOGGED_IN"),
						$acl => $site->name,
						MENU_BUILDER_ACCESS_LOGGED_OUT => elgg_echo("LOGGED_OUT"),
						ACCESS_PRIVATE => elgg_echo("menu_builder:add:access:admin_only")
					);
				}
			}
		}

		return $result;
	}

	/**
	* Add the subsite ACL to the read access list if on a subsite.
	* On main site they are provided by Elgg core
	*
	* Subsite ACL's have a site_guid of main site
	*
	* @param string $hook
	* @param string $type
	* @param mixed $returnvalue
	* @param mixed $params
	* @return mixed
	*/
	function subsite_manager_access_read_hook($hook, $type, $returnvalue, $params){
		static $read_cache;
		$result = $returnvalue;

		$user_guid = (int) elgg_extract("user_id", $params);
		$site_guid = (int) elgg_extract("site_id", $params);

		if (!empty($user_guid) && !empty($site_guid)) {
			if (!isset($read_cache)) {
				$read_cache = array();
			}

			$checksum = md5($user_guid . "-" . $site_guid);

			// check cache
			if (!isset($read_cache[$checksum])) {
				$read_cache[$checksum] = false;

				$ia = elgg_get_ignore_access();
				elgg_set_ignore_access(true);

				if (($site = elgg_get_site_entity()) && ($site->getGUID() == $site_guid)) {
					if (elgg_instanceof($site, "site", Subsite::SUBTYPE, "Subsite")) {

						if ($site->isUser($user_guid)) {
							if (($acl = $site->getACL()) && !in_array($acl, $result)) {
								$read_cache[$checksum] = $acl;
							}
						}
					}
				}

				elgg_set_ignore_access($ia);
			}

			// get the result from cache
			if ($read_cache[$checksum]) {
				$result[] = $read_cache[$checksum];
			}
		}

		return $result;
	}

	/**
	* checks if a user is a member of a subsite, otherwise display a different page
	*
	* @param string $hook
	* @param string $type
	* @param mixed $returnvalue
	* @param mixed $params
	* @return mixed
	*/
	function subsite_manager_profile_route_hook($hook, $type, $returnvalue, $params){
		if(subsite_manager_on_subsite()){
			$page = elgg_extract("segments", $returnvalue);

			if(isset($page[0])){
				$username = $page[0];

				if($user = get_user_by_username($username)){
					$site = elgg_get_site_entity();

					// is the user a member of this site
					if(!$site->isUser($user->getGUID())){
						// if the user has a pending request and an admin is viewing the allow

						if(!($site->pendingMembershipRequest($user->getGUID()) && elgg_is_admin_logged_in())){
							$body = elgg_view_layout("one_column", array(
								"content" => elgg_view("subsite_manager/subsites/no_member", array("entity" => $user))
							));

							echo elgg_view_page(elgg_echo("subsite_manager:profile:no_member:title", array($user->name)), $body);

							return false;
						}
					}
				}
			}
		}
	}

	/**
	* Get the icon of a user from main site
	*
	* @param string $hook
	* @param string $type
	* @param string $returnvalue
	* @param mixed $params
	* @return string
	*/
	function subsite_manager_usericon_hook($hook, $type, $returnvalue, $params){
		$result = $returnvalue;

		if(subsite_manager_on_subsite()){
			$site = elgg_get_site_entity();
			$main_site = $site->getOwnerEntity();
			if(!empty($result)){
				$result = elgg_normalize_url($result);

				$result = str_ireplace($site->url, $main_site->url, $result);
			}
		}

		return $result;
	}

	/**
	* Change the options to get metadata
	*
	* User metadata can come from a different site then current site
	*
	* @param string $hook
	* @param string $type
	* @param mixed $returnvalue
	* @param mixed $params
	* @return mixed
	*/
	function subsite_manager_metastring_objects_get_hook($hook, $type, $returnvalue, $params){
		$result = $returnvalue;

		$entity_guids = elgg_extract("guids", $params);
		if (!empty($entity_guids) && !is_array($entity_guids)) {
			$entity_guids = array($entity_guids);
		} elseif (empty($entity_guids)) {
			$entity_guids = array();
		}

		if ($entity_guid = elgg_extract("guid", $params)) {
			$entity_guids[] = $entity_guid;
		}

		if (!empty($entity_guids)) {
			$site = elgg_get_site_entity();

			if(!isset($result["wheres"])){
				$result["wheres"] = array();
			}

			$metadata_wheres = array();

			foreach ($entity_guids as $entity_guid) {
				// make sure we have a valid guid
				$entity_guid = (int) $entity_guid;
				if ($entity_guid <= 0) {
					continue;
				}

				// default to current site_guid
				$metadata_site_guid = $site->getGUID();

				// can't use get_entity() because of deadloops
				if ($entity = get_entity_as_row($entity_guid)) {
					if ($entity->type != "user") {
						// default get metadata from the site of the entity
						$metadata_site_guid = $entity->site_guid;
						$metadata_wheres[] = "(n_table.entity_guid = " . $entity_guid . " AND (n_table.site_guid IS NULL OR n_table.site_guid = 0 OR n_table.site_guid = " . $metadata_site_guid . "))";
						$result["site_guids"] = false;
					} elseif(subsite_manager_on_subsite()) {
						global $SUBSITE_MANAGER_MAIN_PROFILE_FIELDS;

						$global_metadata_fields = array(
							// user validation
							"validated",
							"validation_method",
							// official validator
							"validated_official",
							// profile icon
							"icontime",
							"x1",
							"x2",
							"y1",
							"y2"
						);
						if (!empty($SUBSITE_MANAGER_MAIN_PROFILE_FIELDS) && is_array($SUBSITE_MANAGER_MAIN_PROFILE_FIELDS)) {
							$global_metadata_fields = array_merge($global_metadata_fields, array_keys($SUBSITE_MANAGER_MAIN_PROFILE_FIELDS));
						}

						$local_wheres = "((n_table.site_guid IS NULL OR n_table.site_guid = 0 OR n_table.site_guid = " . $metadata_site_guid . ") AND n.string NOT IN ('" . implode("', '", $global_metadata_fields) . "'))";
						$global_wheres = "((n_table.site_guid IS NULL OR n_table.site_guid = 0 OR n_table.site_guid = " . $site->getOwnerGUID() . ") AND n.string IN ('" . implode("', '", $global_metadata_fields) . "'))";

						$metadata_wheres[] = "(n_table.entity_guid = " . $entity_guid . " AND (" . $local_wheres . " OR " . $global_wheres . "))";
						$result["site_guids"] = false;
					} else {
						$metadata_wheres[] = "(n_table.entity_guid = " . $entity_guid . " AND (n_table.site_guid IS NULL OR n_table.site_guid = 0 OR n_table.site_guid = " . $metadata_site_guid . "))";
					}
				}
			}

			if ($metadata_wheres) {
				$result["wheres"][] = "(" . implode(" OR ", $metadata_wheres) . ")";
			}
		}

		return $result;
	}

	function subsite_manager_metastring_objects_get_hook_annotations($hook, $type, $returnvalue, $params){
		$result = $returnvalue;

		if ($entity_guid = elgg_extract("guid", $params)) {
			if (!isset($result["wheres"])) {
				$result["wheres"] = array();
			}

			if ($entity = get_entity_as_row($entity_guid)) {
				$result["site_guids"] = false;

				if ($entity->type != "user") {
					$annotation_site_guid = $entity->site_guid;

					$result["wheres"][] = "(n_table.site_guid IS NULL OR n_table.site_guid = 0 OR n_table.site_guid = " . $annotation_site_guid . ")";
				}
			}
		}

		return $result;
	}

	/**
	 * Create metadata on the correct site
	 *
	 * @param string $hook
	 * @param string $type
	 * @param int $returnvalue
	 * @param mixed $params
	 * @return int $site_guid
	 */
	function subsite_manager_create_metadata_hook($hook, $type, $returnvalue, $params){
		$result = $returnvalue;

		$entity_guid = elgg_extract("entity_guid", $params);
		$metadata_name = elgg_extract("metadata_name", $params);
		$site_guid = elgg_extract("site_guid", $params);

		if(!empty($entity_guid)){
			if($entity_row = get_entity_as_row($entity_guid)){
				if($entity_row->type != "user"){
					// default set metadata to the site of the entity
					$result = (int) $entity_row->site_guid;
				} elseif(subsite_manager_on_subsite()) {
					global $SUBSITE_MANAGER_MAIN_PROFILE_FIELDS;

					$global_metadata_fields = array(
						// user validation
						"validated",
						"validation_method",
						// official validator
						"validated_official",
						// profile icon
						"icontime",
						"x1",
						"x2",
						"y1",
						"y2"
					);
					if(!empty($SUBSITE_MANAGER_MAIN_PROFILE_FIELDS) && is_array($SUBSITE_MANAGER_MAIN_PROFILE_FIELDS)){
						$global_metadata_fields = array_merge($global_metadata_fields, array_keys($SUBSITE_MANAGER_MAIN_PROFILE_FIELDS));
					}

					if(in_array($metadata_name, $global_metadata_fields)){
						$result = elgg_get_site_entity()->getOwnerGUID();
					}
				}
			}
		}

		return $result;
	}

	/**
	* Make sure the entity URL is from the correct site
	*
	* @param string $hook
	* @param string $type
	* @param mixed $returnvalue
	* @param mixed $params
	* @return mixed
	*/
	function subsite_manager_entities_get_url_hook($hook, $type, $returnvalue, $params){
		$result = $returnvalue;

		if(!empty($params) && is_array($params)){
			$entity = elgg_extract("entity", $params);

			if(!empty($entity) && (elgg_instanceof($entity, "group", null, "ElggGroup")) || elgg_instanceof($entity, "object")){
				$site = elgg_get_site_entity();

				if($entity->site_guid != $site->getGUID()){
					if(($correct_site = elgg_get_site_entity($entity->site_guid))){
						// messages are special
						if(elgg_instanceof($entity, "object", "messages") && elgg_instanceof($correct_site, "site", Subsite::SUBTYPE, "Subsite")){
							if($user = elgg_get_logged_in_user_entity()){
								if(!$correct_site->isUser($user->getGUID())){
									$correct_site = elgg_get_site_entity($correct_site->getOwnerGUID());
								}
							}
						}

						// rewrite url
						$result = elgg_normalize_url($result);
						$result = str_ireplace($site->url, $correct_site->url, $result);
					}
				}
			}
		}

		return $result;
	}

	/**
	* Make sure we get the profile manager user categories correctly
	* Merge the site configuration with main site configuration
	*
	* @param string $hook
	* @param string $type
	* @param mixed $returnvalue
	* @param mixed $params
	* @return mixed
	*/
	function subsite_manager_profile_manager_profile_hook($hook, $type, $returnvalue, $params){
		static $running;
		$result = $returnvalue;

		if(subsite_manager_on_subsite() && empty($running)){
			$running = true;
			$site = elgg_get_site_entity();
			elgg_set_config("site_guid", $site->getOwnerGUID());

			$user = elgg_extract("user", $params);
			$edit = elgg_extract("edit", $params);
			$register = elgg_extract("register", $params);
			$profile_type_limit = elgg_extract("profile_type_limit", $params);
			$profile_type_guid = elgg_extract("profile_type_guid", $params);

			// get main fields
			$main_cat_fields = profile_manager_get_categorized_fields($user, $edit, false, $profile_type_limit, $profile_type_guid);

			if($register){

				$main_register_fields = subsite_manager_get_main_profile_fields_configuration(true);

				foreach($main_cat_fields["fields"] as $cat_key => $category){
					foreach($category as $key => $field){
						if($main_register_fields && array_key_exists($field->metadata_name, $main_register_fields)){
							$field->setVolatileData("mandatory", $main_register_fields[$field->metadata_name]['mandatory']);
							$field->setVolatileData("show_on_register", $main_register_fields[$field->metadata_name]['show_on_register']);
						} else {
							if($field->show_on_register !== "yes"){
								unset($main_cat_fields["fields"][$cat_key][$key]);
							}
						}
					}
				}

				// cleanup categories
				foreach($main_cat_fields["fields"] as $cat_key => $category){
					if(empty($category)){
						unset($main_cat_fields["fields"][$cat_key]);
						unset($main_cat_fields["categories"][$cat_key]);
					}
				}
			}

			// merge categories
			$site_cats = elgg_extract("categories", $result, array());
			$main_cats = elgg_extract("categories", $main_cat_fields);

			if(isset($main_cats[0])){
				unset($site_cats[0]);
			}

			$merged_cats = $site_cats + $main_cats;
			ksort($merged_cats);

			if(array_key_exists(-1, $merged_cats)){
				$admin = $merged_cats[-1];
				unset($merged_cats[-1]);
				$merged_cats[-1] = $admin;
			}

			// merge fields
			$site_fields = elgg_extract("fields", $result, array());
			$main_fields = elgg_extract("fields", $main_cat_fields);

			$merged_fields = $main_fields + $site_fields;
			if(is_array($main_fields[0]) && is_array($site_fields[0])){
				$merged_fields[0] = array_merge($main_fields[0], $site_fields[0]);
			}
			ksort($merged_fields);

			$result = array(
				"categories" => $merged_cats,
				"fields" => $merged_fields
			);

			$running = false;
			elgg_set_config("site_guid", $site->getGUID());

		}

		return $result;
	}

	/**
	* Make sure we get the profile manager group categories correctly
	* Merge the site configuration with main site configuration
	*
	* @param string $hook
	* @param string $type
	* @param mixed $returnvalue
	* @param mixed $params
	* @return mixed
	*/
	function subsite_manager_profile_manager_group_hook($hook, $entity_type, $return_value, $params){
		static $running;
		$result = $return_value;

		if(subsite_manager_on_subsite() && empty($running)){
			$running = true;
			$site = elgg_get_site_entity();
			elgg_set_config("site_guid", $site->getOwnerGUID());

			$group = elgg_extract("group", $params);

			$main_cat_fields = profile_manager_get_categorized_group_fields($group);

			$site_fields = elgg_extract("fields", $result, array());
			$main_fields = elgg_extract("fields", $main_cat_fields);

			$merged_fields = array_merge($main_fields, $site_fields);

			$result = array(
				"fields" => $merged_fields
			);

			$running = false;
			elgg_set_config("site_guid", $site->getGUID());
		}

		return $result;
	}

	/**
	 * Allow full views of objects and public(open) groups to be indexed by search engines
	 *
	 * @param unknown_type $hook
	 * @param unknown_type $entity_type
	 * @param unknown_type $return_value
	 * @param unknown_type $params
	 */
	function subsite_manager_display_view_hook($hook, $entity_type, $return_value, $params){
		global $SUBSITE_MANAGER_INDEX_ALLOWED;

		if(!isset($SUBSITE_MANAGER_INDEX_ALLOWED)){
			$vars = elgg_extract("vars", $params);

			if(!empty($vars)){
				if(elgg_extract("full_view", $vars, false) && elgg_extract("entity", $vars, false)){
					if($page_owner = elgg_get_page_owner_entity()){
						if(!elgg_instanceof($page_owner, "group", null, "ElggGroup") || (elgg_instanceof($page_owner, "group", null, "ElggGroup") && $page_owner->isPublicMembership())){
							$SUBSITE_MANAGER_INDEX_ALLOWED = true;
						}
					}
				}
			}
		}
	}

	/**
	* The settings of a plugin have changed, check if we need to reset the cron cache file (only on subsites)
	*
	* @param string $hook
	* @param string $action => the action being called
	* @param unknown_type $return_value
	* @param unknown_type $params
	*/
	function subsite_manager_plugin_action_hook($hook, $action, $return_value, $params){

		// are we on a Subsite, so we can handle cron reset
		if(subsite_manager_on_subsite() && ($action == "plugins/settings/save")){
			$site = elgg_get_site_entity();

			// handling of the cron cache reset is done by the event function
			subsite_manager_remove_cron_cache($site->getGUID());
		}

		// clear plugin order cache
		if (is_memcache_available()) {
			$memcache = new ElggMemcache('subsite_manager');
			$memcache->delete('plugin_order');
		}
	}

	/**
	 * Hook to overrule the default active users done by Elgg core
	 *
	 * @param string $hook
	 * @param string $entity_type
	 * @param mixed $return_value
	 * @param mixed $params
	 * @return mixed ElggEntities
	 */
	function subsite_manager_find_active_users_hook($hook, $entity_type, $return_value, $params){
		$result = false;

		$seconds = (int) elgg_extract("seconds", $params, 600);

		$limit = (int) elgg_extract("limit", $params, 10);
		$offset = (int) elgg_extract("offset", $params, 0);
		$count = (bool) elgg_extract("count", $params, false);

		$site = elgg_get_site_entity();

		if(!empty($seconds)){
			$time = time() - $seconds;

			$result = elgg_get_entities_from_relationship(array(
				"type" => "user",
				"limit" => $limit,
				"offset" => $offset,
				"count" => $count,
				"relationship" => "member_of_site",
				"relationship_guid" => $site->getGUID(),
				"inverse_relationship" => true,
				"site_guids" => false,
				"joins" => array("JOIN " . get_config("dbprefix") . "users_entity u ON e.guid = u.guid"),
				"wheres" => array("u.last_action >= " . $time),
				"order_by" => "u.last_action DESC"
			));
		}

		return $result;
	}

	function subsite_manager_groups_route_hook($hook, $entity_type, $return_value, $params){
		if(!subsite_manager_on_subsite()){
			$page = elgg_extract("segments", $return_value);

			switch($page[0]){
				case "invitations":
					gatekeeper();

					set_input("username", $page[1]);
					$user = elgg_get_page_owner_entity();
					if (empty($user)) {
						$user = elgg_get_logged_in_user_entity();
					}

					// set breadcrumb
					elgg_push_breadcrumb(elgg_echo("groups"), "groups/all");
					$title = elgg_echo("groups:invitations");
					elgg_push_breadcrumb($title);

					// @todo temporary workaround for exts #287.
					$ia = elgg_set_ignore_access(TRUE);
					$invitations = elgg_get_entities_from_relationship(array(
						"type" => "group",
						"limit" => false,
						"site_guids" => false,
						"relationship" => "invited",
						"relationship_guid" => elgg_get_logged_in_user_guid(),
						"inverse_relationship" => true
					));
					elgg_set_ignore_access($ia);

					// get membership requests
					$request_options = array(
						"type" => "group",
						"relationship" => "membership_request",
						"relationship_guid" => $user->getGUID(),
						"limit" => false,
						"full_view" => false,
						"pagination" => false
					);
					$requests = elgg_get_entities_from_relationship($request_options);

					// invite by email allowed
					$invite_email = false;
					$email_invitations = false;

					if (elgg_get_plugin_setting("invite_email", "group_tools") == "yes") {
						$invite_email = true;

						$email_invitations = group_tools_get_invited_groups_by_email($user->email);
					}

					$content = elgg_view("groups/invitationrequests", array(
						"user" => $user,
						"invitations" => $invitations,
						"requests" => $requests,
						"invite_email" => $invite_email,
						"email_invitations" => $email_invitations
					));

					$params = array(
						"content" => $content,
						"title" => $title,
						"filter" => "",
					);
					$body = elgg_view_layout("content", $params);

					echo elgg_view_page($title, $body);
					return false;
					break;
				case "member":
					set_input("username", $page[1]);
					$page_owner = elgg_get_page_owner_entity();

					// set breadcrumb
					elgg_push_breadcrumb(elgg_echo("groups"), "groups/all");
					$title = elgg_echo("groups:yours");
					elgg_push_breadcrumb($title);

					elgg_register_title_button();

					$options = array(
						"type" => "group",
						"relationship" => "member",
						"relationship_guid" => $page_owner->getGUID(),
						"inverse_relationship" => false,
						"full_view" => false,
					);

					if($page_owner->getGUID() == elgg_get_logged_in_user_guid()){
						$options["site_guids"] = false;
					}

					if (!($content = elgg_list_entities_from_relationship_count($options))) {
						$content = elgg_echo("groups:none");
					}

					$params = array(
						"content" => $content,
						"title" => $title,
						"filter" => "",
					);
					$body = elgg_view_layout("content", $params);

					echo elgg_view_page($title, $body);
					return false;
					break;
			}
		}
	}

	/**
	 * Modify the access sql suffix
	 *
	 * @param string $hook
	 * @param string $type
	 * @param string $return_value
	 * @param mixed $params
	 * @return string SQL suffix
	 */
	function subsite_manager_access_get_sql_suffix_hook($hook, $type, $return_value, $params){
		$result = $return_value;

		if(!empty($params) && is_array($params)){
			$table_prefix = elgg_extract("table_prefix", $params);
			$owner = elgg_extract("owner", $params);

			// check if the owner is a subsite admin
			if(subsite_manager_on_subsite() && ($site = elgg_get_site_entity()) && ($site->isAdmin($owner))){
				// extend sql to allow access to all content in this site
				$result = "(" . $result . " OR (" . $table_prefix . "site_guid IN (0, " . $site->getGUID() . ")))";
			}
		}

		return $result;
	}

	/**
	 * Modify the $options used by elgg_get_entities()
	 *
	 * @param string $hook
	 * @param string $type
	 * @param mixed $return_value
	 * @param mixed $params
	 * @return mixed
	 */
	function subsite_manager_get_entities_hook($hook, $type, $return_value, $params){
		$result = $return_value;

		if(subsite_manager_on_subsite() && ($site = elgg_get_site_entity())){
			$types = elgg_extract("types", $params);

			if(!empty($types) && (is_array($types) && in_array("user", $types)) || (!is_array($types) && ($types == "user"))){
				$site_guids = elgg_extract("site_guids", $params);

				if($site_guids !== false){
					if($site_guids === null){
						$site_guids = array();
					} elseif(!is_array($site_guids)){
						$site_guids = array($site_guids);
					}

					$site_guids[] = $site->getOwnerGUID();
					$result["site_guids"] = $site_guids;
				}
			}
		}

		return $result;
	}

	/**
	 * Modify the forward() location
	 *
	 * @param string $hook
	 * @param string $type
	 * @param string $return_value
	 * @param mixed $params
	 * @return string => location to forward to
	 */
	function subsite_manager_forward_hook($hook, $type, $return_value, $params){
		$result = $return_value;

		if(!empty($params) && is_array($params)){
			$forward_url = elgg_extract("forward_url", $params);

			// check walled_garden_by_ip forward to login page
			$login_url = elgg_normalize_url("login");

			if($forward_url == $login_url){
				// you are logged in but not a member of the site
				if(elgg_is_logged_in() && elgg_is_active_plugin("walled_garden_by_ip")){
					// so forward to the join page
					$result = elgg_normalize_url("subsites/join");
				}
			}
		}

		return $result;
	}

	function subsite_manager_messages_route_hook($hook, $type, $return_value, $params){
		$result = $return_value;

		if(!empty($return_value) && is_array($return_value)){
			// you can't access messages on a subsite you're not a member of
			if(subsite_manager_on_subsite() && ($user = elgg_get_logged_in_user_entity())){
				$subsite = elgg_get_site_entity();

				if(!$subsite->isUser($user->getGUID())){
					// not a member forward to main site
					$current_url = current_page_url();
					$parent_site = elgg_get_site_entity($subsite->getOwnerGUID());

					$forward = str_ireplace($subsite->url, $parent_site->url, $current_url);
					forward($forward);
				}
			}

			// check if we need to overrule the page
			$page = elgg_extract("segments", $return_value);

			switch($page[0]){
				case "inbox":
					$result = false;

					set_input("username", $page[1]);
					include(dirname(dirname(__FILE__)) . "/pages/messages/inbox.php");
					break;
				case "sent":
					$result = false;

					set_input("username", $page[1]);
					include(dirname(dirname(__FILE__)) . "/pages/messages/sent.php");
					break;
			}
		}

		return $result;
	}

	function subsite_manager_widget_url_hook($hook, $type, $return_value, $params){
		$result = $return_value;

		if(!$result && !empty($params) && is_array($params)){
			$widget = elgg_extract("entity", $params);

			if(!empty($widget) && elgg_instanceof($widget, "object", "widget")){
				switch($widget->handler){
					case "subsites":
						$result = "/subsites";
						break;
					case "subsite_membership_requests":
						$result = "/admin/users/membership";
						break;
				}
			}
		}

		return $result;
	}

	function subsite_manager_user_support_admins_hook($hook, $type, $return_value, $params){
		$result = $return_value;

		if(!empty($params) && is_array($params)){
			$ticket = elgg_extract("entity", $params);

			$subsite_admin_guids = array();
			$site = elgg_get_site_entity($ticket->site_guid);
			if(elgg_instanceof($site, "site", Subsite::SUBTYPE, "Subsite")){
				if($admin_guids = $site->getAdminGuids()){
					$subsite_admin_guids = $admin_guids;
				}
			}

			$plugin_setting_name = elgg_namespace_plugin_private_setting("user_setting", "admin_notify", "user_support");
			if(!subsite_manager_check_global_plugin_setting("user_support", "use_global_usersettings")){
				$plugin_setting_name = str_replace(ELGG_PLUGIN_USER_SETTING_PREFIX . "user_support:", ELGG_PLUGIN_USER_SETTING_PREFIX . "user_support:" . $site->getGUID() . ":", $plugin_setting_name);
			}

			$subsite_admin_query = "";
			if(!empty($subsite_admin_guids)){
				$subsite_admin_query = " OR e.guid IN (" . implode(",", $subsite_admin_guids) . ")";
			}

			$options = array(
				"type" => "user",
				"limit" => false,
				"site_guids" => false,
				"relationship" => "member_of_site",
				"relationship_guid" => $site->getGUID(),
				"inverse_relationship" => true,
				"joins" => array(
					"JOIN " . get_config("dbprefix") . "private_settings ps ON e.guid = ps.entity_guid",
					"JOIN " . get_config("dbprefix") . "users_entity ue ON e.guid = ue.guid"
				),
				"wheres" => array(
					"(ps.name = '" . $plugin_setting_name . "' AND ps.value = 'yes')",
					"(ue.admin = 'yes' " . $subsite_admin_query . ")",
					"(e.guid <> " . $ticket->getOwnerGUID() . ")"
				)
			);

			if($new_admins = elgg_get_entities_from_relationship($options)){
				if(!empty($result)){
					if(!is_array($result)){
						$result = array($result);
					}

					$admin_guids = array();
					foreach($result as $old_admin){
						$admin_guids[] = $old_admin->getGUID();
					}

					foreach($new_admins as $admin){
						if(!in_array($admin->getGUID(), $admin_guids)){
							$result[] = $admin;
						}
					}
				} else {
					// haven't found anything yet, so overrule with new value
					$result = $new_admins;
				}
			}
		}

		return $result;
	}

	function subsite_manager_object_notification_user_options_hook($hook, $type, $return_value, $params){
		$result = $return_value;

		if (!empty($params) && is_array($params)) {
			$entity = elgg_extract("entity", $params);
			$options = elgg_extract("options", $params);

			// set limit to false
			$options["limit"] = false;

			// prepare options
			if (!isset($options["joins"])) {
				$options["joins"] = array();
			} elseif (!is_array($options["joins"])) {
				$options["joins"] = array($options["joins"]);
			}

			if (!isset($options["wheres"])) {
				$options["wheres"] = array();
			} elseif (!is_array($options["wheres"])) {
				$options["wheres"] = array($options["wheres"]);
			}

			$site = elgg_get_site_entity($entity->site_guid);
			$container = $entity->getContainerEntity();

			if (!empty($container) && elgg_instanceof($container, "group")) {
				// user has to be a member of the group
				$options["joins"][] = "JOIN " . elgg_get_config("dbprefix") . "entity_relationships r2 ON e.guid = r2.guid_one";
				$options["wheres"][] = "(r2.relationship = 'member' AND r2.guid_two = " . $container->getGUID() . ")";

			} elseif (!empty($site) && elgg_instanceof($site, "site", Subsite::SUBTYPE, "Subsite")) {
				// user has to be a member of the site
				$options["joins"][] = "JOIN " . elgg_get_config("dbprefix") . "entity_relationships r2 ON e.guid = r2.guid_one";
				$options["wheres"][] = "(r2.relationship = 'member_of_site' AND r2.guid_two = " . $site->getGUID() . ")";

			}

			// overrule options
			$result = $options;
		}

		return $result;
	}

	/* tell the search_advanced plugin this user can multisite search */
	function subsite_manager_search_multisite_search_hook($hook, $type, $return_value, $params){
		if(!$return_value){
			if(subsite_manager_get_user_subsites()){
				$return_value = true;
			}

		}
		return $return_value;
	}

	/**
	* function to check if custom fields on register have been filled (if required)
	*
	* @param $hook_name
	* @param $entity_type
	* @param $return_value
	* @param $parameters
	* @return unknown_type
	*/
	function subsite_manager_action_register_hook($hook_name, $entity_type, $return_value, $parameters){

		elgg_make_sticky_form('register');
		elgg_make_sticky_form('profile_manager_register');

		$required_fields = array();

		// new
		if($fields = subsite_manager_get_main_profile_fields_configuration(true)){
			foreach($fields as $field_name => $field_config){
				if($field_config["mandatory"] == "yes"){
					$required_fields[] = $field_name;
				}
			}
		}

		if($required_fields){

			$custom_profile_fields = array();

			foreach($_POST as $key => $value){
				if(strpos($key, "custom_profile_fields_") == 0){
					$key = substr($key, 22);
					$custom_profile_fields[$key] = $value;
				}
			}

			foreach($required_fields as $field_name){

				$passed_value = $custom_profile_fields[$field_name];

				if(empty($passed_value)){
					register_error(elgg_echo("profile_manager:register_pre_check:missing", array(elgg_echo("profile:" . $field_name))));
					forward(REFERER);
				}
			}
		}
	}

	function subsite_manager_saml_route_hook($hook, $type, $returnvalue, $params) {

		$page = elgg_extract("segments", $returnvalue);

		switch ($page[0]) {
			case "login":
				if (isset($page[1])) {
					set_input("saml_source", $page[1]);
				}

				include(dirname(dirname(__FILE__)) . "/procedures/simplesaml/login.php");

				break;
		}
	}

	/**
	 * Prevent a user from registering on a site
	 *
	 * @param string $hook
	 * @param string $type
	 * @param bool $returnvalue
	 * @param array $params
	 * @return boolean
	 */
	function subsite_manager_block_user_registration($hook, $type, $returnvalue, $params) {
		// need to show all users in order to be able to delete it
		access_show_hidden_entities(true);

		// make sure registration fails, so the user gets removed
		return false;
	}

	/**
	 * User support staff must be made staff members on the current site
	 *
	 * @param string $hook
	 * @param string $type
	 * @param array $returnvalue
	 * @param array $params
	 * @return array
	 */
	function subsite_manager_user_support_staff_hook($hook, $type, $returnvalue, $params) {
		$result = $returnvalue;

		if (!empty($result) && is_array($result)) {
			$support_staff_id = add_metastring("support_staff");
			$site = elgg_get_site_entity();

			$result["wheres"] = array("(md.name_id = " . $support_staff_id . " AND md.site_guid = " . $site->getGUID() . ")");
		}

		return $result;
	}

	/**
	 * Depending who and where you are, the plugin view is different
	 *
	 * @param string	$hook			What hook is fired
	 * @param string	$type			Of what type
	 * @param string	$returnvalue	The default return value
	 * @param array		$params			Provided parameters
	 * @return string
	 */
	function subsite_manager_plugin_view_hook($hook, $type, $returnvalue, $params) {
		$result = $returnvalue;

		// restrict the displayed plugin for subsite admins (on subsites)
		if (subsite_manager_on_subsite() && !subsite_manager_is_superadmin_logged_in()) {

			if (!empty($params) && is_array($params)) {
				$vars = elgg_extract("vars", $params);
				$entity = elgg_extract("entity", $vars);

				if (!empty($entity) && elgg_instanceof($entity, "object", "plugin")) {
					if (!subsite_manager_show_plugin($entity)) {
						$result = "<div class='hidden'></div>";
					}
				}
			}
		}

		return $result;
	}

	/**
	 *	Change the display of the plugin action button (activate/deactivate)
	 *
	 * @param string	$hook			What hook is fired
	 * @param string	$type			Of what type
	 * @param string	$returnvalue	The default return value
	 * @param array		$params			Provided parameters
	 * @return string
	 */
	function subsite_manager_plugin_action_button_hook($hook, $type, $returnvalue, $params) {
		$result = $returnvalue;

		if (!empty($params) && is_array($params)) {
			$entity = elgg_extract("entity", $params);

			if (!empty($entity) && elgg_instanceof($entity, "object", "plugin")) {
				$global_enabled_plugins = subsite_manager_get_global_enabled_plugins();

				if (!empty($global_enabled_plugins) && is_array($global_enabled_plugins) && in_array($entity->getID(), $global_enabled_plugins)) {
					$result = "<div style='width:20px;height:16px'></div>";
				}
			}
		}

		return $result;
	}

	/**
	 * Make sure the correct site administrators are notified for security tools updates, not all admins
	 *
	 * @param string     $hook        notify_admins
	 * @param string     $type        security_tools
	 * @param ElggUser[] $returnvalue default admins to be notified (this is too many)
	 * @param array      $params      some more information to bas the result set on
	 *
	 * @return ElggUser[]
	 */
	function subsite_manager_notify_admins_security_tools_hook($hook, $type, $returnvalue, $params) {
		$result = array();

		if (!empty($params) && is_array($params)) {
			$user = elgg_extract("user", $params);

			if (subsite_manager_on_subsite()) {
				// get subsite admins
				$site = elgg_get_site_entity();

				$user_guids = $site->getAdminGuids();
				if (!empty($user_guids)) {

					foreach ($user_guids as $user_guid) {
						if ($user_guid != $user->getGUID()) {
							$admin = get_user($user_guid);

							if (!empty($admin)) {
								$result[] = $admin;
							}
						}
					}
				}
			} else {
				// get main admins
				$options = array(
					"type" => "user",
					"limit" => false,
					"private_setting_name_value_pairs" => array(
						"name" => "superadmin",
						"value" => true
					),
					"joins" => array("JOIN " . get_config("dbprefix") . "users_entity ue ON ue.guid = e.guid"),
					"wheres" => array(
						"(ue.admin = 'yes')",
						"(e.guid <> " . $user->getGUID() . ")"
					),
				);

				$admins = elgg_get_entities_from_private_settings($options);
				if (!empty($admins)) {
					$result = $admins;
				}
			}
		}

		return $result;
	}

	/**
	 * Protect the translation editor from unautherized admins
	 *
	 * @param string $hook        'route'
	 * @param string $type        'translation_editor'
	 * @param array  $returnvalue page elements
	 * @param null   $params      null
	 */
	function subsite_manager_translation_editor_route_hook($hook, $type, $returnvalue, $params) {

		if (subsite_manager_on_subsite() && !subsite_manager_is_superadmin_logged_in()) {
			register_error(elgg_echo("subsite_manager:action:error:on_subsite"));
			forward(REFERER);
		}
	}

	/**
	 * Determine if a certain user is member of the site and thus can write to it.
	 *
	 * @param ElggUser $user
	 * @param ElggSite $site
	 *
	 * @return bool true if write is allowed
	 */
	function subsite_manager_can_write_to_site($user, $site) {
		if (!$user) {
			return false;
		}

		if (!$site) {
			$site = elgg_get_site_entity();
		}

		if (!elgg_instanceof($site, "site", Subsite::SUBTYPE, "Subsite")) {
			return true; // not on subsite, write is always allowed on main site
		}

		if (subsite_manager_is_superadmin()) {
			return true;
		}

		if ($site->isUser()) {
			return true;
		} else {
			return false;
		}
	}

    /**
	 * Do not allow non-subsite members to join an open group if the subsite is closed
	 *
	 * @return bool false if write is not allowed
	 */
    function subsite_manager_group_join_action_hook($hook, $type, $returnvalue, $params) {
		if (!subsite_manager_on_subsite()) {
			return;
		}

		$site = elgg_get_site_entity();
		if ($site->getMembership() == Subsite::MEMBERSHIP_OPEN) {
			return; // site is open, so everyone can become member of the group
		}

		$group_guid = get_input('group_guid');
		$group = get_entity($group_guid);
		if ($group->canEdit()) {
			return; // user is admin of the group of subsite, so let the default permission handler do its work
		}

		if (!$group->isPublicMembership()) {
			return; // group is closed, so let the default group permission handler do its work
		}

		if (!$site->isUser()) {
			register_error(elgg_echo('subsite_manager:group:could_not_join'));
			return false;
		}
    }

    function subsite_manager_admin_user_delete_hook($hook, $type, $returnvalue, $params) {
    	if (!subsite_manager_is_superadmin()) {
    		return false;
    	}
    }

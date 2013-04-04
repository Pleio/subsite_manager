<?php 

	require_once(dirname(__FILE__) . "/lib/functions.php");

	global $SUBSITE_MANAGER_CUSTOM_DOMAIN;

	function subsite_manager_siteid_hook($hook, $type, $return, $params){
		global $SUBSITE_MANAGER_CUSTOM_DOMAIN;
		
		$result = false;
		
		elgg_register_classes(dirname(__FILE__) . "/classes/");
		
		if(array_key_exists("HTTPS", $_SERVER)){
			$protocol = "https";
		} else {
			$protocol = "http";
		}
		
		if(strpos($_SERVER["HTTP_HOST"], "www.") === 0){
			$alt_host = str_replace("www.", "", $_SERVER["HTTP_HOST"]);
		} else {
			$alt_host = "www." . $_SERVER["HTTP_HOST"];
		}
		
		$url = $protocol . "://" . $_SERVER["HTTP_HOST"] . "/";
		$alt_url = $protocol . "://" . $alt_host . "/";
		
		if($site = get_site_by_url($url)){
			$result = $site->getGUID();
		} elseif($site = get_site_by_url($alt_url)){
			$result = $site->getGUID();
		} else {
			// no site found, forward to main site
			$default_site_guid = (int) datalist_get("default_site");
			$default_site = get_entity($default_site_guid);
			
			forward($default_site->url);
		}
		
		// check if we have a custom domain
		if(!empty($site)){
			$current_cookie_domain = ini_get("session.cookie_domain");
			
			if(!empty($current_cookie_domain)){
				$url_parts = parse_url($site->url);
				
				$offset = 0;
				if(substr($current_cookie_domain, 0, 1) === "."){
					$offset = 1;
				}
				
				if(substr($url_parts["host"], $offset - strlen($current_cookie_domain)) !== substr($current_cookie_domain, $offset)){
					ini_set("session.cookie_domain", $url_parts["host"]);
					$SUBSITE_MANAGER_CUSTOM_DOMAIN = true;
				}
			}
		}
		
		return $result;
	}
	
	function subsite_manager_boot_system_handler($event, $type, $object){
		global $CONFIG;
		
		// enable simple cache
 		//$CONFIG->simplecache_enabled = false;
 		//$CONFIG->viewpath_cache_enabled = false;
	}
	
	function subsite_manager_boot_system_plugins_event_handler($event, $type, $object){
		global $CONFIG;
		
		// needs to be set for links in html head
		$viewtype = get_input('view', 'default');
		$site_guid = elgg_get_site_entity()->getGUID();
		
		$lastcached = datalist_get("sc_lastcached_" . $viewtype . "_" . $site_guid);
		$CONFIG->lastcache = $lastcached;
		
		if(subsite_manager_on_subsite()){
			// make sure we can access everything
			$old_ia = elgg_set_ignore_access(true);
			
			// get the current site
			$site = elgg_get_site_entity();
			
			// check if we need to update plugin order
			$main_plugin_update = (int) datalist_get("plugin_order_last_update");
			$site_plugin_order_update = (int) $site->getPrivateSetting("plugin_order_last_update");
			
			if($site_plugin_order_update < $main_plugin_update){
				// reorder of the plugins is needed
				if($main_plugin_order = subsite_manager_get_main_plugin_order()){
					// we need to trace an error, so add logging
					error_log("SUBSITE MANAGER: reorder plugins: " . $site->name . "(" . $site_guid . ")");
					
					// make sure all plugins are there
					elgg_generate_plugin_entities();
					
					elgg_set_plugin_priorities($main_plugin_order);
					
					$site->setPrivateSetting("plugin_order_last_update", time());
					
					elgg_register_event_handler("plugins_boot", "system", "elgg_filepath_cache_reset", 100);
					elgg_register_event_handler("plugins_boot", "system", "elgg_invalidate_simplecache", 200);
				}
			}
			
			// get currently active plugin
			$active_plugins = elgg_get_plugins("active");

			// make into names
			$active_plugin_names = array();
			$activate_plugins = array();
			if(!empty($active_plugins)){
				foreach($active_plugins as $plugin){
					$active_plugin_names[] = $plugin->getID();
				}
			}

			// check if required plugins are active
			if(($global_plugins = subsite_manager_get_global_enabled_plugins()) && !empty($global_plugins)){

				// check if required plugins are active
				foreach($global_plugins as $should_be_active){
					if(!in_array($should_be_active, $active_plugin_names)){
						$activate_plugins[] = $should_be_active;
					}
				}
			}
			
			// check if this is the first run, if so do stuff
			if($site->getPrivateSetting("firstrun")){
				$site->removePrivateSetting("firstrun");
				
				if($enable_on_create = $site->getOwnerEntity()->getPrivateSetting("enabled_for_new_subsites")){
					$enable_on_create = string_to_tag_array($enable_on_create);
					
					foreach($enable_on_create as $should_be_active){
						if(!in_array($should_be_active, $active_plugin_names)){
							$activate_plugins[] = $should_be_active;
						}
					}
				}
			}
			
			// make sure we don't do duplicate plugins
			$activate_plugins = array_unique($activate_plugins);
				
			// enable plugins that should be active
			if(!empty($activate_plugins)){
				// check for new plugin entities
				elgg_generate_plugin_entities();
				
				set_time_limit(0);
				$plugins = elgg_get_plugins('any');
				
				foreach($plugins as $plugin){
					if(in_array($plugin->getID(), $activate_plugins)){
						try {
							$plugin->activate();
						} catch (Exception $e){
						}	
					}
				}

				elgg_register_event_handler("plugins_boot", "system", "elgg_filepath_cache_reset", 100);
				elgg_register_event_handler("plugins_boot", "system", "elgg_invalidate_simplecache", 200);
			}
			
			// restore access settings
			elgg_set_ignore_access($old_ia);
		}
	}

	// register hook to get correct site_id
	elgg_register_plugin_hook_handler("siteid", "system", "subsite_manager_siteid_hook");
	
	// register events
	elgg_register_event_handler("boot", "system", "subsite_manager_boot_system_handler", 11);
	elgg_register_event_handler("boot", "system", "subsite_manager_boot_system_plugins_event_handler", 600); // handles mandatory plugins
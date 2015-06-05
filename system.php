<?php

	require_once(dirname(__FILE__) . "/lib/functions.php");

	global $SUBSITE_MANAGER_CUSTOM_DOMAIN;

	function subsite_manager_siteid_hook($hook, $type, $return, $params){
		global $SUBSITE_MANAGER_CUSTOM_DOMAIN;

		$result = false;

		elgg_register_classes(dirname(__FILE__) . "/classes/");

		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "") {
			$protocol = "https";
		} elseif (isset($_SERVER['HTTP_X_HTTPS_SESSION']) && $_SERVER['HTTP_X_HTTPS_SESSION'] != "") {
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
		global $SUBSITE_MANAGER_PLUGINS_BOOT;

		// needs to be set for links in html head
		$viewtype = get_input('view', 'default');
		$site_guid = elgg_get_site_entity()->getGUID();

		$lastcached = datalist_get("sc_lastcached_" . $viewtype . "_" . $site_guid);
		$CONFIG->lastcache = $lastcached;

		// skip non-subsites
		if(!subsite_manager_on_subsite()) {
			return true;
		}

		$site = elgg_get_site_entity();
		$to_activate = $site->getPrivateSetting('subsite_manager_plugins_activate');
		if ($to_activate) {
			$SUBSITE_MANAGER_PLUGINS_BOOT = true;

			$site->removePrivateSetting('subsite_manager_plugins_activate');
			$to_activate = unserialize($to_activate);

			set_time_limit(0);

			elgg_generate_plugin_entities();

			$old_ia = elgg_set_ignore_access(true);

			$plugins = elgg_get_plugins('any');
			foreach ($plugins as $plugin) {
				if (in_array($plugin->getID(), $to_activate)) {
					try {
						$plugin->activate();
					} catch (Exception $e) {}
				}
			}

			elgg_set_ignore_access($old_ia);
			$SUBSITE_MANAGER_PLUGINS_BOOT = false;

			elgg_register_event_handler("ready", "system", "subsite_manager_ready_system_handler", 100);
		}
	}

	// register hook to get correct site_id
	elgg_register_plugin_hook_handler("siteid", "system", "subsite_manager_siteid_hook");

	// register events
	elgg_register_event_handler("boot", "system", "subsite_manager_boot_system_handler", 11);
	elgg_register_event_handler("boot", "system", "subsite_manager_boot_system_plugins_event_handler", 600); // handles mandatory plugins
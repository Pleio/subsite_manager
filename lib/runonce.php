<?php

	function subsite_manager_runonce() {
		// register a new substype in the database and link it to the right classname
		add_subtype("site", Subsite::SUBTYPE, "Subsite");

		// Update the database with extra columns for multisite
		run_sql_script(dirname(dirname(__FILE__)) . "/scripts/add_columns.sql");
	}

	function subsite_manager_runonce_elgg18(){
		$options = array(
			"type" => "site",
			"subtype" => Subsite::SUBTYPE,
			"limit" => false
		);

		if($subsites = elgg_get_entities($options)){
			$global_plugins = subsite_manager_get_global_enabled_plugins();

			foreach ($subsites as $subsite) {
				$subsite->setPrivateSetting('subsite_manager_plugins_activate', serialize($global_plugins));
			}
		}
	}

	function subsite_manager_fix_piwik_settings() {
		global $SUBSITE_MANAGER_IGNORE_WRITE_ACCESS;

		if (subsite_manager_on_subsite()) {
			$site = elgg_get_site_entity();

			if ($piwik_settings = $site->phloor_analytics_piwik_settings) {
				$SUBSITE_MANAGER_IGNORE_WRITE_ACCESS = true;

				if ($site->canEdit()) {
					// log to the error log that we did something
					error_log("PIWIK saving settings for " . $site->name . " (" . $site->getGUID() . ")");
					error_log("PIWIK settings: " . $piwik_settings);

					if ($piwik_settings = json_decode($piwik_settings, true)) {
						if (!empty($piwik_settings) && is_array($piwik_settings)) {
							$enabled = elgg_extract("enable_tracking", $piwik_settings);
							$piwik_url = elgg_extract("path_to_piwik", $piwik_settings);
							$piwik_site_id = elgg_extract("site_guid", $piwik_settings);

							if ($enabled == "true") {
								if (!empty($piwik_url) && !empty($piwik_site_id)) {
									// check if analytics is enabled
									if (!elgg_is_active_plugin("analytics")) {
										// not active so enable
										if ($plugin = elgg_get_plugin_from_id("analytics")) {
											$plugin->activate();

											elgg_invalidate_simplecache();
											elgg_reset_system_cache();
										}
									}

									// save settings, if not exists
									if(!elgg_get_plugin_setting("piwik_url", "analytics") && !elgg_get_plugin_setting("piwik_site_id", "analytics")) {
										elgg_set_plugin_setting("piwik_url", $piwik_url, "analytics");
										elgg_set_plugin_setting("piwik_site_id", $piwik_site_id, "analytics");
									}
								}
							}
						}
					}

					// remove the settings so we don't do this again
					unset($site->phloor_analytics_piwik_settings);
				}

				// unset write access
				$SUBSITE_MANAGER_IGNORE_WRITE_ACCESS = false;
			}
		}
	}

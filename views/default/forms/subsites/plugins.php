<?php

	$plugins = elgg_extract("plugins", $vars);
	$subsites_count = elgg_extract("subsites_count", $vars, 0);
	$counters = elgg_extract("counters", $vars);
	$required_plugins = elgg_extract("required_plugins", $vars, array());

	if(!empty($plugins)){
		$list = "<div>";
		$list .= "<table class='elgg-table'>";

		$list .= "<tr>";
		$list .= "<th>" . elgg_echo("admin:plugins") . "</th>";
		$list .= "<th class='center'>" . elgg_echo("subsite_manager:subsite:plugins:enable_everywhere") . "</th>";
		$list .= "<th class='center'>" . elgg_echo("subsite_manager:subsite:plugins:enabled_for_new_subsites") . "</th>";
		$list .= "<th class='center'>" . elgg_echo("subsite_manager:subsite:plugins:fallback_to_main_settings") . "</th>";
		$list .= "<th class='center'>" . elgg_echo("subsite_manager:subsite:plugins:use_global_usersettings") . "</th>";
		$list .= "<th class='center'>" . elgg_echo("subsite_manager:subsite:plugins:subsite_default_manageable") . "</th>";
		$list .= "</tr>";

		foreach($plugins as $plugin){
			$description = $plugin->getFriendlyName();
			if($manifest = $plugin->getManifest()){
				$description .= ": " . $manifest->getDescription();
			}

			$enabled_everywhere = "";
			$enabled_new = "";
			$fallback_main_settings = "";
			$use_global_settings = "";
			$manageable = "";

			if(subsite_manager_check_global_plugin_setting($plugin->getID(), "fallback_to_main_settings")){
				$fallback_main_settings = "checked='checked'";
			}
			if(subsite_manager_check_global_plugin_setting($plugin->getID(), "use_global_usersettings")){
				$use_global_settings = "checked='checked'";
			}
			if(subsite_manager_check_global_plugin_setting($plugin->getID(), "enabled_for_new_subsites")){
				$enabled_new = "checked='checked'";
			}
			if(subsite_manager_check_global_plugin_setting($plugin->getID(), "enabled_everywhere")){
				$enabled_everywhere = "checked='checked'";
			}
			if(subsite_manager_check_global_plugin_setting($plugin->getID(), "subsite_default_manageable")){
				$manageable = "checked='checked'";
			}

			$settings = array();

			// is required plugin
			if(in_array($plugin->getID(), $required_plugins)){
				$settings["enabled_everywhere"] = "<input type='checkbox' name='enabled_everywhere[]' value='" . $plugin->getID() . "' checked='checked' disabled='disabled' />";
				$settings["enabled_for_new_subsites"] = "<input type='checkbox' name='enabled_for_new_subsites[]' value='" . $plugin->getID() . "' checked='checked' disabled='disabled' />";
				$settings["subsite_default_manageable"] = "<input type='checkbox' name='subsite_default_manageable[]' value='" . $plugin->getID() . "' checked='checked' disabled='disabled' />";
			} else {
				$settings["enabled_everywhere"] = "<input type='checkbox' name='enabled_everywhere[]' value='" . $plugin->getID() . "' " . $enabled_everywhere . " />";
				$settings["enabled_for_new_subsites"] = "<input type='checkbox' name='enabled_for_new_subsites[]' value='" . $plugin->getID() . "' " . $enabled_new . " />";
				$settings["subsite_default_manageable"] = "<input type='checkbox' name='subsite_default_manageable[]' value='" . $plugin->getID() . "' " . $manageable . " />";
			}

			if(elgg_view_exists("plugins/" . $plugin->getID() . "/settings") || $plugin->getAllSettings()){
				$settings["fallback_to_main_settings"] = "<input type='checkbox' name='fallback_to_main_settings[]' value='" . $plugin->getID() . "' " . $fallback_main_settings . " />";
			} else {
				$settings["fallback_to_main_settings"] = "&nbsp;";
			}

			$list .= "<tr title='" . $description . "'>";
			$list .= "<td>" . $plugin->getID() . "</td>";
			$list .= "<td class='center'>" . $settings["enabled_everywhere"] . "</td>";
			$list .= "<td class='center'>" . $settings["enabled_for_new_subsites"] . "</td>";
			$list .= "<td class='center'>" . $settings["fallback_to_main_settings"] . "</td>";
			$list .= "<td class='center'><input type='checkbox' name='use_global_usersettings[]' value='" . $plugin->getID() . "' " . $use_global_settings . " /></td>";
			$list .= "<td class='center'>" . $settings["subsite_default_manageable"] . "</td>";
			$list .= "</tr>";
		}

		$list .= "</table>";
		$list .= "</div>";

		$list .= "<div class='elgg-foot'>";
		$list .= elgg_view("input/submit", array("value" => elgg_echo("update")));
		$list .= elgg_view("output/url", array(
			"href" => "/admin/subsites/update_plugins",
			"class" => "elgg-button elgg-button-action",
			"text" => elgg_echo("subsite_manager:subsite:plugins:update_subsites")
		));
		$list .= "</div>";
	} else {
		$list .= elgg_echo("subsite_manager:plugin:no_plugins");
	}

	echo $list;
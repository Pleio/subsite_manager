<?php

	/**
	 * Shows the main admins and all Subsite admins per Subsite
	 */

	// get main admins
	$main_options = array(
		"type" => "user",
		"limit" => false,
		"site_guids" => false,
		"private_setting_name_value_pairs" => array(
			"name" => "superadmin",
			"value" => true
		),
		"joins" => array("JOIN " . get_config("dbprefix") . "users_entity ue ON ue.guid = e.guid"),
		"wheres" => array("ue.admin = 'yes'"),
		"order_by" => "ue.name",
		"full_view" => false,
		"pagination" => false,
		"view_type_toggle" => false
	);
	
	if ($main_admins = elgg_get_entities_from_private_settings($main_options)) {
		$main_list = elgg_view_entity_list($main_admins, $main_options);
	} else {
		$main_list = elgg_echo("notfound");
	}
	
	// list main admins
	echo elgg_view_module("inline", elgg_echo("subsite_manager:subsites:admins:main_admins"), $main_list);
	
	// get all subsites
	$limit = (int) max(get_input('limit', 50), 0);
	$offset = (int) max(get_input('offset', 0), 0);
	$subsite_options = array(
		"type" => "site",
		"subtype" => Subsite::SUBTYPE,
		"limit" => $limit,
		"offset" => $offset,
		"joins" => array("JOIN " . get_config("dbprefix") . "sites_entity se ON se.guid = e.guid"),
		"order_by" => "se.name ASC"
	);
	
	$export_button = "";
	if ($subsites = elgg_get_entities($subsite_options)) {
		
		// create export button
		$export_button = elgg_view("output/url", array(
			"text" => elgg_echo("export"),
			"href" => "action/subsites/export_admins",
			"is_action" => true,
			"class" => "elgg-button elgg-button-action float-alt"
		));
		
		// add pagination
		$subsite_options["count"] = true;
		$count = elgg_get_entities($subsite_options);
		
		$pagination = elgg_view("navigation/pagination", array("limit" => $limit, "offset" => $offset, "count" => $count));
		
		$subsite_list = $pagination;
		
		foreach ($subsites as $subsite) {
			// some stats
			$temp_list = "<div class='elgg-subtext'>";
			$temp_list .= elgg_echo("members") . ": " . $subsite->getMembers(array("count" => true)) . "<br />";
			if ($time = subsite_manager_get_subsite_last_activity($subsite->getGUID())) {
				$temp_list .= elgg_echo("content:latest") . ": " . elgg_view_friendly_time($time);
			} else {
				$temp_list .= elgg_echo("content:latest") . ": " . elgg_echo("river:none");
			}
			$temp_list .= "</div>";
			
			// get admins
			if ($admins = $subsite->getAdminGuids()) {
				$subadmin_options = array(
					"type" => "user",
					"limit" => false,
					"site_guids" => false,
					"joins" => array("JOIN " . get_config("dbprefix") . "users_entity ue ON ue.guid = e.guid"),
					"wheres" => array("e.guid IN (" . implode(",", $admins) . ")"),
					"order_by" => "ue.name",
					"full_view" => false,
					"pagination" => false,
					"view_type_toggle" => false
				);
				
				$temp_list .= "<div class='elgg-divide-left pls'>";
				$temp_list .= elgg_list_entities($subadmin_options);
				$temp_list .= "</div>";
			} else {
				$temp_list .= elgg_echo("subsite_manager:subsites:admins:subsite_admins:none");
			}
			
			// add to list
			$subsite_list .= elgg_view_module("info", $subsite->name, $temp_list);
			
			// invalidate cache
			_elgg_invalidate_cache_for_entity($subsite->getGUID());
		}
		
		$subsite_list .= $pagination;
	} else {
		$subsite_list = elgg_echo("notfound");
	}
	
	// list subsite admins
	echo elgg_view_module("inline", elgg_echo("subsite_manager:subsites:admins:subsite_admins") . $export_button, $subsite_list);
	
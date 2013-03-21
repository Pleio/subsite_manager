<?php 

	if(subsite_manager_on_subsite()){
		$site = elgg_get_site_entity();
		
		if($admins = $site->getAdminGuids()){
			$options = array(
				"type" => "user",
				"limit" => false,
				"site_guids" => false,
				"joins" => array("JOIN " . get_config("dbprefix") . "users_entity ue ON ue.guid = e.guid"),
				"wheres" => array(
					"(e.guid IN (" . implode(",", $admins) . "))"
				),
				"order_by" => "ue.name",
				"full_view" => false,
				"pagination" => false,
				"view_type_toggle" => false
			);
			
			$body = elgg_list_entities($options);
		}
	} else {
		$options = array(
			"type" => "user",
			"limit" => false,
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
		
		if($users = elgg_get_entities_from_private_settings($options)){
			$body = elgg_view_entity_list($users, $options);
		}
	}
	
	if(empty($body)){
		$body = elgg_echo("notfound");
	}
	
	echo elgg_view_module("inline", "", $body);
<?php

	if (!subsite_manager_on_subsite() && subsite_manager_is_superadmin_logged_in()) {
		// offer bulk actions to superadmins
		$form_vars = array(
			"id" => "subsite-manager-newest-users-bulk-action"
		);
		echo elgg_view_form("subsite_manager/newest_users/bulk_action", $form_vars);
	} else {
		
		// newest users
		$users = elgg_list_entities_from_relationship(array(
			"type" => "user",
			"site_guids" => false,
			"relationship"=> "member_of_site",
			"relationship_guid" => elgg_get_site_entity()->getGUID(),
			"inverse_relationship" => true,
			"order_by" => "r.time_created DESC",
			"full_view" => FALSE
		));
		
		echo elgg_view_module("inline", elgg_echo('admin:users:newest'), $users);
	}
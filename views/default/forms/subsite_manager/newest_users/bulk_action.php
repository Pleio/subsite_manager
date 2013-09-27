<?php

	$limit = (int) get_input("limit", 25);
	$offset = (int) get_input("offset", 0);
	
	$options = array(
		"type" => "user",
		"site_guids" => false,
		"relationship"=> "member_of_site",
		"relationship_guid" => elgg_get_site_entity()->getGUID(),
		"inverse_relationship" => true,
		"order_by" => "r.time_created DESC",
		"limit" => max(0, $limit),
		"offset" => max(0, $offset),
		"count" => true
	);
	
	// get count for pagination
	$count = elgg_get_entities_from_relationship($options);
	
	// build pagination
	$nav = elgg_view("navigation/pagination",array(
		"base_url" => "admin/users/newest",
		"offset" => $offset,
		"count" => $count,
		"limit" => $limit,
	));
	
	// get users
	$options["count"] = false;
	
	$users = elgg_get_entities_from_relationship($options);
	
	// build title
	$check_all = elgg_view("input/checkbox", array("id" => "subsite-manager-newest-users-check-all"));
	$check_all .= "<label for='subsite-manager-newest-users-check-all'>" . elgg_echo("subsite_manager:newest_users:bulk_action:check_all") . "</label>";
	
	$delete_all = elgg_view("output/confirmlink", array(
		"text" => elgg_echo("delete"),
		"confirm" => elgg_echo("deleteconfirm:plural"),
		"href" => "action/subsite_manager/bulk_action/user_delete",
		"is_trusted" => true,
		"class" => "subsite-manager-newest-users-submit"
	));
	
	$title = "<ul class='elgg-menu elgg-menu-general elgg-menu-hz float-alt'>";
	$title .= "<li>" . $delete_all . "</li>";
	$title .= "</ul>";
	$title .= $check_all;
	
	// build content
	$content = "<ul class='elgg-list elgg-list-distinct'>";
	
	foreach ($users as $user) {
		$content .= "<li class='elgg-item subsite-manager-newest-users-user'>";
		
		$checkbox = elgg_view("input/checkbox", array(
			"name" => "user_guids[]",
			"value" => $user->getGUID(),
			"default" => false
		));
		
		$delete_user = elgg_view("output/confirmlink", array(
			"text" => elgg_echo("delete"),
			"href" => "action/admin/user/delete?guid=" . $user->getGUID(),
			"confirm" => elgg_echo("deleteconfirm"),
			"is_trusted" => true
		));
		
		$user_menu = "<ul class='elgg-menu elgg-menu-general elgg-menu-hz float-alt'>";
		$user_menu .= "<li>" . $delete_user . "</li>";
		$user_menu .= "</ul>";
		
		$user_content = "<label>" . $user->username . ": \"" . $user->name . "\" &lt;" . $user->email . "&gt;</label>";
		$user_content .= "<div>";
		$user_content .= elgg_echo("subsite_manager:newest_users:bulk_action:created", array(elgg_view_friendly_time($user->time_created)));
		$user_content .= "</div>";
		
		$content .= elgg_view_image_block($checkbox, $user_content, array("image_alt" => $user_menu));
		
		$content .= "</li>";
	}
	
	$content .= "</ul>";
	
	$content .= $nav;
	
	echo elgg_view_module("inline", $title, $content);
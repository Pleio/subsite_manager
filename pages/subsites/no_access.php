<?php 

	gatekeeper();
	
	$user = elgg_get_logged_in_user_entity();
	$site = elgg_get_site_entity();
	
	// are we on main site or is the user a member of the site
	if(!elgg_instanceof($site, "site", Subsite::SUBTYPE, "Subsite") || $site->isUser()){
		$forward_url = "";
		
		if(!empty($_SESSION["no_access_forward_from"])){
			// did we come here from access validation?
			// this shouldn't happen
			$forward_url = $_SESSION["no_access_forward_from"];
			unset($_SESSION["no_access_forward_from"]);
		} elseif(!empty($_SESSION["last_forward_from"])){
			// did we get here by gatekeeper
			$forward_url = $_SESSION["last_forward_from"];
			unset($_SESSION["last_forward_from"]);
		}
		
		// forward the user
		forward($forward_url);
	}
	
	// make page content
	$title_text = elgg_echo("subsite_manager:subsites:no_access:title");
	
	// no access
	$no_access = elgg_view("subsite_manager/subsites/no_access", array("site" => $site, "user" => $user));
	
	// but show group membership
	$options = array(
		"type" => "group",
		"count" => true,
		"site_guids" => array($site->getGUID()),
		"relationship" => "member",
		"relationship_guid" => $user->getGUID(),
		"full_view" => false,
		"pagination" => false
	);
	$groups_listing = "";
	if($group_count = elgg_get_entities_from_relationship($options)){
		$options["count"] = false;
		$options["limit"] = 8;
		
		// we want a cleaner listing, so fake widgets
		elgg_push_context("widgets");
		$groups = elgg_list_entities_from_relationship($options);
		elgg_pop_context();
		
		$groups_listing = elgg_view("subsite_manager/subsites/group_membership", array("user" => $user, "group_count" => $group_count, "groups" => $groups));
	}
	
	if(!empty($groups_listing)){
		$content = elgg_view("subsite_manager/subsites/no_access_wrapper", array("subsite" => $no_access, "groups" => $groups_listing));
	} else {
		$content = $no_access;
	}
	
	// build page body
	$body = elgg_view_layout("one_column", array(
		"content" => $content
	));
	
	echo elgg_view_page($title_text, $body);
<?php

$guid = (int) get_input("guid");
$site_guid = (int) get_input("site_guid");

if (empty($guid) || empty($site_guid)) {
	register_error(elgg_echo("InvalidParameterException:MissingParameter"));
	forward(REFERER);
}

$entity = get_entity($guid);
if (empty($entity) || !elgg_instanceof($entity, "group")) {
	register_error(elgg_echo("InvalidParameterException:NoEntityFound"));
	forward(REFERER);
}

$current_site = elgg_get_site_entity();
if ($site_guid == $current_site->getGUID()) {
	register_error(elgg_echo("subsite_manager:action:groups:move:error:same_site"));
	forward(REFERER);
}

$site = get_entity($site_guid);
if (empty($site) || !elgg_instanceof($site, "site")) {
	register_error(elgg_echo("InvalidParameterException:NoEntityFound"));
	forward(REFERER);
}

$site_member = false;
if (subsite_manager_on_subsite()) {
	if ($site_guid == $current_site->getOwnerGUID()) {
		// moving to main site, everyone is member
		$site_member = true;
	}
}

if (!$site_member) {
	$user_sites = subsite_manager_get_user_subsites();
	if (empty($user_sites)) {
		register_error(elgg_echo("subsite_manager:forms:groups:move:no_subsites"));
		forward(REFERER);
	}
	
	foreach ($user_sites as $subsite) {
		if ($site_guid == $subsite->getGUID()) {
			$site_member = true;
			break;
		}
	}
}

if (!$site_member) {
	register_error(elgg_echo("subsite_manager:action:groups:move:error:not_a_member"));
	forward(REFERER);
}

if (!subsite_manager_move_group_to_site($entity, $site)) {
	register_error(elgg_echo("subsite_manager:action:groups:move:error:move"));
	forward(REFERER);
}

system_message(elgg_echo("subsite_manager:action:groups:move:success", array($site->name)));
forward("groups/all");

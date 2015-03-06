<?php

$group = elgg_extract("entity", $vars);
if (empty($group) || !elgg_instanceof($group, "group")) {
	return;
}

if (!elgg_is_admin_logged_in()) {
	return;
}

$title = elgg_echo("subsite_manager:groups:move:title");

$description = elgg_view("output/longtext", array("value" => elgg_echo("subsite_manager:groups:move:description")));

$form = elgg_view_form("subsite_manager/groups/move", null, array("entity" => $group));

echo elgg_view_module("info", $title, $description . $form);
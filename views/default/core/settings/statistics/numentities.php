<?php

$dbprefix = elgg_get_config("dbprefix");

$owner_guid = (int) elgg_get_page_owner_guid();
$site = elgg_get_site_entity();

$all_query = "SELECT distinct e.type, e.subtype, count(*) as count from " . $dbprefix . "entities e
	where e.owner_guid = " . $owner_guid . " group by e.type, e.subtype";

$site_query = "SELECT distinct e.type, e.subtype, count(*) as count from " . $dbprefix . "entities e
	where e.owner_guid = " . $owner_guid . " and e.site_guid = " . $site->guid . " group by e.type, e.subtype";

// Get a list of major types
$all_results = get_data($all_query);
$site_results = get_data($site_query);

$rows = "<tr>";
$rows .= "<th>&nbsp;</th>";

if (elgg_is_admin_logged_in()) {
	$rows .= "<th>" . elgg_echo("all") . "</th>";
}

$rows .= "<th>" . $site->name . "</th>";
$rows .= "</tr>";

foreach ($all_results as $row) {
	if (!elgg_is_admin_logged_in() && !is_registered_entity_type($row->type, get_subtype_from_id($row->subtype))) {
		// skip unsearchable entities for regular users
		continue;
	}
	if ($row->subtype) {
		$label = elgg_echo("item:" . $row->type . ":" . get_subtype_from_id($row->subtype));
	} else {
		$label = elgg_echo("item:" . $row->type);
	}
	
	if (strpos($label, "item:") === 0) {
		if ($row->subtype) {
			$label = elgg_echo(get_subtype_from_id($row->subtype));
		} else {
			$label = elgg_echo($row->type);
		}
	}
	
	$site_count = "&nbsp;";
	foreach ($site_results as $site_row) {
		if (($site_row->type == $row->type) && ($site_row->subtype == $row->subtype)) {
			$site_count = $site_row->count;
			break;
		}
	}
	
	if (!elgg_is_admin_logged_in()) {
		$rows .= "<tr><td>" . $label . "</td><td>" . $site_count . "</td></tr>";
	} else {
		$rows .= "<tr><td>" . $label . "</td><td>" . $row->count . "</td><td>" . $site_count . "</td></tr>";
	}
}

$title = elgg_echo('usersettings:statistics:label:numentities');
$content = "<table class='elgg-table'>$rows</table>";

echo elgg_view_module('info', $title, $content);

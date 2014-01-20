<?php
/**
 * Activity widget content view
 */

$num = (int) $vars['entity']->num_display;

$options = array(
	'limit' => $num,
	'pagination' => false,
);

if (elgg_in_context('dashboard')) {
	if ($vars['entity']->content_type == 'friends') {
		$options['relationship_guid'] = elgg_get_page_owner_guid();
		$options['relationship'] = 'friend';
	}
} else {
	$options['subject_guid'] = elgg_get_page_owner_guid();
}

$activity_site_guid = $vars['entity']->activity_site_guid;

if ($activity_site_guid) {
	if ($activity_site_guid == "all") {
		
		$sites = subsite_manager_get_user_subsites($vars['entity']->owner_guid);
		$site_guids = array();
		
		foreach ($sites as $site) {
			$site_guids[] = $site->guid;
		}
		
		$options["site_guids"] = $site_guids;
	} else {
		$options["site_guid"] = $activity_site_guid;
	}
}

$content = elgg_list_river($options);
if (!$content) {
	$content = elgg_echo('river:none');
}

echo $content;

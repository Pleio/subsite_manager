<?php
/**
 * Members index
 *
 */

$site = elgg_get_site_entity();

$options = array(
	'type' => 'user',
	'site_guids' => false,
	'relationship' => 'member_of_site',
	'relationship_guid' => $site->getGUID(),
	'inverse_relationship' => true,
	'full_view' => false,
	'count' => true
);


$num_members = elgg_get_entities_from_relationship($options);

$title = elgg_echo('members');


switch ($vars['page']) {
	case 'popular':
		$options['relationship'] = 'friend';
		$options['inverse_relationship'] = false;
		unset($options['relationship_guid']);
		
		$options["joins"] = array("JOIN " . elgg_get_config("dbprefix") . "entity_relationships r2 ON e.guid = r2.guid_one");
		$options["wheres"] = array("(r2.guid_two = " . $site->getGUID() . " AND r2.relationship = 'member_of_site')");
		
		$content = elgg_list_entities_from_relationship_count($options);
		break;
	case 'online':
		$content = get_online_users();
		break;
	case 'newest':
	default:
		$content = elgg_list_entities_from_relationship($options);
		break;
}

if(empty($content)) {
	$content = elgg_echo("notfound");
}


$params = array(
	'content' => $content,
	'sidebar' => elgg_view('members/sidebar'),
	'title' => $title . " ($num_members)",
	'filter_override' => elgg_view('members/nav', array('selected' => $vars['page'])),
);

$body = elgg_view_layout('content', $params);

echo elgg_view_page($title, $body);

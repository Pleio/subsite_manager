<?php
/**
 * Members search page
 *
 */

$site = elgg_get_site_entity();

if ($vars['search_type'] == 'tag') {
	$tag = get_input('tag');

	$title = elgg_echo('members:title:searchtag', array($tag));

	$options = array();
	$options['query'] = $tag;
	$options['type'] = "user";
	$options['offset'] = $offset;
	$options['limit'] = $limit;
	$options['joins'] = array("JOIN " . elgg_get_config("dbprefix") . "entity_relationships r ON r.guid_one = e.guid");
	$options['wheres'] = array("(r.guid_two = " . $site->getGUID() . " AND r.relationship = 'member_of_site')");
	
	$results = elgg_trigger_plugin_hook('search', 'tags', $options, array());
	
	$count = $results['count'];
	$users = $results['entities'];
	$content = elgg_view_entity_list($users, array(
		'count' => $count,
		'offset' => $offset,
		'limit' => $limit,
		'full_view' => false,
		'list_type_toggle' => false,
		'pagination' => true,
	));
} else {
	$name = sanitize_string(get_input('name'));

	$title = elgg_echo('members:title:searchname', array($name));

	$db_prefix = elgg_get_config('dbprefix');
	$params = array(
		'type' => 'user',
		'site_guids' => false,
		'relationship' => "member_of_site",
		'relationship_guid' => $site->getGUID(),
		'inverse_relationship' => true,
		'full_view' => false,
		'joins' => array("JOIN {$db_prefix}users_entity u ON e.guid=u.guid"),
		'wheres' => array("(u.name LIKE \"%{$name}%\" OR u.username LIKE \"%{$name}%\")"),
	);
	$content = elgg_list_entities_from_relationship($params);
}

if(empty($content)) {
	$content = elgg_echo("notfound");
}

$params = array(
	'title' => $title,
	'content' => $content,
	'sidebar' => elgg_view('members/sidebar'),
);

$body = elgg_view_layout('one_sidebar', $params);

echo elgg_view_page($title, $body);

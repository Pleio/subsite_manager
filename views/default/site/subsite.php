<?php 

	$entity = $vars["entity"];

	$icon = elgg_view_entity_icon($entity, "small");

	$category = $entity->category;
	$color = substr(sha1($category, false), 0, 6);
	$cat_link = elgg_view("output/url", array(
		"text" => elgg_echo("subsite_manager:category:options:" . $category), 
		"href" => "subsites/" . get_input("filter") . "?category=" . $category,
		"style" => "color: #" . $color . ";"
	));
	
	$subtitle = $entity->member_count  . " " . strtolower(elgg_echo("members"));
	
	$subtitle .= " | " . elgg_echo("subsite_manager:membership") . ": <b>" . elgg_echo("subsite_manager:membership:" . $entity->getMembership()) . "</b>";
	$subtitle .= " | " . elgg_echo("subsite_manager:category:label") . ": <b>" . $cat_link . "</b>";
	
	$content = elgg_view_menu("subsite", array(
		"entity" => $entity,
		"sort_by" => "priority",
		"class" => "elgg-menu-hz float-alt"
	));
	$content .= $entity->description;
	
	$params = array(
		"subtitle" => $subtitle,
		"content" => $content,
		"metadata" => elgg_view_menu("entity", array(
			"entity" => $entity,
			"sort_by" => "priority",
			"class" => "elgg-menu-hz"
		))
	);
	$params = $params + $vars;
	
	$body = elgg_view("object/elements/summary", $params);
	
	echo elgg_view_image_block($icon, $body, $vars);

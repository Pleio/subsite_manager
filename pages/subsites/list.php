<?php 

	gatekeeper();
	
	// get input
	$filter = get_input("filter");
	$offset = (int) get_input("offset", 0);
	$limit = (int) get_input("limit", 10);
	
	$text = sanitise_string(get_input("text", elgg_echo("search")));
	$category = get_input("category");
	
	// default options
	$search_box = true;
	
	$options = array(
		"type" => "site",
		"subtype" => Subsite::SUBTYPE,
		"limit" => max(0, $limit),
		"offset" => max(0, $offset),
		"site_guids" => false,
		"joins" => array("JOIN " . get_config("dbprefix") . "sites_entity se ON e.guid = se.guid"),
		"order_by" => "se.name ASC",
		"full_view" => false,
		"metadata_name_value_pairs" => array(),
		"wheres" => array()
	);
	
	// Limit the view to only visible Subsites
	if(!elgg_is_admin_logged_in()){
		$options["metadata_name_value_pairs"][] = array(
			"name" => "visibility",
			"value" => true, 
			"operand" => "=", 
			"case_sensitive" => TRUE
		);
	}
	
	// Search based on text
	if(!empty($text) && ($text != elgg_echo("search"))){
		$options["wheres"][] = "(se.name LIKE '%" . $text . "%' || se.description LIKE '%" . $text . "%' || se.url LIKE '%" . $text . "%')";
	}
	
	// Search on category
	if(!empty($category)){
		$options["metadata_name_value_pairs"][] = array(
			"name" => "category",
			"value" => $category, 
			"operand" => "=", 
			"case_sensitive" => TRUE
		);
	}
	
	// Get subsites I requested membership for
	$mem_options = array(
		"type" => "site",
		"subtype" => Subsite::SUBTYPE,
		"site_guids" => false,
		"annotation_names" => array("request_membership"),
		"annotation_owner_guids" => elgg_get_logged_in_user_guid(),
		"count" => true
	);
	$membership_count = elgg_get_entities_from_annotations($mem_options);
	
	// make filter menu
	$filter_all = array(
		"name" => "all",
		"href" => elgg_http_add_url_query_elements("subsites", array("text" => $text, "category" => $category)),
		"text" => elgg_echo("subsite_manager:subsites:filter:all"),
		"priority" => 10
	);
	$filter_featured = array(
		"name" => "featured",
		"href" => elgg_http_add_url_query_elements("subsites/featured", array("text" => $text, "category" => $category)),
		"text" => elgg_echo("subsite_manager:subsites:filter:featured"),
		"priority" => 30
	);
	$filter_open = array(
		"name" => "open",
		"href" => elgg_http_add_url_query_elements("subsites/open", array("text" => $text, "category" => $category)),
		"text" => elgg_echo("subsite_manager:subsites:filter:open"),
		"priority" => 40
	);
	$filter_closed = array(
		"name" => "closed",
		"href" => elgg_http_add_url_query_elements("subsites/closed", array("text" => $text, "category" => $category)),
		"text" => elgg_echo("subsite_manager:subsites:filter:closed"),
		"priority" => 50
	);
	$filter_popular = array(
		"name" => "popular",
		"href" => elgg_http_add_url_query_elements("subsites/popular", array("text" => $text, "category" => $category)),
		"text" => elgg_echo("subsite_manager:subsites:filter:popular"),
		"priority" => 60
	);
	$filter_mine = array(
		"name" => "mine",
		"href" => elgg_http_add_url_query_elements("subsites/mine", array("text" => $text, "category" => $category)),
		"text" => elgg_echo("subsite_manager:subsites:filter:mine"),
		"priority" => 70
	);
	$filter_membership = array(
		"name" => "mine",
		"href" => elgg_http_add_url_query_elements("subsites/membership", array("text" => $text, "category" => $category)),
		"text" => elgg_echo("subsite_manager:subsites:filter:membership"),
		"priority" => 80
	);
	
	// What to display
	switch($filter){
		case "mine":
			$filter_mine["selected"] = true;
			
			$title_text = elgg_echo("subsite_manager:subsites:title:mine");
				
			$options["relationship"] = "member_of_site";
			$options["relationship_guid"] = elgg_get_logged_in_user_guid();
				
			$body = elgg_list_entities_from_relationship($options);
				
			break;
		case "membership":
			$filter_membership["selected"] = true;
			
			$search_box = false;
			$title_text = elgg_echo("subsite_manager:subsites:title:membership");
				
			if(!empty($membership_count)){
				$mem_options["count"] = false;
				$mem_options["limit"] = $count;
	
				$sites = elgg_get_entities_from_annotations($mem_options);
	
				$body = elgg_view_entity_list($sites, count($sites), $offset, $limit, false, false);
			}
				
			break;
		case "open":
			$filter_open["selected"] = true;
			
			$title_text = elgg_echo("subsite_manager:subsites:title:open");
				
			$options["joins"][] = "JOIN " . get_config("dbprefix") . "private_settings ps ON ps.entity_guid = e.guid";
			$options["wheres"][] = "(ps.name = 'membership' AND ps.value = '" . Subsite::MEMBERSHIP_OPEN . "')";
			unset($options["order_by"]); //order by time created desc
				
			$body = elgg_list_entities_from_relationship($options);
				
			break;
		case "closed":
			$filter_closed["selected"] = true;
			
			$title_text = elgg_echo("subsite_manager:subsites:title:closed");
				
			$options["joins"][] = "JOIN " . get_config("dbprefix") . "private_settings ps ON ps.entity_guid = e.guid";
			$options["wheres"][] = "(ps.name = 'membership' AND ps.value <> '" . Subsite::MEMBERSHIP_OPEN . "')";
			unset($options["order_by"]); //order by time created desc
				
			$body = elgg_list_entities_from_relationship($options);
				
			break;
		case "popular":
			$filter_membership["selected"] = true;
			
			$title_text = elgg_echo("subsite_manager:subsites:title:popular");

			$options["order_by_metadata"] = array("name" => "member_count", "direction" => "DESC", "as" => "integer");
				
			$body = elgg_list_entities_from_metadata($options);
				
			break;
		case "featured":
			$filter_featured["selected"] = true;
			
			$title_text = elgg_echo("subsite_manager:subsites:title:featured");
			
			$options["order_by_metadata"] = array("name" => "featured", "direction" => "DESC", "as" => "integer");
			
			$body = elgg_list_entities_from_metadata($options);
				
			break;
		default:
			$filter_all["selected"] = true;
		
			// title
			$title_text = elgg_echo("subsite_manager:subsites:title:all");
			
			$body = elgg_list_entities_from_relationship($options);
			
			break;
	}
	
	// search box
	if($search_box){
		$search = elgg_view("subsite_manager/subsites/forms/search", array("filter" => $filter, "text" => $text, "category" => $category));
	}
	
	// tabbed nav
	elgg_register_menu_item("filter", $filter_all);
	elgg_register_menu_item("filter", $filter_featured);
	elgg_register_menu_item("filter", $filter_closed);
	elgg_register_menu_item("filter", $filter_open);
	elgg_register_menu_item("filter", $filter_mine);
	elgg_register_menu_item("filter", $filter_popular);
	if($membership_count > 0){
		elgg_register_menu_item("filter", $filter_membership);
	}
	
	$tabbed_nav = elgg_view_menu("filter", array(
		"sort_by" => "priority"
	));
	
	if(empty($body)){
		$body = elgg_echo("notfound");
	}

	if(!subsite_manager_on_subsite() && elgg_is_admin_logged_in()){
		// title menu
		elgg_register_menu_item("title", array(
								"name" => "new",
								"href" => "admin/subsites/new",
								"text" => elgg_echo("admin:subsites:new"),
								"link_class" => "elgg-button elgg-button-action"
		));
	}
	
	// title menu
	elgg_register_menu_item("title", array(
							"name" => "request",
							"href" => "deelsiteaanvragen",
							"text" => elgg_echo("subsite_manager:menu:subsites:request"),
							"link_class" => "elgg-button elgg-button-action"
	));
	
	$header = elgg_view('page/layouts/content/header', array("title" => $title_text));
	
	// make layout
	$body = elgg_view_layout("one_column", array(
			"content" => $header . $search . $tabbed_nav . $body,
			"filter" => ""
	));
	
	echo elgg_view_page($title_text, $body);
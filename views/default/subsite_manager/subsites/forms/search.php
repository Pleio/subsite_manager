<?php 

	global $SUBSITE_MANAGER_SUBSITE_CATEGORIES;
	
	$text = elgg_extract("text", $vars);
	$category = elgg_extract("category", $vars);
	$filter = elgg_extract("filter", $vars);

	$categories = array("" => elgg_echo("all"));
	$categories = array_merge($categories, $SUBSITE_MANAGER_SUBSITE_CATEGORIES);
	
	$action = "/subsites";
	if(!empty($filter)){
		$action .= "/" . $filter;
	}

	$formbody = elgg_view("input/text", array("name" => "text", "value" => $text)) . " ";
	$formbody .= elgg_view("input/dropdown", array("name" => "category", "options_values" => $categories, "value" => $category));
	$formbody .= elgg_view("input/submit", array("value" => elgg_echo("search")));
	$formbody .= elgg_view("input/button", array("value" => elgg_echo("reset"), "type" => "button", "onclick" => "document.location.href='" . $action. "'", "class" => "elgg-button-submit"));
	
	$form = elgg_view("input/form", array("id" => "subsite_manager_search_form",
											"action" => $action, 
											"body" => $formbody,
											"disable_security" => true,
											"method" => "GET"));
	
	echo $form; 
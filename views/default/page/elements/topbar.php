<?php

if (elgg_is_logged_in()) {
	// check if we need to show the topbar
	if (elgg_get_config("navigation_bar_position") != "disabled") {
		
		// link back to main site.
		$site = elgg_get_site_entity();
		$site_name = $site->name;
		$site_url = elgg_get_site_url();
		
		
		echo "<h1>";
		echo elgg_view("output/url", array("text" => $site->name, "title" => $site->name, "href" => $site->url, "class"=> "subsite-manager-topbar-logo"));
		echo "</h1>";
		
		echo elgg_view("subsite_manager/subsites/dropdown");
		echo elgg_view("search/search_box");
		echo elgg_view("subsite_manager/account/dropdown");
	}
}
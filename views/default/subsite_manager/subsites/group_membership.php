<?php 

	/**
	 * This view will be shown on the no access page of a subsite, when you have group memberships.
	 * It shows the first 3 groups
	 * 
	 */

	$user = elgg_extract("user", $vars);
	$group_count = (int) elgg_extract("group_count", $vars, 0);
	$groups = elgg_extract("groups", $vars);
	
	$title = elgg_echo("groups:yours");
	
	$body = "<div>";
	$body .= elgg_echo("subsite_manager:subsites:no_access:groups:description");
	$body .= "</div>";
	$body .= $groups;
	
	if($group_count > 8){
		$body .= elgg_view("output/url", array("href" => "groups/member/" . $user->username, "text" => elgg_echo("groups:more"), "class" => "float-alt"));
	}
	
	echo "<div id='subsite-manager-no-access-groups'>";
	echo elgg_view_module("aside", $title, $body);
	echo "</div>";
	
	
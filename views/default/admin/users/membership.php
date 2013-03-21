<?php 

	$site = elgg_get_site_entity();
	
	$count = $site->countMembershipRequests();
	
	if($count > 0){
		$old_ia = elgg_set_ignore_access(true);
		
		$options = array(
			"guid" => $site->getGUID(),
			"annotation_names" => array("request_membership"),
			"limit" => $count,
			"site_guids" => false
		);
		
		$body = elgg_list_annotations($options);
		
		elgg_set_ignore_access($old_ia);
	} else {
		$body = elgg_echo("notfound");
	}
	
	echo "<div>";
	echo elgg_echo("subsite_manager:subsites:membership:description");
	echo "</div>";
	
	echo elgg_view_module("inline", elgg_echo("subsite_manager:subsites:membership:list:title"), $body);
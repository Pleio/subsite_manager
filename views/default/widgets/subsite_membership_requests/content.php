<?php 

	$site = elgg_get_site_entity();
	
	$count = $site->countMembershipRequests();
	
	if($count > 0){
		$options = array(
			"guid" => $site->getGUID(),
			"annotation_names" => array("request_membership"),
			"limit" => 5
		);
		
		$body = elgg_list_annotations($options);
		
		if($count > 5){
			$more_text = "<div>+" . ($count - 5) . " " . strtolower(elgg_echo("more")) . "</div>";
			$body .= elgg_view("output/url", array("text" => $more_text, "href" => "admin/users/membership"));
		}
	} else {
		$body = elgg_echo("notfound");
	}
	
	echo $body;
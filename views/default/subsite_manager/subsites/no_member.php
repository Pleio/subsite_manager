<?php 
	if($user = $vars["entity"]){
		$site = get_entity($user->site_guid);
		echo "<div id='subsite-manager-profile-no-member'>";
		echo "<div>";
		echo elgg_view_entity_icon($user, "medium", array("use_hover" => false, "use_link" => false)) . "<br />";
		echo "<label>" . $user->name . "</label><br />";
		echo elgg_echo("subsite_manager:profile:no_member:header");
		echo "</div>";
		echo elgg_echo("subsite_manager:profile:no_member:visit", array("<a href='" . $site->url . "profile/" . $user->username . "'>", "</a>"));
		echo "</div>";
	} else {
		forward();
	}

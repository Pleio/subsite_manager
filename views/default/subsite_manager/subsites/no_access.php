<?php 

	$site = elgg_extract("site", $vars, elgg_get_site_entity());
	$user = elgg_extract("user", $vars, elgg_get_logged_in_user_entity());
	
	echo "<div id='subsite-manager-no-access'>";
	
	// site info part
	echo "<div>";
	echo elgg_view_entity_icon($site, "large") . "<br />";
	echo "<label>" . $site->name . "</label><br />";
	echo elgg_echo("subsite_manager:subsites:no_access");
	echo "</div>";
	
	if($site->canJoin($user->getGUID())){
		// you can join this site
		echo elgg_echo("subsite_manager:subsites:no_access:can_join");
		echo "<br />";
		
		echo elgg_view("output/url", array(
			"text" => elgg_echo("subsite_manager:subsite:join"),
			"href" => $site->url . "subsites/join",
			"class" => "elgg-button elgg-button-submit"
		));
	} elseif($site->pendingMembershipRequest($user->getGUID())) {
		// already requested
		echo elgg_echo("subsite_manager:subsites:no_access:pending");
	} else {
		// you can't join the site, so request membership
		switch($site->getMembership()){
			case Subsite::MEMBERSHIP_APPROVAL:
			case Subsite::MEMBERSHIP_DOMAIN_APPROVAL:
				echo elgg_echo("subsite_manager:subsites:no_access:approval");
				echo "<br />";
				
				echo elgg_view("output/url", array(
					"text" => elgg_echo("subsite_manager:subsite:request"),
					"href" => $site->url . "subsites/join/request",
					"class" => "elgg-button elgg-button-submit"
				));
				break;
			case Subsite::MEMBERSHIP_DOMAIN:
				echo elgg_echo("subsite_manager:subsites:no_access:domain");
				echo "<br />";
				
				echo elgg_view("output/url", array(
					"text" => elgg_echo("subsite_manager:subsite:validate_domain"),
					"href" => $site->url . "subsites/join/domain",
					"class" => "elgg-button elgg-button-submit"
				));
				break;
			case Subsite::MEMBERSHIP_INVITATION:
				echo elgg_echo("subsite_manager:subsites:no_access:invitation");
				break;
		}
	}
	
	echo "</div>";
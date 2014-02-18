<?php 

	$subsite = elgg_extract("subsite", $vars, elgg_get_site_entity());
	$profile_fields = elgg_extract("profile_fields", $vars, false);
	$domains = elgg_extract("domains", $vars);
	
	// explain
	$explain = elgg_echo("subsite_manager:subsites:join:validate_domain:description");
	$explain .= "<br />";
	$explain .= "<br />";

	foreach($domains as $domain){
		if(substr($domain, 0, 1) == "."){
			$domain = substr($domain, 1);
			
			$explain .= elgg_echo("subsite_manager:subsites:join:validate_domain:email_subdomain", array($domain));
		} else {
			$explain .= elgg_echo("subsite_manager:subsites:join:validate_domain:email_domain", array($domain));
		}
		$explain .= "<br />";
	}
	
	$form_body = elgg_view_module("info", "", $explain);
	
	$form_body .= "<div>";
	$form_body .= "<label>" . elgg_echo("email") . "</label>";
	$form_body .= "<br />";
	$form_body .= elgg_view("input/email", array("name" => "email"));
	$form_body .= "</div>";
	
	if(!empty($profile_fields)){
		$form_body .= elgg_view("forms/subsites/profile_fields", array("profile_fields" => $profile_fields, "show_buttons" => false));
	}
	
	$form_body .= "<div class='elgg-foot'>";
	$form_body .= elgg_view("input/hidden", array("name" => "subsite_guid", "value" => $subsite->getGUID()));
	$form_body .= elgg_view("input/submit", array("value" => elgg_echo("submit")));
	$form_body .= "</div>";
	
	echo $form_body;
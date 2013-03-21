<?php 

	$subsite = elgg_extract("subsite", $vars, elgg_get_site_entity());
	$profile_fields = elgg_extract("profile_fields", $vars, false);
	
	$form_body = elgg_view_module("info", "", elgg_echo("subsite_manager:subsites:join:request_approval:description"));
	
	$form_body .= "<div>";
	$form_body .= "<label>" . elgg_echo("subsite_manager:subsites:join:request_approval:reason") . "</label>";
	$form_body .= "<br />";
	$form_body .= elgg_view("input/longtext", array("name" => "reason"));
	$form_body .= "</div>";
	
	if(!empty($profile_fields)){
		$form_body .= elgg_view("forms/subsites/join/missing_fields", array("profile_fields" => $profile_fields, "show_buttons" => false));
	}
	
	$form_body .= "<div class='elgg-foot'>";
	$form_body .= elgg_view("input/hidden", array("name" => "subsite_guid", "value" => $subsite->getGUID()));
	$form_body .= elgg_view("input/submit", array("value" => elgg_echo("submit")));
	$form_body .= "</div>";
	
	echo $form_body;
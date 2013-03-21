<?php 

	echo "<div>" . elgg_echo("subsite_manager:invite:description") . "</div>";
	
	$form_vars = array(
		"class" => "elgg-form-settings",
		"enctype" => "multipart/form-data"
	);
	$body_vars = array();
	
	echo elgg_view_form("subsites/invite/invite", $form_vars, $body_vars);
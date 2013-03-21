<?php 

	$form_data = "<div>" . elgg_echo("subsite_manager:import:step1:description") . "</div>";
	
	$form_data .= "<div>" . elgg_echo("subsite_manager:import:step1:file");
	$form_data .= "<br />";
	$form_data .= elgg_view("input/file", array("name" => "csv"));
	$form_data .= "</div>";
	
	$form_data .= "<div>";
	$form_data .= elgg_view("input/submit", array("value" => elgg_echo("subsite_manager:continue")));
	$form_data .= "</div>";
	
	echo $form_data;
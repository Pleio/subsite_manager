<?php 

	// can only be viewed on main site
	if(subsite_manager_on_subsite()){
		forward("admin");
	}

	$form_vars = array(
		"enctype" => "multipart/form-data",
		"id" => "subsite_manager_subsite_new_form",
		"class" => "elgg-form-settings"
	);
	
	echo elgg_view_form("subsites/new", $form_vars);
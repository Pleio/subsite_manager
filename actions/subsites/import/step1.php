<?php

	$forward_url = REFERER;
	
	if(($csv = get_uploaded_file("csv")) && !empty($csv)){
		$tmp_location = $_FILES["csv"]["tmp_name"];
	
		if($fh = fopen($tmp_location, "r")){
			if(($data = fgetcsv($fh, 0, ";")) !== false){
				$new_location = tempnam(sys_get_temp_dir(), "subsite_import_" . get_config("site_guid"));
				move_uploaded_file($tmp_location, $new_location);
	
				$_SESSION["subsite_manager_import"] = array(
					"location" => $new_location,
					"sample" => $data
				);
	
				$forward_url = elgg_get_site_url() . "admin/users/import?step=2";
	
				system_message(elgg_echo("subsite_manager:action:import:step1:success"));
			} else {
				register_error(elgg_echo("subsite_manager:action:import:step1:error:content"));
			}
		} else {
			register_error(elgg_echo("subsite_manager:action:import:step1:error:file"));
		}
	} else {
		register_error(elgg_echo("subsite_manager:action:import:step1:error:csv"));
	}
	
	forward($forward_url);
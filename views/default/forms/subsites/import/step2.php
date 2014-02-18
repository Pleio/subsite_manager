<?php 
	
	$tmp_data = $_SESSION["subsite_manager_import"];
	
	if(!empty($tmp_data)){
		$import_options = array(
			"" => elgg_echo("subsite_manager:import:step2:select_field"),
			"displayname" => elgg_echo("name") . "*",
			"email" => elgg_echo("email") . "*",
			"username" => elgg_echo("username"),
			"password" => elgg_echo("password")
		);
		
		$profile_fields = get_config("profile_fields");
		
		if(!empty($profile_fields)){
			foreach($profile_fields as $name => $type){
				$lan_key = "profile:" . $name;
				
				if($lan_key == elgg_echo($lan_key)){
					$import_options[$name] = $name;
				} else {
					$import_options[$name] = elgg_echo($lan_key);
				}
			}
		}
		
		// build form
		$form_data = "<div>" . elgg_echo("subsite_manager:import:step2:description") . "</div>";
		
		$form_data .= "<table class='elgg-table'>";
		
		$form_data .= "<tr>";
		$form_data .= "<th>" . elgg_echo("subsite_manager:column") . "</th>";
		$form_data .= "<th>" . elgg_echo("subsite_manager:import:step2:select_field") . "</th>";
		$form_data .= "</tr>";
		
		foreach($tmp_data["sample"] as $index => $sample){
			$form_data .= "<tr>";
			$form_data .= "<td>" . ($index + 1) . " (" . elgg_echo("subsite_manager:sample") . ": " . $sample . ")</td>";
			$form_data .= "<td>" . elgg_view("input/dropdown", array("name" => "columns[" . $index . "]", "options_values" => $import_options)) . "</td>";
			$form_data .= "</tr>";
		}
		$form_data .= "</table>";
		
		$form_data .= "<br />";
		
		$form_data .= "<div><b>" . elgg_echo("subsite_manager:import:step2:message") . "</b><br />";
		$form_data .= elgg_view("input/longtext", array("name" => "message"));
		$form_data .= "</div>";
		
		$form_data .= "<div>";
		$form_data .= elgg_view("input/submit", array("value" => elgg_echo("import")));
		$form_data .= "</div>";
		
		echo $form_data;
	} else {
		echo elgg_echo("subsite_manager:import:step2:error", array(
			"<a href='" . elgg_get_site_url() . "admin/subsites/import'>",
			"</a>"
		));
	}
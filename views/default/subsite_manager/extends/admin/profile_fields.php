<?php 

	$main_site_guid = elgg_get_site_entity()->getOwnerGUID();

	$noyes_options = array(
		"no" => elgg_echo("option:no"),
		"yes" => elgg_echo("option:yes")
	);
	
	$options = array(
		"type" => "object",
		"subtype" => "custom_profile_field",
		"limit" => false,
		"site_guids" => array($main_site_guid)
	);

	$current_configuration = subsite_manager_get_main_profile_fields_configuration();
	
	$field_entities = elgg_get_entities($options);
	
	if($field_entities){
		
		$title = elgg_echo("subsite_manager:profile_fields:global:title");
		
		$body = elgg_echo("subsite_manager:profile_fields:global:description") . "<br /><br />";
		
		$form_body = "<table class='elgg-table mbm'>";
		$form_body .= "<tr><th>" . elgg_echo('profile_manager:admin:metadata_label') . "</th><th>" . elgg_echo('profile_manager:admin:show_on_register') . "</th><th>" . elgg_echo('profile_manager:admin:mandatory') . "</th></tr>";
		foreach($field_entities as $field){
			if($field->user_editable !== "no"){
				$name = $field->metadata_name;
				
				$show_on_register = "";
				$mandatory = "";
				if(!empty($current_configuration) && array_key_exists($name, $current_configuration)){
					$show_on_register = $current_configuration[$name]["show_on_register"];
					$mandatory = $current_configuration[$name]["mandatory"];
				}
				
				$form_body .= "<tr>";
				$form_body .= "<td>" . $field->getTitle() . "</td>";
				$form_body .= "<td>" . elgg_view("input/dropdown", array("name" => "params[" . $name . "][show_on_register]", "value" => $show_on_register, "options_values" => $noyes_options)) . "</td>";
				$form_body .= "<td>" . elgg_view("input/dropdown", array("name" => "params[" . $name . "][mandatory]", "value" => $mandatory, "options_values" => $noyes_options)) . "</td>";
				$form_body .= "</tr>";
			}
		}
		$form_body .= "</table>";
		$form_body .= elgg_view("input/submit", array("value" => elgg_echo("save")));
		
		$body .= elgg_view("input/form", array("action" => "action/subsite_manager/main_profile_fields", "body" => $form_body));
		
		echo elgg_view_module("inline", $title, $body);
		
	}
	
	
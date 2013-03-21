<?php 

	if(!empty($_SESSION["subsite_manager_csv"])){
		echo "<div>" . elgg_echo("subsite_manager:invite:csv:description") . "</div>";
		echo "<br />";
		
		$form_vars = array(
			"class" => "elgg-form-settings"
		);
		$body_vars = array(
			"message" => elgg_get_sticky_value("subsite_manager_invite", "message", ""),
			"column" => elgg_extract("column", $_SESSION["subsite_manager_csv"])
		);
		
		echo elgg_view_form("subsites/invite/csv", $form_vars, $body_vars);
	} else {
		forward("admin/users/invite");
	}
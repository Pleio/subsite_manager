<?php 

	$step = (int) get_input("step", 1);
	
	switch($step){
		case 2:
			echo elgg_view_form("subsites/import/step2");
			break;
		case 1:
		default:
			$form_vars = array(
				"enctype" => "multipart/form-data"
			);
			
			echo elgg_view_form("subsites/import/step1", $form_vars);
			break;
	}
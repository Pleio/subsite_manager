<?php 

	function subsite_manager_runonce() {
		// register a new substype in the database and link it to the right classname
		add_subtype("site", Subsite::SUBTYPE, "Subsite");
	
		// Update the database with extra columns for multisite
		run_sql_script(dirname(dirname(__FILE__)) . "/scripts/add_columns.sql");
	}
	
	function subsite_manager_runonce_elgg18(){
		$options = array(
			"type" => "site",
			"subtype" => Subsite::SUBTYPE,
			"limit" => false
		);
		
		if($subsites = elgg_get_entities($options)){
			$time = time();
			
			foreach($subsites as $subsite){
				$subsite->firstrun = $time;
			}
			
			unset($subsites);
		}
	}
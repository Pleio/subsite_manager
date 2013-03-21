<?php
	/**
	 * Elgg administration site advanced settings
	 *
	 * @package Elgg
	 * @subpackage Core
	 */
	
	if(subsite_manager_on_subsite()){
		echo elgg_view_form("subsites/update_advanced", array(
			"id" => "subsite_manager_subsite_update_form",
			"class" => "elgg-form-settings",
			"enctype" => "multipart/form-data"
		));
	} else {
		echo elgg_view_form("admin/site/update_advanced", array("class" => "elgg-form-settings"));
	}

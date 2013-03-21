<?php 

	$widget = $vars["entity"];
	
	$num_display = (int) $widget->num_display;
	if($num_display < 1){
		$num_display = 5;
	}
	
	$options = array(
		"type" => "site",
		"subtype" => Subsite::SUBTYPE,
		"limit" => $num_display,
		"pagination" => false
	);
	
	if($widget->show_featured == "yes"){
		$options["metadata_names"] = array("featured");
		$options["order_by_metadata"] = array("name" => "featured", "direction" => "DESC");
	}
	
	if(!($list = elgg_list_entities_from_metadata($options))){
		$list = elgg_echo("notfound");
	}
	
	echo $list;
<?php 

	$annotation = $vars["annotation"];
	
	$hidden = access_get_show_hidden_status();
	access_show_hidden_entities(true);
	
	if($owner = $annotation->getOwnerEntity()){
		$icon = elgg_view_entity_icon($owner, "small");
		
		$info = elgg_view_menu("annotation", array(
			"annotation" => $annotation,
			"sort_by" => "priority",
			"class" => "elgg-menu-hz float-alt"
		));
		
		$info .= elgg_view("output/url", array("text" => $owner->name, "href" => $owner->getURL()));
		$info .= "&nbsp;(" . $owner->email . ")";
		$info .= "<div class='elgg-subtext'>" . elgg_view_friendly_time($annotation->time_created) . "</div>";
		
		if($annotation->value){
			$info .= elgg_view("output/longtext", array("value" => $annotation->value));
		}
		
		echo elgg_view_image_block($icon, $info);
	} else {
		// user no longer exists, so delete request
		$annotation->delete();
	}
	
	access_show_hidden_entities($hidden);
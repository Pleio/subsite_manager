<?php 

	$widget = $vars["entity"];
	
	$noyes_options = array(
		"no" => elgg_echo("option:no"),
		"yes" => elgg_echo("option:yes"),
	);
	
	$num_display = (int) $widget->num_display;
	if($num_display < 1){
		$num_display = 5;
	}
	
	echo "<div>";
	echo elgg_echo("widget:numbertodisplay");
	echo "&nbsp;" . elgg_view("input/dropdown", array("name" => "params[num_display]", "value" => $num_display, "options" => range(1, 10)));
	echo "</div>";
	
	echo "<div>";
	echo elgg_echo("subsite_manager:widgets:subsite:show_featured");
	echo "&nbsp;" . elgg_view("input/dropdown", array("name" => "params[show_featured]", "value" => $widget->show_featured, "options_values" => $noyes_options));
	echo "</div>";
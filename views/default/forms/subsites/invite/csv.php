<?php 

	$message = elgg_extract("message", $vars);
	$column = elgg_extract("column", $vars);
	
	$column = array_reverse($column, true);
	$column[-1] = elgg_echo("subsite_manager:invite_csv:column:select");
	$column = array_reverse($column, true);
	
	echo "<div>";
	echo "<label>" . elgg_echo("subsite_manager:invite_csv:column:label") . "</label><br />";
	echo "<div class='elgg-subtext'>" . elgg_echo("subsite_manager:invite_csv:column:description") . "</div>";
	echo elgg_echo("name") . "&nbsp;" . elgg_view("input/dropdown", array("name" => "name", "options_values" => $column)) . "<br />";
	echo elgg_echo("email") . "&nbsp;" . elgg_view("input/dropdown", array("name" => "email", "options_values" => $column));
	echo "</div>";
	
	echo "<div>";
	echo "<label>" . elgg_echo("subsite_manager:invite:message:label") . "</label><br />";
	echo elgg_view("input/longtext", array("name" => "message", "value" => $message));
	echo "</div>";
	
	echo "<div class='elgg-footer'>";
	echo elgg_view("input/submit", array("value" => elgg_echo("invite")));
	echo "</div>";
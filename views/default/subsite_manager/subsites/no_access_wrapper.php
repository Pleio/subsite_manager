<?php 

	$subsite = elgg_extract("subsite", $vars);
	$groups = elgg_extract("groups", $vars);
	
	echo "<table id='subsite-manager-no-access-table'>";
	echo "<tr>";
	echo "<td>" . $subsite . "</td>";
	echo "<td style='vertical-align: middle;'>" . $groups . "</td>";
	echo "</tr>";
	echo "</table>";
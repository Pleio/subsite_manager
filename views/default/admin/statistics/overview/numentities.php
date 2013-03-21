<?php

	$site = elgg_get_site_entity();
	$even_odd = "";
	
	// Get entity statistics
	$site_stats = subsite_manager_get_site_statistics($site->getGUID());
	
	if(!subsite_manager_on_subsite()){
		$all_stats = subsite_manager_get_site_statistics();
	}

	if(!empty($all_stats)){
		echo "<table class='elgg-table-alt'>";
		echo "<tr>";
		echo "<th>&nbsp;</th>";
		echo "<th>" . elgg_echo("all") . "</th>";
		echo "<th>" . $site->name . "</th>";
		echo "</tr>";
		
		foreach ($all_stats as $type => $entry) {
			arsort($entry);
				
			foreach ($entry as $subtype => $count) {
				//This function controls the alternating class
				if($even_odd != "odd"){
					$even_odd = "odd";
				} else {
					$even_odd = "even";
				}
		
				if ($subtype == "__base__") {
					$entity_title = elgg_echo("item:" . $type);
					if (empty($entity_title)) {
						$entity_title = $type;
					}
				} else {
					if (empty($subtype)) {
						$entity_title = elgg_echo("item:" . $type);
					} else {
						$entity_title = elgg_echo("item:" . $type . ":" . $subtype);
					}
		
					if (empty($entity_title)) {
						$entity_title = $type . " " . $subtype;
					}
				}
		
				echo "<tr class='" . $even_odd . "'>";
				echo "<td>" . $entity_title . ":</td>";
				echo "<td>" . $count . "</td>";
				if(!empty($site_stats) && isset($site_stats[$type]) && isset($site_stats[$type][$subtype])){
					echo "<td>" . $site_stats[$type][$subtype] . "</td>";
				} else {
					echo "<td>&nbsp;</td>";
				}
				echo "</tr>";
			}
		}
		
		echo "</table>";
	} elseif(!empty($site_stats)){
		echo "<table class='elgg-table-alt'>";
		
		foreach ($site_stats as $k => $entry) {
			arsort($entry);
			
			foreach ($entry as $a => $b) {
				//This function controls the alternating class
				if($even_odd != "odd"){
					$even_odd = "odd";
				} else {
					$even_odd = "even";
				}
		
				if ($a == "__base__") {
					$a = elgg_echo("item:{$k}");
					if (empty($a)){
						$a = $k;
					}
				} else {
					if (empty($a)) {
						$a = elgg_echo("item:{$k}");
					} else {
						$a = elgg_echo("item:{$k}:{$a}");
					}
		
					if (empty($a)) {
						$a = "$k $a";
					}
				}
		
				echo "<tr class='" . $even_odd . "'>";
				echo "<td>". $a . ":</td>";
				echo "<td>" . $b . "</td>";
				echo "</tr>";
			}
		}
		echo "</table>";
	} else {
		echo elgg_echo("notfound");
	}
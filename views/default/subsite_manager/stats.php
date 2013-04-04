<?php 

	/**
	 * Display some debugging stats
	 */

	global $START_MICROTIME; 
	global $SUBSITE_MANAGER_DB_TOTAL_TIME;
	global $dbcalls;
	global $DB_QUERY_CACHE_HITS;
	global $COLDTRICK_DB_LOG;
	global $SUBSITE_MANAGER_MEMCACHE_TOTAL_TIME;
	global $SUBSITE_MANAGER_MEMCACHE_TOTAL;
	global $SUBSITE_MANAGER_MEMCACHE_ERROR_TOTAL;
	
	echo "<div>";
	echo "<input type='hidden' name='script_run_time' value='" . (microtime(true) - $START_MICROTIME) . "' />";
	echo "<input type='hidden' name='script_db_time' value='" . $SUBSITE_MANAGER_DB_TOTAL_TIME . "' />";
	echo "<input type='hidden' name='script_db_calls' value='" . $dbcalls . "' />";
	
	if($user = elgg_get_logged_in_user_entity()){
		$username = $user->username;
		
		if(in_array($username, array("jdalsem", "jeabakker"))){
			echo "Script runtime: " . (microtime(true) - $START_MICROTIME) . "<br />";
			echo "Peak memory: " . number_format(memory_get_peak_usage(true), 0, ",", ".") . "<br />";
			echo "DB runtime: " . $SUBSITE_MANAGER_DB_TOTAL_TIME . "<br />";
			echo "DB query count: " . $dbcalls . "<br />";
			echo "DB Query cache hits: " . $DB_QUERY_CACHE_HITS . "<br /><br />";
			
			echo "Memcache runtime: " . $SUBSITE_MANAGER_MEMCACHE_TOTAL_TIME . " <br />";
			echo "Memcache count: " . $SUBSITE_MANAGER_MEMCACHE_TOTAL . "<br />";
			echo "Memcache not found: " . $SUBSITE_MANAGER_MEMCACHE_ERROR_TOTAL . "<br /><br />";
			
			echo "SERVER: " . $_SERVER["SERVER_ADDR"] . "<br /><br />";
				
			echo "<input type='hidden' name='server' value='" . $_SERVER["SERVER_ADDR"] . "' />";
			
			if(!empty($COLDTRICK_DB_LOG)){
				echo "TOP 5 SLOW:<br /><br />";
				
				$ordered = $COLDTRICK_DB_LOG;
				arsort($ordered, SORT_NUMERIC);
				$i = 0;
				foreach($ordered as $key => $query){
					if($i == 5) {
						break;
					}
					$i++;
					echo $key . " - " . $query . "<br />". "<br />";
				}
	
				echo "QUERY EXECUTION ORDER:<br /><br />";
				foreach($COLDTRICK_DB_LOG as $key => $query){
					echo $key . " - " . $query . "<br />". "<br />";
				}
			}
			
			echo "<br /><br />";
		}
	}
	echo "</div>";
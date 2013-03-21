<?php 

	/**
	 * Display some debugging stats
	 */

	global $START_MICROTIME; 
	global $SUBSITE_MANAGER_DB_TOTAL_TIME;
	global $dbcalls;
	global $COLDTRICK_DB_LOG;

	echo "<input type='hidden' name='script_run_time' value='" . (microtime(true) - $START_MICROTIME) . "' />";
	echo "<input type='hidden' name='script_db_time' value='" . $SUBSITE_MANAGER_DB_TOTAL_TIME . "' />";
	echo "<input type='hidden' name='script_db_calls' value='" . $dbcalls . "' />";
	
	if($user = elgg_get_logged_in_user_entity()){
		$username = $user->username;
		
		if(in_array($username, array("jdalsem", "jeabakker"))){
			echo "SCRIPT RUN TIME: " . (microtime(true) - $START_MICROTIME) . " <br />";
			echo "DB RUN TIME: " . $SUBSITE_MANAGER_DB_TOTAL_TIME . " <br />";
			echo "TOTAL QUERY COUNT: $dbcalls <br /><br />";
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
		}
	}
<?php 

	$session_id = base64_decode($_GET["sid"]);
	
	session_id($session_id);
	
	require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . "/engine/start.php");
	
	$forward_url = "";
	
	$valid = false;
	$validate = get_input("validate");
	$ts = (int) get_input("ts");
	
	if(!empty($validate) && !empty($ts)) {
		$site_secret = get_site_secret();
		$host = $_SERVER["HTTP_HOST"];
		
		$new_validate = md5($session_id . $site_secret . $ts . $host);
		
		if($validate === $new_validate){
			$valid = true;
			
			$forward_url = get_input("forward");
		}
	}
	
	if(!$valid && elgg_is_logged_in()){
		logout();
	}
	
	forward($forward_url);
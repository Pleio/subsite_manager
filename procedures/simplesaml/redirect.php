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
			
			// check for attributes
			if (isset($_SESSION["sm_saml_attributes"]) && isset($_SESSION["sm_saml_source"])) {
				$user = elgg_get_logged_in_user_entity();
				$source = $_SESSION["sm_saml_source"];
				$saml_attributes = $_SESSION["sm_saml_attributes"];
			
				if (!empty($user)) {
					simplesaml_save_authentication_attributes($user, $source, $saml_attributes);
				}
			} elseif (isset($_SESSION["saml_attributes"]) && isset($_SESSION["saml_source"])) {
				$source = $_SESSION["saml_source"];
				$saml_attributes = $_SESSION["saml_attributes"];
				
				if (subsite_manager_simplesaml_check_auto_create_account($source, $saml_attributes)) {
					// check for account creation
					$forward_url = "action/simplesaml/register?saml_source=" . $source;
					$forward_url = elgg_add_action_tokens_to_url($forward_url);
				}
			}
		}
	}
	
	if(!$valid && elgg_is_logged_in()){
		logout();
	}
	
	forward($forward_url);
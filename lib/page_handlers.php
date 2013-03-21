<?php 

	function subsite_manager_subsites_page_handler($page){
		$result = false;
		
		switch($page[0]){
			case "join":
				$result = true;
				
				include(dirname(dirname(__FILE__)) . "/pages/subsites/join.php");
				break;
			case "no_access":
				$result = true;
				
				include(dirname(dirname(__FILE__)) . "/pages/subsites/no_access.php");
				break;
			case "join_domain":
				$result = true;
				
				include(dirname(dirname(__FILE__)) . "/procedures/subsites/join_domain.php");
				break;
			case "invitation":
				$result = true;
				
				include(dirname(dirname(__FILE__)) . "/procedures/subsites/invitation.php");
				break;
			case "invite_autocomplete":
				$result = true;
			
				include(dirname(dirname(__FILE__)) . "/procedures/subsites/invite_autocomplete.php");
				break;
			default:
				$result = true;
			
				if(!empty($page[0])){
					set_input("filter", $page[0]);
				}
			
				include(dirname(dirname(__FILE__)) . "/pages/subsites/list.php");
				break;
		}
		
		return $result;
	}
	
	function subsite_manager_subsite_icon_page_handler($page){
		
		if(isset($page[0])){
			set_input("site_guid", $page[0]);
		}
		
		if(isset($page[1])){
			set_input("size", $page[1]);
		}
		
		include(dirname(dirname(__FILE__)) . "/pages/subsite_icon/icon.php");
		
		return true;
	}
	
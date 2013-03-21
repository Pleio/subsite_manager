<?php 

if(!elgg_is_admin_logged_in()){
	return true;
}
global $CONFIG;

$q = sanitize_string(get_input("q"));
$current_users = sanitize_string(get_input("user_guids"));
$limit = (int) get_input("limit", 50);
$group_guid = (int) get_input("group_guid", 0);
$relationship = sanitize_string(get_input("relationship", "none"));

$include_self = get_input("include_self", false);
if(!empty($include_self)){
	$include_self = true;
}

$result = array();

if(($user = elgg_get_logged_in_user_entity()) && !empty($q)){
	// show hidden (unvalidated) users
	$hidden = access_get_show_hidden_status();
	access_show_hidden_entities(true);
	
	$dbprefix = elgg_get_config("dbprefix");
		
	// find existing users
	$query_options = array(
			"type" => "user",
			"limit" => $limit,
			"joins" => array("JOIN {$dbprefix}users_entity u ON e.guid = u.guid"),
			"wheres" => array("(u.name LIKE '%{$q}%' OR u.username LIKE '%{$q}%')", "u.banned = 'no'"),
			"order_by" => "u.name asc"
	);
		
	if(!$include_self){
		if(empty($current_users)){
			$current_users = $user->getGUID();
		} else {
			$current_users .= "," . $user->getGUID();
		}
	}
		
	if(!empty($current_users)){
		$query_options["wheres"][] = "e.guid NOT IN (" . $current_users . ")";
	}
		
	if($entities = elgg_get_entities_from_relationship($query_options)){
		foreach($entities as $entity){
			if(!check_entity_relationship($entity->getGUID(), "member", $group_guid)){
				$result[] = array("type" => "user", "value" => $entity->getGUID(),"content" => "<img src='" . $entity->getIconURL("tiny") . "' /> " . $entity->name, "name" => $entity->name);
			}
		}
	}

	// add an email option
	$regexpr = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/";
	if(preg_match($regexpr, $q)){
		if(!($users = get_user_by_email($q))){
			$result[] = array("type" => "email", "value" => $q, "content" => $q);
		} else {
			$result[] = array("type" => "user", "value" => $users[0]->getGUID(),"content" => "<img src='" . $users[0]->getIconURL("tiny") . "' /> " . $users[0]->name, "name" => $users[0]->name);
		}
	}
	// restore hidden users
	access_show_hidden_entities($hidden);
}

header("Content-Type: application/json");
echo json_encode(array_values($result));
exit();
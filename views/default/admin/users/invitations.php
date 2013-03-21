<?php 

	if(subsite_manager_on_subsite()){
		$site = elgg_get_site_entity();
		
		$invitations = $site->getInvitations();
		
		$users = elgg_extract("users", $invitations);
		$emails = elgg_extract("email_addresses", $invitations);
		
		if(!empty($users) || !empty($emails)){
			echo "<div>" . elgg_echo("subsite_manager:invitations:description") . "</div>";
			
			if(!empty($users)){
				$users_body = "<table class='elgg-table'>";
				$users_body .= "<tr>";
				$users_body .= "<th>" . elgg_echo("name") . "</th>";
				$users_body .= "<th>&nbsp;</th>";
				$users_body .= "</tr>";
				
				foreach($users as $user){
					$users_body .= "<tr>";
					$users_body .= "<td>" . $user->name . "</td>";
					$users_body .= "<td class='center'>";
					$users_body .= elgg_view("output/confirmlink", array(
												"text" => elgg_echo("subsite_manager:revoke"), 
												"href" => "action/subsites/invite/revoke?user_guid=" . $user->getGUID(),
												"confirm" => elgg_echo("subsite_manager:invitations:revoke:confirm"),
												"class" => "elgg-button elgg-button-action"));
					$users_body .= "</td>";
					$users_body .= "</tr>";
				}
				
				$users_body .= "</table>";
				
				echo elgg_view_module("inline", elgg_echo("subsite_manager:invitations:users:title"), $users_body);
			}
			
			if(!empty($emails)){
				$email_body = "<table class='elgg-table'>";
				$email_body .= "<tr>";
				$email_body .= "<th>" . elgg_echo("email") . "</th>";
				$email_body .= "<th>&nbsp;</th>";
				$email_body .= "</tr>";
				
				foreach($emails as $email){
					$email_body .= "<tr>";
					$email_body .= "<td>" . $email . "</td>";
					$email_body .= "<td class='center'>";
					$email_body .= elgg_view("output/confirmlink", array(
												"text" => elgg_echo("subsite_manager:revoke"), 
												"href" => "action/subsites/invite/revoke?email=" . $email,
												"confirm" => elgg_echo("subsite_manager:invitations:revoke:confirm"),
												"class" => "elgg-button elgg-button-action"));
					$email_body .= "</td>";
					$email_body .= "</tr>";
				}
				
				$email_body .= "</table>";
				
				echo elgg_view_module("inline", elgg_echo("subsite_manager:invitations:email:title"), $email_body);
			}
		} else {
			echo elgg_view_module("inline", elgg_echo("subsite_manager:invitations:none:title"), elgg_echo("notfound"));
		}
	} else {
		forward(REFERER);
	}
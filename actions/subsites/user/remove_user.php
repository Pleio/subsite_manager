<?php
/**
 * Remove a user from the subsite.
 *
 * This action can be called by a site admin to remove a user from the subsite,
 * or by the user to remove him/herself from the subsite
 * Site admins have the option to tell the user why they were removed.
 *
 */

$site_guid = (int) get_input("site_guid", elgg_get_site_entity()->getGUID());
$user_guid = (int) get_input("user_guid", elgg_get_logged_in_user_guid());
$msg = get_input("msg");

if(($subsite = get_entity($site_guid)) && ($user = get_user($user_guid))){
	if(elgg_instanceof($subsite, "site", Subsite::SUBTYPE, "Subsite")){
		
		if($subsite->removeUser($user->getGUID(), $msg)){
			system_message(elgg_echo("subsite_manager:action:subsites:remove_user:success", array($user->name, $subsite->name)));
		} else {
			register_error(elgg_echo("subsite_manager:action:subsites:remove_user:error:remove", array($user->name, $subsite->name)));
		}
	} else {
		register_error(elgg_echo("InvalidClassException:NotValidElggStar", array($site_guid, "Subsite")));
	}
} else {
	register_error(elgg_echo("InvalidParameterException:MissingParameter"));
}

forward(REFERER);

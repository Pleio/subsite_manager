<?php
	global $SUBSITE_MANAGER_SUBSITE_CATEGORIES;
	
	$subsite = elgg_get_site_entity();
	
	// subsite membership
	$membership_options = array(
		Subsite::MEMBERSHIP_OPEN => elgg_echo("subsite_manager:membership:open"),
		Subsite::MEMBERSHIP_APPROVAL => elgg_echo("subsite_manager:membership:approval"),
		Subsite::MEMBERSHIP_DOMAIN => elgg_echo("subsite_manager:membership:domain"),
		Subsite::MEMBERSHIP_INVITATION => elgg_echo("subsite_manager:membership:invitation"),
		Subsite::MEMBERSHIP_DOMAIN_APPROVAL => elgg_echo("subsite_manager:membership:domain_approval"),
	);
	
	$visibility_options = array(
		1 => elgg_echo("subsite_manager:new:visibility:option:visible"),
		0 => elgg_echo("subsite_manager:new:visibility:option:invisible")
	);
	
	$yesno_options = array(
		"yes" => elgg_echo("option:yes"),
		"no" => elgg_echo("option:no")
	);
	
	$default_access_options = array(
		ACCESS_PRIVATE => elgg_echo("PRIVATE"),
		ACCESS_FRIENDS => elgg_echo("access:friends:label"),
		$subsite->getACL() => elgg_echo("members") . " " . $subsite->name,
		ACCESS_LOGGED_IN => elgg_echo("LOGGED_IN")
	);
	if($subsite->hasPublicACL()){
		$default_access_options[ACCESS_PUBLIC] = elgg_echo("PUBLIC");
	}
	
	$navigation_bar_options = array(
		"top" => elgg_echo("top"),
		"bottom" => elgg_echo("bottom"),
		"disabled" => elgg_echo("disable")
	);
	
	if(elgg_is_sticky_form("subsites_update")){
		$sticky_vars = elgg_get_sticky_values("subsites_update");
		elgg_clear_sticky_form("subsites_update");
	} else {
		$sticky_vars = array();
	}
	
	// get default values
	$url = elgg_extract("url", $sticky_vars, $subsite->url);
	$category = elgg_extract("category", $sticky_vars, $subsite->category);
	$navigation_bar_postition = elgg_extract("navigation_bar_position", $sticky_vars, get_config("navigation_bar_position"));
	
	$remove_icon = elgg_extract("remove_icon", $sticky_vars);
	if(!empty($remove_icon)){
		$remove_icon = array("checked" => "checked");
	} else {
		$remove_icon = array();
	}
	
	$membership = elgg_extract("membership", $sticky_vars, $subsite->getMembership());
	$visibility = elgg_extract("visibility", $sticky_vars, sanitise_int($subsite->getVisibility()));
	$domains = elgg_extract("domains", $sticky_vars, $subsite->domains);
	
	$limit_admins = elgg_extract("limit_admins", $sticky_vars, $subsite->limit_admins);
	if(!empty($limit_admins)) {
		$limit_admins = array("checked" => "checked");
	} else {
		$limit_admins = array();
	}
	
	$has_public_acl = elgg_extract("has_public_acl", $sticky_vars, $subsite->hasPublicACL());
	if($has_public_acl === true){
		$has_public_acl = "yes";
	} elseif($has_public_acl === false){
		$has_public_acl = "no";
	}
	
	// general settings
	$general = "<div>";
	
	if(subsite_manager_is_superadmin_logged_in()){
		$general .= "<label>" . elgg_echo("subsite_manager:new:url:label") . "</label>";
		$general .= elgg_view("input/url", array("name" => "url", "value" => $url));
		$general .= "<div class='elgg-subtext'>" . elgg_echo("subsite_manager:new:url:description") . "</div>";
	}
	
	$general .= "<label>" . elgg_echo("subsite_manager:category:label") . "</label>";
	$general .= "<br />";
	$general .= elgg_view("input/dropdown", array("name" => "category", "value" => $category, "options_values" => $SUBSITE_MANAGER_SUBSITE_CATEGORIES));
	$general .= "<div class='elgg-subtext'>" . elgg_echo("subsite_manager:category:description") . "</div>";
	$general .= "</div>";
	
	$general .= "<div>";
	$general .= "<label>" . elgg_echo("subsite_manager:new:icon:label") . "</label>";
	if(!empty($subsite->icontime)){
		$general .= elgg_view_entity_icon($subsite, "medium", array("img_class" => "float-alt"));
	}
	$general .= "<br />";
	$general .= elgg_view("input/file", array("name" => "icon"));
	$general .= "<div class='elgg-subtext'>" . elgg_echo("subsite_manager:new:icon:description") . "</div>";
	
	if(!empty($subsite->icontime)){
		$general .= elgg_view("input/checkbox", array("name" => "remove_icon", "value" => "1") + $remove_icon);
		$general .= "&nbsp;" . elgg_echo("subsite_manager:subsites:update:remove_icon");
	}
	$general .= "</div>";
	
	$general .= "<div>";
	$general .= "<label>" . elgg_echo("subsite_manager:navigation_bar_position:label") . "</label>";
	$general .= "<br />";
	$general .= elgg_view("input/dropdown", array("name" => "navigation_bar_position", "value" => $navigation_bar_postition, "options_values" => $navigation_bar_options));
	$general .= "<div class='elgg-subtext'>" . elgg_echo("subsite_manager:navigation_bar_position:description") . "</div>";
	$general .= "</div>";
	
	// add general part to form
	$form_data .= elgg_view_module("inline", elgg_echo("subsite_manager:new:general"), $general);
	
	// subsite security
	$security = "<label>" . elgg_echo("subsite_manager:new:membership:label") . "</label>";
	$security .= "<br />";
	$security .= elgg_view("input/dropdown", array("name" => "membership", "value" => $membership, "options_values" => $membership_options, "onchange" => "subsite_new_change_membership();"));
	$security .= "<div class='elgg-subtext'>" . elgg_echo("subsite_manager:new:membership:description") . "</div>";
	
	$security .= "<div id='subsite_manager_subsite_update_form_visibility'>";
	$security .= "<label>" . elgg_echo("subsite_manager:new:visibility:label") . "</label>";
	$security .= "<br />";
	$security .= elgg_view("input/dropdown", array("name" => "visibility", "value" => $visibility, "options_values" => $visibility_options));
	$security .= "<div class='elgg-subtext'>" . elgg_echo("subsite_manager:new:visibility:description") . "</div>";
	$security .= "</div>";
	
	$security .= "<div id='subsite_manager_subsite_update_form_domains'>";
	$security .= "<label>" . elgg_echo("subsite_manager:new:domains:label") . "</label>";
	$security .= elgg_view("input/text", array("name" => "domains", "value" => $domains));
	$security .= "<div class='elgg-subtext'>" . elgg_echo("subsite_manager:new:domains:description") . "</div>";
	$security .= "</div>";
	$security .= "<br />";
	
	$security .= "<div>";
	$security .= "<label>" . elgg_echo("subsite_manager:update:limit_admins:label") . "</label>";
	$security .= "<br />";
	$security .= elgg_view("input/checkbox", array("name" => "limit_admins", "value" => "1") + $limit_admins);
	$security .= "&nbsp;" . elgg_echo("subsite_manager:update:limit_admins:description");
	$security .= "</div>";
	$security .= "<br />";
	
	$security .= "<label>" . elgg_echo("subsite_manager:new:has_public_acl:label") . "</label>";
	$security .= "<br />";
	$security .= elgg_view("input/dropdown", array("name" => "has_public_acl", "value" => $has_public_acl, "options_values" => $yesno_options));
	$security .= "<div class='elgg-subtext'>" . elgg_echo("subsite_manager:new:has_public_acl:description") . "</div>";
	
	$form_data .= elgg_view_module("inline", elgg_echo("subsite_manager:new:security"), $security);
	
	// Access level
	// default access level
	$access = "<div>" . elgg_echo("admin:site:access:warning") . "<br />";
	$access .= "<label>" . elgg_echo("installation:sitepermissions") . "</label>";
	$access .= "&nbsp;" . elgg_view("input/access", array("name" => "default_access", "value" => elgg_get_config("default_access"), "options_values" => $default_access_options));
	$access .= "</div>";
	$access .= "<br />";
	
	// user default access
	$access .= "<div>" . elgg_echo("installation:allow_user_default_access:description") . "<br />";
	$access .= elgg_view("input/checkboxes", array(
		"options" => array(elgg_echo("installation:allow_user_default_access:label") => elgg_echo("installation:allow_user_default_access:label")),
		"name" => "allow_user_default_access",
		"value" => (elgg_get_config("allow_user_default_access") ? elgg_echo("installation:allow_user_default_access:label") : "")
	));
	$access .= "</div>";
	$access .= "<br />";
	
	// registration
	$access .= "<div>" . elgg_echo("installation:registration:description") . "<br />";
	$access .= elgg_view("input/checkboxes", array(
		"options" => array(elgg_echo("installation:registration:label") => elgg_echo("installation:registration:label")),
		"name" => "allow_registration",
		"value" => elgg_get_config("allow_registration") ? elgg_echo("installation:registration:label") : "",
	));
	$access .= "</div>";
	$access .= "<br />";
	
	// walled garden
	$access .= "<div>" . elgg_echo("installation:walled_garden:description") . "<br />";
	$access .= elgg_view("input/checkboxes", array(
		"options" => array(elgg_echo("installation:walled_garden:label") => elgg_echo("installation:walled_garden:label")),
		"name" => "walled_garden",
		"value" => elgg_get_config("walled_garden") ? elgg_echo("installation:walled_garden:label") : "",
	));
	$access .= "</div>";
	
	// webservices api
	if(subsite_manager_is_superadmin_logged_in()){
		$access .= "<div>" . elgg_echo("installation:disableapi") . "<br />";
		$on = elgg_echo("installation:disableapi:label");
		$disable_api = elgg_get_config("disable_api");
		if ($disable_api) {
			$on = (disable_api ?  "" : elgg_echo("installation:disableapi:label"));
		}
		$access .= elgg_view("input/checkboxes", array(
			"options" => array(elgg_echo("installation:disableapi:label") => elgg_echo("installation:disableapi:label")),
			"name" => "api",
			"value" => $on,
		));
		$access .= "</div>";
	}
	
	$form_data .= elgg_view_module("inline", elgg_echo("access"), $access);
	
	// buttons
	$buttons .= elgg_view("input/submit", array("value" => elgg_echo("update")));
	
	$form_data .= elgg_view_module("inline", "", $buttons);
	
	echo $form_data;
?>
<script type="text/javascript">

	$(document).ready(function(){
		subsite_new_change_membership();

		$('#subsite_manager_subsite_update_form').submit(function(){
			//  validate form
			var result = false;
			var error_count = 0;
			var error_msg = "";

			if(($(this).find('select[name="membership"]').val() == "<?php echo Subsite::MEMBERSHIP_DOMAIN; ?>" || $(this).find('select[name="membership"]').val() == "<?php echo Subsite::MEMBERSHIP_DOMAIN_APPROVAL; ?>") && $(this).find('input[name="domains"]').val() == ""){
				error_count++;
				error_msg = error_msg + elgg.echo("subsite_manager:new:js:domains") + "\n";
			}
	
			if(error_count > 0){
				alert(error_msg);
			} else {
				result = true;
			}
			
			return result;
		});
	});

	function subsite_new_change_membership(){
		if($('#subsite_manager_subsite_update_form select[name="membership"]').val() == "<?php echo Subsite::MEMBERSHIP_DOMAIN; ?>" || $('#subsite_manager_subsite_update_form select[name="membership"]').val() == "<?php echo Subsite::MEMBERSHIP_DOMAIN_APPROVAL; ?>"){
			$('#subsite_manager_subsite_update_form_domains').show();
		} else {
			$('#subsite_manager_subsite_update_form_domains').hide();
		}
	}

</script>
<?php 
	global $SUBSITE_MANAGER_SUBSITE_CATEGORIES;
	
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
	
	// was this form filled in before
	if(elgg_is_sticky_form("subsites_new")){
		$sticky_vars = elgg_get_sticky_values("subsites_new");
		elgg_clear_sticky_form("subsites_new");
	} else {
		$sticky_vars = array();
	}
	
	// get values
	$name = elgg_extract("name", $sticky_vars, "");
	$description = elgg_extract("description", $sticky_vars, "");
	$url = elgg_extract("url", $sticky_vars, "https://");
	$admin = elgg_extract("admin", $sticky_vars);
	
	$membership = elgg_extract("membership", $sticky_vars, Subsite::MEMBERSHIP_OPEN);
	$visibility = elgg_extract("visibility", $sticky_vars, 1);
	$domains = elgg_extract("domains", $sticky_vars, "");
	
	// info part
	$form_body = elgg_view_module("inline", "", elgg_echo("subsite_manager:new:info"));
	
	// general config
	$general = "<label>" . elgg_echo("subsite_manager:new:name:label") . "</label>";
	$general .= elgg_view("input/text", array("name" => "name", "value" => $name));
	$general .= "<div class='elgg-subtext'>" . elgg_echo("subsite_manager:new:name:description") . "</div>";
	
	$general .= "<label>" . elgg_echo("subsite_manager:new:description:label") . "</label>";
	$general .= elgg_view("input/text", array("name" => "description", "value" => $description));
	$general .= "<div class='elgg-subtext'>" . elgg_echo("subsite_manager:new:description:description") . "</div>";
	
	$general .= "<label>" . elgg_echo("subsite_manager:new:url:label") . "</label>";
	$general .= elgg_view("input/text", array("name" => "url", "value" => $url));
	$general .= "<div class='elgg-subtext'>" . elgg_echo("subsite_manager:new:url:description") . "</div>";
	
	// ToDo: make this work again
	$general .= "<label>" . elgg_echo("subsite_manager:new:admin:label") . "</label>";
	$general .= "<br />";
	$general .= elgg_view("input/autocomplete", array("name" => "admin", "value" => $admin, "match_on" => array("users")));
	$general .= "<div class='elgg-subtext'>" . elgg_echo("subsite_manager:new:admin:description") . "</div>";
	
	$general .= "<label>" . elgg_echo("subsite_manager:category:label") . "</label>";
	$general .= "<br />";
	$general .= elgg_view("input/dropdown", array("name" => "category", "value" => $category, "options_values" => $SUBSITE_MANAGER_SUBSITE_CATEGORIES));
	$general .= "<div class='elgg-subtext'>" . elgg_echo("subsite_manager:category:description") . "</div>";
	
	$general .= "<label>" . elgg_echo("subsite_manager:new:icon:label") . "</label>";
	$general .= "<br />";
	$general .= elgg_view("input/file", array("name" => "icon"));
	$general .= "<div class='elgg-subtext'>" . elgg_echo("subsite_manager:new:icon:description") . "</div>";
	
	// add general part to form
	$form_body .= elgg_view_module("inline", elgg_echo("subsite_manager:new:general"), $general);
	
	// Security config
	$security = "<label>" . elgg_echo("subsite_manager:new:membership:label") . "</label>";
	$security .= "<br />";
	$security .= elgg_view("input/dropdown", array("name" => "membership", "value" => $membership, "options_values" => $membership_options, "onchange" => "subsite_new_change_membership();"));
	$security .= "<div class='elgg-subtext'>" . elgg_echo("subsite_manager:new:membership:description") . "</div>";
	
	$security .= "<div id='subsite_manager_subsite_new_form_visibility'>";
	$security .= "<label>" . elgg_echo("subsite_manager:new:visibility:label") . "</label>";
	$security .= "<br />";
	$security .= elgg_view("input/dropdown", array("name" => "visibility", "value" => $visibility, "options_values" => $visibility_options));
	$security .= "<div class='elgg-subtext'>" . elgg_echo("subsite_manager:new:visibility:description") . "</div>";
	$security .= "</div>";
	
	$security .= "<div id='subsite_manager_subsite_new_form_domains'>";
	$security .= "<label>" . elgg_echo("subsite_manager:new:domains:label") . "</label>";
	$security .= elgg_view("input/text", array("name" => "domains", "value" => $domains));
	$security .= "<div class='elgg-subtext'>" . elgg_echo("subsite_manager:new:domains:description") . "</div>";
	$security .= "</div>";
	
	$security .= "<label>" . elgg_echo("subsite_manager:new:has_public_acl:label") . "</label>";
	$security .= "<br />";
	$security .= elgg_view("input/dropdown", array("name" => "has_public_acl", "value" => $has_public_acl, "options_values" => $yesno_options));
	$security .= "<div class='elgg-subtext'>" . elgg_echo("subsite_manager:new:has_public_acl:description") . "</div>";
	
	// add security part to form
	$form_body .= elgg_view_module("inline", elgg_echo("subsite_manager:new:security"), $security);
	
	// buttons part
	if(!empty($entity)){
		$buttons = elgg_view("input/submit", array("value" => elgg_echo("udpate")));
		$buttons .= elgg_view("input/hidden", array("name" => "guid", "value" => $entity->getGUID()));
	} else {
		$buttons = elgg_view("input/submit", array("value" => elgg_echo("save")));
	}
	
	// add buttons to form
	$form_body .= elgg_view_module("inline", "", $buttons);
	
	// show form
	echo $form_body;
?>
<script type="text/javascript">

	$(document).ready(function(){
		subsite_new_change_membership();

		$('#subsite_manager_subsite_new_form').submit(function(){
			//  validate form
			var result = false;
			var error_count = 0;
			var error_msg = "";

			if($(this).find('input[name="name"]').val() == ""){
				error_count++;
				error_msg = error_msg + elgg.echo("subsite_manager:new:js:name") + "\n";
			}

			var regexpr = /^(http|https):\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,4}\/$/;
			if(!$(this).find('input[name="url"]').val().match(regexpr)){
				error_count++;
				error_msg = error_msg + elgg.echo("subsite_manager:new:js:url") + "\n";
			}

			if(($(this).find('select[name="membership"]').val() == "<?php echo Subsite::MEMBERSHIP_DOMAIN; ?>" || $(this).find('select[name="membership"]').val() == "<?php echo Subsite::MEMBERSHIP_DOMAIN_APPROVAL; ?>") && $(this).find('input[name="domains"]').val() == ""){
				error_count++;
				error_msg = error_msg + elgg.echo("subsite_manager:new:js:domains") + "\n";
			}
	
			if(error_count > 0){
				alert(error_msg);
			} else {
				if($(this).find('input[name="admin"]').length == 0){
					result = confirm(elgg.echo("subsite_manager:new:js:confirm:admin"));
				} else {
					result = true;
				}
			}
			
			return result;
		});
	});

	function subsite_new_change_membership(){
		if($('#subsite_manager_subsite_new_form select[name="membership"]').val() == "<?php echo Subsite::MEMBERSHIP_DOMAIN; ?>" || $('#subsite_manager_subsite_new_form select[name="membership"]').val() == "<?php echo Subsite::MEMBERSHIP_DOMAIN_APPROVAL; ?>"){
			$('#subsite_manager_subsite_new_form_domains').show();
		} else {
			$('#subsite_manager_subsite_new_form_domains').hide();
		}
	}

</script>
<?php 

	admin_gatekeeper();
	
	// make a sticky form in case of an error
	elgg_make_sticky_form("subsites_update");
	
	// get input
	$url = get_input("url");
	$category = get_input("category");
	$topbar_icon = get_resized_image_from_uploaded_file("icon", 16, 16, true);
	$remove_icon = get_input("remove_icon");
	$navigation_bar_position = get_input("navigation_bar_position", "top");
	
	$membership = get_input("membership");
	$visibility = get_input("visibility");
	$domains = get_input("domains");
	
	$limit_admins = get_input("limit_admins");
	$has_public_acl = get_input("has_public_acl");
	
	$default_access = (int) get_input("default_access", ACCESS_PUBLIC);
	$allow_user_default_access = get_input("allow_user_default_access");
	$allow_registration = get_input("allow_registration", false);
	$walled_garden = get_input("walled_garden", false);
	
	$forward_url = REFERER;
	
	if(($subsite = elgg_get_site_entity()) && (elgg_instanceof($subsite, "site", Subsite::SUBTYPE, "Subsite"))){
		
		// set a new url
		if(!empty($url) && ($subsite->url != $url)){
			// only by super admins
			if(subsite_manager_is_superadmin_logged_in()){
				$valid = true;
				
				if(!(stristr($url, "http://") || stristr($url, "https://")) || (substr($url, -1) != "/")){
					$valid = false;
					register_error(elgg_echo("subsite_manager:action:subsites:new:error:validate:url:invalid"));
				} elseif(get_site_by_url($url)) {
					$valid = false;
					register_error(elgg_echo("subsite_manager:action:subsites:new:error:validate:url:duplicate"));
				}
				
				if($valid){
					$subsite->url = $url;
					$forward_url = $url . "admin/settings/advanced";
					
					// invalidate cache
					elgg_invalidate_simplecache();
					elgg_filepath_cache_reset();
				}
			}
		}
		
		// set site category
		$subsite->category = $category;
		
		if(!empty($remove_icon)){
			// remove current icon
			$subsite->removeIcon();
		} elseif(!empty($topbar_icon)){
			// upload new icon
			$tiny = get_resized_image_from_uploaded_file("icon", 25, 25, true);
			$small = get_resized_image_from_uploaded_file("icon", 40, 40, true);
			$medium = get_resized_image_from_uploaded_file("icon", 100, 100, true);
			$large = get_resized_image_from_uploaded_file("icon", 200, 200);
			$master = get_resized_image_from_uploaded_file("icon", 500, 500);
				
			// Add images to Subsite
			$subsite->uploadIcon("topbar", $topbar_icon);
			$subsite->uploadIcon("favicon", $topbar_icon);
			$subsite->uploadIcon("tiny", $tiny);
			$subsite->uploadIcon("small", $small);
			$subsite->uploadIcon("medium", $medium);
			$subsite->uploadIcon("large", $large);
			$subsite->uploadIcon("master", $master);
		}
		
		$subsite->setMembership($membership);
		$subsite->setVisibility($visibility);
		
		if(($membership == Subsite::MEMBERSHIP_DOMAIN) || ($membership == Subsite::MEMBERSHIP_DOMAIN_APPROVAL)){
			$subsite->domains = $domains;
		} else {
			unset($subsite->domains);
		}
		
		if(!empty($limit_admins)){
			$subsite->limit_admins = true;
		} else {
			unset($subsite->limit_admins);
		}
		
		// has public acl available
		if($has_public_acl == "yes"){
			$subsite->setPublicACL(true);
		} else {
			$subsite->setPublicACL(false);
		}
		
		// set default access
		set_config("default_access", $default_access, $subsite->getGUID());
		if(!empty($allow_user_default_access)){
			set_config("allow_user_default_access", true, $subsite->getGUID());
		} else {
			set_config("allow_user_default_access", false, $subsite->getGUID());
		}
		
		// setup walled garden
		if (!empty($allow_registration) && ($membership != Subsite::MEMBERSHIP_INVITATION)) {
			set_config("allow_registration", true, $subsite->getGUID());
		} else {
			set_config("allow_registration", false, $subsite->getGUID());
		}
		// setup walled garden
		if (!empty($walled_garden)) {
			set_config("walled_garden", true, $subsite->getGUID());
		} else {
			set_config("walled_garden", false, $subsite->getGUID());
		}
		
		// set navigation bar position
		set_config("navigation_bar_position", $navigation_bar_position, $subsite->getGUID());
		
		if($subsite->save()){
			// clear sticky form
			elgg_clear_sticky_form("subsites_update");
			
			system_message(elgg_echo("admin:configuration:success"));
		} else {
			register_error(elgg_echo("admin:configuration:fail"));
		}
	} else {
		register_error(elgg_echo("InvalidClassException:NotValidElggStar", array("Subsite")));
	}
	
	forward($forward_url);
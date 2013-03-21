<?php 

	global $CONFIG;

	admin_gatekeeper();
	
	// make a sticky form in case of an error
	elgg_make_sticky_form("subsites_new");
	
	$forward_url = REFERER;
	
	// get input
	$name = get_input("name");
	$description = get_input("description");
	$url = get_input("url");
	$category = get_input("category");
	
	$membership = get_input("membership");
	$visibility = get_input("visibility");
	$domains = get_input("domains");
	$has_public_acl = get_input("has_public_acl");
	
	$admin = get_input("admin");
	
	if(!empty($name) && !empty($url) && !empty($membership) && !empty($has_public_acl)){
		$valid = true;
		
		if(!(stristr($url, "http://") || stristr($url, "https://")) || (substr($url, -1) != "/")){
			$valid = false;
			register_error(elgg_echo("subsite_manager:action:subsites:new:error:validate:url:invalid"));
		} elseif(get_site_by_url($url)) {
			$valid = false;
			register_error(elgg_echo("subsite_manager:action:subsites:new:error:validate:url:duplicate"));
		}
		
		if($valid){
			$url = strtolower($url);
			
			$site = new Subsite();
			
			$site->name = $name;
			$site->description = $description;
			$site->url = $url;
			
			if(!($guid = $site->save())){
				register_error(elgg_echo("IOException:UnableToSaveNew", array(get_class($site))));
				$site = null;
			} else {
				$site = get_entity($guid);
				$site->createACL();
				
				// set default language
				$lan = get_config("language", $CONFIG->site_guid);
				set_config("language", $lan, $site->getGUID());
				
				// set default access
				$default_access = get_config("default_access", $CONFIG->site_guid);
				set_config("default_access", $default_access, $site->getGUID());
				
				// default allow registration
				set_config("allow_registration", true, $site->getGUID());
				
				// enable simple cache
				datalist_set("simplecache_enabled_" . $site->getGUID(), 1);
				
				// enable file path cache
				datalist_set("viewpath_cache_enabled_" . $site->getGUID(), 1);
			}
			
			if(!empty($site)){
				// Default site attributes
				$site->name = $name;
				$site->description = $description;
				$site->email = "noreply@" . get_site_domain($site->getGUID());
				$site->url = $url;
				
				// site category
				$site->category = $category;
				
				// Site icon
				if(get_resized_image_from_uploaded_file("icon", 16, 16)){
					// prepare image sizes
					$topbar = get_resized_image_from_uploaded_file("icon", 16, 16, true);
					$tiny = get_resized_image_from_uploaded_file("icon", 25, 25, true);
					$small = get_resized_image_from_uploaded_file("icon", 40, 40, true);
					$medium = get_resized_image_from_uploaded_file("icon", 100, 100, true);
					$large = get_resized_image_from_uploaded_file("icon", 200, 200);
					$master = get_resized_image_from_uploaded_file("icon", 500, 500);
					
					// Add images to Subsite
					$site->uploadIcon("topbar", $topbar);
					$site->uploadIcon("favicon", $topbar);
					$site->uploadIcon("tiny", $tiny);
					$site->uploadIcon("small", $small);
					$site->uploadIcon("medium", $medium);
					$site->uploadIcon("large", $large);
					$site->uploadIcon("master", $master);
				}
				
				$site->setMembership($membership);
				
				// allow the site to be hidden
				$site->setVisibility($visibility);
					
				if($membership == Subsite::MEMBERSHIP_INVITATION){
					// disable registration on invitation only sites
					set_config("allow_registration", false, $site->getGUID());
				}
				
				if(($membership == Subsite::MEMBERSHIP_DOMAIN) || ($membership == Subsite::MEMBERSHIP_DOMAIN_APPROVAL)){
					$site->domains = $domains;
				} else {
					unset($site->domains);
				}
				
				// has public acl available
				if($has_public_acl == "yes"){
					$site->setPublicACL(true);
				} else {
					$site->setPublicACL(false);
				}
				
				// set subsite admin
				if(!empty($admin)){
					if($user = get_user_by_username($admin)){
						$site->addUser($user->getGUID());
						$site->makeAdmin($user->getGUID());
					}
				}
				
				// set a flag to initiate firstrun procedure
				$site->setPrivateSetting("firstrun", time());
				
				// End save
				if($site->save()){
					// clear sticky form
					elgg_clear_sticky_form("subsites_new");
					
					// Set forward URL
					$forward_url = elgg_get_site_url() . "subsites";
					
					system_message(elgg_echo("subsite_manager:action:subsites:new:success"));
				} else {
					register_error(elgg_echo("subsite_manager:action:subsites:new:error:last_save"));
				}
			}
		}
	} else {
		register_error(elgg_echo("subsite_manager:action:error:input"));
	}
	
	forward($forward_url);
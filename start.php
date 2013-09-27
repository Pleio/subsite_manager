<?php

	// load libary files
	require_once(dirname(__FILE__) . "/lib/functions.php");
	require_once(dirname(__FILE__) . "/lib/hooks.php");
	require_once(dirname(__FILE__) . "/lib/events.php");
	require_once(dirname(__FILE__) . "/lib/page_handlers.php");
	require_once(dirname(__FILE__) . "/lib/runonce.php");

	function subsite_manager_plugins_boot(){
		// handle run once functions
		run_function_once("subsite_manager_runonce");
		run_function_once("subsite_manager_runonce_elgg18");
		
		// Check if current user should be a admin of this site
		if($user = elgg_get_logged_in_user_entity()){
			// don't check for super admins
			if(!subsite_manager_is_superadmin($user->getGUID())){
				// where are we
				if(subsite_manager_on_subsite()){
					// is the user suppose to be an admin
					if(elgg_get_site_entity()->isAdmin($user->getGUID())){
						// is the user currently an admin
						if(!$user->isAdmin()){
							$user->makeAdmin();
						}
					} elseif($user->isAdmin()) {
						// user is an admin, but shouldn't be
						$user->removeAdmin();
					}
				} elseif($user->isAdmin()) {
					// user is an admin, but shouldn't be
					$user->removeAdmin();
				}
			}
		}
		
		// check the site email address
		$config_email = elgg_get_config("siteemail");
		$site = elgg_get_site_entity();
		
		if(empty($config_email) || ($site->email != $config_email)){
			elgg_set_config("siteemail", $site->email);
		}
	}
	
	function subsite_manager_init(){
		global $SUBSITE_MANAGER_SUBSITE_CATEGORIES;
		
		$SUBSITE_MANAGER_SUBSITE_CATEGORIES = array(
			"organizational" => elgg_echo("subsite_manager:category:options:organizational"),
			"thematic" => elgg_echo("subsite_manager:category:options:thematic")
		);
		
		// register page handlers
		elgg_register_page_handler("subsites", "subsite_manager_subsites_page_handler");
		elgg_register_page_handler("subsite_icon", "subsite_manager_subsite_icon_page_handler");
		
		// extend CSS
		elgg_extend_view("css/elgg", "subsite_manager/css/site");
		elgg_extend_view("css/admin", "subsite_manager/css/autocomplete");
		elgg_extend_view("css/admin", "subsite_manager/css/admin");
		elgg_extend_view("css/ie7", "subsite_manager/css/ie7");
		
		// extend JS
		elgg_extend_view("js/elgg", "subsite_manager/js/site");
		elgg_extend_view("js/admin", "subsite_manager/js/admin");
		
		elgg_extend_view("page/elements/foot", "subsite_manager/stats");
		
		// only on main site
		if(!subsite_manager_on_subsite()){
			// subsites are searchable
			elgg_register_entity_type("site", Subsite::SUBTYPE);
			
			// (featured) subsites widget
			elgg_register_widget_type("subsites", elgg_echo("subsite_manager:widgets:subsites:title"), elgg_echo("subsite_manager:widgets:subsites:description"), "index", true);
		} else {
			// add Google Analytics view to subsites
			elgg_extend_view("page/elements/head", "subsite_manager/analytics", 999);
			
			// admin membership request widget
			elgg_register_widget_type("subsite_membership_requests", elgg_echo("admin:users:membership"), elgg_echo("subsite_manager:subsites:title:membership"), "admin");
			
			// check cron cache status
			$site = elgg_get_site_entity();
			$file_path = get_config("dataroot") . "subsite_manager/" . $site->getGUID() . "/cron_cache.json";
			if(!file_exists($file_path)){
				register_shutdown_function("subsite_manager_make_cron_cache");
			}
			
			elgg_register_plugin_hook_handler("access:collections:write", "user", "subsite_manager_access_write_hook_menu_builder");
			
			// main profile fields on subsite
			elgg_extend_view("admin/appearance/profile_fields", "subsite_manager/extends/admin/profile_fields");
			
			// login box register link
			elgg_extend_view("forms/login", "subsite_manager/extends/forms/login_prepend", 1);
			elgg_extend_view("forms/login", "subsite_manager/extends/forms/login_append", 9999999999);
		}
		
		// remove extension in header because SM places it in topbar
		elgg_unextend_view('page/elements/header', 'search/header');

		// extend head for logged in users
		elgg_extend_view('page/elements/head', 'subsite_manager/topbar_fix');
		
		// user hover menu
		elgg_register_plugin_hook_handler('register', 'menu:user_hover', 'subsite_manager_user_hover_menu');
		
		// temporary restore Piwik settings
		subsite_manager_fix_piwik_settings();
		
		// do we need to disable htmlawed for admins
		if (elgg_is_admin_logged_in() && elgg_get_config("disable_htmlawed")) {
			elgg_unregister_plugin_hook_handler("validate", "input", "htmlawed_filter_tags");
		}
	}
	
	function subsite_manager_pagesetup(){
		
		// validate access to the site
		subsite_manager_validate_subsite_access();
		
		$context = elgg_get_context();
		
		// handle menu items
		if(elgg_is_admin_logged_in()){
			// add to admin menu
			if(subsite_manager_is_superadmin_logged_in()){
				//only for super admins
				elgg_register_admin_menu_item("administer", "import", "users", 150);
			}
			
			// list subsite administrators
			elgg_register_admin_menu_item("administer", "admins", "users", 110);
			
			if(!subsite_manager_on_subsite()){
				elgg_register_admin_menu_item("administer", "new", "subsites", 20);
				elgg_register_admin_menu_item("administer", "plugins", "subsites", 30);
				elgg_register_admin_menu_item("administer", "admins", "subsites", 40);
				elgg_register_menu_item("page", array(
					"name" => "subsites",
					"href" => "subsites",
					"text" => elgg_echo("subsite_manager:menu:admin:manage"),
					"context" => "admin",
					"section" => "administer",
					"parent_name" => "subsites",
					"priority" => 10,
					"target" => "_blank"
				));
			} else {
				// remove add user menu item
				if(!subsite_manager_is_superadmin_logged_in()){
					elgg_unregister_menu_item("page", "users:add");
				}
				
				// list membership requests
				elgg_register_admin_menu_item("administer", "membership", "users", 120);
				// invite members
				elgg_register_admin_menu_item("administer", "invite", "users", 130);
				// view invitations
				elgg_register_admin_menu_item("administer", "invitations", "users", 140);
			}
		}
		
		if(elgg_is_logged_in() && !subsite_manager_on_subsite()){
			// site menu
			elgg_register_menu_item("site", array(
				"name" => "subsites",
				"href" => "subsites",
				"text" => elgg_echo("subsite_manager:menu:subsites")
			));
		}
		
		// disable search engine indexing
		elgg_extend_view("page/elements/head", "metatags/noindex");
		elgg_extend_view("page/elements/head", "subsite_manager/remove_rss", 499);
		elgg_unextend_view("page/elements/head", "profile/metatags");
		
		if(in_array($context, array("profile", "friends", "friendsof"))){
			elgg_unregister_plugin_hook_handler("output:before", "layout", "elgg_views_add_rss_link");
		}
	}

	// default elgg events
	elgg_register_event_handler("plugins_boot", "system", "subsite_manager_plugins_boot");
	elgg_register_event_handler("init", "system", "subsite_manager_init");
	elgg_register_event_handler("pagesetup", "system", "subsite_manager_pagesetup");

	elgg_register_event_handler("upgrade", "system", "subsite_manager_upgrade_system_handler");
	
	// plugin hooks
	elgg_register_plugin_hook_handler("register", "menu:entity", "subsite_manager_entity_menu_handler", 550);
	elgg_register_plugin_hook_handler("register", "menu:subsite", "subsite_manager_subsite_menu_handler");
	elgg_register_plugin_hook_handler("register", "menu:page", "subsite_manager_page_menu_handler");
	elgg_register_plugin_hook_handler("prepare", "menu:page", "subsite_manager_page_prepare_menu_handler");
	elgg_register_plugin_hook_handler("register", "menu:annotation", "subsite_manager_annotation_menu_handler");
	
	elgg_register_plugin_hook_handler("public_pages", "walled_garden", "subsite_manager_walled_garden_handler");
	
	elgg_register_plugin_hook_handler("cron", "all", "subsite_manager_cron_handler");
	
	elgg_register_plugin_hook_handler("action", "register", "subsite_manager_action_register_hook");

	// access
	elgg_register_plugin_hook_handler("permissions_check", "all", "subsite_manager_permissions_check_hook");
	elgg_register_plugin_hook_handler("container_permissions_check", "all", "subsite_manager_container_permissions_check_hook");
	elgg_register_plugin_hook_handler("access:collections:write", "all", "subsite_manager_access_write_hook", 999999);
	elgg_register_plugin_hook_handler("access:collections:read", "user", "subsite_manager_access_read_hook");
	elgg_register_plugin_hook_handler("permissions_check:metadata", "site", "subsite_manager_permissions_check_metadata");
	elgg_register_plugin_hook_handler("access:get_sql_suffix", "user", "subsite_manager_access_get_sql_suffix_hook");
	
	elgg_register_plugin_hook_handler("route", "profile", "subsite_manager_profile_route_hook");
	elgg_register_plugin_hook_handler("route", "groups", "subsite_manager_groups_route_hook");
	elgg_register_plugin_hook_handler("route", "messages", "subsite_manager_messages_route_hook");
	elgg_register_plugin_hook_handler("route", "saml", "subsite_manager_saml_route_hook");
	
	elgg_register_plugin_hook_handler("entity:icon:url", "user", "subsite_manager_usericon_hook", 1000);
	elgg_register_plugin_hook_handler("entities:get_url", "group", "subsite_manager_entities_get_url_hook");
	elgg_register_plugin_hook_handler("entities:get_url", "object", "subsite_manager_entities_get_url_hook");
	
	elgg_register_plugin_hook_handler("metastring:objects:get", "metadata", "subsite_manager_metastring_objects_get_hook");
	elgg_register_plugin_hook_handler("metastring:objects:get", "annotation", "subsite_manager_metastring_objects_get_hook_annotations");
	elgg_register_plugin_hook_handler("metastring:objects:get", "annotations", "subsite_manager_metastring_objects_get_hook_annotations");
	elgg_register_plugin_hook_handler("create", "metadata", "subsite_manager_create_metadata_hook");
	
	elgg_register_plugin_hook_handler("entities:get", "system", "subsite_manager_get_entities_hook");
	
	// custom profile fields
	elgg_register_plugin_hook_handler("profile:fields", "profile", "subsite_manager_profile_fields_hook", 9999); // need to be last
	elgg_register_plugin_hook_handler("profile:fields", "group", "subsite_manager_group_fields_hook", 9999); // need to be last
	elgg_register_plugin_hook_handler("categorized_profile_fields", "profile_manager", "subsite_manager_profile_manager_profile_hook", 1100);
	elgg_register_plugin_hook_handler("categorized_group_fields", "profile_manager", "subsite_manager_profile_manager_group_hook", 1100);
	
	elgg_register_plugin_hook_handler("search", "site:" . Subsite::SUBTYPE, "subsite_manager_search_subsite_hook");
	elgg_register_plugin_hook_handler("search_multisite", "search", "subsite_manager_search_multisite_search_hook");
	
	elgg_register_plugin_hook_handler("display", "view", "subsite_manager_display_view_hook");
	
	elgg_register_plugin_hook_handler("action", "plugins/settings/save", "subsite_manager_plugin_action_hook");
	elgg_register_plugin_hook_handler("action", "admin/plugins/set_priority", "subsite_manager_plugin_action_hook");
	
	elgg_register_plugin_hook_handler("find_active_users", "system", "subsite_manager_find_active_users_hook");
	
	elgg_register_plugin_hook_handler("forward", "system", "subsite_manager_forward_hook");
	
	elgg_register_plugin_hook_handler("widget_url", "widget_manager", "subsite_manager_widget_url_hook");
	
	elgg_register_plugin_hook_handler("admin_notify", "user_support", "subsite_manager_user_support_admins_hook");
	
	elgg_register_plugin_hook_handler("interested_users:options", "all", "subsite_manager_object_notification_user_options_hook");
	
	// events
	elgg_register_event_handler("make_admin", "user", "subsite_manager_make_admin_handler");
	elgg_register_event_handler("remove_admin", "user", "subsite_manager_remove_admin_handler");
	
	elgg_register_event_handler("create", "site", "subsite_manager_create_site_event_handler");
	elgg_register_event_handler("create", "metadata", "subsite_manager_create_event_handler");
	elgg_register_event_handler("create", "user", "subsite_manager_create_event_handler", 600);
	
	elgg_register_event_handler("activate", "plugin", "subsite_manager_plugin_event_handler");
	elgg_register_event_handler("deactivate", "plugin", "subsite_manager_plugin_event_handler");
	
	elgg_register_event_handler("login", "user", "subsite_manager_login_event_handler");
	
	elgg_register_event_handler("join", "group", "subsite_manager_group_join_handler");
	
	elgg_register_event_handler("delete", "annotation", "subsite_manager_delete_annotation_handler");
	
	// actions
	elgg_register_action("subsites/new", dirname(__FILE__) . "/actions/subsites/new.php", "admin");
	elgg_register_action("subsites/update_advanced", dirname(__FILE__) . "/actions/subsites/update_advanced.php", "admin");
	elgg_register_action("subsites/delete", dirname(__FILE__) . "/actions/subsites/delete.php", "admin");
	elgg_register_action("subsites/plugins", dirname(__FILE__) . "/actions/subsites/plugins.php", "admin");
	elgg_register_action("subsites/toggle_featured", dirname(__FILE__) . "/actions/subsites/toggle_featured.php", "admin");
	elgg_register_action("subsites/import/step1", dirname(__FILE__) . "/actions/subsites/import/step1.php", "admin");
	elgg_register_action("subsites/import/step2", dirname(__FILE__) . "/actions/subsites/import/step2.php", "admin");
	
	elgg_register_action("subsites/add_user", dirname(__FILE__) . "/actions/subsites/user/add_user.php");
	elgg_register_action("subsites/remove_user", dirname(__FILE__) . "/actions/subsites/user/remove_user.php");
	elgg_register_action("subsites/user/toggle_admin", dirname(__FILE__) . "/actions/subsites/user/toggle_admin.php", "admin");
	
	elgg_register_action("subsites/join/request_approval", dirname(__FILE__) . "/actions/subsites/join/request_approval.php");
	elgg_register_action("subsites/join/validate_domain", dirname(__FILE__) . "/actions/subsites/join/validate_domain.php");
	elgg_register_action("subsites/join/missing_fields", dirname(__FILE__) . "/actions/subsites/user/add_user.php");
	
	elgg_register_action("subsites/membership/approve", dirname(__FILE__) . "/actions/subsites/membership/approve.php", "admin");
	elgg_register_action("subsites/membership/decline", dirname(__FILE__) . "/actions/subsites/membership/decline.php", "admin");
	
	elgg_register_action("subsites/invite/invite", dirname(__FILE__) . "/actions/subsites/invite/invite.php", "admin");
	elgg_register_action("subsites/invite/csv", dirname(__FILE__) . "/actions/subsites/invite/csv.php", "admin");
	elgg_register_action("subsites/invite/revoke", dirname(__FILE__) . "/actions/subsites/invite/revoke.php", "admin");
	
	elgg_register_action("plugins/fallback", dirname(__FILE__) . "/actions/plugins/fallback.php", "admin");
	elgg_register_action("plugins/disable_all", dirname(__FILE__) . "/actions/plugins/disable_all.php", "admin");
	
	elgg_register_action("subsite_manager/main_profile_fields", dirname(__FILE__) . "/actions/main_profile_fields.php", "admin");
	
	elgg_register_action("subsite_manager/newest_users/bulk_action", dirname(__FILE__) . "/actions/bulk_action/dummy.php", "admin");
	elgg_register_action("subsite_manager/bulk_action/user_delete", dirname(__FILE__) . "/actions/bulk_action/user_delete.php", "admin");
	
<?php 

	gatekeeper();
	
	// this page is only available on subsites
	if(subsite_manager_on_subsite()){
		$subsite = elgg_get_site_entity();
		$user = elgg_get_logged_in_user_entity();
		
		// only available if not a member
		if(!$subsite->isUser($user->getGUID())){
			// @todo: get missing profile fields
			$profile_fields = subsite_manager_get_missing_subsite_profile_fields($user->getGUID());
			
			// global form settings
			$form_vars = array(
				"class" => "elgg-form-alt"
			);
			$body_vars = array(
				"subsite" => $subsite,
				"profile_fields" => $profile_fields
			);
			
			if($subsite->canJoin()){
				if(empty($profile_fields) || $user->isAdmin()){
					$url = elgg_add_action_tokens_to_url($subsite->url . "action/subsites/add_user");
					
					forward($url);
				} else {
					$title = elgg_echo("subsite_manager:subsites:join:missing_fields");
					
					$content = elgg_view_form("subsites/join/missing_fields", $form_vars, $body_vars);
				}
			} else {
				switch($subsite->getMembership()){
					case Subsite::MEMBERSHIP_APPROVAL:
					case Subsite::MEMBERSHIP_DOMAIN_APPROVAL:
						$title = elgg_echo("subsite_manager:subsites:join:request");
						
						$content = elgg_view_form("subsites/join/request_approval", $form_vars, $body_vars);
						
						break;
					case Subsite::MEMBERSHIP_DOMAIN:
						
						// get registered domains
						$domains = $subsite->domains;
						
						if(!empty($domains)){
							// get domains to validate against
							$domains = string_to_tag_array($domains);
							
							$title = elgg_echo("subsite_manager:subsites:join:validate_domain");
							
							$body_vars["domains"] = $domains;
							$content = elgg_view_form("subsites/join/validate_domain", $form_vars, $body_vars);
						} else {
							register_error(elgg_echo("subsite_manager:subsites:join:validate_domain:error:domains"));
							forward($subsite->getOwnerEntity()->url . "subsites");
						}
						break;
				}
			}
			
			$params = array(
				"title" => $title,
				"content" => $content
			);
			
			echo elgg_view_page($title, elgg_view_layout("one_column", $params));
		} else {
			forward();
		}
	} else {
		forward();
	}
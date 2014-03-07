<?php

class Subsite extends ElggSite {
	const SUBTYPE = "subsite";

	const MEMBERSHIP_OPEN = "open";
	const MEMBERSHIP_APPROVAL = "approval";
	const MEMBERSHIP_DOMAIN = "domain";
	const MEMBERSHIP_INVITATION = "invitation";
	const MEMBERSHIP_DOMAIN_APPROVAL = "domain_approval";

	private $icon_sizes = array("topbar", "favicon", "tiny", "small", "medium", "large", "master");
	private $admin_guids;
	private $subsite_acl_cache;

	protected function initializeAttributes() {
		global $CONFIG;

		parent::initializeAttributes();

		$this->attributes["subtype"] = self::SUBTYPE;
		$this->attributes["access_id"] = ACCESS_PUBLIC;
		$this->attributes["owner_guid"] = $CONFIG->site_guid;
		$this->attributes["container_guid"] = $CONFIG->site_guid;
	}

	public function save(){
		$this->attributes["site_guid"] = $this->get("owner_guid");

		return parent::save();
	}

	public function delete(){
		if($acl = $this->getACL()){
			delete_access_collection($acl);
		}

		if($this->icontime){
			$this->removeIcon();
		}

		return parent::delete();
	}

	public function getURL(){
		return $this->url;
	}

	public function createACL(){
		$result = false;

		$name = "subsite_acl_" . $this->guid;

		if($acl = create_access_collection($name, $this->owner_guid, $this->owner_guid)){
			if($this->setPrivateSetting("subsite_acl", $acl)){
				$result = $acl;
			}
		}

		return $result;
	}

	/**
	* returns the ACL of the site
	*
	* Needs a custom query because of deadloop problems with get_private_setting
	*
	* @return int
	*/
	public function getACL(){
		if(!isset($this->subsite_acl_cache)){
			$this->subsite_acl_cache = false;

			$query = "SELECT value";
			$query .= " FROM " . get_config("dbprefix") . "private_settings";
			$query .= " WHERE name = 'subsite_acl'";
			$query .= " AND entity_guid = " . $this->getGUID();

			if($setting = get_data_row($query)){
				$this->subsite_acl_cache = $setting->value;
			}
		}

		return $this->subsite_acl_cache;
	}

	public function uploadIcon($size, $contents){
		$result = false;

		if(in_array($size, $this->icon_sizes) && !empty($contents)){
			$this->username = $this->guid;

			$icon = new ElggFile();
			$icon->owner_guid = $this->guid;

			$icon->setFilename("subsite_manager/" . $this->guid . "/subsite_icon/" . $size . ".jpg");
			$icon->open("write");
			$icon->write($contents);
			$icon->close();

			$this->icontime = time();
			unset($this->username);

			if($this->save()){
				$result = true;
			}
		}

		return $result;
	}

	public function getIconUrl($size = "medium"){
		$result = false;

		if($this->icontime){
			if(in_array($size, $this->icon_sizes)){
				$result = $this->url . "subsite_icon/" . $this->getGUID(). "/" . $size . "/" . $this->icontime . ".jpg";
			}
		} else {
			$result = parent::getIconUrl($size);
		}

		return $result;
	}

	public function getIconContents($size = "medium"){
		$result = false;

		if(!empty($this->icontime) && in_array($size, $this->icon_sizes)){
			$this->username = $this->guid;

			$icon = new ElggFile();
			$icon->owner_guid = $this->guid;

			$icon->setFilename("subsite_manager/" . $this->guid . "/subsite_icon/" . $size . ".jpg");

			if($icon->exists()){
				$result = $icon->grabFile();
			}

			unset($this->username);

			// fallback to old path
			if(empty($result)){
				$icon_path = elgg_get_config("dataroot") . "subsite_manager/" . $this->guid . "/subsite_icon/" . $size . ".jpg";

				if(file_exists($icon_path)){
					$result = file_get_contents($icon_path);
				}
			}
		}

		return $result;
	}

	public function removeIcon(){
		$result = false;

		if(!empty($this->icontime)){
			$this->username = $this->guid;

			$icon = new ElggFile();
			$icon->owner_guid = $this->guid;

			foreach($this->icon_sizes as $size){
				$icon->setFilename("subsite_icon/" . $size . ".jpg");

				if($icon->exists()){
					$icon->delete();
				}

				// check old path
				$icon_path = elgg_get_config("dataroot") . "subsite_manager/" . $this->guid . "/subsite_icon/" . $size . ".jpg";

				if(file_exists($icon_path)){
					unlink($icon_path);
				}
			}

			unset($this->icontime);
			unset($this->username);

			if($this->save()){
				$result = true;
			}
		}

		return $result;
	}

	public function setPublicACL($value = false){
		$result = false;

		if(is_bool($value)){
			$result = $this->setPrivateSetting("has_public_acl", $value);
		}

		return $result;
	}

	public function hasPublicACL(){
		$result = false;

		if($this->getPrivateSetting("has_public_acl")){
			$result = true;
		}

		return $result;
	}

	public function setAdminGuids($user_guids = null){
		$result = false;

		if(!empty($user_guids)){
			if(!is_array($user_guids)){
				$user_guids = array($user_guids);
			}

			foreach($user_guids as $guid){
				if(!$this->isUser($guid)){
					$this->addUser($guid);
				}
			}

			$result = $this->setPrivateSetting("admin_guids", implode(",", $user_guids));
		} else {
			$result = $this->setPrivateSetting("admin_guids", "");
		}

		// reset cache
		unset($this->admin_guids);
		$this->save();

		return $result;
	}

	public function getAdminGuids(){

		if(!isset($this->admin_guids)){
			$this->admin_guids = false;

			// need to bypass security
			$ia = elgg_get_ignore_access();
			elgg_set_ignore_access(true);

			if($user_guids = $this->getPrivateSetting("admin_guids")){
				$user_guids = explode(",", $user_guids);

				if(!is_array($user_guids)){
					$user_guids = array($user_guids);
				}

				$this->admin_guids = $user_guids;
			}

			// restore security
			elgg_set_ignore_access($ia);
		}

		return $this->admin_guids;
	}

	public function notifyAdmins($subject = "", $message){
		$result = false;

		if(!empty($message)){
			$result = true;

			if($admins = $this->getAdminGuids()){
				notify_user($admins, $this->getGUID(), $subject, $message, null, "email");
			}
		}

		return $result;
	}

	public function isAdmin($user_guid = 0){
		$result = false;

		if(empty($user_guid)){
			$user_guid = elgg_get_logged_in_user_guid();
		}

		if($admin_guids = $this->getAdminGuids()){
			if(in_array($user_guid, $admin_guids)){
				$result = true;
			}
		}

		return $result;
	}

	/**
	 * Assign a user as administrator to this Subsite
	 *
	 * @param number $user_guid the guid of the user to assign
	 * @param string $initial   is this action taken during the initial creation of the site. Added to prevent problems with event confilcts
	 *
	 * @return boolean
	 */
	public function makeAdmin($user_guid = 0, $initial = false) {
		$result = false;
		
		if (empty($user_guid)) {
			$user_guid = elgg_get_logged_in_user_guid();
		}
		
		if (!empty($user_guid)) {
			$admin_guids = $this->getAdminGuids();
			if (empty($admin_guids)) {
				$admin_guids = array();
			} elseif (!is_array($admin_guids)) {
				$admin_guids = array($admin_guids);
			}
			
			if (!in_array($user_guid, $admin_guids)) {
				// addition for security tools
				$new_admin = get_user($user_guid);
				if ($initial || elgg_trigger_event("make_admin", "user", $new_admin)) {
					
					$admin_guids[] = $user_guid;
					
					$result = $this->setAdminGuids($admin_guids);
				}
			} else {
				$result = true;
			}
		}
		
		return $result;
	}

	public function removeAdmin($user_guid = 0) {
		$result = false;

		if (empty($user_guid)) {
			$user_guid = elgg_get_logged_in_user_guid();
		}
		
		if (!empty($user_guid)) {
			$admin_guids = $this->getAdminGuids();
			if (empty($admin_guids)) {
				$admin_guids = array();
			} elseif (!is_array($admin_guids)) {
				$admin_guids = array($admin_guids);
			}
			
			$key = array_search($user_guid, $admin_guids);
			if ($key !== false) {
				// addition for security tools
				$old_admin = get_user($user_guid);
				if (elgg_trigger_event("remove_admin", "user", $old_admin)) {
					unset($admin_guids[$key]);
					
					$result = $this->setAdminGuids($admin_guids);
				}
			} else {
				$result = true;
			}
		}
		
		return $result;
	}

	/**
	 * This function checks if a user is given the possibility of joining a site
	 */
	public function canJoin($user_guid = 0){

		$result = false;

		if(empty($user_guid)){
			$user_guid = elgg_get_logged_in_user_guid();
		}

		if($user = get_user($user_guid)){
			if(!$this->isUser($user_guid)){
				// can join only returns true if you are not yet a member
				if($user->isAdmin() && empty($this->limit_admins)){
					// admin can join if not a private site
					$result = true;
				} else {
					// current membership
					$membership = $this->getMembership();

					switch($membership){
						case self::MEMBERSHIP_OPEN:
							// everyone can join
							$result = true;
							break;
						case self::MEMBERSHIP_APPROVAL:
							// only after approval
							break;
						case self::MEMBERSHIP_DOMAIN:
						case self::MEMBERSHIP_DOMAIN_APPROVAL:
							// based on email domain
							if($this->validateEmailDomain($user_guid)){
								$result = true;
							}
							break;
						case self::MEMBERSHIP_INVITATION:
							// if an outstanding invitation is available you can also join
							// this is NOT related to a membership type
							if($this->hasInvitation($user_guid, $user->email)){
								$result = true;
							}
							break;
					}

					if(!$result && $this->hasInvitation($user_guid, $user->email)){
						// if user has been invited he can always join
						$result = true;
					}
				}
			}
		}

		return $result;
	}

	public function isUser($user_guid = 0){
		$result = false;

		if(empty($user_guid)){
			$user_guid = elgg_get_logged_in_user_guid();
		}

		if(!empty($user_guid)){
			$result = check_entity_relationship($user_guid, "member_of_site", $this->guid);
		}

		return $result;
	}

	public function addUser($user_guid = 0){
		$result = false;

		if(empty($user_guid)){
			$user_guid = elgg_get_logged_in_user_guid();
		}

		if(!empty($user_guid)){
			$result = parent::addUser($user_guid);

			// add the user to the ACL
			add_user_to_access_collection($user_guid, $this->getACL());

			// remove optional invitations for this site
			$this->removeInvitation($user_guid);

			// remove optional membership requests
			$this->removeMembershipRequests($user_guid);

			// update member_count
			$this->getMembers(array("count" => true, "force_update_member_count" => true));
		}

		return $result;
	}

	public function removeUser($user_guid = 0, $notify_message = "") {
		$result = false;

		if (empty($user_guid)) {
			$user_guid = elgg_get_logged_in_user_guid();
		}

		if (!empty($user_guid)) {
			// check if this user is not an admin of this site
			if (!$this->isAdmin($user_guid)) {
				// get the user for further use
				$user = get_user($user_guid);

				// remove the user from the subsite ACL
				remove_user_from_access_collection($user_guid, $this->getACL());

				$result = parent::removeUser($user_guid);

				// update member_count
				$this->getMembers(array("count" => true, "force_update_member_count" => true));

				// remove the user from every group on this site
				$options = array(
					"relationship" => "member",
					"relationship_guid" => $user_guid,
					"type" => "group",
					"limit" => false,
					"site_guid" => $this->getGUID()
				);

				// exclude invited groups
				global $SUBSITE_MANAGER_INVITED_GROUPS;
				if (!empty($SUBSITE_MANAGER_INVITED_GROUPS)) {
					$options["wheres"] = array("e.guid NOT IN (" . implode(",", $SUBSITE_MANAGER_INVITED_GROUPS) . ")");
				}

				if ($groups = elgg_get_entities_from_relationship($options)) {
					foreach ($groups as $group) {
						$group->leave($user);
					}
				}

				// remove optional membership requests
				$this->removeMembershipRequests($user_guid);

				// do we need to notify the user about this
				if (elgg_is_logged_in() && ($user_guid != elgg_get_logged_in_user_guid())) {
					$admin = elgg_get_logged_in_user_entity();

					$subject = elgg_echo("subsite_manager:subsite:remove_user:subject", array($this->name));
					$message = elgg_echo("subsite_manager:subsite:remove_user:message", array(
						$user->name,
						$admin->name,
						$this->name,
						$notify_message
					));

					notify_user($user->getGUID(), $admin->getGUID(), $subject, $message, array(), "email");
				}
			}
		}

		return $result;
	}

	public function createInvitation($user_guid = 0, $email_address = ""){
		// only returns true if a new invitation has been created

		$result = false;

		// no autodetection of user as he/she will never create an invitation for him/herself
		if(!empty($user_guid)){
			if(($user = get_user($user_guid)) && !$this->isUser($user_guid)){
				// only add if user is not already a member of this site
				$result = add_entity_relationship($user_guid, "membership_invitation",  $this->guid);
			}
		} elseif(!empty($email_address)){
			// register invitation based on emailaddress
			// make sure we use lowercase email adres, for better checks
			$email_address = strtolower($email_address);

			$invitations = $this->getPrivateSetting("membership_invitation");
			if(empty($invitations)){
				// first invitation
				$result = $this->setPrivateSetting("membership_invitation", $email_address);
			} else {
				// invitations exist... append this email address if not already invited
				$invitations = explode(",",$invitations);
				if(!in_array($email_address, $invitations)){
					$invitations[] = $email_address;
					$invitations = implode(",", $invitations);
					$result = $this->setPrivateSetting("membership_invitation", $invitations);
				}
			}
		}

		return $result;
	}

	public function removeInvitation($user_guid = 0, $email_address = ""){
		$result = false;

		if(!empty($user_guid)){
			$result = remove_entity_relationship($user_guid, "membership_invitation", $this->guid);
			if(!$result && empty($email_address)){
				// no result? try it with users email address
				if($user = get_user($user_guid)){
					$email_address = $user->email;
				}
			}
		}

		if(!empty($email_address) && !$result){
			// make sure we use lowercase email adres, for better checks
			$email_address = strtolower($email_address);

			$invitations = $this->getPrivateSetting("membership_invitation");
			if(!empty($invitations)){
				$invitations = explode(",",$invitations);
				$key = array_search($email_address, $invitations);
				if($key !== false){
					// the email_address exists
					unset($invitations[$key]);
					$invitations = implode(",", $invitations);

					$result = $this->setPrivateSetting("membership_invitation", $invitations);
				}
			}
		}

		return $result;
	}

	public function hasInvitation($user_guid = 0, $email_address = ""){
		$result = false;

		if (empty($user_guid) && empty($email_address) && elgg_is_logged_in()) {
			$user_guid = elgg_get_logged_in_user_guid();
			$email_address = elgg_get_logged_in_user_entity()->email;
		}

		if (!empty($user_guid)) {
			$result = check_entity_relationship($user_guid, "membership_invitation", $this->guid);
		}

		if (!$result && !empty($email_address)) {
			// check if an invitation exists based on email_address
			// make sure we use lowercase email adres, for better checks
			$email_address = strtolower($email_address);

			$invitations = $this->getPrivateSetting("membership_invitation");
			if (!empty($invitations)) {
				$invitations = explode(",", $invitations);
				$result = in_array($email_address, $invitations);
			}
		}

		return $result;
	}

	public function getInvitations(){
		$result = array();

		// get users
		$options = array(
			"relationship" => "membership_invitation",
			"relationship_guid" => $this->guid,
			"inverse_relationship" => true,
			"limit" => 500,
			"site_guids" => false,
			"type" => "user");
		$users = elgg_get_entities_from_relationship($options);

		if(!empty($users)){
			$result["users"] = $users;
		}

		// get mailaddresses
		if($mail_invitations = $this->getPrivateSetting("membership_invitation")){

			$mail_invitations = explode(",",$mail_invitations);

			$result["email_addresses"] = $mail_invitations;
		}


		return $result;
	}

	public function setMembership($membership){
		$result = false;

		$allowed_memberships = array(
			self::MEMBERSHIP_OPEN,
			self::MEMBERSHIP_APPROVAL,
			self::MEMBERSHIP_INVITATION,
			self::MEMBERSHIP_DOMAIN,
			self::MEMBERSHIP_DOMAIN_APPROVAL
		);

		if(in_array($membership, $allowed_memberships)){
			$result = $this->setPrivateSetting("membership", $membership);
		}

		return $result;
	}

	public function getMembership(){
		$result = false;

		if($membership = $this->getPrivateSetting("membership")){
			$result = $membership;
		}

		return $result;
	}

	public function setVisibility($visibility = true){
		$result = false;

		if($visibility){
			if($this->visibility = true){
				$result = true;
			}
		} else {
			unset($this->visibility);

			if(!isset($this->visibility)){
				$result = true;
			}
		}

		return $result;
	}

	public function getVisibility(){
		$result = false;

		if(isset($this->visibility)){
			$result = true;
		}

		return $result;
	}

	public function requestMembership($reason, $user_guid = 0){
		$result = false;

		if ($user_guid == 0) {
	       	$user_guid = elgg_get_logged_in_user_guid();
		}

		if($this->annotate("request_membership", $reason, ACCESS_PRIVATE, $user_guid)){
			$user = get_user($user_guid);

			$result = true;

			$manage_link = $this->getURL() . "admin/users/membership";

			$this->notifyAdmins(elgg_echo("subsite_manager:subsite:request_membership:request:subject", array($this->name)), elgg_echo("subsite_manager:subsite:request_membership:request:message", array($user->name, $reason, $manage_link)));
		}

		return $result;
	}

	function pendingMembershipRequest($user_guid = 0){
		$result = false;

		if($user_guid == 0){
			$user_guid = elgg_get_logged_in_user_guid();
		}

		if($user_guid && ($count = $this->countMembershipRequests())){
			$annotations = $this->getAnnotations("request_membership", $count);

			foreach($annotations as $annotation){
				if($annotation->owner_guid == $user_guid){
					$result = $annotation->id;
					break;
				}
			}
		}

		return $result;
	}

	public function countMembershipRequests(){
		$old_ia = elgg_set_ignore_access(true);

		$options = array(
			"guid" => $this->getGUID(),
			"annotation_names" => array("request_membership"),
			"count" => true,
			"site_guids" => false
		);

		$result = elgg_get_annotations($options);

		elgg_set_ignore_access($old_ia);

		return $result;
	}

	public function approveMembershipRequest($annotation_id){
		$result = false;

		if($annotation = elgg_get_annotation_from_id($annotation_id)){
			if(($annotation->name == "request_membership") && ($annotation->entity_guid == $this->guid)){
				$user_guid = $annotation->owner_guid;

			 	if($this->isUser($user_guid)){
					$result = $annotation->delete();
				} elseif($this->addUser($user_guid)){
					$subject = elgg_echo("subsite_manager:subsite:request_membership:approve:subject");
					$msg = elgg_echo("subsite_manager:subsite:request_membership:approve:message", array($this->name, $this->getURL()));

					notify_user($user_guid, $this->guid, $subject, $msg, null, "email");

					// fail save remove, should happen in $site->addUser()
					$annotation->delete();
					$this->removeMembershipRequests($user_guid);

					// always ok
					$result = true;
				}
			}
		}

		return $result;
	}

	public function declineMembershipRequest($annotation_id, $notify_message = "") {
		$result = false;

		if ($annotation = elgg_get_annotation_from_id($annotation_id)) {
			if (($annotation->name == "request_membership") && ($annotation->entity_guid == $this->guid)) {
				$user_guid = $annotation->owner_guid;

				$subject = elgg_echo("subsite_manager:subsite:request_membership:decline:subject");
				$msg = elgg_echo("subsite_manager:subsite:request_membership:decline:message", array(
					$this->name,
					$notify_message
				));

				notify_user($user_guid, $this->guid, $subject, $msg, null, "email");

				$result = $annotation->delete();

				// cleanup other requests
				$this->removeMembershipRequests($user_guid);
			}
		}

		return $result;
	}

	protected function removeMembershipRequests($user_guid = 0) {
		$user_guid = sanitise_int($user_guid, false);

		if (empty($user_guid)) {
			$user_guid = elgg_get_logged_in_user_guid();
		}

		if (!empty($user_guid)) {
			$annotation_options = array(
				"annotation_name" => "request_membership",
				"guid" => $this->getGUID(),
				"annotation_owner_guid" => $user_guid,
				"limit" => false
			);

			if ($annotations = elgg_get_annotations($annotation_options)) {
				foreach ($annotations as $annotation) {
					$annotation->delete();
				}
			}
		}
	}

	public function validateEmailDomain($user_guid = 0, $email_address = ""){
		$result = false;

		if (($this->getMembership() == self::MEMBERSHIP_DOMAIN) || ($this->getMembership() == self::MEMBERSHIP_DOMAIN_APPROVAL)) {
			if (empty($user_guid)) {
				$user = elgg_get_logged_in_user_entity();
			} else {
				$user = get_user($user_guid);
			}

			$domains = $this->domains;
			$domains = strtolower($domains); // need to be lowercase

			if (!empty($domains)) {
				$domains = explode(",", str_replace(" ", "", $domains));

				if(!is_array($domains)){
					$domains = array($domains);
				}

				if (!empty($user)) {
					// check user's email
					$email = $user->email;
					$email = strtolower($email); // need to be lowercase

					list($dummy, $u_domain) = explode("@", $email);

					if (in_array($u_domain, $domains)) {
						$result = true;
					} else {
						foreach ($domains as $domain) {
							$domain = trim($domain);

							if (substr($domain, 0, 1) == ".") {
								$len = strlen($domain);

								if (substr($u_domain, -($len)) == $domain) {
									$result = true;
									break;
								}
							}
						}
					}
				}

				// check given email address
				if (!$result && !empty($email_address)) {
					if (validate_email_address($email_address)) {
						list($dummy, $u_domain) = explode("@", $email_address);

						if (in_array($u_domain, $domains)) {
							$result = true;
						} else {
							foreach ($domains as $domain) {
								$domain = trim($domain);

								if (substr($domain, 0, 1) == ".") {
									$len = strlen($domain);

									if (substr($u_domain, -$len) == $domain) {
										$result = true;
										break;
									}
								}
							}
						}
					}
				}
			}
		}

		return $result;
	}

	function getMembers($options = array(), $offset = 0){
		if(elgg_extract("count", $options, false) === true){
			$member_count = $this->member_count;
			$bypass = elgg_extract("force_update_member_count", $options, false);
			if(($member_count === NULL) || $bypass){
				$member_count = parent::getMembers($options, $offset);
				$this->member_count = $member_count;
			}
			return $member_count;
		}
		return parent::getMembers($options, $offset);
	}
}

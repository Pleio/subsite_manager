<?php
    function subsite_manager_get_subsite_last_activity($site_guid){
        $result = false;

        $site_guid = (int) $site_guid;

        if(!empty($site_guid)){
            $sql = "SELECT max(time_updated) as activity";
            $sql .= " FROM " . get_config("dbprefix") . "entities";
            $sql .= " WHERE site_guid = " . $site_guid;

            if($data = get_data($sql)){
                $result = $data[0]->activity;
            }
        }

        return $result;
    }

    function subsite_manager_make_superadmin($user_guid = 0){
        $result = false;

        if(empty($user_guid)){
            $user_guid = elgg_get_logged_in_user_guid();
        }

        if(!empty($user_guid) && subsite_manager_is_superadmin_logged_in()){
            if(set_private_setting($user_guid, "superadmin", true)){
                $result = true;
            }
        }

        return $result;
    }

    function subsite_manager_remove_superadmin($user_guid = 0){
        $result = false;

        if(empty($user_guid)){
            $user_guid = elgg_get_logged_in_user_guid();
        }

        if(!empty($user_guid) && subsite_manager_is_superadmin_logged_in()){
            if(remove_private_setting($user_guid, "superadmin")){
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Return whether or not the user is a super admin
     *
     * Needs a had coded query because of deadloop problems with get_private_setting
     *
     * @param int $user_guid
     * @return boolean
     */
    function subsite_manager_is_superadmin($user_guid = 0){
        static $superadmin_cache;

        $result = false;

        $user_guid = sanitise_int($user_guid, false);

        if(empty($user_guid)){
            $user_guid = elgg_get_logged_in_user_guid();
        }

        if(!empty($user_guid)){
            if(!isset($superadmin_cache)){
                $superadmin_cache = array();
            }

            if(!isset($superadmin_cache[$user_guid])){
                $superadmin_cache[$user_guid] = false;

                $query = "SELECT value";
                $query .= " FROM " . get_config("dbprefix") . "private_settings";
                $query .= " WHERE name = 'superadmin'";
                $query .= " AND entity_guid = " . $user_guid;

                if ($setting = get_data_row($query)) {
                    if($setting->value == true){
                        $superadmin_cache[$user_guid] = true;
                    }
                }
            }

            $result = $superadmin_cache[$user_guid];
        }

        return $result;
    }

    function subsite_manager_is_superadmin_logged_in(){
        $result = false;

        if($user_guid = elgg_get_logged_in_user_guid()){
            $result = subsite_manager_is_superadmin($user_guid);
        }

        return $result;
    }

    function subsite_manager_on_subsite(){
        static $result;

        if(!isset($result)){
            $site = elgg_get_site_entity();

            $result = elgg_instanceof($site, "site", Subsite::SUBTYPE, "Subsite");
        }

        return $result;
    }

    function subsite_manager_check_subsite_user(){
        $site = elgg_get_site_entity();

        if (elgg_is_logged_in() &&
            elgg_instanceof($site, "site", Subsite::SUBTYPE, "Subsite") &&
            !$site->isUser() &&
            !subsite_manager_is_superadmin())
        {
            if (!isset($_SESSION['msg'])) {
                $_SESSION['msg'] = array();
            }
            if (!isset($_SESSION['msg']['success'])) {
                $_SESSION['msg']['success'] = array();
            }

            if (elgg_get_page_owner_entity() instanceof ElggGroup) {
                // remove message when viewing group pages
                $_SESSION['msg']['success'] = array_diff($_SESSION['msg']['success'], array(elgg_echo("subsite_manager:subsite:wanttojoin")));
            } else {
                if (!is_array($_SESSION['msg']['success'])) {
                    $_SESSION['msg']['success'] = array();
                }
                // show message for non-members of subsite
                if (!in_array(elgg_echo("subsite_manager:subsite:wanttojoin"), $_SESSION['msg']['success'])) {
                    array_push($_SESSION['msg']['success'], elgg_echo("subsite_manager:subsite:wanttojoin"));
                }
            }
        }
    }

    function subsite_manager_get_user_subsites($user_guid = 0, $refresh = false){
        static $SUBSITES_CACHE;

        $result = false;
        $sites = false;

        $user_guid = sanitise_int($user_guid, false);

        if(empty($user_guid)){
            $user_guid = elgg_get_logged_in_user_guid();
        }

        if(!is_array($SUBSITES_CACHE)){
            $SUBSITES_CACHE = array();
        }

        if(!empty($user_guid)){
            if(!isset($SUBSITES_CACHE[$user_guid]) || $refresh){
                $sites = elgg_get_entities_from_relationship(array(
                            "relationship" => "member_of_site",
                            "relationship_guid" => $user_guid,
                            "inverse_relationship" => FALSE,
                            "types" => "site",
                            "subtypes" => Subsite::SUBTYPE,
                            "site_guids" => false,
                            "joins" => array("JOIN " . get_config("dbprefix") . "sites_entity s ON e.guid=s.guid"),
                            "order_by" => "s.name asc",
                            "limit" => false));

                $SUBSITES_CACHE[$user_guid] = $sites;
            }

            if(isset($SUBSITES_CACHE[$user_guid])){
                $result = $SUBSITES_CACHE[$user_guid];
            }
        }

        return $result;
    }

    function subsite_manager_create_username_from_email($email){
        $result = false;

        if(!empty($email) && is_email_address($email)){
            list($name, $dummy) = explode("@", $email);

            $name = trim($name);

            if(!empty($name)){
                // show hidden entities (unvalidated users)
                $hidden = access_get_show_hidden_status();
                access_show_hidden_entities(true);

                if(get_user_by_username($name)){
                    $i = 1;

                    while(get_user_by_username($name . $i)){
                        $i++;
                    }

                    $result = $name . $i;
                } else {
                    $result = $name;
                }

                // restore hidden entities
                access_show_hidden_entities($hidden);
            }
        }

        return $result;
    }

    function subsite_manager_get_missing_subsite_profile_fields($user_guid = 0){
        global $CONFIG, $SUBSITE_MANAGER_MAIN_PROFILE_FIELDS;

        $result = false;

        if(empty($user_guid)){
            $user_guid = elgg_get_logged_in_user_guid();
        }

        if(subsite_manager_on_subsite() && !empty($user_guid) && ($user = get_user($user_guid)) && elgg_is_active_plugin("profile_manager")){

            if($categorized_fields = profile_manager_get_categorized_fields($user, true, true)){
                $fields = $categorized_fields["fields"];
                $cats = $categorized_fields["categories"];

                if(!empty($fields)){
                    // filter out main site fields
                    foreach($fields as $cat_guid => $cat_fields){
                        if(!empty($cat_fields)){
                            foreach($cat_fields as $index => $field){
                                if($user->get($field->metadata_name)){
                                    // user already filled out this field
                                    unset($fields[$cat_guid][$index]);
                                }
                            }

                            if(empty($fields[$cat_guid])){
                                unset($fields[$cat_guid]);
                            }
                        }
                    }

                    // filter empty categories
                    foreach($cats as $cat_guid => $cat){
                        if(!array_key_exists($cat_guid, $fields)){
                            unset($cats[$cat_guid]);
                        }
                    }

                    if(!empty($fields) && !empty($cats)){
                        $categorized_fields = array(
                            "categories" => $cats,
                            "fields" => $fields
                        );
                    } else {
                        $categorized_fields = false;
                    }
                } else {
                    $categorized_fields = false;
                }

                if(!empty($categorized_fields)){
                    $result = $categorized_fields;
                }
            }
        }

        // restore global to get correct metadata
        profile_manager_get_categorized_fields($user, true);

        return $result;
    }

    function subsite_manager_set_missing_subsite_profile_fields($user_guid = 0){
        $result = false;
        $accesslevel = get_input('accesslevel');

        elgg_make_sticky_form("subsite_missing_profile_fields");

        if(empty($user_guid)){
            $user_guid = elgg_get_logged_in_user_guid();
        }

        if(!empty($user_guid) && ($user = get_user($user_guid))){
            $form_vars = elgg_get_sticky_values("subsite_missing_profile_fields");
            $profile_fields = array();

            // filter the input
            foreach($form_vars as $key => $value){
                if(strpos($key, "custom_profile_fields_") === 0){
                    $key = substr($key, 22);

                    $profile_fields[$key] = $value;
                }
            }

            if(!empty($profile_fields)){
                foreach($profile_fields as $key => $value){
                    remove_metadata($user->getGUID(), $key);

                    if(!empty($value)){

                        if ($accesslevel && array_key_exists($key, $accesslevel)) {
                            $access_id = $accesslevel[$key];
                        } else {
                            $access_id = get_default_access($user);
                        }

                        if(is_array($value)){
                            foreach($value as $index => $v){
                                $multiple = false;
                                if($index > 0){
                                    $multiple = true;
                                }

                                create_metadata($user->getGUID(), $key, $v, "text", $user->getGUID(), $access_id, $multiple);
                            }
                        } else {
                            create_metadata($user->getGUID(), $key, $value, "text", $user->getGUID(), $access_id);
                        }
                    }
                }

                // in javascript we trust ;)
                $result = true;
            } else {
                $result = true;
            }
        }

        return $result;
    }

    function subsite_manager_get_subsites($limit = 10, $offset = 0, $count = false){
        $limit = sanitise_int($limit, false);
        $offset = sanitise_int($offset, false);
        $count = (bool) $count;

        $subsite_options = array(
            "type" => "site",
            "subtype" => Subsite::SUBTYPE,
            "limit" => $limit,
            "offset" => $offset,
            "site_guids" => false,
            "count" => $count
        );

        return elgg_get_entities($subsite_options);
    }

    function subsite_manager_get_required_plugins(){
        return array("subsite_manager", "elgg_modifications");
    }

    function subsite_manager_check_global_plugin_setting($plugin_name, $setting){
        static $global_plugin_settings;

        $result = false;

        if(!empty($plugin_name) && !empty($setting)){
            if(!isset($global_plugin_settings)){
                $global_plugin_settings = array();
            }

            if(!isset($global_plugin_settings[$setting])){
                $global_plugin_settings[$setting] = array();

                $site = elgg_get_site_entity();
                if(subsite_manager_on_subsite()){
                    $site = $site->getOwnerEntity();
                }

                if($configured_plugins = $site->getPrivateSetting($setting)){
                    $global_plugin_settings[$setting] = string_to_tag_array($configured_plugins);
                }
            }

            if(in_array($plugin_name, $global_plugin_settings[$setting])){
                $result = true;
            }
        }

        return $result;
    }

    function subsite_manager_get_global_enabled_plugins(){
        static $result;

        if(!isset($result)){
            $result = subsite_manager_get_required_plugins();

            $site = elgg_get_site_entity();
            if(subsite_manager_on_subsite()){
                $site = $site->getOwnerEntity();
            }

            if($enable_everywhere = $site->getPrivateSetting("enabled_everywhere")){
                $enable_everywhere = string_to_tag_array($enable_everywhere);

                $result = array_merge($result, $enable_everywhere);
            }

            $result = array_values(array_unique($result));
        }

        return $result;
    }

    function subsite_manager_show_plugin(ElggPlugin $plugin){
        $result = false;

        if(!empty($plugin) && elgg_instanceof($plugin, "object", "plugin", "ElggPlugin")){
            // valid plugin, can it be managed
            $enabled_everywhere = subsite_manager_check_global_plugin_setting($plugin->getID(), "enabled_everywhere");
            $subsite_default_manageable = subsite_manager_check_global_plugin_setting($plugin->getID(), "subsite_default_manageable");

            $has_settings = false;
            $settings_view_old = "settings/" . $plugin->getID() . "/edit";
            $settings_view_new = "plugins/" . $plugin->getID() . "/settings";
            if (elgg_view_exists($settings_view_new) || elgg_view_exists($settings_view_old)) {
                $has_settings = true;
            }

            if($subsite_default_manageable){
                $result = true;
            }

            if($result && subsite_manager_on_subsite() && !$has_settings && $enabled_everywhere){
                $result = false;
            }

            if(!$result && subsite_manager_on_subsite() && $has_settings){
                $result = true;
            }
        }

        return $result;
    }

    function subsite_manager_get_active_plugin_relationships($plugin_name){
        $result = false;

        if(!empty($plugin_name)){
            $dbprefix = get_config("dbprefix");

            $plugin_name = sanitise_string($plugin_name);
            $plugin_subtype_id = get_subtype_id("object", "plugin");
            $subsite_subtype_id = get_subtype_id("site", Subsite::SUBTYPE);

            $query = "SELECT r.*";
            $query .= " FROM " . $dbprefix . "entity_relationships r";
            $query .= " JOIN " . $dbprefix . "entities oe ON oe.guid = r.guid_one";
            $query .= " JOIN " . $dbprefix . "entities se ON se.guid = r.guid_two";
            $query .= " JOIN " . $dbprefix . "objects_entity oee ON oe.guid = oee.guid";
            $query .= " WHERE (oe.type = 'object' AND oe.subtype = " . $plugin_subtype_id . ")";
            $query .= " AND (se.type = 'site' AND se.subtype = " . $subsite_subtype_id . ")";
            $query .= " AND oee.title = '" . $plugin_name . "'";
            $query .= " AND r.relationship = 'active_plugin'";

            $result = get_data($query, "row_to_elggrelationship");
        }

        return $result;
    }

    function subsite_manager_subsite_invite_email($email, $message = ""){
        $result = false;

        if(subsite_manager_on_subsite() && !empty($email) && ($user = elgg_get_logged_in_user_entity()) && $user->isAdmin()){
            $site = elgg_get_site_entity();
            $parent_site = $site->getOwnerEntity();

            $registration_link = $site->url . "register";
            if(get_config("disable_registration") === true){
                $registration_link = $parent_site->url . "register";
            }

            // make mail
            $subject = elgg_echo("subsite_manager:subsite_admin:invite:new_user:subject", array($site->name));
            $body = elgg_echo("subsite_manager:subsite_admin:invite:new_user:message", array(
                $user->name,
                $site->name,
                $parent_site->name,
                $message,
                $registration_link
            ));

            // make site email
            if(!empty($site->email)){
                if(!empty($site->name)){
                    $site_from = $site->name . " <" . $site->email . ">";
                } else {
                    $site_from = $site->email;
                }
            } else {
                // no site email, so make one up
                if(!empty($site->name)){
                    $site_from = $site->name . " <noreply@" . get_site_domain($site->getGUID()) . ">";
                } else {
                    $site_from = "noreply@" . get_site_domain($site->getGUID());
                }
            }

            $result = elgg_send_email($site_from, $email, $subject, $body);
        }

        return $result;
    }

    /**
     * Function to create a cache file of which crons to run on this subsite
     *
     */
    function subsite_manager_make_cron_cache(){

        if(subsite_manager_on_subsite()){
            $site = elgg_get_site_entity();
            $file_path = get_config("dataroot") . "subsite_manager/";

            // make correct file structure
            if(!is_dir($file_path)){
                mkdir($file_path);
            }

            $file_path .= $site->getGUID() . "/";

            if(!is_dir($file_path)){
                mkdir($file_path);
            }

            // cache file
            $file_path .= "cron_cache.json";

            if(!file_exists($file_path)){
                $crons = array();

                $hooks = get_config("hooks");

                if(!empty($hooks["cron"])){
                    foreach($hooks["cron"] as $interval => $functions){
                        if(($interval != "all") && !empty($functions)){
                            $crons[] = $interval;
                        }
                    }
                }

                file_put_contents($file_path, json_encode($crons));
            }
        }
    }

    /**
     * Cleanup the cron cache file
     *
     * @param int $site_guid (optional) defaults to current site
     */
    function subsite_manager_remove_cron_cache($site_guid = 0){

        $site_guid = sanitize_int($site_guid, false);

        if(empty($site_guid)){
            $site_guid = elgg_get_site_entity()->getGUID();
        }

        if(($site = elgg_get_site_entity($site_guid)) && elgg_instanceof($site, "site", Subsite::SUBTYPE, "Subsite")){
            $file_path = get_config("dataroot") . "subsite_manager/" . $site->getGUID() . "/cron_cache.json";

            if(file_exists($file_path)){
                unlink($file_path);
            }
        }
    }

    /**
     * Reset the user in memcache
     *
     * @param ElggUser $user
     */
    function subsite_manager_login_shutdown_hook(ElggUser $user){

        if(!empty($user) && elgg_instanceof($user, "user", null, "ElggUser")){
            $user->save();
        }
    }

    /**
    * Return an array reporting the number of various entities in the system.
    *
    * @param int $owner_guid Optional owner of the statistics
    * @return array
    */
    function subsite_manager_get_site_statistics($site_guid = 0) {
        global $CONFIG;

        $site_stats = array();
        $site_guid = (int) $site_guid;

        $dbprefix = get_config("dbprefix");

        $query = "SELECT distinct e.type,s.subtype,e.subtype as subtype_id from {$dbprefix}entities e left join {$dbprefix}entity_subtypes s on e.subtype=s.id";

        $site_query = "";
        if ($site_guid) {
            $query .= " where site_guid=$site_guid";
            $site_query = "and site_guid=$site_guid ";
        }

        // Get a list of major types
        if($types = get_data($query)){
            foreach ($types as $type) {
                // assume there are subtypes for now
                if (!is_array($site_stats[$type->type])) {
                    $site_stats[$type->type] = array();
                }

                $query = "SELECT count(*) as count from {$dbprefix}entities where type='{$type->type}' $site_query";
                if ($type->subtype) {
                    $query.= " and subtype={$type->subtype_id}";
                }

                $subtype_cnt = get_data_row($query);

                if ($type->subtype) {
                    $site_stats[$type->type][$type->subtype] = $subtype_cnt->count;
                } else {
                    $site_stats[$type->type]['__base__'] = $subtype_cnt->count;
                }
            }
        }

        return $site_stats;
    }

    function subsite_manager_get_main_plugin_order(){
        $dbprefix = get_config('dbprefix');
        $priority = elgg_namespace_plugin_private_setting('internal', 'priority');

        $site = elgg_get_site_entity();
        if(elgg_instanceof($site, "site", Subsite::SUBTYPE, "Subsite")){
            $site_guid = $site->getOwnerGUID();
        } else {
            $site_guid = $site->getGUID();
        }

        $options = array(
            "type" => "object",
            "subtype" => "plugin",
            "limit" => false,
            "site_guids" => array($site_guid),
            "joins" => array(
                "JOIN " . $dbprefix . "private_settings ps ON e.guid = ps.entity_guid",
                "JOIN " . $dbprefix . "objects_entity oe ON e.guid = oe.guid"
            ),
            "wheres" => array("(ps.name = '" . $priority . "')"),
            "order_by" => "CAST(ps.value AS unsigned), e.guid",
            "selects" => array("oe.title"),
            "callback" => "subsite_manager_row_to_plugin_name"
        );

        $old_ia = elgg_set_ignore_access(true);
        $result = elgg_get_entities($options);
        elgg_set_ignore_access($old_ia);
        return $result;
    }

    function subsite_manager_row_to_plugin_name($row){
        return $row->title;
    }

    function subsite_manager_get_invited_subsites(ElggUser $user){
        $result = false;

        if(!empty($user) && ($user instanceof Elgguser)){
            // based on email adres
            $options = array(
                "type" => "site",
                "subtype" => Subsite::SUBTYPE,
                "limit" => false,
                "site_guids" => false,
                "joins" => array("JOIN " . elgg_get_config("dbprefix") . "private_settings s ON e.guid = s.entity_guid"),
                "wheres" => array("(s.name = 'membership_invitation' AND s.value LIKE '%" . sanitise_string($user->email) . "%')")
            );

            $subsites_email = elgg_get_entities($options);

            // based on relationship
            $options = array(
                "type" => "site",
                "subtype" => Subsite::SUBTYPE,
                "limit" => false,
                "site_guids" => false,
                "relationship" => "membership_invitation",
                "relationship_guid" => $user->getGUID()
            );

            $subsites_relations = elgg_get_entities_from_relationship($options);

            // make result
            if(!empty($subsites_email) && !empty($subsites_relations)){
                $result = array_merge($subsites_email, $subsites_relations);
            } elseif(!empty($subsites_email)){
                $result = $subsites_email;
            } elseif(!empty($subsites_relations)){
                $result = $subsites_relations;
            }
        }

        return $result;
    }


    function subsite_manager_get_main_profile_fields_configuration($register = false){
        $result = false;

        if(subsite_manager_on_subsite()){
            $subsite = elgg_get_site_entity();

            if($settings = $subsite->getPrivateSetting("main_profile_fields_configuration")){
                $result = json_decode($settings, true);

                // limit fields to those in register field
                if($register){
                    foreach($result as $field_name => $settings){
                        if(elgg_extract("show_on_register", $settings, "no") !== "yes"){
                            unset($result[$field_name]);
                        }
                    }
                }
            }
        }

        return $result;
    }

    function subsite_manager_simplesaml_check_auto_create_account($source, $auth_attributes) {
        $result = false;

        if (!empty($source) && !empty($auth_attributes) && is_array($auth_attributes)) {
            // is the source enabled
            if (!subsite_manager_on_subsite() || simplesaml_is_enabled_source($source)) {
                // check if auto create is enabled for this source
                if (!subsite_manager_on_subsite() || elgg_get_plugin_setting($source . "_auto_create_accounts", "simplesaml")) {
                    // do we have all the require information
                    $email = elgg_extract("elgg:email", $auth_attributes);
                    $firstname = elgg_extract("elgg:firstname", $auth_attributes);
                    $lastname = elgg_extract("elgg:lastname", $auth_attributes);
                    $external_id = elgg_extract("elgg:external_id", $auth_attributes);

                    if (!empty($email) && (!empty($firstname) || !empty($lastname)) && !empty($external_id)) {
                        $result = true;
                    } else {
                        error_log("SAML: fail 5");
                    }
                } else {
                    error_log("SAML: fail 4");
                }
            } else {
                error_log("SAML: fail 3");
            }
        } else {
            error_log("SAML: fail 2");
        }

        return $result;
    }

    /**
     * Move a group to a new (sub)site
     *
     * @param ElggGroup $group       the group to move
     * @param ElggSite  $target_site the target site
     *
     * @return bool
     */
    function subsite_manager_move_group_to_site(ElggGroup $group, ElggSite $target_site) {

        if (!elgg_is_admin_logged_in()) {
            return false;
        }

        if (empty($group) || !elgg_instanceof($group, "group")) {
            return false;
        }

        if (empty($target_site) || !elgg_instanceof($target_site, "site")) {
            return false;
        }

        if ($group->site_guid == $target_site->getGUID()) {
            return false;
        }

        // access conversion rules
        $access = array(
            "group_acl" => (int) $group->group_acl,
            "site_acl" => null
        );

        $site = elgg_get_site_entity($group->site_guid);
        if (!empty($site) && elgg_instanceof($site, "site", Subsite::SUBTYPE)) {
            $access["site_acl"] = (int) $site->getACL();
        }

        // this could take a while
        set_time_limit(0);

        return subsite_manager_move_entity_to_site($group, $target_site, $access);
    }

    /**
     * Move an entity to a new site (can anly be call by subsite_manager_move_group_to_site())
     *
     * @param ElggEntity $entity            the entity to move
     * @param ElggSite   $target_site       the target site
     * @param array      $access_conversion array with group and site access levels
     *
     * @access private
     *
     * @return bool
     */
    function subsite_manager_move_entity_to_site(ElggEntity $entity, ElggSite $target_site, array $access_conversion) {
        static $newentity_cache;

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        if (!isset($backtrace[1])) {
            return false;
        }

        $function = elgg_extract("function", $backtrace[1]);
        if (empty($function) || !in_array($function, array("subsite_manager_move_group_to_site", "subsite_manager_move_entity_to_site"))) {
            // because this is a dangerous function only allow it to be called the correct way
            return false;
        }

        if (empty($entity) || !($entity instanceof ElggEntity)) {
            return false;
        }

        if (empty($target_site) || !elgg_instanceof($target_site, "site")) {
            return false;
        }

        if ($entity->site_guid == $target_site->getGUID()) {
            return false;
        }

        // check for access conversion rules
        if (empty($access_conversion) || !is_array($access_conversion)) {
            return false;
        }

        $group_acl = (int) elgg_extract("group_acl", $access_conversion);
        if (empty($group_acl)) {
            return false;
        }

        $old_site_acl = (int) elgg_extract("site_acl", $access_conversion);
        $affected_access = array(
            ACCESS_PUBLIC,
            ACCESS_LOGGED_IN
        );
        if (!empty($old_site_acl)) {
            $affected_access[] = $old_site_acl;
        }

        // ignore access and show hidden entities
        $ia = elgg_set_ignore_access(true);
        $hidden = access_get_show_hidden_status();
        access_show_hidden_entities(true);

        // first move sub entities (eg blogs in group, event registrations, etc)
        $options = array(
            "type" => "object",
            "container_guid" => $entity->getGUID(),
            "limit" => false
        );
        $batch = new ElggBatch("elgg_get_entities", $options);
        $batch->setIncrementOffset(false);
        foreach ($batch as $sub_entity) {
            if (!subsite_manager_move_entity_to_site($sub_entity, $target_site, $access_conversion)) {
                elgg_set_ignore_access($ia);
                access_show_hidden_entities($hidden);
                return false;
            }
        }

        // also move owned (sub) entities
        $options = array(
            "type" => "object",
            "owner_guid" => $entity->getGUID(),
            "wheres" => array("(e.guid <> {$entity->getGUID()})"),
            "limit" => false
        );
        $batch = new ElggBatch("elgg_get_entities", $options);
        $batch->setIncrementOffset(false);
        foreach ($batch as $sub_entity) {
            if (!subsite_manager_move_entity_to_site($sub_entity, $target_site, $access_conversion)) {
                elgg_set_ignore_access($ia);
                access_show_hidden_entities($hidden);
                return false;
            }
        }

        $dbprefix = elgg_get_config("dbprefix");

        // move access collections
        $query = "UPDATE {$dbprefix}access_collections";
        $query .= " SET site_guid = {$target_site->getGUID()}";
        $query .= " WHERE owner_guid = {$entity->getGUID()}";

        try {
            update_data($query);
        } catch (Exception $e) {
            elgg_log("Subsite manager move entity({$entity->getGUID()}) access collections: " . $e->getMessage(), "ERROR");

            elgg_set_ignore_access($ia);
            access_show_hidden_entities($hidden);
            return false;
        }

        // move annotations
        $query = "UPDATE {$dbprefix}annotations";
        $query .= " SET site_guid = {$target_site->getGUID()}";
        $query .= " WHERE entity_guid = {$entity->getGUID()}";

        try {
            update_data($query);
        } catch (Exception $e) {
            elgg_log("Subsite manager move entity({$entity->getGUID()}) annotations: " . $e->getMessage(), "ERROR");

            elgg_set_ignore_access($ia);
            access_show_hidden_entities($hidden);
            return false;
        }

        // change annotation access
        $query = "UPDATE {$dbprefix}annotations";
        $query .= " SET access_id = {$group_acl}";
        $query .= " WHERE entity_guid = {$entity->getGUID()}";
        $query .= " AND access_id IN (" . implode(", ", $affected_access) . ")";

        try {
            update_data($query);
        } catch (Exception $e) {
            elgg_log("Subsite manager change entity({$entity->getGUID()}) annotation access: " . $e->getMessage(), "ERROR");

            elgg_set_ignore_access($ia);
            access_show_hidden_entities($hidden);
            return false;
        }

        // move river
        $query = "UPDATE {$dbprefix}river";
        $query .= " SET site_guid = {$target_site->getGUID()}";
        $query .= " WHERE subject_guid = {$entity->getGUID()}";
        $query .= " OR object_guid = {$entity->getGUID()}";

        try {
            update_data($query);
        } catch (Exception $e) {
            elgg_log("Subsite manager move entity({$entity->getGUID()}) river: " . $e->getMessage(), "ERROR");

            elgg_set_ignore_access($ia);
            access_show_hidden_entities($hidden);
            return false;
        }

        // change river access
        $query = "UPDATE {$dbprefix}river";
        $query .= " SET access_id = {$group_acl}";
        $query .= " WHERE (subject_guid = {$entity->getGUID()}";
        $query .= " OR object_guid = {$entity->getGUID()})";
        $query .= " AND access_id IN (" . implode(", ", $affected_access) . ")";

        try {
            update_data($query);
        } catch (Exception $e) {
            elgg_log("Subsite manager change entity({$entity->getGUID()}) river access: " . $e->getMessage(), "ERROR");

            elgg_set_ignore_access($ia);
            access_show_hidden_entities($hidden);
            return false;
        }

        // move metadata
        $query = "UPDATE {$dbprefix}metadata";
        $query .= " SET site_guid = {$target_site->getGUID()}";
        $query .= " WHERE entity_guid = {$entity->getGUID()}";

        try {
            update_data($query);
        } catch (Exception $e) {
            elgg_log("Subsite manager move entity({$entity->getGUID()}) metadata: " . $e->getMessage(), "ERROR");

            elgg_set_ignore_access($ia);
            access_show_hidden_entities($hidden);
            return false;
        }

        // change metadata access
        $query = "UPDATE {$dbprefix}metadata";
        $query .= " SET access_id = {$group_acl}";
        $query .= " WHERE entity_guid = {$entity->getGUID()}";
        $query .= " AND access_id IN (" . implode(", ", $affected_access) . ")";

        try {
            update_data($query);
        } catch (Exception $e) {
            elgg_log("Subsite manager change entity({$entity->getGUID()}) metadata access: " . $e->getMessage(), "ERROR");

            elgg_set_ignore_access($ia);
            access_show_hidden_entities($hidden);
            return false;
        }

        // move entity
        $query = "UPDATE {$dbprefix}entities";
        $query .= " SET site_guid = {$target_site->getGUID()}";
        $query .= " WHERE guid = {$entity->getGUID()}";

        try {
            update_data($query);
        } catch (Exception $e) {
            elgg_log("Subsite manager move entity({$entity->getGUID()}) entity: " . $e->getMessage(), "ERROR");

            elgg_set_ignore_access($ia);
            access_show_hidden_entities($hidden);
            return false;
        }

        // change entity access
        $query = "UPDATE {$dbprefix}entities";
        $query .= " SET access_id = {$group_acl}";
        $query .= " WHERE guid = {$entity->getGUID()}";
        $query .= " AND access_id IN (" . implode(", ", $affected_access) . ")";

        try {
            update_data($query);
        } catch (Exception $e) {
            elgg_log("Subsite manager change entity({$entity->getGUID()}) entity access: " . $e->getMessage(), "ERROR");

            elgg_set_ignore_access($ia);
            access_show_hidden_entities($hidden);
            return false;
        }

        // cache cleanup
        _elgg_invalidate_cache_for_entity($entity->getGUID());

        if ((!$newentity_cache) && (is_memcache_available())) {
            $newentity_cache = new ElggMemcache('new_entity_cache');
        }
        if ($newentity_cache) {
            $newentity_cache->delete($entity->getGUID());
        }

        elgg_set_ignore_access($ia);
        access_show_hidden_entities($hidden);

        return true;
    }

    /**
     * Returns an unordered list of plugins for the current site.
     * This is a copy of get_plugins(), but without the ordering part.
     *
     * @return ElggPlugin[]
     */
    function subsite_manager_get_plugins() {

        // grab plugins
        $options = array(
            'type' => 'object',
            'subtype' => 'plugin',
            'limit' => false
        );

        $old_ia = elgg_set_ignore_access(true);
        $plugins = elgg_get_entities($options);
        elgg_set_ignore_access($old_ia);

        return $plugins;
    }

    /**
     * Sync the plugins for a specific subsite
     *
     * @param ElggSite   $subsite
     *
     * @return array ($sorted, $activated)  number of sorted and activated plugins.
     */
    function subsite_manager_sync_plugins(Subsite $subsite) {

        $main_site = elgg_get_config('site');
        $plugins_by_id_map = elgg_get_config('plugins_by_id_map');

        $global_order = subsite_manager_get_main_plugin_order();
        $global_plugins_active = subsite_manager_get_global_enabled_plugins();

        // pretend to be on the subsite
        elgg_set_config('site', $subsite);
        elgg_set_config('site_guid', $subsite->guid);
        elgg_set_config('plugins_by_id_map', array());

        // make sure all plugins exist in the database
        elgg_generate_plugin_entities();

        // mark plugins to activate
        $private_setting = $subsite->getPrivateSetting('subsite_manager_plugins_activate');
        if (isset($private_setting)) {
            $to_activate = unserialize($private_setting);
        } else {
            $to_activate = array();
        }

        $sorted = 0;
        $activated = 0;

        // reorder plugins according to global order and activate global plugins
        $plugins = subsite_manager_get_plugins();

        foreach ($plugins as $plugin) {
            $priority = array_search($plugin->getID(), $global_order)+1;
            if ($priority === false) {
                $priority = 'last';
            }

            $priority_name = elgg_namespace_plugin_private_setting('internal', 'priority');
            if ($plugin->get($priority_name) != $priority) {
                $plugin->set($priority_name, $priority);
               $sorted++;
            }

            if (!$plugin->isActive() && in_array($plugin->getID(), $global_plugins_active)) {
                if (!in_array($plugin->getID(), $to_activate)) {
                    $to_activate[] = $plugin->getID();
                    $activated++;
                }
            }
        }

        $subsite->setPrivateSetting('subsite_manager_plugins_activate', serialize($to_activate));

        // clean the caches
        elgg_invalidate_simplecache();
        elgg_reset_system_cache();

        // restore original "main" site
        elgg_set_config('site', $main_site);
        elgg_set_config('site_guid', $main_site->guid);
        elgg_set_config('plugins_by_id_map', $plugins_by_id_map);

        return array($sorted, $activated);
    }

    function subsite_manager_get_plugin_order() {
        if (is_memcache_available()) {
            $memcache = new ElggMemcache('subsite_manager');
        }

        if (isset($memcache)) {
            if ($plugin_order = $memcache->load('plugin_order')) {
                return $plugin_order;
            }
        }

        $db_prefix = get_config('dbprefix');
        $priority = elgg_namespace_plugin_private_setting('internal', 'priority');

        $options = array(
            'type' => 'object',
            'subtype' => 'plugin',
            'limit' => ELGG_ENTITIES_NO_VALUE,
            'selects' => array('plugin_oe.*'),
            'joins' => array(
                "JOIN {$db_prefix}private_settings ps on ps.entity_guid = e.guid",
                "JOIN {$db_prefix}objects_entity plugin_oe on plugin_oe.guid = e.guid"
                ),
            'wheres' => array("ps.name = '$priority'"),
            'order_by' => "CAST(ps.value as unsigned), e.guid",
            'site_guids' => array(1)
        );

        $plugins = elgg_get_entities_from_relationship($options);

        $plugin_order = array();
        foreach ($plugins as $i => $plugin) {
            $plugin_order[$plugin->title] = $i;
        }

        if (isset($memcache)) {
            $memcache->save('plugin_order', $plugin_order);
        }

        return $plugin_order;
    }
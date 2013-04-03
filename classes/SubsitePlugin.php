<?php 

	class SubsitePlugin extends ElggPlugin {
		protected $fallback_plugin;
		
		// Plugin settings
		
		/**
		 * Returns an array of all settings saved for this plugin.
		 *
		 * @note Unlike user settings, plugin settings are not namespaced.
		 *
		 * @return array An array of key/value pairs.
		 */
		public function getAllSettings() {
			if (!$this->guid) {
				return false;
			}
		
			$db_prefix = elgg_get_config('dbprefix');
			// need to remove all namespaced private settings.
			$us_prefix = elgg_namespace_plugin_private_setting('user_setting', '', $this->getID());
			$is_prefix = elgg_namespace_plugin_private_setting('internal', '', $this->getID());
		
			// Get private settings for user
			$q = "SELECT * FROM {$db_prefix}private_settings
					WHERE entity_guid = $this->guid
					AND name NOT LIKE '$us_prefix%'
					AND name NOT LIKE '$is_prefix%'";
		
			$private_settings = get_data($q);
		
			if ($private_settings) {
				$return = array();
		
				foreach ($private_settings as $setting) {
					$name = substr($setting->name, $ps_prefix_len);
					$value = $setting->value;
		
					$return[$name] = $value;
				}
		
				return $return;
			}
		
			return false;
		}
		
		/**
		* Removes all settings for this plugin.
		*
		* @todo Should be a better way to do this without dropping to raw SQL.
		* @todo If we could namespace the plugin settings this would be cleaner.
		* @return bool
		*/
		public function unsetAllSettings() {
			$db_prefix = get_config('dbprefix');
			$ps_prefix = elgg_namespace_plugin_private_setting('internal', '');
	
			$q = "DELETE FROM {$db_prefix}private_settings
				WHERE entity_guid = $this->guid
				AND name NOT LIKE '$ps_prefix%'
				AND name <> 'path'";
	
			$result = delete_data($q);
		
			// check memcache
			if(($result !== false) && is_memcache_available()){
				$private_setting_cache = new ElggMemcache("private_settings");
				
				// remove settings from memcache
				$private_setting_cache->delete($this->guid);
			}
			
			return $result;
		}
		
		// User settings
		
		/**
		 * Returns a user's setting for this plugin
		 *
		 * @param string $name      The setting name
		 * @param int    $user_guid The user GUID
		 *
		 * @return mixed The setting string value or false
		 */
		public function getUserSetting($name, $user_guid = null) {
			$result = false;
			$user_guid = (int) $user_guid;
		
			if ($user_guid) {
				$user = get_entity($user_guid);
			} else {
				$user = elgg_get_logged_in_user_entity();
			}
		
			if (!($user instanceof ElggUser)) {
				return false;
			}
		
			$name = elgg_namespace_plugin_private_setting('user_setting', $name, $this->getID());
			
			// Subsite adjustment
			$site = elgg_get_site_entity();
			$org_name = $name;
			
			if(!subsite_manager_check_global_plugin_setting($this->getID(), "use_global_usersettings")){
				$name = str_replace(ELGG_PLUGIN_USER_SETTING_PREFIX . $this->getID() . ":", ELGG_PLUGIN_USER_SETTING_PREFIX . $this->getID() . ":" . $site->getGUID() . ":", $name);
			}
			
			$tmp_result = get_private_setting($user->getGUID(), $name);
				
			if($tmp_result === false){
				// fallback
				if($name == $org_name){
					$name = str_replace(ELGG_PLUGIN_USER_SETTING_PREFIX . $this->getID() . ":", ELGG_PLUGIN_USER_SETTING_PREFIX . $this->getID() . ":" . $site->getGUID() . ":", $name);
				} else {
					$name = $org_name;
				}
			
				$result = get_private_setting($user->getGUID(), $name);
			} else {
				$result = $tmp_result;
			}
			
			return $result;
		}
		
		/**
		 * Returns an array of all user settings saved for this plugin for the user.
		 *
		 * @note Plugin settings are saved with a prefix. This removes that prefix.
		 *
		 * @param int $user_guid The user GUID. Defaults to logged in.
		 * @return array An array of key/value pairs.
		 */
		public function getAllUserSettings($user_guid = null) {
			$user_guid = (int)$user_guid;
		
			if ($user_guid) {
				$user = get_entity($user_guid);
			} else {
				$user = elgg_get_logged_in_user_entity();
			}
		
			if (!($user instanceof ElggUser)) {
				return false;
			}
		
			$db_prefix = elgg_get_config('dbprefix');
			// send an empty name so we just get the first part of the namespace
			$ps_prefix = elgg_namespace_plugin_private_setting('user_setting', '', $this->getID());
			$ps_prefix_len = strlen($ps_prefix);
		
			// Get private settings for user
			$q = "SELECT * FROM {$db_prefix}private_settings
					WHERE entity_guid = {$user->guid}
					AND name LIKE '$ps_prefix%'";
		
			$private_settings = get_data($q);
		
			if ($private_settings) {
				$return = array();
		
				// Subsite adjustment
				$site = elgg_get_site_entity();
				$global_prefix = $ps_prefix . $site->getGUID() . ":";
				
				foreach ($private_settings as $setting) {
					$name = $setting->name;
					$value = $setting->value;
		
					// local setting before global settings
					if(stristr($name, $global_prefix)){
						$name = str_replace($global_prefix, "", $name);
					} elseif(stristr($name, $ps_prefix)){
						// fallback
						$name = str_replace($ps_prefix, "", $name);
					}
					
					$return[$name] = $value;
				}
		
				return $return;
			}
		
			return false;
		}
		
		/**
		 * Sets a user setting for a plugin
		 *
		 * @param string $name      The setting name
		 * @param string $value     The setting value
		 * @param int    $user_guid The user GUID
		 *
		 * @return mixed The new setting ID or false
		 */
		public function setUserSetting($name, $value, $user_guid = null) {
			$user_guid = (int) $user_guid;
		
			if ($user_guid) {
				$user = get_entity($user_guid);
			} else {
				$user = elgg_get_logged_in_user_entity();
			}
		
			if (!($user instanceof ElggUser)) {
				return false;
			}
		
			// Hook to validate setting
			// note: this doesn't pass the namespaced name
			$value = elgg_trigger_plugin_hook('usersetting', 'plugin', array(
					'user' => $user,
					'plugin' => $this,
					'plugin_id' => $this->getID(),
					'name' => $name,
					'value' => $value
			), $value);
		
			// set the namespaced name.
			$name = elgg_namespace_plugin_private_setting('user_setting', $name, $this->getID());
			
			// Subsite adjustment
			$site = elgg_get_site_entity();
			
			if(!subsite_manager_check_global_plugin_setting($this->getID(), "use_global_usersettings")){
				$name = str_replace(ELGG_PLUGIN_USER_SETTING_PREFIX . $this->getID() . ":", ELGG_PLUGIN_USER_SETTING_PREFIX . $this->getID() . ":" . $site->getGUID() . ":", $name);
			}
			
			return set_private_setting($user->getGUID(), $name, $value);
		}
		
		
		/**
		 * Removes a user setting name and value.
		 *
		 * @param string $name      The user setting name
		 * @param int    $user_guid The user GUID
		 * @return bool
		 */
		public function unsetUserSetting($name, $user_guid = null) {
			$user_guid = (int)$user_guid;
		
			if ($user_guid) {
				$user = get_entity($user_guid);
			} else {
				$user = elgg_get_logged_in_user_entity();
			}
		
			if (!($user instanceof ElggUser)) {
				return false;
			}
		
			// set the namespaced name.
			$name = elgg_namespace_plugin_private_setting('user_setting', $name, $this->getID());
			
			// Subsite adjustment
			$site = elgg_get_site_entity();
			$alt_name = str_replace(ELGG_PLUGIN_USER_SETTING_PREFIX . $this->getID() . ":", ELGG_PLUGIN_USER_SETTING_PREFIX . $this->getID() . ":" . $site->getGUID() . ":", $name);
			
			$main_res = remove_private_setting($user->getGUID(), $name);
			$alt_res = remove_private_setting($user->getGUID(), $alt_name);
			
			return $main_res || $alt_res;
		}
		
		// generic helpers and overrides
		
		/**
		 * Get a value from private settings.
		 *
		 * @param string $name Name
		 *
		 * @return mixed
		 */
		public function get($name) {
			// rewrite for old and inaccurate plugin:setting
			if (strstr($name, 'plugin:setting:')) {
				$msg = 'Direct access of user settings is deprecated. Use ElggPlugin->getUserSetting()';
				elgg_deprecated_notice($msg, 1.8);
				$name = str_replace('plugin:setting:', '', $name);
				$name = elgg_namespace_plugin_private_setting('user_setting', $name);
			}
		
			// See if its in our base attribute
			if (array_key_exists($name, $this->attributes)) {
				return $this->attributes[$name];
			}
		
			// we can always access plugins, so don't check access
			$ia = elgg_set_ignore_access(true);
			
			// No, so see if its in the private data store.
			// get_private_setting() returns false if it doesn't exist
			$result = $this->getPrivateSetting($name);
		
			if ($result === false) {
				// Can't find it, so return null
				$result = NULL;
				
				// Subsite adjustment
				if($fallback_plugin = $this->getFallbackPlugin()){
					$result = $fallback_plugin->$name;
				}
			}
		
			// restore access settings
			elgg_set_ignore_access($ia);
			
			return $result;
		}
		
		/**
		* Sets the priority of the plugin
		*
		* @param mixed $priority  The priority to set. One of +1, -1, first, last, or a number.
		*                         If given a number, this will displace all plugins at that number
		*                         and set their priorities +1
		* @param mixed $site_guid Optional site GUID.
		* @return bool
		*/
		public function setPriority($priority, $site_guid = null) {
			if (!$this->guid) {
				return false;
			}
		
			$db_prefix = get_config('dbprefix');
			$name = elgg_namespace_plugin_private_setting('internal', 'priority');
			$plugin_subtype_id = get_subtype_id("object", "plugin");
			
			// if no priority assume a priority of 1
			$old_priority = (int) $this->getPriority();
			$old_priority = (!$old_priority) ? 1 : $old_priority;
			$max_priority = elgg_get_max_plugin_priority();
		
			// can't use switch here because it's not strict and
			// php evaluates +1 == 1
			if ($priority === '+1') {
				$priority = $old_priority + 1;
			} elseif ($priority === '-1') {
				$priority = $old_priority - 1;
			} elseif ($priority === 'first') {
				$priority = 1;
			} elseif ($priority === 'last') {
				$priority = $max_priority;
			}
		
			// should be a number by now
			if ($priority > 0) {
				if (!is_numeric($priority)) {
					return false;
				}
		
				// there's nothing above the max.
				if ($priority > $max_priority) {
					$priority = $max_priority;
				}
		
				// there's nothing below 1.
				if ($priority < 1) {
					$priority = 1;
				}
		
				if ($priority > $old_priority) {
					$op = '-';
					$where = "CAST(value as unsigned) BETWEEN {$old_priority} AND {$priority}";
				} else {
					$op = '+';
					$where = "CAST(value as unsigned) BETWEEN {$priority} AND {$old_priority}";
				}
		
				// displace the ones affected by this change
				$q = "UPDATE {$db_prefix}private_settings";
				$q .= " SET value = CAST(value as unsigned) {$op} 1";
				$q .= " WHERE entity_guid != {$this->guid}";
				$q .= " AND name = '{$name}'";
				$q .= " AND entity_guid IN (";
					$q .= " SELECT guid";
					$q .= " FROM {$db_prefix}entities";
					$q .= " WHERE (type = 'object' AND subtype = {$plugin_subtype_id})";
					$q .= " AND site_guid = {$this->site_guid}";
				$q .= ")";
				$q .= " AND {$where}";
		
				if (!update_data($q)) {
					return false;
				}
		
				// set this priority
				if ($this->set($name, $priority)) {
					return true;
				} else {
					return false;
				}
			}
		
			return false;
		}
		
		protected function getFallbackPlugin(){
			
			if(!isset($this->fallback_plugin)){
				$this->fallback_plugin = false;
				
				if(($site = elgg_get_site_entity($this->site_guid)) && elgg_instanceof($site, "site", Subsite::SUBTYPE, "Subsite")){
					if(subsite_manager_check_global_plugin_setting($this->getID(), "fallback_to_main_settings")){
						// ignore access for this part, as plugins are public
						$old_ia = elgg_set_ignore_access(true);
						
						// do we already know the fallback plugin
						$fallback_plugin_guid = (int) parent::getPrivateSetting("fallback_plugin_guid");
						
						if(!empty($fallback_plugin_guid)){
							if($temp_plugin = get_entity($fallback_plugin_guid)){
								if(elgg_instanceof($temp_plugin, "object", "plugin")){
									$this->fallback_plugin = $temp_plugin;
								}
							}
							
							// something is wrong with the guid, cleanup
							if(empty($this->fallback_plugin)){
								parent::removePrivateSetting("fallback_plugin_guid");
							}
						}
						
						// we haven't found a falback plugin yet (or it is invalid)
						if(empty($this->fallback_plugin)){
							$options = array(
								"type" => "object",
								"subtype" => "plugin",
								"limit" => 1,
								"site_guids" => array($site->getOwnerGUID()),
								"joins" => array("JOIN " . elgg_get_config("dbprefix") . "objects_entity oe ON e.guid = oe.guid"),
								"wheres" => array("(oe.title = '" . $this->getID() . "')")
							);
							
							if($plugins = elgg_get_entities($options)){
								$temp_plugin = $plugins[0];
								$this->fallback_plugin = $temp_plugin;
								parent::setPrivateSetting("fallback_plugin_guid", $temp_plugin->getGUID());
							} else {
								// we should have found a main plugin, but didn't log this
								elgg_log("Subsite plugin(" . $this->getID() . ") with fallback didnt find main plugin", "ERROR");
							}
						}
						
						// restore access settings
						elgg_set_ignore_access($old_ia);
					}
				}
			}
			
			return $this->fallback_plugin;
		}
		
		/**
		* Includes one of the plugins files
		*
		* @param string $filename The name of the file
		*
		* @throws PluginException
		* @return mixed The return value of the included file (or 1 if there is none)
		*/
		protected function includeFile($filename) {
			// This needs to be here to be backwards compatible for 1.0-1.7.
			// They expect the global config object to be available in start.php.
			if ($filename == 'start.php') {
				global $CONFIG;
			}
		
			$filepath = $this->getPath() . "/$filename";
		
			if (!$this->canReadFile($filename)) {
				$msg = elgg_echo('ElggPlugin:Exception:CannotIncludeFile',
				array($filename, $this->getID(), $this->guid, $this->getPath()));
				throw new PluginException($msg);
			}
		
			return include_once($filepath);
		}
	}
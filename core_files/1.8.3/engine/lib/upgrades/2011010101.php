<?php
/**
 * Migrate plugins to the new system using ElggPlugin and private settings
 */

$old_ia = elgg_set_ignore_access(true);

$site = get_config('site');
$old_plugin_order = unserialize($site->pluginorder);
$old_enabled_plugins = $site->enabled_plugins;

$db_prefix = get_config('dbprefix');
$plugin_subtype_id = get_subtype_id('object', 'plugin');

// easy one first: make sure the the site owns all plugin entities.
$q = "UPDATE {$db_prefix}entities e
	SET owner_guid = site_guid, container_guid = site_guid
	WHERE e.type = 'object' AND e.subtype = $plugin_subtype_id";

$r = update_data($q);

// rewrite all plugin:setting:* to ELGG_PLUGIN_USER_SETTING_PREFIX . *
$q = "UPDATE {$db_prefix}private_settings
	SET name = replace(name, 'plugin:settings:', '" . ELGG_PLUGIN_USER_SETTING_PREFIX . "')
	WHERE name LIKE 'plugin:settings:%'";

$r = update_data($q);

// grab current plugin GUIDs to add a temp priority
$q = "SELECT * FROM {$db_prefix}entities e
	JOIN {$db_prefix}objects_entity oe ON e.guid = oe.guid
	WHERE e.type = 'object' AND e.subtype = $plugin_subtype_id";

$plugins = get_data($q);

foreach ($plugins as $plugin) {
	$priority = elgg_namespace_plugin_private_setting('internal', 'priority');
	set_private_setting($plugin->guid, $priority, 0);
}

// force regenerating plugin entities
elgg_generate_plugin_entities();

// set the priorities for all plugins
// this function rewrites it to a normal index so use the current one.
elgg_set_plugin_priorities($old_plugin_order);

// add relationships for enabled plugins
if ($old_enabled_plugins) {
	// they might only have one plugin enabled.
	if (!is_array($old_enabled_plugins)) {
		$old_enabled_plugins = array($old_enabled_plugins);
	}

	// sometimes there were problems and you'd get 1000s of enabled plugins.
	$old_enabled_plugins = array_unique($old_enabled_plugins);

	foreach ($old_enabled_plugins as $plugin_id) {
		$plugin = elgg_get_plugin_from_id($plugin_id);

		if ($plugin) {
			$plugin->activate();
		}
	}
}

// invalidate caches
elgg_invalidate_simplecache();
elgg_filepath_cache_reset();

// clean up.
remove_metadata($site->guid, 'pluginorder');
remove_metadata($site->guid, 'enabled_plugins');

// do subsite plugin upgrade
$site_options = array(
	"type" => "site",
	"subtype" => "subsite",
	"limit" => false,
	"site_guids" => false
);

if($subsites = elgg_get_entities($site_options)){
	set_time_limit(0);
	
	$old_site_guid = elgg_get_config("site_guid");
	
	$dir = elgg_get_plugins_path();
	$physical_plugins = elgg_get_plugin_ids_in_dir($dir);
	
	$hidden = access_get_show_hidden_status();
	access_show_hidden_entities(true);
	
	$plugin_options = array(
		'type' => 'object',
		'subtype' => 'plugin',
		'limit' => false,
	);
	
	$enabled_plugin_options = array(
		"metadata_name" => "enabled_plugins",
		"site_guids" => false,
		"limit" => false
	);
	
	$subsites_done = 0;
	
	foreach($subsites as $subsite){
		if(!datalist_get("plugins_done_" . $subsite->getGUID())){
			elgg_set_config("site", $subsite);
			elgg_set_config("site_guid", $subsite->getGUID());
			
			// get known plugins
			$plugin_options["site_guids"] = array($subsite->getGUID());
			
			$known_plugins = elgg_get_entities($plugin_options);
			
			if (!$known_plugins) {
				$known_plugins = array();
			}
			
			// map paths to indexes
			$id_map = array();
			foreach ($known_plugins as $i => $plugin) {
				// if the ID is wrong, delete the plugin because we can never load it.
				$id = $plugin->getID();
				if (!$id) {
					$plugin->delete();
					unset($known_plugins[$i]);
					continue;
				}
				$id_map[$plugin->getID()] = $i;
			}
			
			if (!$physical_plugins) {
				break;
			}
			
			// check real plugins against known ones
			foreach ($physical_plugins as $plugin_id) {
				// is this already in the db?
				if (array_key_exists($plugin_id, $id_map)) {
					$index = $id_map[$plugin_id];
					$plugin = $known_plugins[$index];
					// was this plugin deleted and its entity disabled?
					if ($plugin->enabled != 'yes') {
						$plugin->enable();
						$plugin->deactivate();
						$plugin->setPriority('last');
					}
			
					// remove from the list of plugins to disable
					unset($known_plugins[$index]);
				} else {
					// add new plugins
					// priority is force to last in save() if not set.
					$plugin = new ElggPlugin($plugin_id);
					$plugin->save();
				}
			}
			
			// everything remaining in $known_plugins needs to be disabled
			// because they are entities, but their dirs were removed.
			// don't delete the entities because they hold settings.
			foreach ($known_plugins as $plugin) {
				if ($plugin->isActive()) {
					$plugin->deactivate();
				}
				// remove the priority.
				$name = elgg_namespace_plugin_private_setting('internal', 'priority');
				remove_private_setting($plugin->guid, $name);
				$plugin->disable();
			}
			
			// get old enabled plugins
			$enabled_plugin_options["guids"] = array($subsite->getGUID());
			
			$old_enabled_plugins = elgg_get_metadata($enabled_plugin_options);
			
			if(!empty($old_enabled_plugins)){
				$old_enabled_plugins = metadata_array_to_values($old_enabled_plugins);
				$old_enabled_plugins = array_unique($old_enabled_plugins);
				
				foreach($old_enabled_plugins as $plugin_id){
					if($plugin = elgg_get_plugin_from_id($plugin_id)){
						if(!check_entity_relationship($plugin->getGUID(), 'active_plugin', $subsite->getGUID())){
							add_entity_relationship($plugin->getGUID(), 'active_plugin', $subsite->getGUID());
						}
					}
				}
			}
			
			// remove old metadata
			remove_metadata($subsite->getGUID(), 'pluginorder');
			remove_metadata($subsite->getGUID(), 'enabled_plugins');
			
			elgg_set_config("site", $site);
			elgg_set_config("site_guid", $old_site_guid);
			
			datalist_set("plugins_done_" . $subsite->getGUID(), true);
			
			if($subsites_done == 10){
				forward("upgrade.php");
			}
			$subsites_done++;
		}
	}
	
	// cleanup datalist
	$query = "DELETE FROM " . elgg_get_config("dbprefix") . "datalists";
	$query .= " WHERE name LIKE 'plugins_done_%'";
	
	delete_data($query);
	
	access_show_hidden_entities($hidden);
}

elgg_set_ignore_access($old_id);

/**
 * @hack
 *
 * We stop the upgrade at this point because plugins weren't given the chance to
 * load due to the new plugin code introduced with Elgg 1.8. Instead, we manually
 * set the version and start the upgrade process again.
 *
 * The variables from upgrade_code() are available because this script was included
 */
if ($upgrade_version > $version) {
	datalist_set('version', $upgrade_version);
}

// add ourselves to the processed_upgrades.
$processed_upgrades[] = '2011010101.php';

$processed_upgrades = array_unique($processed_upgrades);
elgg_set_processed_upgrades($processed_upgrades);

forward('upgrade.php');

<?php 

$installed_plugins = elgg_extract("installed_plugins", $vars);

// Get a list of the all categories
// and trim down the plugin list if we're not viewing all categories.
// @todo this could be cached somewhere after have the manifest loaded
$plugin_list = array();

foreach ($installed_plugins as $id => $plugin) {
	if (!$plugin->isValid()) {
		if ($plugin->isActive()) {
			// force disable and warn
			elgg_add_admin_notice('invalid_and_deactivated_' . $plugin->getID(),
			elgg_echo('ElggPlugin:InvalidAndDeactivated', array($plugin->getId())));
			$plugin->deactivate();
		}
		continue;
	}
	
	// valid plugin, can it be managed
	if(subsite_manager_show_plugin($plugin)){
		$plugin_list[] = $plugin;
	}
}

if(subsite_manager_is_superadmin_logged_in()){
	$url = "<br />" . elgg_view("output/url", array("href" => "admin/plugins?advanced=yes", "text" => elgg_echo("subsite_manager:plugins:simple:switch")));
}
echo elgg_view_module("info", "", elgg_echo("subsite_manager:plugins:description") . $url);

?>
<div id="elgg-plugin-list">
	<?php
	
		$options = array(
			'limit' => 0,
			'full_view' => true,
			'list_type_toggle' => false,
			'pagination' => false,
		);
		
		echo elgg_view_entity_list($plugin_list, $options);
	
	?>
</div>
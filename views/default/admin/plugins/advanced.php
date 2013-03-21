<?php
/**
 * Elgg administration plugin screen
 *
 * Shows a list of plugins that can be sorted and filtered.
 *
 * @package Elgg.Core
 * @subpackage Admin.Plugins
 */

$plugin_list = elgg_extract("installed_plugins", $vars);

// Get a list of the all categories
// and trim down the plugin list if we're not viewing all categories.
// @todo this could be cached somewhere after have the manifest loaded

// foreach ($installed_plugins as $id => $plugin) {
// 	if (!$plugin->isValid()) {
// 		if ($plugin->isActive()) {
// 			// force disable and warn
// 			elgg_add_admin_notice('invalid_and_deactivated_' . $plugin->getID(),
// 					elgg_echo('ElggPlugin:InvalidAndDeactivated', array($plugin->getId())));
// 			$plugin->deactivate();
// 		}
// 		continue;
// 	}
// }

// construct page header
$url = elgg_view("output/url", array("href" => "admin/plugins", "text" => elgg_echo("subsite_manager:plugins:advanced:switch")));
echo elgg_view_module("info", "", elgg_echo("subsite_manager:plugins:description") . "<br />" . $url);

?>
<div id="elgg-plugin-list">
	<?php
	
		$options = array(
			'limit' => 0,
			'full_view' => true,
			'list_type_toggle' => false,
			'pagination' => false,
			'display_reordering' => !subsite_manager_on_subsite()
		);
		
		echo elgg_view_entity_list($plugin_list, $options);
	
	?>
</div>
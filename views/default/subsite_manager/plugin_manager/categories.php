<?php

	/**
	 * Change the plugin categories for Plugin Manager
	 * because some plugin will not always be displayed
	 *
	 */

	// restrict the displayed plugin for subsite admins (on subsites)
	if (subsite_manager_on_subsite() && !subsite_manager_is_superadmin_logged_in()) {
		$plugins = elgg_get_plugins("any");
		
		$categories = array();
		
		foreach ($plugins as $id => $plugin) {
			$show_plugin = subsite_manager_show_plugin($plugin);
			
			if ($show_plugin) {
				$plugin_categories = $plugin->getManifest()->getCategories();
				
				if (isset($plugin_categories)) {
					foreach ($plugin_categories as $category) {
						if (!array_key_exists($category, $categories)) {
							$categories[$category] = ElggPluginManifest::getFriendlyCategory($category);
						}
					}
				}
			}
		}
		
		asort($categories);
		
		// we want bundled/nonbundled pulled to be at the top of the list
		unset($categories['bundled']);
		unset($categories['nonbundled']);
		
		$common_categories = array(
			'all' => elgg_echo('admin:plugins:category:all'),
			'active' => elgg_echo('admin:plugins:category:active'),
			'inactive' => elgg_echo('admin:plugins:category:inactive'),
			'bundled' => elgg_echo('admin:plugins:category:bundled'),
			'nonbundled' => elgg_echo('admin:plugins:category:nonbundled'),
		);
		
		$categories = array_merge($common_categories, $categories);
		
		$vars["category_options"] = $categories;
	}
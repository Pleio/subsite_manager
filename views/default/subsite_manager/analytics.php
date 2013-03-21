<?php 

	// load google analytics

	// track subsite usage with main site settings
	$load_js = true;
	$gaq = "_gaq";
	if(elgg_is_active_plugin("analytics") && ($main_trackID = elgg_get_plugin_setting("analyticsSiteID", "analytics"))){
		$load_js = false;
		$gaq = "_gaq2";
	}

	// ignore access
	$ignore_access = elgg_get_ignore_access();
	elgg_set_ignore_access(true);
	
	$main_site = elgg_get_site_entity()->getOwnerEntity();
	
	$plugin_options = array(
		"type" => "object",
		"subtype" => "plugin",
		"site_guid" => $main_site->getGUID(),
		"relationship" => "active_plugin",
		"relationship_guid" => $main_site->getGUID(),
		"inverse_relationship" => true,
		"limit" => 1,
		"joins" => array("JOIN " . get_config("dbprefix") . "objects_entity oe ON e.guid = oe.guid"),
		"wheres" => array("oe.title = 'analytics'")
	);
	
	$old_ia = elgg_set_ignore_access(true);
	$plugins = elgg_get_entities_from_relationship($plugin_options);
	elgg_set_ignore_access($old_ia);
	
	if($plugins){
		$plugin = $plugins[0];
		
		$trackID = $plugin->analyticsSiteID;
		$domain = $plugin->analyticsDomain;

		if(!empty($trackID) && ($trackID != $main_trackID)){
?>
<script type="text/javascript">

	<?php 
		echo "var " . $gaq . " = _gaq || [];\n";
		echo $gaq . ".push(['_setAccount', '" . $trackID . "']);\n";
		if(!empty($domain)) {
			echo $gaq . ".push(['_setDomainName', '" . $domain . "']);\n";
		}
		
		echo $gaq . ".push(['_setSiteSpeedSampleRate', 20]);\n";
		echo $gaq . ".push(['_trackPageview']);\n";
		
		if($load_js){ 
	?>
	(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();
	<?php } ?>

</script>
<?php
		}
	}
	
	
	
	// load piwik analytics
	
	
	
	
	$plugin_options = array(
			"type" => "object",
			"subtype" => "plugin",
			"site_guid" => $main_site->getGUID(),
			"relationship" => "active_plugin",
			"relationship_guid" => $main_site->getGUID(),
			"inverse_relationship" => true,
			"limit" => 1,
			"joins" => array("JOIN " . get_config("dbprefix") . "objects_entity oe ON e.guid = oe.guid"),
			"wheres" => array("oe.title = 'phloor_analytics_piwik'")
	);
	
	$old_ia = elgg_set_ignore_access(true);
	$plugins = elgg_get_entities_from_relationship($plugin_options);
	elgg_set_ignore_access($old_ia);
	
	if($plugins){
		
		if(isset($main_site->phloor_analytics_piwik_settings)) {
			$params = json_decode($main_site->phloor_analytics_piwik_settings, true);
		}
		
		$js_url = $params['path_to_piwik'] . "piwik.js";
		elgg_register_js('phloor-piwik-lib', $js_url, 'footer', 500);
		elgg_load_js('phloor-piwik-lib');
		
		?>
			<!-- Piwik --> 
			<script type="text/javascript">
			$(document).ready(function() {
				try {
					var piwikTracker_global = Piwik.getTracker("<?php echo $params['path_to_piwik']; ?>piwik.php", <?php echo $params['site_guid']; ?>);
					piwikTracker_global.trackPageView();
					piwikTracker_global.enableLinkTracking();
				} catch( err ) {}
			});
			</script>
			<!-- End Piwik Tracking Code -->
		<?php 
	}	
	
	// restore access
	elgg_set_ignore_access($ignore_access);
	
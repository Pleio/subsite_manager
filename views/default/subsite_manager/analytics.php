<?php

	// load google analytics

	// track subsite usage with main site settings
	$load_js = true;
	$gaq = "_gaq";
	if(elgg_is_active_plugin("analytics") && ($main_trackID = elgg_get_plugin_setting("analyticsSiteID", "analytics"))){
		$load_js = false;
		$gaq = "_gaq2";
	}

	// get the main site plugin for settings
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
<!-- Google Analytics from Pleio -->
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
<!-- End Google Analytics -->
<?php
		}
		
		// Piwik tracking
		$piwik_url = $plugin->piwik_url;
		$piwik_site_id = (int) $plugin->piwik_site_id;
		
		if (!empty($piwik_url) && !empty($piwik_site_id)) {
			$load_js = true;
			$paq = "_paq";
			if(elgg_is_active_plugin("analytics") && ($local_piwik_site_id = elgg_get_plugin_setting("piwik_site_id", "analytics"))){
				$paq = "_paq2";
				$load_js = false;
			}
			if($piwik_site_id !== $local_piwik_site_id){
					
				// validate piwik url
				if (((stripos($piwik_url, "https://") === 0) || (stripos($piwik_url, "http://") === 0)) && (substr($piwik_url, -1, 1) === "/")) {
					
					if ($load_js) {
					?>
						<!-- Piwik from Pleio -->
						<script type="text/javascript">
							var <?php echo $paq; ?> = _paq || [];
						
							(function() {
								var u = "<?php echo $piwik_url; ?>";
								<?php echo $paq; ?>.push(['setSiteId', <?php echo $piwik_site_id; ?>]);
								<?php echo $paq; ?>.push(['setTrackerUrl', u + 'piwik.php']);
								<?php echo $paq; ?>.push(['trackPageView']);
								<?php echo $paq; ?>.push(['enableLinkTracking']);
						
								var d = document, g = d.createElement('script'), s = d.getElementsByTagName('script')[0];
								g.type = 'text/javascript';
								g.defer = true;
								g.async = true;
								g.src = u + 'piwik.js';
								s.parentNode.insertBefore(g,s);
							})();
						 </script>
						<!-- End Piwik Code -->
					<?php
					} else {
					?>
						<!-- Piwik from Pleio -->
						<script type="text/javascript" src="<?php echo $piwik_url; ?>piwik.js"></script>
						<script type="text/javascript">
							try {
								var piwikTracker = Piwik.getTracker("<?php echo $piwik_url; ?>piwik.php", <?php echo $piwik_site_id; ?>);
								piwikTracker.trackPageView();
							} catch( err ) {
								console.log(err);
							}
						 </script>
						<!-- End Piwik Code -->
					<?php
					}
				}
			}
		}
	}
	
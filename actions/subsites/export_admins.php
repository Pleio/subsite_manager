<?php

if (subsite_manager_on_subsite()) {
	register_error(elgg_echo("subsite_manager:action:error:on_subsite"));
	forward(REFERER);
}

// this could take a while
set_time_limit(0);

$subsite_options = array(
	"type" => "site",
	"subtype" => Subsite::SUBTYPE,
	"limit" => false,
	"joins" => array("JOIN " . get_config("dbprefix") . "sites_entity se ON se.guid = e.guid"),
	"order_by" => "se.name ASC"
);

$batch = new ElggBatch("elgg_get_entities", $subsite_options);
$batch->rewind();
if ($batch->valid()) {
	// create a temp file for storage
	$fh = tmpfile();
	
	$headers = array(
		"name",
		"url",
		"member count",
		"last activity (unix)",
		"last activity (YYYY-MM-DD HH:MM:SS)",
		"admin name",
		"admin email",
		"admin profile"
	);
	fwrite($fh, "\"" . implode("\",\"", $headers) . "\"" . PHP_EOL);
	
	foreach ($batch as $subsite) {
		$member_count = $subsite->getMembers(array("count" => true));
		$last_activity = subsite_manager_get_subsite_last_activity($subsite->getGUID());
		$last_activity_readable = date("Y-m-d G:i:s", $last_activity);
		
		$admin_guids = $subsite->getAdminGuids();
		
		$output = array(
			$subsite->name,
			$subsite->url,
			$member_count,
			$last_activity,
			$last_activity_readable
		);
		if (!empty($admin_guids)) {
			
			foreach ($admin_guids as $admin_guid) {
				$admin = get_user($admin_guid);
				
				if (!empty($admin)) {
					$admin_output = $output;
					
					$admin_output[] = $admin->name;
					$admin_output[] = $admin->email;
					$admin_output[] = $admin->getURL();
					
					// write to file
					fwrite($fh, "\"" . implode("\",\"", $admin_output) . "\"" . PHP_EOL);
					
					// cache cleanup
					_elgg_invalidate_cache_for_entity($admin->getGUID());
				}
			}
		} else {
			// write to file
			fwrite($fh, "\"" . implode("\",\"", $output) . "\"" . PHP_EOL);
		}
		
		// cache cleanup
		_elgg_invalidate_cache_for_entity($subsite->getGUID());
	}
	
	// read the csv in to a var before output
	$contents = "";
	rewind($fh);
	while (!feof($fh)) {
		$contents .= fread($fh, 2048);
	}
	
	// cleanup the temp file
	fclose($fh);
	
	// output the csv
	header("Content-Type: text/csv");
	header("Content-Disposition: attachment; filename=\"subsite_admins.csv\"");
	header("Content-Length: " . strlen($contents));
	
	echo $contents;
	
} else {
	forward(REFERER);
}

<?php
	// Work out number of users
	if(!subsite_manager_on_subsite()){
		$users_stats = get_number_users();
		$total_users = get_number_users(true);
		
		$users = $users_stats . " " . elgg_echo('active') . " / " . $total_users . " " . elgg_echo('total');
	} else {
		$site = elgg_get_site_entity();
		
		$options = array(
			"type" => "user",
			"site_guids" => false,
			"count" => true
		);
		$users_stats = $site->getMembers($options);
		$users_pending = (int) $site->countMembershipRequests();
		
		$users = $users_stats . " " . elgg_echo("active") . " / " . $users_pending . " " .  elgg_echo('admin:users:membership');
	}
	
	// Get version information
	$version = get_version();
	$release = get_version(true);
?>
<table class="elgg-table-alt">
	<tr class="odd">
		<td><b><?php echo elgg_echo('admin:statistics:label:version'); ?> :</b></td>
		<td><?php echo elgg_echo('admin:statistics:label:version:release'); ?> - <?php echo $release; ?>, <?php echo elgg_echo('admin:statistics:label:version:version'); ?> - <?php echo $version; ?></td>
	</tr>
	<tr class="even">
		<td><b><?php echo elgg_echo('admin:statistics:label:numusers'); ?> :</b></td>
		<td><?php echo $users; ?></td>
	</tr>
</table>
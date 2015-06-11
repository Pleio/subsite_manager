<?php
/**
 * Sync and order plugins of a subsite.
 *
 * @package SubsiteManager
 */

admin_gatekeeper();

// can only be viewed on main site
if (subsite_manager_on_subsite()) {
    forward("admin");
    exit();
}

// this could take a while
set_time_limit(0);

// stream sites
ob_implicit_flush(true);
ob_end_flush();

$old_ia = elgg_set_ignore_access(true);

$site_guid = get_input('site_guid');

$start = microtime(true);
$subsite = get_entity((int) get_input('site_guid'));

if (!$subsite instanceof Subsite) {
    throw new Exception('Not a subsite');
}

$result = subsite_manager_sync_plugins($subsite);

echo json_encode(array(
    'sorted' => $result[0],
    'activated' => $result[1],
    'time' => round(microtime(true) - $start, 2)
));

elgg_set_ignore_access($old_ia);
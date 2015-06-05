<?php
/**
 * Sync and order plugins of all subsites.
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

$options = array(
    'type' => 'site',
    'subtype' => Subsite::SUBTYPE,
    'limit' => false
);

echo '<ul>';
$batch = new ElggBatch('elgg_get_entities', $options);
foreach ($batch as $subsite) {
    $result = subsite_manager_sync_plugins($subsite);
    echo '<li>';
    echo '<b>' . $subsite->name . ' (' . $subsite->url . ') ';
    echo $result[0] . ' sorted ';
    echo $result[1] . ' added';
    echo '</b></li>';

    ob_flush();
}
echo '</ul>';

echo 'Ready, <a href="/admin/">back to admin</a>.';

elgg_set_ignore_access($old_ia);

exit(); // do not forward
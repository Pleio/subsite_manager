<?php
/**
 * Download the video thumbnail in the server and link it to video
 *
 * First determine if the upgrade is needed and then if needed, batch the update
 */
return;

$items = elgg_get_entities(array(
	'type' => 'object',
	'subtype' => 'videolist_item',
	'limit' => 5,
	'order_by' => 'e.time_created asc',
));

// if not items, no upgrade required
if (!$items) {
	return;
}

// if all five of the items have empty thumbnails, we need to upgrade
foreach ($items as $item) {
	if ($item->thumbnail === true) {
		return;
	}
}


/**
 * Downloads the thumbnail and saves into data folder
 *
 * @param ElggObject $item
 * @return bool
 */
function videolist_2012022501($item) {

	// do not upgrade videos that have already been upgraded
	if ($item->thumbnail === true) {
		return true;
	}

	$thumbnail = file_get_contents($item->thumbnail);
	if (!$thumbnail) {
		return false;
	}
	
	$prefix = "videolist/" . $item->guid;
	$filehandler = new ElggFile();
	$filehandler->owner_guid = $item->owner_guid;
	$filehandler->setFilename($prefix . ".jpg");
	$filehandler->open("write");
	$filehandler->write($thumbnail);
	$filehandler->close();
	
	$item->thumbnail = true;
	return true;
}

$previous_access = elgg_set_ignore_access(true);
$options = array(
	'type' => 'object',
	'subtype' => 'videolist_item',
	'limit' => 0,
);
$batch = new ElggBatch('elgg_get_entities', $options, 'videolist_2012022501', 100);
elgg_set_ignore_access($previous_access);

if ($batch->callbackResult) {
	error_log("Elgg videolist upgrade (2012022501) succeeded");
} else {
	error_log("Elgg videolist upgrade (2012022501) failed");
}

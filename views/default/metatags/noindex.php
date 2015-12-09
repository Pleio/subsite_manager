<?php

$indexingEnabled = elgg_get_config("enable_frontpage_indexing");
if(!$indexingEnabled) {
	echo "<meta name=\"robots\" content=\"noindex\" />";
}
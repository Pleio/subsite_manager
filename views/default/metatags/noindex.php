<?php

$indexingEnabled = elgg_get_config("enable_frontpage_indexing");
if(subsite_manager_on_subsite() && !$indexingEnabled) {
	echo "<meta name=\"robots\" content=\"noindex\" />";
}
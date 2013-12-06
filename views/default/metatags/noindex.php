<?php
global $SUBSITE_MANAGER_INDEX_ALLOWED;

// frontpage indexing
if (elgg_get_site_url() == current_page_url()) {
	$enable_frontpage_indexing = (int) elgg_get_config("enable_frontpage_indexing");
	if ($enable_frontpage_indexing) {
		$SUBSITE_MANAGER_INDEX_ALLOWED = true;
	}
}

// only allow for indexing if it is explicitly defined as allowed
if(!isset($SUBSITE_MANAGER_INDEX_ALLOWED)) {
	?>
	<meta name="robots" content="noindex" />
	<?php
}

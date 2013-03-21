<?php 
global $SUBSITE_MANAGER_INDEX_ALLOWED;
// only allow for indexing if it is explicitly defined as allowed
if(!isset($SUBSITE_MANAGER_INDEX_ALLOWED)) {
	?>
	<meta name="robots" content="noindex" />
	<?php 
}

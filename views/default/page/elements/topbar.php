<?php 
if(elgg_is_logged_in()){
	// link back to main site.
	$site = elgg_get_site_entity();
	$site_name = $site->name;
	$site_url = elgg_get_site_url();
	
	?>
	
	<h1>
		<a class="subsite-manager-topbar-logo" href="<?php echo $site_url; ?>">
			<?php echo $site_name; ?>
		</a>
	</h1>
	<?php
	echo elgg_view('subsite_manager/subsites/dropdown');
	echo elgg_view("search/search_box");
	echo elgg_view('subsite_manager/account/dropdown');
}
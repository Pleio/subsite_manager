<?php 

$base_url = elgg_get_site_url();

echo "<ul class='subsite-manager-subsite-dropdown'>";
echo "<li class='hidden'>";
echo "<ul>";

if(subsite_manager_on_subsite()){
	// link back to parent site
	$parent = elgg_get_config("site")->getOwnerEntity();
	$base_url = $parent->url;
	echo "<li>" . elgg_view("output/url", array("href" => $base_url, "text" => $parent->name, "is_trusted" => true)) . "</li>";
}

$sites = subsite_manager_get_user_subsites();
if($sites){
	// list subsites
	foreach($sites as $site){
		if($site->url != elgg_get_site_url()){
			// only add a link to other sites not to current
			echo "<li title='" . $site->name . "'>" . elgg_view("output/url", array("href" => $site->url, "text" => $site->name, "is_trusted" => true)) . "</li>";
		}
	}
}

echo "<li>" . elgg_view("output/url", array("href" => $base_url . "subsites", "text" => elgg_echo("subsite_manager:menu:subsites:all"), "class" => "elgg-quiet", "is_trusted" => true)) . "</li>";
if($sites){
	echo "<li>" . elgg_view("output/url", array("href" => $base_url . "subsites/mine", "text" => elgg_echo("subsite_manager:menu:subsites:mine"), "class" => "elgg-quiet", "is_trusted" => true)) . "</li>";
}

echo "</ul><li></ul>";

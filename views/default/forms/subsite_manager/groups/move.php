<?php

$entity = elgg_extract("entity", $vars);
if (empty($entity) || !elgg_instanceof($entity, "group")) {
	return;
}

$subsites = subsite_manager_get_user_subsites();
if (empty($subsites)) {
	echo elgg_view("output/longtext", array("value" => elgg_echo("subsite_manager:forms:groups:move:no_subsites")));
	return;
}

$current_site = elgg_get_site_entity();
$subsite_options = array(
	"" => elgg_echo("subsite_manager:forms:groups:move:select_subsite:default")
);
if (subsite_manager_on_subsite()) {
	$main_site = $current_site->getOwnerEntity();

	$subsite_options[$main_site->getGUID()] = $main_site->name;
}

foreach ($subsites as $subsite) {
	if ($subsite->getGUID() == $current_site->getGUID()) {
		// can't move to this site
		continue;
	}
	$subsite_options[$subsite->getGUID()] = $subsite->name;
}

$content = "<div>";
$content .= "<label>" . elgg_echo("subsite_manager:forms:groups:move:select_subsite") . "</label>";
$content .= elgg_view("input/dropdown", array("name" => "site_guid", "options_values" => $subsite_options, "class" => "mlm"));
$content .= "</div>";

$content .= "<div class='elgg-foot'>";
$content .= elgg_view("input/hidden", array("name" => "guid", "value" => $entity->getGUID()));
$content .= elgg_view("input/submit", array(
	"value" => elgg_echo("subsite_manager:forms:groups:move:submit"),
	"class" => "elgg-button-submit elgg-requires-confirmation",
	"rel" => elgg_echo("subsite_manager:forms:groups:move:confirm")
));
$content .= "</div>";

echo $content;
<?php

// can only be viewed on main site
if (subsite_manager_on_subsite()) {
    forward("admin");
}

$plugins = subsite_manager_get_plugins();
$priority_name = elgg_namespace_plugin_private_setting('internal', 'priority');

// make sure all main plugins have an order
foreach ($plugins as $plugin) {
    if (!$plugin->get($priority_name)) {
        $plugin->set($priority_name, 'last');
    }
}
elgg_generate_plugin_entities();

$options = array(
    'type' => 'site',
    'subtype' => Subsite::SUBTYPE,
    'limit' => false
);

$subsites = array();
$batch = new ElggBatch('elgg_get_entities', $options);
foreach ($batch as $subsite) {
    $subsites[] = array(
        'guid' => $subsite->guid,
        'name' => $subsite->name
    );
}

echo '<p><div id="status">Klik op update</div><div id="result"></div></p>';

echo elgg_view("input/button", array(
    'href' => '#',
    'id' => 'subsite-manager-update-plugins',
    'class' => 'elgg-button elgg-button-action',
    'data-subsites' => json_encode($subsites),
    'value' => 'Update plugins'
));
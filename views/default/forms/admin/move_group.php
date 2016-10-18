<?php
$dbprefix = elgg_get_config("dbprefix");

$options = [
    "type" => "group",
    "joins" => array("JOIN {$dbprefix}groups_entity oe ON e.guid = oe.guid"),
    "order_by" => "oe.name",
    "limit" => 0
];

$options_values = [];
foreach (elgg_get_entities($options) as $group) {
    $options_values[$group->guid] = $group->name;
}
?>

<div>
    <label><?php echo elgg_echo("subsite_manager:admin:move_group:from"); ?></label><br />
    <?php echo elgg_view("input/dropdown", array(
        "name" => "from",
        "options_values" => $options_values,
        "value" => (int) get_input("from")
    )); ?>
</div>

<div>
    <label><?php echo elgg_echo("subsite_manager:admin:move_group:to"); ?></label><br />
    <?php echo elgg_view("input/dropdown", array(
        "name" => "to",
        "options_values" => $options_values,
        "value" => (int) get_input("to")
    )); ?>
</div>

<?php echo elgg_view("input/submit", array(
    "class" => "elgg-button elgg-button-cancel",
    "name" => "preview",
    "value" => elgg_echo("subsite_manager:admin:move_group:preview")
)); ?>

<?php echo elgg_view("input/submit", array(
    "name" => "submit",
    "class" => "elgg-button elgg-button-submit",
    "value" => elgg_echo("subsite_manager:admin:move_group:move")
)); ?>

<?php
$preview = get_input("preview");
if ($preview) {
    echo elgg_view("admin/administer_utilities/move_group_preview", array(
        "from" => get_entity((int) get_input("from")),
        "to" => get_entity((int) get_input("to"))
    ));
}
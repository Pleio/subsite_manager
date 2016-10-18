<?php
$from = $vars["from"];
$to = $vars["to"];

if (!$from instanceof ElggGroup || !$from->canEdit()) {
    register_error(elgg_echo("subsite_manager:admin:move_group:permission_denied"));
    forward(REFERER);
}

if (!$to instanceof ElggGroup || !$to->canEdit()) {
    register_error(elgg_echo("subsite_manager:admin:move_group:permission_denied"));
    forward(REFERER);
}

$dbprefix = elgg_get_config("dbprefix");

$from_acl = $from->group_acl;
$to_acl = $to->group_acl;

$entities = get_data("SELECT guid FROM {$dbprefix}entities WHERE container_guid = {$from->guid} LIMIT 1000");
$entities_acl = get_data("SELECT guid FROM {$dbprefix}entities WHERE access_id = {$from_acl} LIMIT 1000");
$annotations = get_data("SELECT id FROM {$dbprefix}annotations WHERE access_id = {$from_acl} LIMIT 1000");

?>
<p>
    <ul>
    <h2>
        <?php echo elgg_echo("subsite_manager:admin:move_group:scheduled"); ?>
    </h2>
    <?php if (!$entities && !$entities_acl && !$annotations): ?>
        <?php echo elgg_echo("subsite_manager:admin:move_group:no_items"); ?>
    <?php endif; ?>
    <?php foreach ($entities as $entity): ?>
        <?php $entity = get_entity($entity->guid); ?>
        <li>- <?php echo elgg_echo("subsite_manager:admin:move_group:move_entity"); ?> <i><?php echo $entity->title; ?></i></li>
    <?php endforeach; ?>

    <?php foreach ($entities_acl as $entity_acl): ?>
        <?php $entity = get_entity($entity->guid); ?>
        <li>- <?php echo elgg_echo("subsite_manager:admin:move_group:permission_of"); ?> <i><?php echo $entity->title; ?></i></li>
    <?php endforeach; ?>

    <?php foreach ($annotations as $annotation): ?>
        <?php $annotation = elgg_get_annotation_from_id($annotation->id); ?>
        <li>- <?php echo elgg_echo("subsite_manager:admin:move_group:annotation"); ?> <i><?php echo $annotation->name; ?></i> <?php echo elgg_echo("subsite_manager:admin:move_group:on"); ?> <i><?php echo $annotation->getEntity()->title; ?></i></li>
    <?php endforeach; ?>
    </ul>
</p>
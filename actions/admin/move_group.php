<?php
$from = (int) get_input("from");
$to = (int) get_input("to");

$from = get_entity($from);
$to = get_entity($to);

if (!$from instanceof ElggGroup || !$from->canEdit()) {
    register_error(elgg_echo("subsite_manager:admin:move_group:permission_denied"));
    forward(REFERER);
}

if (!$to instanceof ElggGroup || !$to->canEdit()) {
    register_error(elgg_echo("subsite_manager:admin:move_group:permission_denied"));
    forward(REFERER);
}

if ($from == $to) {
    register_error(elgg_echo("subsite_manager:admin:move_group:from_to_same"));
    forward(REFERER);
}

$submit = get_input("submit");

if (!$submit) {
    forward("/admin/administer_utilities/move_group?preview=true&from={$from->guid}&to={$to->guid}");
}

$dbprefix = elgg_get_config("dbprefix");

$from_acl = $from->group_acl;
$to_acl = $to->group_acl;

$invalidate_entities = get_data("SELECT guid FROM {$dbprefix}entities WHERE container_guid = {$from->guid}");

update_data("UPDATE {$dbprefix}entities SET container_guid = {$to->guid} WHERE container_guid = {$from->guid}");
update_data("UPDATE {$dbprefix}entities SET access_id = {$to_acl} WHERE access_id = {$from_acl}");
update_data("UPDATE {$dbprefix}metadata SET access_id = {$to_acl} WHERE access_id = {$from_acl}");

update_data("UPDATE {$dbprefix}annotations SET access_id = {$to_acl} WHERE access_id = {$from_acl}");

if (is_memcache_available()) {
    foreach ($invalidate_entities as $entity) {
        _elgg_invalidate_memcache_for_entity($entity->guid);
    }
}

system_message(elgg_echo("subsite_manager:admin:move_group:success"));
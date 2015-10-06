<?php

set_time_limit(0);

if(!subsite_manager_is_superadmin_logged_in()){
    register_error('subsite_manager:users:bulk_remove:no_access');
    forward(REFERER);
};

if (!array_key_exists('csv', $_FILES)) {
    register_error('subsite_manager:users:bulk_remove:no_file');
    forward(REFERER);
}

$fh = fopen($_FILES["csv"]["tmp_name"], "r");
if (!fh) {
    register_error('subsite_manager:users:bulk_remove:no_file');
    forward(REFERER);
}

$removed = 0; // number of removed users
$not_found = 0; // number of users not found
$already_activated = 0; // number of users not removed because they where already activated

while (($data = fgetcsv($fh, 0, ";")) !== false) {

    if (!$data[0]) {
        //continue;
    }

    if (strpos($data[0], '@') !== false) {
        $users = get_user_by_email($data[0]);
        $user = $users[0];
    } else {
        $user = get_user_by_username($data[0]);
    }

    if (!$user) {
        $not_found += 1;
        continue;
    }

    if (get_private_setting($user->guid, 'general_terms_accepted')) {
        $already_activated += 1;
    } else {
        $user->delete();
        $removed += 1;
    }
}

system_message(elgg_echo("subsite_manager:users:bulk_remove:success", array(
    $removed,
    $not_found,
    $already_activated
)));
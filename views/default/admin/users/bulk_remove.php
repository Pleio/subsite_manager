<?php

if(!subsite_manager_is_superadmin_logged_in()){
    forward('/');
};

echo "<p>" . elgg_echo('subsite_manager:users:bulk_remove:description') . "</p>";
echo elgg_view_form('subsite_manager/users/bulk_remove', array(
    'enctype' => 'multipart/form-data'
));
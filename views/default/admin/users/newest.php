<?php
// newest users
$users = elgg_list_entities_from_relationship(array(
	"type" => "user",
	"site_guids" => false,
	"relationship"=> "member_of_site",
	"relationship_guid" => elgg_get_site_entity()->getGUID(),
	"inverse_relationship" => true,
	"order_by" => "r.time_created DESC",
	"full_view" => FALSE
));

?>

<div class="elgg-module elgg-module-inline">
	<div class="elgg-head">
		<h3><?php echo elgg_echo('admin:users:newest'); ?></h3>
	</div>
	<div class="elgg-body">
		<?php echo $users; ?>
	</div>
</div>

<?php
/**
 * Edit settings for river widget
 */

// dashboard widget has type parameter
if (elgg_in_context('dashboard')) {
	if (!isset($vars['entity']->content_type)) {
		$vars['entity']->content_type = 'friends';
	}
	$params = array(
		'name' => 'params[content_type]',
		'value' => $vars['entity']->content_type,
		'options_values' => array(
			'friends' => elgg_echo('river:widgets:friends'),
			'all' => elgg_echo('river:widgets:all'),
		),
	);
	$type_dropdown = elgg_view('input/dropdown', $params);
	?>
	<div>
		<?php echo elgg_echo('river:widget:type'); ?>:
		<?php echo $type_dropdown; ?>
	</div>
	<?php
}


// set default value for number to display
if (!isset($vars['entity']->num_display)) {
	$vars['entity']->num_display = 8;
}

$params = array(
	'name' => 'params[num_display]',
	'value' => $vars['entity']->num_display,
	'options' => array(5, 8, 10, 12, 15, 20),
);
$num_dropdown = elgg_view('input/dropdown', $params);

if (elgg_in_context('dashboard') && !subsite_manager_on_subsite()) {
	
	$sites = subsite_manager_get_user_subsites(page_owner_entity()->guid);
	
	if ($sites) {
		
		$pulldown_options = array(
				"0" => elgg_echo("widget:river_widget:edit:site_selection:current"),
				"all" => elgg_echo("widget:river_widget:edit:site_selection:all")
			);
		
		foreach ($sites as $site) {
			$pulldown_options[$site->guid] = $site->name;
		}
		
		echo "<div>";
		echo elgg_echo("widget:river_widget:edit:site_selection") . ": ";
		echo elgg_view("input/dropdown", array(
				"name" => "params[activity_site_guid]",
				"value" => $vars['entity']->activity_site_guid,
				"options_values" => $pulldown_options,
				"style" => "width: 100px"
			));
		echo "</div>";
	}
}

?>
<div>
	<?php echo elgg_echo('widget:numbertodisplay'); ?>:
	<?php echo $num_dropdown; ?>
</div>

<?php
// pass the context so we have the correct output upon save.
if (elgg_in_context('dashboard')) {
	$context = 'dashboard';
} else {
	$context = 'profile';
}

echo elgg_view('input/hidden', array(
	'name' => 'context',
	'value' => $context
));

<?php
/**
 * Page shell for upgrade script
 *
 * Displays an ajax loader until upgrade is complete
 */

if(get_input("all") == "true"){
	$all = "&all=true";
}
?>
<html>
	<head>
		<?php echo elgg_view('page/elements/head', $vars); ?>
		<meta http-equiv="refresh" content="1;url=<?php echo elgg_get_site_url(); ?>upgrade.php?upgrade=upgrade<?php echo $all; ?>"/>
	</head>
	<body>
		<div style="margin-top:200px">
			<?php echo elgg_view('graphics/ajax_loader', array('hidden' => false)); ?>
		</div>
	</body>
</html>
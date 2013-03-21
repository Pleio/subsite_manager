<?php 

	$site = elgg_get_site_entity();
	
	$users = ElggMenuItem::factory(array(
		"name" => "users",
		"href" => "#subsite-manager-invite-users",
		"text" => elgg_echo("item:user"),
		"selected" => true,
		"priority" => 50
	));
	$csv = ElggMenuItem::factory(array(
		"name" => "csv",
		"href" => "#subsite-manager-invite-csv",
		"text" => elgg_echo("subsite_manager:invite:csv:tab"),
		"priority" => 100
	));
	
	elgg_register_menu_item("subsite_manager_invite", $users);
	elgg_register_menu_item("subsite_manager_invite", $csv);
	
	echo elgg_view_menu("subsite_manager_invite", array(
		"class" => "elgg-tabs",
		"sort_by" => "priority"
	));

	// invite users (by username, name, email)
	echo "<div id='subsite-manager-invite-users'>";
	echo "<label>" . elgg_echo("subsite_manager:invite:users:label") . "</label><br />";
	echo elgg_view("input/subsite_manager_autocomplete", array("name" => "user_guids"));
	echo "<div class='elgg-subtext'>" . elgg_echo("subsite_manager:invite:users:description") . "</div>";
	echo "</div>";
	
	// invite users by csv
	echo "<div id='subsite-manager-invite-csv' class='hidden'>";
	echo "<label>" . elgg_echo("subsite_manager:invite:csv:label") . "</label><br />";
	echo elgg_view("input/file", array("name" => "csv"));
	echo "<div>" . elgg_echo("subsite_manager:invite:csv:description") . "</div>";
	echo "</div>";
	
	echo "<div>";
	echo "<label>" . elgg_echo("subsite_manager:invite:message:label") . "</label><br />";
	echo elgg_view("input/longtext", array("name" => "message"));
	echo "</div>";
	
	echo "<div class='elgg-footer'>";
	echo elgg_view("input/submit", array("value" => elgg_echo("invite")));
	echo "</div>";
?>
<script type="text/javascript">
	$(document).ready(function(){
		$(".elgg-menu-subsite-manager-invite a").live("click", function(event){
			event.preventDefault();

			var $ul = $(this).parent().parent();
			var $target = $(this).attr("href");

			$ul.find("li.elgg-state-selected").removeClass("elgg-state-selected");
			$(this).parent("li").addClass("elgg-state-selected");

			
			$("#subsite-manager-invite-users").hide();
			$("#subsite-manager-invite-csv").hide();

			$($target).show();
		});
	});
</script>
<?php ?>
//<script>
elgg.provide("elgg.subsite_manager");

elgg.subsite_manager.init = function() {
	$('#subsite_manager_search_form input[name="text"]').live({
		focus: function(){
			if($(this).val() == elgg.echo("search")){
				$(this).val("");
			}
		},
		blur: function(){
			if($(this).val() == ""){
				$(this).val(elgg.echo("search"));
			}
		}
	});

	$(".elgg-menu-item-subsite-manager-remove-user a").live("click", function() {
		var rel = $(this).attr("rel");
		var result = false;
		
		if (rel.length > 0) {
			var msg = prompt(rel);

			if (msg != null) {
				result = true;
				var href = $(this).attr("href");

				href = href + "&msg=" + encodeURIComponent(msg);
				$(this).attr("href", href);
			}
		}

		return result;
	});
};

elgg.register_hook_handler('init', 'system', elgg.subsite_manager.init);

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
};

elgg.register_hook_handler('init', 'system', elgg.subsite_manager.init);
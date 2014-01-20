<?php ?>
//<script>

elgg.provide('elgg.subsite_manager');

function subsite_manager_autocomplete_format_item(row, pos, items, search) {
	var result = "";
	
	if(row[0] == "user"){
		if(row[3]){
			result += "<img src='" + row[3] + "' />&nbsp;";
		}
		result += row[2];
	}
	if(row[0] == "email"){
		result += row[1];
	}

	return result;
}

function subsite_manager_autocomplete_format_result(row, elem_id, input_name) {
	var result = "";
	result += "<div class='" + elem_id + "_result'>";

	if(row[0] == "user"){
		result += "<input type='hidden' value='" + row[1] + "' name='" + input_name + "[]' />";

		if(row[3]){
			result += "<img src='" + row[3] + "' />&nbsp;";
		}
		
		result += row[2].replace(/(<.+?>)/gi, '');
	}

	if(row[0] == "email"){
		result += "<input type='hidden' value='" + row[1] + "' name='" + input_name + "_email[]' />";
		result += row[1].replace(/(<.+?>)/gi, '');
	}

	result += "<span class='elgg-icon elgg-icon-delete-alt'></span>";
	result += "</div>";

	$('#' + elem_id + '_autocomplete_results').append(result);
}

elgg.subsite_manager.init = function() {
	$('#subsite-manager-newest-users-check-all').live('click', function(){
		var checked = $(this).attr('checked') == 'checked';

		$('#subsite-manager-newest-users-bulk-action .elgg-body').find('input[type=checkbox]').attr('checked', checked);
	});

	$('#subsite-manager-newest-users-bulk-action .subsite-manager-newest-users-submit').live('click', function(event){
		if (!event.isDefaultPrevented()) {
			// check if there are selected users
			if ($('#subsite-manager-newest-users-bulk-action .elgg-body').find('input[type=checkbox]:checked').length < 1) {
				return false;
			}

			$('#subsite-manager-newest-users-bulk-action').attr('action', $(this).attr('href')).submit();
		}

		event.preventDefault();
	});

	$(".elgg-menu-annotation .elgg-menu-item-decline a").live("click", function() {
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
}

elgg.register_hook_handler('init', 'system', elgg.subsite_manager.init);
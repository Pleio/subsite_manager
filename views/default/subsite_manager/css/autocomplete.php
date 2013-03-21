<?php 

	/**
	 * default Elgg doesn't load autocomplete CSS on admin
	 * @todo JD: why not load from original location?
	 */

?>
/* ***************************************
	AUTOCOMPLETE
*************************************** */
<?php //autocomplete will expand to fullscreen without max-width ?>
.ui-autocomplete {
	position: absolute;
	cursor: default;
}
.elgg-autocomplete-item .elgg-body {
	max-width: 600px;
}
.ui-autocomplete {
	background-color: white;
	border: 1px solid #ccc;
	overflow: hidden;

	-webkit-border-radius: 5px;
	-moz-border-radius: 5px;
	border-radius: 5px;
}
.ui-autocomplete .ui-menu-item {
	padding: 0px 4px;
	
	-webkit-border-radius: 5px;
	-moz-border-radius: 5px;
	border-radius: 5px;
}
.ui-autocomplete .ui-menu-item:hover {
	background-color: #eee;
}
.ui-autocomplete a:hover {
	text-decoration: none;
	color: #4690D6;
}
/* custom autocomplete result styling */
.ac_results {
	position: absolute;
	
	max-width: 600px;
	background-color: white;
	border: 1px solid #ccc;
	overflow: hidden;

}
.ac_results ul>li {
	padding: 1px 4px;
}
.ac_results ul>li img {
	vertical-align: middle;
}
.ac_results ul>li.ac_odd {
	background-color: #eee;
}
.ac_results ul>li.ac_over {
	background-color: #E4ECF5
}
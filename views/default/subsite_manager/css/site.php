<?php 
	$graphics_folder =  elgg_get_site_url() . "mod/subsite_manager/_graphics/";
?>

/* Topbar Changes */
.elgg-page-topbar {
	background: white url(<?php echo $graphics_folder; ?>topbar.png) repeat-x bottom left;
	border-bottom: 1px solid #BDBDBD;
	height: 28px;
	z-index: 2;
	width: 100%;
	position: fixed;
	top: 0;
	left: 0;
}

.elgg-page-topbar > .elgg-inner {
	padding: 0px;
	position: relative;
	width: 990px;
	margin: 0 auto;
}

.subsite-manager-topbar-logo {
	font-size: 0em;
	line-height: 1.4em;
	color: white;
	text-decoration: none;
	background: transparent url(<?php echo $graphics_folder; ?>logo.png);
	width: 64px;
	height: 26px;
	display: block;
}

/* topbar search */
.elgg-page-topbar > .elgg-inner > .elgg-search {
	position: absolute;
	top: 4px;
	left: 130px;
	border-color: #BDBDBD;
	
}

.elgg-page-topbar > .elgg-inner > .elgg-search input[type="text"] {
	color: grey;
	border-color: #BDBDBD;
}

.elgg-page-topbar .search-advanced-type-selection > li > a {
	background: #BDBDBD;
}

/* account dropdown */
#subsite-manager-login-dropdown {
	position: absolute;
    right: 0;
    top: 1px;
}

a.subsite-manager-account-dropdown-button {
	padding: 0px 6px 0 0;
	border: 1px solid #CCC;
	background: white;
	color: #CCC;
	font-weight: bold;
	line-height: 23px;
	font-size: 14px;
	height: 25px;
}

a.subsite-manager-account-dropdown-button:hover {
	background: #CCC;
	color: white;
	text-decoration: none;
}	

a.subsite-manager-account-dropdown-button:after {
    content: " ▼";
    font-size: smaller;
}

.subsite-manager-account-dropdown-button .elgg-avatar {
	display: inline-block;
	width: 25px;
	vertical-align: middle;
	border-right: 1px solid #CCC;
	margin-right: 6px;
}

.subsite-manager-account-dropdown {
	width: 300px;
	padding: 1px;
	z-index: 9001;
}

.subsite-manager-account-dropdown .elgg-body > div {
	padding: 15px;
}

.subsite-manager-account-dropdown .elgg-avatar {
	border: 1px solid #CCC;
	padding: 1px;
	float: left;
	margin-right: 10px;
}

.subsite-manager-account-dropdown-user {
	border-bottom: 1px solid #CCC;
}

.subsite-manager-account-dropdown-user > a {
	margin-left: 5px;
	line-height: 2em;
}

.subsite-manager-account-dropdown-messages {
	color: #383838;
}

/* end account dropdown */

/* subsites dropdown */
.subsite-manager-subsite-dropdown {
	background: transparent url(<?php echo $graphics_folder; ?>arrows_down.png);
	height: 13px;
    left: 70px;
    position: absolute;
    top: 6px;
    width: 12px;
	display: block;
	cursor: pointer;
	z-index: 1;
}
.subsite-manager-subsite-dropdown ul {
	background: white;
	border-top: 1px solid #BDBDBD;
}

.subsite-manager-subsite-dropdown ul li {
	border-style: solid;
	border-width: 0 1px 1px;
	border-color: #BDBDBD;	
}

.subsite-manager-subsite-dropdown ul li a{
	display: block;
	white-space: nowrap;
	padding: 5px 10px;
	
}
.subsite-manager-subsite-dropdown ul li a:hover {
	color: white;
	background: #BDBDBD;
	text-decoration: none;
}

.subsite-manager-subsite-dropdown:hover > li {
	display: inline-block;
	position: absolute;
	top: 12px;
	left: 0;
}
/* end subsites dropdown */

#subsite-manager-no-access {
	margin: 0 auto;
	width: 300px;
	text-align: center;
}

#subsite-manager-no-access-groups {
	margin: 0 auto;
	width: 300px;
}

#subsite-manager-no-access-table {
	width: 650px;
	margin: 0 auto;
}

#subsite-manager-no-access > div {
	border: 1px solid #CCCCCC;
	margin-bottom: 15px;
	padding: 30px 20px;
}

/* no member */
#subsite-manager-profile-no-member {
	margin: 0 auto;
	width: 220px;
	text-align: center;
}

#subsite-manager-profile-no-member > div {
	border: 1px solid #CCCCCC;
	margin-bottom:15px;
	padding:30px 20px;
}

/* subsites search form */
#subsite_manager_search_form .elgg-input-text {
	padding: 0 2px;
	width: 350px;
}

#subsite_manager_search_form .elgg-button {
	padding: 0 4px;
}
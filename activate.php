<?php 

	/**
	 * All this code will be executed on activation of this plugin
	 */
	
	// register Subsite subtype and class
	add_subtype("site", Subsite::SUBTYPE, "Subsite");
	
	// overrule Plugin class
	update_subtype("object", "plugin", "SubsitePlugin");
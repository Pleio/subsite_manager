<?php

	/**
	 * Prepend the plugin object view
	 * to make sure no reordering is possible on Subsites
	 *
	 */

	if (subsite_manager_on_subsite()) {
		$vars["display_reordering"] = false;
	}
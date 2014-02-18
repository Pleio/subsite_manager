<?php

	if (subsite_manager_on_subsite()) {
		global $SUBSITE_MANAGER_REGISTATION_RESTORE;
		
		$site = elgg_get_site_entity();
		
		if (($site->getMembership() == Subsite::MEMBERSHIP_INVITATION) && $SUBSITE_MANAGER_REGISTATION_RESTORE) {
			elgg_set_config("allow_registration", $SUBSITE_MANAGER_REGISTATION_RESTORE);
		}
	}
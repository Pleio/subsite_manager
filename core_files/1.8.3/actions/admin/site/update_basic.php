<?php
/**
 * Updates the basic settings for the primary site object.
 *
 * Basic site settings are saved as metadata on the site object,
 * with the exception of the default language, which is saved in
 * the config table.
 *
 * @package Elgg.Core
 * @subpackage Administration.Site
 */

/**
* 2012-01-18 - ColdTrick
*
*  overruled to make it multi site compatible
*  Trac ticket #4307 (http://trac.elgg.org/ticket/4307) should fix this
*/

if ($site = elgg_get_site_entity()) {
	if (!($site instanceof ElggSite)) {
		throw new InstallationException(elgg_echo('InvalidParameterException:NonElggSite'));
	}

	$site->description = get_input('sitedescription');
	$site->name = get_input('sitename');
	$site->email = get_input('siteemail');
	$site->save();

	set_config('language', get_input('language'), $site->getGUID());
}

system_message(elgg_echo('admin:configuration:success'));
forward(REFERER);
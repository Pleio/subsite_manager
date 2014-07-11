Subsite Manager
===============
This plugin allows the creation of subsites within ELGG. These subsites still make use of the central userbase, but are reachable by an own URL, containing an own dashboard, groups, plugin settings and other ELGG content.

Features
--------
- ELGG library adjustments to handle subsites
- Create and manage subsites
- Handle access/write access rights for subsite admins

Installation
------------
1. This plugin requires you also to install the [Elgg Modifications](https://githubcom/Pleio/elgg_modifications) plugin.

2. After installing and activating the plugin, copy the contents of the mod/subsite_manager/core_files/[version]/ over your Elgg installation.

3. Run upgrade.php. The subsite manager should activate itself.

4. The plugin now distinguishes admins and superadmins. To become a superadmin (and to make sure the plugin doesn't reset your current admin rights), add a row to the private_settings with values entity_guid=$AdminUserGUID, name="superadmin", value="true". 

Instructions
------------
1. This plugin requires you also to install the [Elgg Modifications](https://githubcom/Pleio/elgg_modifications) plugin, the Profile plugin and the Profile Manager plugin.

2. After installing and activating the plugin, copy the contents of the mod/subsite_manager/core_files/[version]/ over your Elgg installation.

3. Run upgrade.php. The subsite manager should activate itself.

4. The plugin now distinguishes admins and superadmins. To become a superadmin (and to make sure the plugin doesn't reset your current admin rights), add a row to the private_settings with values entity_guid=$AdminUserGUID, name="superadmin", value="true". 
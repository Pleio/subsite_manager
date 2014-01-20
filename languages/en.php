<?php
/**
 * The English translation for this plugin.
 *
 */

$english = array(

	// general
	'item:site:subsite' => "Subsites",
	'subsite_manager:continue' => "Continue",
	'subsite_manager:sample' => "Sample",
	'subsite_manager:column' => "Column",
	'subsite_manager:feature' => "Feature",
	'subsite_manager:unfeature' => "Unfeature",
	'subsite_manager:approve' => "Approve",
	'subsite_manager:decline' => "Decline",
	'subsite_manager:revoke' => "Revoke",

	// membership
	'subsite_manager:membership' => "Subsite membership",
	'subsite_manager:membership:open' => "Open",
	'subsite_manager:membership:approval' => "Approval required",
	'subsite_manager:membership:domain' => "Limited to email domain(s)",
	'subsite_manager:membership:invitation' => "Invitation only",
	'subsite_manager:membership:domain_approval' => "Domain validation or approval",

	// category
	'subsite_manager:category:label' => "Category",
	'subsite_manager:category:description' => "Select the appropriate category. Used for informational purpose only.",
	'subsite_manager:category:options:thematic' => "Thematic",
	'subsite_manager:category:options:organizational' => "Organizational",
	
	// navigation bar
	'subsite_manager:navigation_bar_position:label' => "Positioning of Subsite navigation bar",
	'subsite_manager:navigation_bar_position:description' => "Where should the Subsite navigation bar be positioned, at the top of the page or the bottom.",

	// menu items
	'subsite_manager:menu:subsites' => "Subsites",
	'subsite_manager:menu:subsites:request' => "Request a subsite",
	'subsite_manager:menu:admin:manage' => "Manage Subsites",
	
	'subsite_manager:menu:subsites:all' => "All Subsites",
	'subsite_manager:menu:subsites:mine' => "My Subsites",
	
	'admin:subsites' => "Subsites",
	'admin:subsites:new' => "New Subsite",
	'admin:subsites:plugins' => "Manage Plugins",
	'admin:subsites:admins' => "Manage admins",
	
	'admin:settings:access' => "Access settings",
	
	'admin:users:admins' => "Administrators",
	'admin:users:membership' => "Membership requests",
	'admin:users:import' => "Import users",
	'admin:users:invite' => "Invite users",
	'admin:users:invite_csv' => "Invite users by CSV (step 2)",
	'admin:users:invitations' => "View invitations",

	// river
	'river:join:site:subsite' => "%s joined %s",
	
	// account dropdown
	'subsite_manager:account:dropdown:advanced_notifictions' => "Personalized activity",
	
	// Subsites - List
	'subsite_manager:subsites:title:all' => "All Subsites",
	'subsite_manager:subsites:title:featured' => "Featured Subsites",
	'subsite_manager:subsites:title:open' => "Open Subsites",
	'subsite_manager:subsites:title:closed' => "Closed Subsites",
	'subsite_manager:subsites:title:popular' => "Popular Subsites",
	'subsite_manager:subsites:title:mine' => "My Subsites",
	'subsite_manager:subsites:title:membership' => "Pending memberships",
	
	'subsite_manager:subsites:filter:all' => "All",
	'subsite_manager:subsites:filter:featured' => "Featured",
	'subsite_manager:subsites:filter:open' => "Open",
	'subsite_manager:subsites:filter:closed' => "Closed",
	'subsite_manager:subsites:filter:popular' => "Popular",
	'subsite_manager:subsites:filter:mine' => "Mine",
	'subsite_manager:subsites:filter:membership' => "Pending",
	
	// Subsite - entity
	'subsite_manager:subsite:leave' => "Leave",
	'subsite_manager:subsite:leave:confirm' => "Are you sure you wish to leave this Subsite",
	'subsite_manager:subsite:admin_leave' => "You're an administrator of this Subsite and can't leave",
	'subsite_manager:subsite:pending' => "Membership pending",
	'subsite_manager:subsite:join' => "Join",
	'subsite_manager:subsite:request' => "Request membership",
	'subsite_manager:subsite:validate_domain' => "Request e-mail validation",
	
	'subsite_manager:subsite:remove_user:confirm' => "Are you sure you wish to remove this user from the site?\nOptionaly you can explain to the user why you removed him/her.",
	'subsite_manager:subsite:remove_user:subject' => "Your membership of the subsite %s was revoked.",
	'subsite_manager:subsite:remove_user:message' => "Hi %s,

The site administrator %s revoked your membership of the subsite %s.

%s",
	
	// Subsite - New
	'subsite_manager:new:general' => "Subsite configuration",
	'subsite_manager:new:security' => "Subsite security",
	
	'subsite_manager:new:info' => "Use this form to create a Subsite. Carefully read the descriptions below the configuration options.",
	'subsite_manager:new:name:label' => "Enter a name for the Subsite",
	'subsite_manager:new:name:description' => "This name will be used as the Subsite title and will show up in the listing of Subsites.",

	'subsite_manager:new:description:label' => "Description of the Subsite",
	'subsite_manager:new:description:description' => "This description will be visible in the listing of Subsites.",

	'subsite_manager:new:url:label' => "Enter the url to the Subsite",
	'subsite_manager:new:url:description' => "This url will be used to access the subsite. Please enter the full url (including http and trailing slash).",

	'subsite_manager:new:icon:label' => "Icon",
	'subsite_manager:new:icon:description' => "Optionally upload a nice Subsite icon. This icon will be used in the listing of Subsites.",
	'subsite_manager:new:icon:current' => "Current icon",
	'subsite_manager:new:icon:remove' => "Remove previously uploaded icon",

	'subsite_manager:new:admin:label' => "Enter the username of the admin of the Subsite",
	'subsite_manager:new:admin:description' => "The username should be the username of a current member of the main site. All main site admins are automatically admin of all Subsites.",

	'subsite_manager:new:membership:label' => "Membership of Subsite",
	'subsite_manager:new:membership:description' => "Select the membership for this site.<br />
<b>Open</b> means everyone can join the Subsite.<br />
<b>Approval required</b> means that everyone can request membership to the Subsite but the Subsite admin has to approve the request (the Subsite admin can also invite users).<br />
<b>Limited to email domain(s)</b> mean that only users that can be validated on a white list of email domains can join.<br />
<b>Invitation only</b> means the users can only join the Subsite when they are invited by the Subsite admin.<br />
<b>Domain validation or approval</b> means that users can join based on a whitelist of email domains, or request membership if domain validation failed.",

	'subsite_manager:new:visibility:label' => "Visibility of Subsite",
	'subsite_manager:new:visibility:option:visible' => "Visible",
	'subsite_manager:new:visibility:option:invisible' => "Invisible",
	'subsite_manager:new:visibility:description' => "Select if the Subsite is visible in the list of available Subsites (only applies to Iniviation only Subsites).",

	'subsite_manager:new:domains:label' => "Whitelist Email Domains",
	'subsite_manager:new:domains:description' => "Enter a comma separated list of email domains that will be used for the verification of a membership request.<br />Syntax: domain.com, sub.other_domain.nl",

	'subsite_manager:new:has_public_acl:label' => "Public content available",
	'subsite_manager:new:has_public_acl:description' => "Select 'Yes' if the Subsite has the option to share content with the public. Selecting 'No' will disable this option.",

	'subsite_manager:new:js:name' => "Please check the site name",
	'subsite_manager:new:js:url' => "Please check the site URL",
	'subsite_manager:new:js:domains' => "Please check the e-mail domains",
	'subsite_manager:new:js:admin' => "Continue without adding subsite admins?",
	
	// Subsite - update
	'subsite_manager:subsites:update:remove_icon' => "Remove the current icon",
	
	'subsite_manager:update:limit_admins:label' => "Limit main site admins",
	'subsite_manager:update:limit_admins:description' => "Main site admins can only join based on the same rules as normal users.",

	'subsite_manager:subsites:update:disable_htmlawed:label' => "Disable HTML filtering for admins",
	'subsite_manager:subsites:update:disable_htmlawed' => "Disable HTML filtering for admin to allow more specialized HTML",
	'subsite_manager:subsites:update:disable_htmlawed:description' => "WARNING: when checked admins can inject any HTML code. This could currupt your site",
	
	'subsite_manager:subsites:update:enable_frontpage_indexing:label' => "Indexing",
	'subsite_manager:subsites:update:enable_frontpage_indexing' => "Enable indexing of the frontpage by search bots",
		
	// Subsite - admins
	'subsite_manager:subsites:admins:main_admins' => "Main administrators",
	'subsite_manager:subsites:admins:subsite_admins' => "Subsite administrators",
	'subsite_manager:subsites:admins:subsite_admins:none' => "No administrators found",
	
	// Subsites - Import
	'subsite_manager:import:step1:description' => "Upload a CSV to import users from. If the users can't be found they will be created, otherwise they will be added to this (Sub)site.<br />
Some rules for the CSV file:<br />
<ul>
<li>No head line</li>
<li>Column delimiter => ; (semi-column)</li>
<li>Text delimiter = \" (double quote)</li>
</ul>
In Step 2 you can select which columns contain which data.",
	'subsite_manager:import:step1:file' => "Please select the CSV file",
	
	'subsite_manager:import:step2:description' => "In this step you have to tell us which column of the CSV holds which information. Fields (in the pulldown) marked with a * are required to create an account.<br />
If no password field is defined a random password will be generated.<br />
If no username field is defined a username will be created from the e-mail address.<br /><br />
If a user can be found by the email address no account will be created, but the user will be notified if he/she joins a new subsite.<br />
The newly created users will receive a welcome message with their username and password and you personal note (optional)",
	'subsite_manager:import:step2:select_field' => "Select Profile field",
	'subsite_manager:import:step2:message' => "Add a personal message to the imported users (optional)",
	'subsite_manager:import:step2:error' => "Something went wrond during CSV upload, please go back to %sstep 1%s",

	// Subsite - Import - Notify
	'subsite_manager:import:notify:new:subject' => "An account was created for you on %s",
	'subsite_manager:import:notify:new:message' => "Hi %s,
	
an account was created for you on %s, to visit the site please click on this link:
%s

%s

In order to login you need to user the following credentials:
Username: %s
Password: %s",
		
	'subsite_manager:import:notify:existing:subject' => "You have been joined to the site %s",
	'subsite_manager:import:notify:existing:message' => "Hi %s,

you have been joined to the site %s, to visit the site please click on this link:
%s

%s",
	// subsites - join - request approval
	'subsite_manager:subsites:join:request' => "Request membership",
	'subsite_manager:subsites:join:request_approval:description' => "Membership to this Subsite has to be approved by the Subsite administrators. To request membership please fill out the form below.",
	'subsite_manager:subsites:join:request_approval:reason' => "Why do you wish to join (optional)?",
	
	// subsite - join - validate email domain
	'subsite_manager:subsites:join:validate_domain' => "E-mail validation for membership",
	'subsite_manager:subsites:join:validate_domain:error:domains' => "Membership for this domain is not configured correctly",
	'subsite_manager:subsites:join:validate_domain:description' => "In order for you to join this subsite you need to validate an e-mail address on one of the domains configured for this subsite. Below you'll find a list of the supported domains. Fill in an e-mail address in the form that is on one of the domains to receive a validation link.",
	'subsite_manager:subsites:join:validate_domain:email_subdomain' => 'Your e-mail address needs to end with: <b>%1$s</b> (example: yourname@sub<b>.%1$s</b>)',
	'subsite_manager:subsites:join:validate_domain:email_domain' => "Your e-mail address needs to be something like: yourname<b>@%s</b>",
	
	'subsite_manager:subsites:join:validate_domain:subject' => "E-mail validation for %s",
	'subsite_manager:subsites:join:validate_domain:message' => "Dear %s,
you requested to join %s.

This requires e-mail domain validation. Please click this link to join:
%s

You can't reply to this email.",

	// subsites - join - join_domain
	'subsite_manager:subsites:join_domain:error:code' => "The provided code is invalid",
	'subsite_manager:subsites:join_domain:error:add_user' => "An unknown error occured while add you to the site",
	'subsite_manager:subsites:join_domain:success' => "You have successfully joined %s",
	
	// subsites - join - invitation
	'subsite_manager:procedures:subsites:invitation:error:code' => "The provided code is invalid",
	'subsite_manager:procedures:subsites:invitation:error:invitation' => "There is no invitation for you",
	'subsite_manager:procedures:subsites:invitation:error:add_user' => "An unknown error occured while add you to the site",
	'subsite_manager:procedures:subsites:invitation:success' => "You have successfully joined %s",

	// subsites - join - missing profile fields
	'subsite_manager:subsites:join:missing_fields' => "Please complete the registration form below",
	'subsite_manager:subsites:join:missing_fields:title' => "Profile information",
	'subsite_manager:subsites:join:missing_fields:description' => "Before you can join this subsite you need to fill out this registration form. Fields marked with * are mandatory.",
	
	// subsites - no access
	'subsite_manager:subsites:no_access:title' => "No access",
	'subsite_manager:subsites:no_access' => "You don't have access to content on this Subsite",
	'subsite_manager:subsites:no_access:can_join' => "To gain access to content on this Subsite, please click the 'Join' button below.",
	'subsite_manager:subsites:no_access:pending' => "You have already requested to join this Subsite, an administrator has to approve your request before you can proceed.",
	'subsite_manager:subsites:no_access:approval' => "Membership to this Subsite requires approval by an administrator, to request membership click the button below.",
	'subsite_manager:subsites:no_access:domain' => "Membership to this Subsite is limited to certain e-mail domains, to validate an e-mail domain please click on the button below.",
	'subsite_manager:subsites:no_access:invitation' => "Membership to this Subsite is limited to users who have been invited by an administrator.",
	
	'subsite_manager:subsites:no_access:groups:description' => "You can go directly to one of your groups without requesting membership to the site.",
	
	// subsites - membership requests
	'subsite_manager:subsite:request_membership:request:subject' => "A new membership request for %s was received",
	'subsite_manager:subsite:request_membership:request:message' => "Hi,
		
%s requested to join your Subsite.

%s

You can manage request here:
%s",
	
	'subsite_manager:subsites:membership:description' => "Here you can find all the users who have requested membership to this Subsite. You can eighter approve or decline their request, the user will be notified about eighter action.",
	'subsite_manager:subsites:membership:list:title' => "Pending membership requests",
	
	'subsite_manager:request_membership:approve:confirm' => "Are you sure you wish to allow this user access to your Subsite",
	'subsite_manager:request_membership:decline:confirm' => "Are you sure you wish to decline this user access to your Subsite",
	
	'subsite_manager:subsite:request_membership:decline:subject' => "Your membership request was declined",
	'subsite_manager:subsite:request_membership:decline:message' => "Hi,

Your membership request to join the Subsite %s was declined.

%s

This is an automated message, please don't reply",
		
	'subsite_manager:subsite:request_membership:approve:subject' => "Your membership request was approved",
	'subsite_manager:subsite:request_membership:approve:message' => "Hi,
	
Your membership request to join the Subsite %s was approved. You can now access this Subsite.
%s

This is an automated message, please don't reply",
	
	// subsites - remove user
	'subsite_manager:subsites:remove_user' => "Remove user from this site",

	// subsites - invite
	'subsite_manager:invite:description' => "Invite users to your Subsite. You can invite users by name, username, email address or CSV upload.",
	
	'subsite_manager:invite:users:label' => "Find users",
	'subsite_manager:invite:users:description' => "Please input the name, username or email address of the user you wish to invite. If an existsing user is found that user will be invited. If no user could be found and you entered a valid email address the user will receive an invitation mail with a special link to register your this site.",
	
	'subsite_manager:invite:csv:tab' => "CSV upload",
	'subsite_manager:invite:csv:label' => "Please select an CSV file (there will be a step 2)",
	'subsite_manager:invite:csv:description' => "The CSV file has the following requirements:<br />
	- no header<br />
	- column delimiter: ; (semicolon)<br />
	- text delimiter: \" (double quote)",
	
	'subsite_manager:invite:message:label' => "A personal message to the users (optional)",

	'subsite_manager:subsite_admin:invite:new_user:subject' => "You have been invited to join %s",
	'subsite_manager:subsite_admin:invite:new_user:message' => "Hello,
%s has invited you to join the subsite %s of %s.

%s

To accept the invitation you have to create an account using this link:
%s

Once you have created an account you will automaticly join the subsite.

This is an automated message, please don't reply",
	'subsite_manager:subsite_admin:invite:existing_user:subject' => "You have been invited to join %s",
	'subsite_manager:subsite_admin:invite:existing_user:message' => "Dear %s,
%s has invited you to join the subsite %s.

%s

To accept the invitation click on this link:
%s

This is an automated message, please don't reply",
	
	// subsites - invite (csv step 2)
	'subsite_manager:invite_csv:column:label' => "Please tell us which column hold which information",
	'subsite_manager:invite_csv:column:description' => "We need to know which column hold the email addresses of the users. Optionaly you can select a column which holds the name of the user.",
	'subsite_manager:invite_csv:column:select' => "Please select a column",
	
	// subsites - invitations
	'subsite_manager:invitations:description' => "Here you can find a list of all the invitations that where send out to the different users",
	
	'subsite_manager:invitations:users:title' => "Invitations to existing users",
	'subsite_manager:invitations:email:title' => "Invitations using email addresses",
	'subsite_manager:invitations:none:title' => "No invitations found",
	
	'subsite_manager:invitations:revoke:confirm' => "Are you sure you wish to revoke this invitation",
	
	// manage plugins
	'subsite_manager:subsite:plugins:description' => "Here you can manage which plugins are active/available on the Subsite. Please be carefull! %sI know what i'm doing%s.",
	'subsite_manager:subsite:plugins:enable_everywhere' => "Enabled everywhere",
	'subsite_manager:subsite:plugins:enabled_for_new_subsites' => "Enable when creating new Subsite",
	'subsite_manager:subsite:plugins:fallback_to_main_settings' => "Fallback to main site plugin settings",
	'subsite_manager:subsite:plugins:use_global_usersettings' => "Use global user plugin settings",
	'subsite_manager:subsite:plugins:subsite_default_manageable' => "Manageable on Subsite",
	
	// plugin list
	'subsite_manager:plugins:description' => "Use this list to manage the different plugin on this site.",
	'subsite_manager:plugins:simple:switch' => "Switch to advanced mode",
	'subsite_manager:plugins:advanced:switch' => "Switch to simple mode",
	
	// plugin super admin options
	'subsite_manager:plugins:advanced:description' => "Advanced plugin options, BE CAREFULL!!!!",
	'subsite_manager:plugins:advanced:disable_all' => "Disable on all Subsites",
	'subsite_manager:plugins:advanced:disable_all:title' => "Disable all instances of this plugin on every Subsite",
	'subsite_manager:plugins:advanced:disable_all:confirm' => "Are you sure you wish to disable this plugin on all Subsites?",
	
	// plugin fallback (on subsite)
	'subsite_manager:plugins:subsite:fallback' => "Reset to default settings",
	'subsite_manager:plugins:subsite:fallback:confirm' => "Are you sure you wish to go back to the default plugin settings? This can't be undone!",
	
	// subsites - widget
	'subsite_manager:widgets:subsites:title' => "Subsites",
	'subsite_manager:widgets:subsites:description' => "Show a list of Subsite, choose between latest Subsites and featured Subsites",
	'subsite_manager:widgets:subsite:show_featured' => "Only show featured Subsites",
	
	// subsites - no member
	'subsite_manager:profile:no_member:title' => "%s has an account, but is no member of this subsite",
	'subsite_manager:profile:no_member:header' => "has an account, but is no member of this subsite",
	'subsite_manager:profile:no_member:visit' => "To view his/her profile you can go %shere%s",
	
	// create users on subsites
	'subsite_manager:create:user:request_membership' => "This user registered on this subsite, however approval was required. Please check if this user can join your subsite.",
	'subsite_manager:create:user:message:request_membership' => "You have successfully created an account, however membership to this subsite requires approval by the administrator. This request has been made for you.",
	'subsite_manager:create:user:message:domain' => "You have successfully created an account, however membership is limited to certain e-mail domain. Your e-mail address didn't match the requirements. Please login and request membership with a valid email.",

	// first login - check invitations
	'subsite_manager:login:subsite:join' => "You have joined the subsite %s",
	'subsite_manager:login:subsite:join:notify:message' => "Hi %s,

You've been added to the Subsite %s, because you were invited.

To visit the Subsite follow this link:
%s",

	// group invite
	'subsite_manager:group:invite:add_users_to_site' => "Add the new users to this subsite",

	// main profile fields on subsite
	'subsite_manager:profile_fields:global:title' => "Manage global profile fields",
	'subsite_manager:profile_fields:global:description' => "The following profile fields are managed from the main level. You can configure a limited set of options on these fields.",
	
	// bulk actions
	// newest users
	'subsite_manager:newest_users:bulk_action:check_all' => "Select all",
	'subsite_manager:newest_users:bulk_action:created' => "Account created: %s",
	
	// actions
	// general errors
	'subsite_manager:action:error:input' => "Invalid input, please check the form",
	'subsite_manager:action:error:on_subsite' => "This action can't be performed on a Subsite",
	'subsite_manager:action:error:subsite_only' => "This action can only be performed on a Subsite",
	'subsite_manager:action:subsites:join:error:missing_fields' => "Please check the mandatory profile fields",
	
	// Subsites - new
	'subsite_manager:action:subsites:new:error:last_save' => "Error while saving the Subsite data",
	'subsite_manager:action:subsites:new:error:validate:url:duplicate' => "The provided URL is already in use",
	'subsite_manager:action:subsites:new:error:validate:url:invalid' => "The provided URL is invalid, please check for http:// or https:// and a trailing /",
	'subsite_manager:action:subsites:new:success' => "The Subsite was created successfully",
	
	// Subsites - import - step 1
	'subsite_manager:action:import:step1:error:csv' => "Please upload a CSV file",
	'subsite_manager:action:import:step1:error:file' => "The uploaded file couldn't be opened, please try again",
	'subsite_manager:action:import:step1:error:content' => "The uploaded file is not a valid CSV file",
	'subsite_manager:action:import:step1:success' => "Successfully uploaded the CSV file, please continue with step 2",

	// Subsites - import - step 2
	'subsite_manager:action:import:step2:error:columns' => "Please select which columns to import",
	'subsite_manager:action:import:step2:error:required_fields' => "Please select at least a column for Displayname and one for Email",
	'subsite_manager:action:import:step2:error:csv_file' => "The uploaded CSV file couldn't be opened, please try uploading it again in Step 1",
	'subsite_manager:action:import:step2:error:unknown' => "No rows in the CSV were proccessed, please try again",
	'subsite_manager:action:import:step2:success' => "Succesfully imported %s users (%s user already on the system, they joined this site)",

	// Subsites - add user
	'subsite_manager:action:subsites:add_user:error:join' => "%s is not allowed to join %s",
	'subsite_manager:action:subsites:add_user:error:add' => "An error occured while adding %s to %s",
	'subsite_manager:action:subsites:add_user:success' => "%s successfully joined %s",
	
	// Subsites - remvove user
	'subsite_manager:action:subsites:remove_user:error:remove' => "An unknown error occured while removing %s from %s",
	'subsite_manager:action:subsites:remove_user:success' => "%s successfully left %s",
	
	// subsites - join - request approval
	'subsite_manager:actions:subsites:join:request_approval:error:request' => "An error occured while requesting membership, please try again",
	'subsite_manager:actions:subsites:join:request_approval:success' => "You've successfully requested membership. You'll be notified when it has been approved.",
	
	// subsites - join - validate domain
	'subsite_manager:actions:subsites:join:validate_domain:error:domain' => "",
	'subsite_manager:actions:subsites:join:validate_domain:success' => "",
	
	// manage plugins
	'subsite_manager:action:plugins:manage:success' => "New plugin settings saved successfully",
	
	// plugin fallback
	'subsite_manager:actions:plugins:fallback:success' => "This plugin will now use the default settings",
	'subsite_manager:actions:plugins:fallback:error:unset' => "An unknown error occured while revering to default settings",
	'subsite_manager:actions:plugins:fallback:error:subsite' => "The action can only be performed on a Subsite",
	
	// subsite - toggle featured
	'subsite_manager:actions:subsites:toggle_featured:success:featured' => "Subsite successfully featured",
	'subsite_manager:actions:subsites:toggle_featured:success:unfeatured' => "Subsite successfully unfeatured",
	
	// plugins - disable all
	'subsite_manager:actions:plugins:disable_all:error:no_active' => "This plugin is not active on any Subsite",
	'subsite_manager:actions:plugins:disable_all:error:some_errors' => "Not all plugins could be disabled",
	'subsite_manager:actions:plugins:disable_all:success' => "The plugin has successfully been disabled on all Subsites",
	
	// subsites - membership - approve
	'subsite_manager:action:subsite:membership:approve:success' => "Membership request approved successfully, the user has been notified",
	'subsite_manager:action:subsite:membership:approve:error' => "Something went wrong while approving the membership request",
	
	// subsites - membership - approve
	'subsite_manager:action:subsite:membership:decline:success' => "Membership request declined successfully, the user has been notified",
	'subsite_manager:action:subsite:membership:decline:error' => "Something went wrong while declining the membership request",
	
	// subsites - invite - revoke
	'subsite_manager:action:invite:revoke:error' => "There was an error while revoking the invitation, please try again",
	'subsite_manager:action:invite:revoke:success' => "The invitation was successfully revoked",
	
	// subsites - invite - csv
	'subsite_manager:action:invite:csv:error:content' => "No CSV was available to invite users from, please upload it again",
	'subsite_manager:action:invite:csv:error:email_column' => "Please provide an email column",
	'subsite_manager:action:invite:csv:error:email_column:invalid' => "The provided email column is invalid",
	'subsite_manager:action:invite:csv:error:csv' => "There was an error while opening the CSV file",
	'subsite_manager:action:invite:csv:error:users' => "No (new) users were invited",
	'subsite_manager:action:invite:csv:success' => "Successfully invited %s users",
	
	// subsites - invite - invite
	'subsite_manager:action:invite:error:users' => "No users were invited",
	'subsite_manager:action:invite:success:csv' => "Please continue with step 2 of the invitation proccess",
	'subsite_manager:action:invite:success:users' => "Successfully invited %s users",
	'subsite_manager:action:invite:success:users_csv' => "Successfully invited %s users, continue with step 2 of the invitation proccess",
	
	// main profile fields configuration
	'subsite_manager:action:main_profile_fields:success' => "Configuration saved successfully",
	'subsite_manager:action:main_profile_fields:error:save' => "Something went wrong while saving the configuration",
	
	// bulk actions
	'subsite_manager:action:bulk_action:dummy' => "Please select a bulk action to perform",
	'' => "",
);
	
add_translation("en", $english);
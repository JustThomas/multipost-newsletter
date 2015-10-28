=== Multipost Newsletter ===
Contributors: Inpsyde, Bueltge, nullbyte, hughwillfayle
Donate Link: http://inpsyde.com
Tags: Newsletter, News, Mailing
Requires at least: 3.2
Tested up to: 3.4.2
Stable tag: 1.1
License: GPLv3

Integrate full featured newsletter tool with WordPress.

== Description ==

The Multi Post Newsletter is a simple plugin, which provides to link several posts to a newsletter. This procedure is similar to the categories. Within the flexible configuration and templating, you're able to set the newsletters appearance to your requirement.

It is available in:
<ul>
 <li>German</li>
 <li>English</li>
</ul>

Please give us feedback, contribute and file technical bugs on 
[GitHub Repo](https://github.com/inpsyde/Multipost-Newsletter).

= Main Features =

* Custom Post Type and Custom Field Support
* Prepare Newsletter Dialog
 * to sort the posts
 * Individual Settings for each Post
 * Preview for the templates
* Templating for Text-Mail
* Mass-Mailing-Feature
* Widget for Subscription
* User Subscribing via Double-Opt-In (german law conform)

= Pro Features =

* User Group Management
* HTML Newsletters
* Automatic PDF generation
* SMTP-Support
* Support for Article Pictures
* Import Subscribers

**[Purchase Pro-Version](http://marketpress.com/product/multipost-newsletter/)**

== Installation ==

1. Upload the folder `multipost-newsletter` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the Newsletter through the Newsletter Menu
4. Use the help tabs for more information

= Upgrade Notice =

Due to a complete rewrite we are not able to offer a backward-compatibility to Version 0.5.5.5. Please test on a local system before you install Version 1.+!

== Screenshots ==

1. Widget for unregistered users
2. Widget for logged in users
3. General settings page
4. SMTP settings page
5. General Template
6. HTML Template
7. Text Template
8. PDF Template
9. Spacer Templates
10. User Settings
11. User Overview
12. Groups
13. Editions Overview
14. Manage Edtions in Bulk Edit
15. Prepare Dialog to sort the posts
16. Preview of the HTML-Newsletter
17. Create newsletter dialog
18. Finished Newsletter

== Frequently Asked Questions ==

Please give us feedback, contribute and file technical bugs on 
[GitHub Repository](https://github.com/inpsyde/Multipost-Newsletter) or use the [Support Forums](http://wordpress.org/support/plugin/multi-post-newsletter).

== Upgrade Notice ==

Due to a complete rewrite we are not able to offer a backward-compatibility to Version 0.5.5.5. Please test on a local system before you install Version 1.0!

== Changelog ==

= 1.1 =
* Feature: Changed the user subscriptions including widget etc.
* Feature: Added Double Opt-In Form to the widget
* Feature: Added activation and unsubscription theme templates for double opt-in
* Feature: Choose more than one user group on the submission
* Feature: Added Import dialog to import subscribers
* Feature: Introduced %UNSUBSCRIBELINK% for the templates
* Feature: Added option not to generate the PDF
* Feature: Introduced Subject Areas
* Feature: New Standard Template
* Feature: Added Documentation
* Code & UI: Improved sending process
* Code & UI: Improved prepare process
* Code: Minor changes due to some performance issues
* Code: Changed Menu Position
* Code: Several Codex Optimizations
* Code: Changed request methods to core standards
* Code: Added local logo
* Code: Fixed some minor warnings and notices

= 1.0.6 =
* Code: Updated Auto Updater
* Version: Version Hopping due to some Auto Update issues

= 1.0.4 =
* Code: Fixed fatal error on widget

= 1.0.3 =
* Code: Fixed several Warnings and Notices
* Code: Fixed SMTP Connection
* Code: Fixed article remove from an edition
* Code: Added target="_blank" at PDF-Link in Preview

= 1.0.2 =
* Code: Fixed Warning in Prepare Dialog
* Code: Fixed Warning in PDF Preview
* Code: Fixed Warning while check for the pro folder
* Code: Fixed Warning in Auto Update
* Code: Fixed Warning in Template
* Code: Fixed unnecessary type in user and widget

= 1.0.1 =
* Code: Fixed several Notices
* Code: Fixed Charset problems
* Code: Fixed phpmailer recipient problems
* Code: Fixed bug in checkboxes on post preview

= 1.0 =
* License: Changed to GPLv3
* Version: Hopped Version due to too many changes
* Code: Complete new Codebase
* Feature: Automattic PDF Export
* Feature: Templating for Text-Mail
* Feature: Support Article Pictures
* Feature: Improved UI
* Feature: One Default Template
* Feature: Add Custom Fields to the template
* Feature: Fixed translations
* Feature: Mass-Mailing-Feature
* Feature: Support for custom post types
* Feature: Widget for Subscription
* Feature: Send mail to specific reciptions (groups)

= 0.5.5.5 =
* Feature: URL-Shortener is.gd for text-mail
* Code: Several Fixes for the text-mail
* Code: Language Check-Ups

= 0.5.5.4 =
* Feature: Frontend-Templating

= 0.5.5.3 =
* Code: Fixed broken full page view
* Code: Fixed a "\" bug in the template options

= 0.5.5.2 =
* Code: Styling
* Code: Fixed a ' bug in the template options

= 0.5.5.1 =
* Code: Fixed annoying Bug in "sending the main newsletter"

= 0.5.5 =
* Code: Fixed Doubled Mail Problem
* Code: Fixed Encoding Issues

= 0.5.4 =
* Code: Fixed limit of posts
* Code: Fixed Encoding Issues

= 0.5.3 =
* Code: Several fixes through the display of the newsletter
* Code: Added a new tag %LINK_NAME%

= 0.5.2 =
* Code: Merged txt and html loop
* Code: Fixed Boundary and Headers
* Code: Some usability fixes
* Code: Fix in title/link conflict

= 0.5.1 =
* Code: Fix in Contents ( Text-Version )
* Code: %LINK% now just gives the permalink
* Code: Language Fixes

= 0.5 =
* Version: Hopping because of many changes
* Code: New improved Code
* Code: Several Checks
* Misc: Custom Collumn "Newsletter" in Article Overview
* Feature: text/plain mail
* Feature: New and better option pages
* Feature: New workflow to generate a Newsletter
* Feature: AJAX-functionalities for sortable Articles

= 0.2 =
* Feature: Option to choose excerpt
* Feature: Option to choose display contents
* Feature: Added %LINK% in Template
* Code: Fixed Capabilities
* Code: Fixed i18n
* Code: Styling
 
= 0.1.1 =
* Code: Clean Ups, added comments and some other stuff

= 0.1 =
* Initial Release
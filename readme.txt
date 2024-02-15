=== PLUGIN_TITLE ===
Contributors: iworks
Donate link: https://ko-fi.com/iworks?utm_source=simple-revision-control&utm_medium=readme-donate
Tags: revision, revisions, post, page, custom post type
Requires at least: PLUGIN_REQUIRES_WORDPRESS
Tested up to: PLUGIN_TESTED_WORDPRESS
Stable tag: PLUGIN_VERSION
Requires PHP: PLUGIN_REQUIRES_PHP
License: GPLv3 or later

PLUGIN_TAGLINE

You can also delete all unwanted revisions at all.

If you are able to edit `wp-config.php` file or you want to have only one number of revisions for all post types, then please consider do not install this plugin, instead just edit `wp-config.php` file.

== Description ==

Simple Revision Control is a plugin for WordPress which gives the user simple control over the Revision functionality.

= Asset image =

[My Filing Cabinet](http://www.flickr.com/photos/theenmoy/8078124630/) by [Theen Moy](http://www.flickr.com/photos/theenmoy/) Creative Common

== Installation ==

1. Upload Simple Revision Control to your plugins directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure numer of revision using Settings -> Revisions

== Frequently Asked Questions ==

= Should I edit `wp-config.php` file instead install this plugin? =

If you are able to edit `wp-config.php` file or you want to have only one number of revisions for all post types, then please consider do not install this plugin, instead just edit `wp-config.php` file.

= Why this plugin is better than the entry in `wp-config.php` file? =

You can set up a number of revisions by post type. Setting in `wp-config.php` file allows setting only one number of revisions for all post types. You can add od remove revisions support per post types. At least you can delete unwanted revisions.

== Screenshots ==

1. Set up with post types.
1. Info screen with ability to delete revisions by post type.
1. Entries list screen with ability to delete revisions by element.

== Changelog ==

= 2.2.0 (2024-02-15) =
* The security of deleting previous versions has been improved, normal entries should not be deleted from now on.
* The [iWorks Rate](https://github.com/iworks/iworks-rate) module has been updated to 2.1.6.
* The [iWorks Options](https://github.com/iworks/wordpress-options-class) module has been updated to 2.9.1.

= 2.1.3 (2023-11-20) =
* The [iWorks Rate](https://github.com/iworks/iworks-rate) module has been updated to 2.1.3.
* The [iWorks Options](https://github.com/iworks/wordpress-options-class) module has been updated to 2.8.8.

= 2.1.2 (2022-04-07) =
* Fixed settings link on plugins page. Props for [tanohex](https://wordpress.org/support/users/tanohex/).

= 2.1.1 (2022-03-29) =
* Fixed warning on settings screen. Props for [tanohex](https://wordpress.org/support/users/tanohex/).

= 2.1.0 (2022-03-01) =
* Added ability to remove revisions - depend on configuration.
* Added ability to turn on revisions for post types without revisions.
* Added filter `iworks_plugin_get_options` to allow to change plugin base configuration.
* Improved UX on configuration screen.
* Updated iWorks Options to 2.8.1.
* Updated iWorks Rate to 2.1.0.

= 2.0.0 (2022-02-02) =
- Refactored whole plugin.
* Updated iWorks Options to 2.8.0.
* Updated iWorks Rate to 2.0.6.

= 1.3.4 (2021-06-16) =
* Updated iWorks Options to 2.6.9.

= 1.3.3 (2017-05-19) =
* Fixed translation incompatibility.

= 1.3.2 (2017-04-27) =
* Fixed class name error.

= 1.3.1 (2017-04-27) =
* Fixed localization.
* Upgraded iworks_options() class to 2.6.0.

= 1.3 (2015-03-25)=
* IMPROVEMENT: Added Swedish translation, created by Peter from WP Daily Themes

= 1.2 =
* CHECK: compatibility with WordPress 3.7

= 1.1 =
* IMPROVEMENT: added revision control to custom post types

= 1.0 =
* INIT: first revision

== Upgrade Notice ==


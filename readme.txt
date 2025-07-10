=== PLUGIN_TITLE ===
Contributors: iworks
Donate link: https://ko-fi.com/iworks?utm_source=simple-revision-control&utm_medium=readme-donate
Tags: PLUGIN_TAGS
Requires at least: PLUGIN_REQUIRES_WORDPRESS
Tested up to: PLUGIN_TESTED_WORDPRESS
Stable tag: PLUGIN_VERSION
Requires PHP: PLUGIN_REQUIRES_PHP
License: GPLv3 or later

PLUGIN_TAGLINE

== Description ==

PLUGIN_TAGLINE


***Simple Revision Control*** is a WordPress plugin that provides an easy way to manage and limit the number of post revisions stored for each post type on your site. Unlike editing the `wp-config.php` file—which only allows setting a single revision limit for all post types—this plugin lets you specify a different revision limit for each post type individually. You can also enable or disable revision support per post type and delete unwanted revisions directly from the plugin’s settings.

= Key features =

* Set custom revision limits for each post type, rather than a global limit.
* Enable or disable revisions for post types that don’t support them by default.
* Delete all unwanted revisions with a single click to keep your database clean.
* Simple setup and configuration via the WordPress admin under Settings → Revisions.
* No coding required—ideal for users who prefer not to edit `wp-config.php`.

This plugin is especially useful for site owners who want granular control over revision storage to optimize database performance and avoid unnecessary clutter, without needing to modify core WordPress files.

= Asset image =

[My Filing Cabinet](http://www.flickr.com/photos/theenmoy/8078124630/) by [Theen Moy](http://www.flickr.com/photos/theenmoy/) Creative Common

= GitHub =

The Simple Revision Control plugin is available also on [GitHub](https://github.com/iworks/simple-revision-control).

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

= 2.2.4 - 2025-07-10 =
* **Dependencies**: Updated the [iWorks Options](https://github.com/iworks/wordpress-options-class) module to version 3.0.7 and the [iWorks Rate](https://github.com/iworks/iworks-rate) module to version 2.3.1.


= 2.2.3 (2025-05-08) =
* Added escaping functions in multiple areas for improved security.
* Fixed `Short Description` section in `readme.txt` was too long.
* Updated the [iWorks Options](https://github.com/iworks/wordpress-options-class) module to version 3.0.0.

= 2.2.2 (2025-03-13) =
* Updated the [iWorks Options](https://github.com/iworks/wordpress-options-class) module to version 2.9.8.

= 2.2.1 (2025-02-21) =
* The build process has been improved.
* Updated the [iWorks Rate](https://github.com/iworks/iworks-rate) module to version 2.2.3.
* Updated the [iWorks Options](https://github.com/iworks/wordpress-options-class) module to version 2.9.5.

= 2.2.0 (2024-02-15) =
* The security of deleting previous versions has been improved, normal entries should not be deleted from now on.
* Updated the [iWorks Rate](https://github.com/iworks/iworks-rate) module to version 2.1.6.
* Updated the [iWorks Options](https://github.com/iworks/wordpress-options-class) module to version 2.9.1.

= 2.1.3 (2023-11-20) =
* Updated the [iWorks Rate](https://github.com/iworks/iworks-rate) module to version 2.1.3.
* Updated the [iWorks Options](https://github.com/iworks/wordpress-options-class) module to version 2.8.8.

= 2.1.2 (2022-04-07) =
* Fixed settings link on plugins page. Props for [tanohex](https://wordpress.org/support/users/tanohex/).

= 2.1.1 (2022-03-29) =
* Fixed warning on settings screen. Props for [tanohex](https://wordpress.org/support/users/tanohex/).

= 2.1.0 (2022-03-01) =
* Added ability to remove revisions - depend on configuration.
* Added ability to turn on revisions for post types without revisions.
* Added filter `iworks_plugin_get_options` to allow to change plugin base configuration.
* Improved UX on configuration screen.
* Updated the [iWorks Options](https://github.com/iworks/wordpress-options-class) module to version 2.8.1.
* Updated the [iWorks Rate](https://github.com/iworks/iworks-rate) module to version 2.1.0.

= 2.0.0 (2022-02-02) =
- Refactored whole plugin.
* Updated the [iWorks Options](https://github.com/iworks/wordpress-options-class) module to version 2.8.0.
* Updated the [iWorks Rate](https://github.com/iworks/iworks-rate) module to version 2.0.6.

= 1.3.4 (2021-06-16) =
* Updated the [iWorks Options](https://github.com/iworks/wordpress-options-class) module to version 2.6.9.

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


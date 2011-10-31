=== Plugin Name ===
Contributors: kucrut
Donate link: http://kucrut.org/
Tags: theme-options, plugin-options, settings, options, term-meta, category-meta, post-meta, custom-fields, user-meta, attachment
Requires at least: 3.2.1
Tested up to: 3.3-beta2
Stable tag: 2.2

Easily create plugin/theme settings page, custom fields metaboxes and term meta settings.

== Description ==

With this plugin, you can easily create a settings/options page for you theme or plugin. You can also create metaboxes for post custom fields, and add some metadata to the terms.

== Installation ==

1. Use standard WordPress plugin installation or upload the `kc-settings` directory to your `wp-content/plugins` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Use the settings builder in Settings &raquo; KC Settings to create a setting or read the documentation (kc-settings-inc/doc/readme.html) on how you can add your options.

== Frequently Asked Questions ==

= How do I add my options? =

Please find the readme.html file inside the plugin directory for the documentation.

== Screenshots ==
1. Settings Builder
2. Theme/Plugin settings
3. Term settings (add new)
4. Term settings (edit)
5. Post settings

== Changelog ==

= 2.2 =
* Code Improvements
* Metaboxes for plugin/theme settings page

= 2.1.2 =
* Deprecate kcs_select() and kc_dropdown_options()
* Bug fixes and WordPress 3.2 support

= 2.1.1 =
* Backward compatibility for WP 3.2.1

= 2.1 =
* Fixed file query, no using get_posts() to avoid messing up the main query
* Fixed 'special' field type in settings builder
* Settings Builder help tab
* Sortable file items
* Pass field name attribute to special's callback
* Special field type created by the builder can now accept callback (strings or function name)
* Settings builder improvements

= 2.0.1 =
* PHP 5.2 support

= 2.0 =
* New field type: File
* New feature: Setting Builder
* Bug fixes
* Improvements

= 1.3.9 =
* Fixed scripts and styles loader
* Cleanups

= 1.3.8 =
* Load scripts and styles only when _really_ needed, props dinesh4monto
* Better JS for handling multiinput

= 1.3.7 =
* New input type: date, supports both old (jQuery UI Datepicker) and new browsers (HTML5 forms)
* Better symlink handling (Linux hosts)
* No more inline styles and javascripts

= 1.3.6 =
* Fixed input ID bug in post meta field
* Fixed bug in post metabox title
* New feature: File type checking on attachment metadata
* Pass the whole $args and $db_value to the field's custom callback function, props Tan

= 1.3.5 =
* New feature: Attachment metadata
* Bug fixes
* Enhancements
= 1.3.1 =
* Fixed bug in user profile form display
= 1.3 =
* New feature: User meta
* File/directory structure compatibility with mu-plugins
* Documentation updates

= 1.2.1 =
* Bug fixes & cleanups
* Screenshots

= 1.2 =
* Bug Fixes

= 1.1 =
* Added support for custom callback for displaying section
* Exclude inline editing
* Added filter before & after setting field
* Added custom attribute support for 'input' and 'textarea'
* Changed default select value to ''
* Set default menu locations
* Added support for top-level menu
* Fixed screen_icon handler
* Small fixes

= 1.0 =
* First release


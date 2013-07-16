=== KC Settings ===
Contributors: kucrut
Donate link: http://kucrut.org/
Tags: theme-options, plugin-options, settings, options, term-meta, category-meta, post-meta, custom-fields, user-meta, attachment, theme-customizer
Requires at least: 3.5
Tested up to: 3.5.2
Stable tag: 2.8.5

Easily create plugin/theme settings pages, custom fields metaboxes and term/user metadata settings.

== Description ==

*Version 2.8 only supports WordPress 3.5+*

With this plugin, you can easily create a settings/options page for you theme or plugin. You can also create metaboxes for post custom fields, and add some metadata to the terms.

If you have created your settings manually prior to version 2.5 of this plugin, please review the sample files and make the needed changes.

== Installation ==

1. Use standard WordPress plugin installation or upload the `kc-settings` directory to your `wp-content/plugins` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Use the settings builder in Settings &raquo; KC Settings to create a setting array or read the documentation (kc-settings-inc/doc/readme.html) on how you can add your options.
4. For other installation methods (version 2.7.6+), please read the 'Installation' section of the documentation.

== Frequently Asked Questions ==

= How do I create my settings? =

Go to Settings &raquo; KC Settings and create it there. You can also export the settings from there.

For complete howto, please find the readme.html file inside the plugin directory or [view it online](http://kucrut.github.com/kc-settings/).

== Screenshots ==

1. Settings Builder
2. Theme/Plugin settings
3. Term settings (add new)
4. Term settings (edit)
5. Post settings
6. Theme customizer

== Changelog ==

= 2.8.5 =
* Improve media field type and add it to the builder

= 2.8.4 =
* Fix media field conflict in post editing screens

= 2.8.3 =
* New field type: media (WP 3.5 media modal dialog)

= 2.8.2 =
* Fixed radio field, props if6was9design

= 2.8.1 =
* Fix bug in backend scripts and styles loading, props Grawl

= 2.8 =
* Only for WordPress 3.5+
* Supports excluding metadata section/field on certain post mime types
* Bug fixes

= 2.7.8 =
* New feature: Menu item metadata

= 2.7.7 =
* Follow core convention on minified JS and CSS files
* Only load JS on demand

= 2.7.6 =
* Make plugin bundle-able with other plugins/themes
* Make theme customizer detachable

= 2.7.5 =
* Finalized theme customizer, now supports realtime preview
* New feature: KC Settings Builder exporter

= 2.7.4 =
* New (experimental) feature: Theme customizer

= 2.7.3 =
* Cleanup JS and Builder
* New options helper class: kcSettings_options_cb
* Fields that need options now accept functions/class methods

= 2.7.2 =
* Fix bug in multiinput fields

= 2.7.1 =
* Fix JS bug in the Builder

= 2.7 =
* New field type: 'editor'
* Settings builder improvement

= 2.6.8 =
* Support multiinput sub-fields in the builder

= 2.6.7 =
* Improve multiinput field type. It now supports unlimited number of string-based sub-fields, see documentation for a sample. Support in the builder will be added in the near future ;)
* Many under-the-hood fixes and improvements

= 2.6.6 =
* Fix and improve file uploads. It's now possible to insert/select newly uploaded files

= 2.6.5 =
* Fix bug in metadata saving
* Cleanup addtag form after successful term creation via ajax
* Support scripts and styles debugging via KC_SETTINGS_SNS_DEBUG constant (need to be set before init hook, priority 99)
* Update Modernizr to version 2.5.3

= 2.6.4 =
* JS Fixes
* New options helper: kcSettings_options::$sidebars
* Improved custom section callback and only allow it for plugin/theme settings
* Bug fixes and small enhancements

= 2.6.3 =
* Fixed settings collection and plugin setting validation

= 2.6.2 =
* Fixed ajax in single file field

= 2.6.1 =
* Cosmetic fixes for single file fields

= 2.6 =
* Remove support for WordPress < 3.3
* New file field mode: Single file
* New options helpers to make your life easier :)
* New HTML5 input types
* Bunch of under-the-hood fixes and improvements

= 2.5.5 =
* Fixed and simplified kcs_update_meta(), props Tan
* Fixed sample setting for file type, props Tan

= 2.5.4 =
* Moved kcSettings call to priority 99 in init's hook

= 2.5.3 =
* Fixed post metadata, props 8manos

= 2.5.2 =
* Fixed file query limit
* Fixed kcSettings::_lock()

= 2.5.1 =
* Fixed kcSettings::get_data(), props rndbit

= 2.5 =
* Tons of fixes and enhancements
* More metaboxes goodies for post/plugin/theme settings
* Contextual help support for plugin/theme settings
* And much, much more :)

= 2.2 =
* Code Improvements
* Metaboxes for plugin/theme settings page
* New field type: color

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


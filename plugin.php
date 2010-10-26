<?php

/*
Plugin name: KC Settings
Plugin URI: http://kucrut.org/2010/10/kc-settings/
Description: Easily create plugin/theme settings page and custom fields metaboxes
Version: 1.0
Author: Dzikri Aziz
Author URI: http://kucrut.org/
License: GPL v2

*/

class kcSettings {

	function __construct() {
		add_action( 'init', array($this, 'init') );
	}

	function init() {
		# i18n
		load_plugin_textdomain( 'kc-settings', null, 'kc-settings' );
		# Load functions
		require( dirname(__FILE__) . '/functions.php' );

		# 1. Plugin / Theme Settings
		$this->plugin_settings_init();
		# 2. Custom Fields / Post Meta
		$this->cfields_init();
		# 3. Terms Meta
		$this->termmeta_init();
	}

	function plugin_settings_init() {
		$plugin_groups = apply_filters( 'kc_plugin_settings', array() );
		if ( !is_array($plugin_groups) || empty( $plugin_groups ) )
			return;

		require( dirname(__FILE__) . '/plugin-theme-settings.php' );
		# Loop through the array and pass each item to kcThemeSettings
		foreach ( $plugin_groups as $group ) {
			if ( !is_array($group) || empty($group) )
				return;

			$do = new kcThemeSettings;
			$do->init( $group );
		}
	}


	function cfields_init() {
		$cfields = kc_meta( 'post' );
		if ( !is_array($cfields) || empty( $cfields ) )
			return;

		require( dirname(__FILE__) . '/post-settings.php' );
		$do = new kcPostSettings;
		$do->init( $cfields );
	}


	function termmeta_init() {
		$term_options = kc_meta( 'term' );
		if ( !is_array($term_options) || empty($term_options) )
			return;

		# Add every term fields to its taxonomy add & edit screen
		foreach ( $term_options as $tax => $sections ) {
			add_action( "{$tax}_add_form_fields", 'kc_term_meta_field' );
			add_action( "{$tax}_edit_form_fields", 'kc_term_meta_field', 10, 2 );
		}
		# Also add the saving routine
		add_action( 'edit_term', 'kc_save_termmeta', 10, 3);
		add_action( 'create_term', 'kc_save_termmeta', 10, 3);
	}


}

$kcSettings = new kcSettings;

# Create and define termmeta table
register_activation_hook( __FILE__, 'kc_termmeta_table_create' );
add_action( 'init', 'kc_termmeta_table_set' );

?>
<?php

/*
Plugin name: KC Settings
Plugin URI: http://kucrut.org/2010/10/kc-settings/
Description: Easily create plugin/theme settings page, custom fields metaboxes, term meta and user meta settings.
Version: 1.3.5
Author: Dzikri Aziz
Author URI: http://kucrut.org/
License: GPL v2

*/

class kcSettings {

	function __construct() {
		$this->inc_path = dirname(__FILE__) . '/kc-settings-inc';

		add_action( 'init', array($this, 'init'), 11 );
	}

	function init() {
		# i18n
		$locale = get_locale();
		$mo = "{$this->inc_path}/languages/kc-settings-{$locale}.mo";
		if ( is_readable($mo) )
			load_textdomain( 'kc-settings', "{$this->inc_path}/languages/kc-settings-{$locale}.mo" );

		require_once( "{$this->inc_path}/metadata.php" );

		# 1. Plugin / Theme Settings
		$this->plugin_settings_init();
		# 2. Custom Fields / Post Meta
		$this->postmeta_init();
		# 3. Terms Meta
		$this->termmeta_init();
		# 4. User Meta
		$this->usermeta_init();

		# Script & style
		add_action( 'admin_print_scripts', array($this, 'admin_print_scripts') );
	}

	function plugin_settings_init() {
		$plugin_groups = apply_filters( 'kc_plugin_settings', array() );
		if ( !is_array($plugin_groups) || empty( $plugin_groups ) )
			return;

		require_once( "{$this->inc_path}/theme.php" );
		# Loop through the array and pass each item to kcThemeSettings
		foreach ( $plugin_groups as $group ) {
			if ( !is_array($group) || empty($group) )
				return;

			$do = new kcThemeSettings;
			$do->init( $group );

			require_once( "{$this->inc_path}/helper.php" );
			require_once( "{$this->inc_path}/form.php" );
		}
	}


	function postmeta_init() {
		$cfields = kc_meta( 'post' );
		if ( !is_array($cfields) || empty( $cfields ) )
			return;

		require_once( "{$this->inc_path}/post.php" );
		require_once( "{$this->inc_path}/helper.php" );
		require_once( "{$this->inc_path}/form.php" );
		$do = new kcPostSettings;
		$do->init( $cfields );
	}


	function termmeta_init() {
		$term_options = kc_meta( 'term' );
		if ( !is_array($term_options) || empty($term_options) )
			return;

		require_once( "{$this->inc_path}/term.php" );
		require_once( "{$this->inc_path}/helper.php" );
		require_once( "{$this->inc_path}/form.php" );

		# Create & set termmeta table
		add_action( 'init', 'kc_termmeta_table', 12 );

		# Add every term fields to its taxonomy add & edit screen
		foreach ( $term_options as $tax => $sections ) {
			add_action( "{$tax}_add_form_fields", 'kc_term_meta_field' );
			add_action( "{$tax}_edit_form_fields", 'kc_term_meta_field', 20, 2 );
		}
		# Also add the saving routine
		add_action( 'edit_term', 'kc_save_termmeta', 10, 3);
		add_action( 'create_term', 'kc_save_termmeta', 10, 3);
	}


	function usermeta_init() {
		$user_options = kc_meta( 'user' );
		if ( !is_array($user_options) || empty($user_options) )
			return;

		require_once( "{$this->inc_path}/user.php" );
		require_once( "{$this->inc_path}/helper.php" );
		require_once( "{$this->inc_path}/form.php" );

		# Display additional fields in user profile page
		add_action( 'show_user_profile', 'kc_user_meta_field' );
		add_action( 'edit_user_profile', 'kc_user_meta_field' );

		# Save the additional data
		add_action( 'personal_options_update', 'kc_user_meta_save' );
		add_action( 'edit_user_profile_update', 'kc_user_meta_save' );
	}


	function admin_print_scripts() {
		#wp_print_scripts( array('jquery', 'media-upload', 'thickbox') );
		wp_print_scripts( array('jquery') );
		require_once( $this->inc_path . '/scripts.php' );
	}
}

$kcSettings = new kcSettings;

?>

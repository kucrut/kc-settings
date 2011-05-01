<?php

/*
Plugin name: KC Settings
Plugin URI: http://kucrut.org/2010/10/kc-settings/
Description: Easily create plugin/theme settings page, custom fields metaboxes, term meta and user meta settings.
Version: 1.3.9
Author: Dzikri Aziz
Author URI: http://kucrut.org/
License: GPL v2

*/

class kcSettings {
	var $prefix;
	var $version;
	var $kcs_pages;

	function __construct() {
		$this->prefix = 'kc-settings';
		$this->version = '1.3.7';
		$this->kcs_pages = array();
		$this->paths();
		$this->actions_n_filters();
	}


	function paths() {
		$paths = array();
		$inc_prefix = "/{$this->prefix}-inc";
		$fname = basename( __FILE__ );

		if ( file_exists(WPMU_PLUGIN_DIR . "/{$fname}") )
			$file = WPMU_PLUGIN_DIR . "/{$fname}";
		else
			$file = WP_PLUGIN_DIR . "/{$this->prefix}/{$fname}";

		$paths['file']		= $file;
		$paths['inc']			= dirname( $file ) . $inc_prefix;
		$url							= plugins_url( '', $file );
		$paths['url']			= $url;
		$paths['scripts']	= "{$url}{$inc_prefix}/scripts";
		$paths['styles']	= "{$url}{$inc_prefix}/styles";

		$this->paths = $paths;
	}


	function actions_n_filters() {
		add_action( 'init', array(&$this, 'init'), 11 );
		add_action( 'admin_head', array(&$this, 'scripts_n_styles') );

		# Development
		//add_action( 'admin_footer', array(&$this, 'dev') );
		//require_once( $this->paths['inc'] . '/doc/sample/__theme_settings.php' );
		//require_once( $this->paths['inc'] . '/doc/sample/settings2.php' );
		//require_once( $this->paths['inc'] . '/doc/sample/__term_settings.php' );
		//require_once( $this->paths['inc'] . '/doc/sample/__post_settings.php' );
		//require_once( $this->paths['inc'] . '/doc/sample/__post_settings2.php' );
		//require_once( $this->paths['inc'] . '/doc/sample/__user_settings.php' );
	}


	function init() {
		# i18n
		$locale = get_locale();
		$mo_file = "{$this->paths['inc']}/languages/{$this->prefix}-{$locale}.mo";
		if ( is_readable($mo_file) )
			load_textdomain( $this->prefix, $mo_file );

		require_once( "{$this->paths['inc']}/metadata.php" );

		# 1. Plugin / Theme Settings
		$this->plugin_settings_init();
		# 2. Custom Fields / Post Meta
		$this->postmeta_init();
		# 3. Terms Meta
		$this->termmeta_init();
		# 4. User Meta
		$this->usermeta_init();
	}

	function plugin_settings_init() {
		$plugin_groups = apply_filters( 'kc_plugin_settings', array() );
		if ( !is_array($plugin_groups) || empty( $plugin_groups ) )
			return;

		require_once( "{$this->paths['inc']}/theme.php" );
		# Loop through the array and pass each item to kcThemeSettings
		foreach ( $plugin_groups as $group ) {
			if ( !is_array($group) || empty($group) )
				return;

			if ( !isset($plugin_settings) )
				$plugin_settings = true;

			$do = new kcThemeSettings;
			$do->init( $group );

		}
		if ( isset($plugin_settings) && $plugin_settings ) {
			$this->kcs_pages[] = 'kc-settings-';
			require_once( "{$this->paths['inc']}/helper.php" );
			require_once( "{$this->paths['inc']}/form.php" );
		}
	}


	function postmeta_init() {
		$cfields = kc_meta( 'post' );
		if ( !is_array($cfields) || empty( $cfields ) )
			return;

		$this->kcs_pages[] = 'post';
		require_once( "{$this->paths['inc']}/post.php" );
		require_once( "{$this->paths['inc']}/helper.php" );
		require_once( "{$this->paths['inc']}/form.php" );
		$do = new kcPostSettings;
		$do->init( $cfields );
	}


	function termmeta_init() {
		$term_options = kc_meta( 'term' );
		if ( !is_array($term_options) || empty($term_options) )
			return;

		$this->kcs_pages[] = 'edit-tags';
		require_once( "{$this->paths['inc']}/term.php" );
		require_once( "{$this->paths['inc']}/helper.php" );
		require_once( "{$this->paths['inc']}/form.php" );

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

		$this->kcs_pages[] = 'profile';
		require_once( "{$this->paths['inc']}/user.php" );
		require_once( "{$this->paths['inc']}/helper.php" );
		require_once( "{$this->paths['inc']}/form.php" );

		# Display additional fields in user profile page
		add_action( 'show_user_profile', 'kc_user_meta_field' );
		add_action( 'edit_user_profile', 'kc_user_meta_field' );

		# Save the additional data
		add_action( 'personal_options_update', 'kc_user_meta_save' );
		add_action( 'edit_user_profile_update', 'kc_user_meta_save' );
	}


	function scripts_n_styles() {
		if ( empty($this->kcs_pages) )
			return;

		#/*
		global $hook_suffix;
		foreach ( $this->kcs_pages as $current_page ) {
			$kcspage = strpos($hook_suffix, $current_page);
			if ( $kcspage !== false ) {
				wp_register_script( 'modernizr', "{$this->paths['scripts']}/modernizr-1.7.min.js", false, '1.7', true );
				wp_register_script( 'jquery-ui-datepicker', "{$this->paths['scripts']}/jquery.ui.datepicker.min.js", array('jquery-ui-core'), '1.8.11', true );
				wp_register_script( $this->prefix, "{$this->paths['scripts']}/{$this->prefix}.js", array('modernizr', 'jquery-ui-datepicker'), $this->version, true );
				wp_print_scripts( $this->prefix );

				wp_register_style( $this->prefix, "{$this->paths['styles']}/{$this->prefix}.css", false, $this->version );
				wp_print_styles( $this->prefix );

				break;
			}
		}
	}


	function dev() {
		echo '<pre>';

		echo '</pre>';
	}
}

$kcSettings = new kcSettings;

?>

<?php

/*
Plugin name: KC Settings
Plugin URI: http://kucrut.org/2010/10/kc-settings/
Description: Easily create plugin/theme settings page, custom fields metaboxes, term meta and user meta settings.
Version: 2.1
Author: Dzikri Aziz
Author URI: http://kucrut.org/
License: GPL v2

*/

class kcSettings {
	var $prefix;
	var $version;
	var $kcs_pages;
	var $kcsb;

	function __construct() {
		$this->prefix = 'kc-settings';
		$this->version = '1.3.7';
		$this->kcs_pages = array();
		$this->paths();
		$this->actions_n_filters();
	}


	function paths() {
		$paths = array();
		$inc_prefix = "/kc-settings-inc";
		$fname = basename( __FILE__ );

		if ( file_exists(WPMU_PLUGIN_DIR . "/{$fname}") )
			$file = WPMU_PLUGIN_DIR . "/{$fname}";
		else
			$file = WP_PLUGIN_DIR . "/kc-settings/{$fname}";

		$paths['file']		= $file;
		$paths['inc']			= dirname( $file ) . $inc_prefix;
		$url							= plugins_url( '', $file );
		$paths['url']			= $url;
		$paths['scripts']	= "{$url}{$inc_prefix}/scripts";
		$paths['styles']	= "{$url}{$inc_prefix}/styles";

		$this->paths = $paths;
	}


	function actions_n_filters() {
		require_once( "{$this->paths['inc']}/helper.php" );
		$this->kcsb = kcsb_settings_bootsrap();

		add_action( 'init', array(&$this, 'init'), 11 );
		if ( is_admin() ) {
			add_action( 'init', array(&$this, 'builder'), 20 );
		}
		add_action( 'admin_init', array(&$this, 'scripts_n_styles_register') );
		add_action( 'admin_head', array(&$this, 'scripts_n_styles_print') );
		add_action( 'admin_head-media-upload-popup', array(&$this, 'scripts_n_styles_uploader') );

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
		$mo_file = "{$this->paths['inc']}/languages/kc-settings-{$locale}.mo";
		if ( is_readable($mo_file) )
			load_textdomain( 'kc-settings', $mo_file );

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
		$plugin_groups = apply_filters( 'kc_plugin_settings', $this->kcsb['plugin'] );
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
			require_once( "{$this->paths['inc']}/form.php" );
		}
	}


	function postmeta_init() {
		$post_options = kc_meta( 'post', $this->kcsb['post'] );
		if ( !is_array($post_options) || empty( $post_options ) )
			return;

		$this->kcs_pages[] = 'post';
		if ( array_key_exists('attachment', $post_options) )
			$this->kcs_pages[] = 'media';
		require_once( "{$this->paths['inc']}/post.php" );
		require_once( "{$this->paths['inc']}/form.php" );
		$do = new kcPostSettings( $post_options );
	}


	function termmeta_init() {
		$term_options = kc_meta( 'term', $this->kcsb['term'] );
		if ( !is_array($term_options) || empty($term_options) )
			return;

		$this->kcs_pages[] = 'edit-tags';
		require_once( "{$this->paths['inc']}/term.php" );
		require_once( "{$this->paths['inc']}/form.php" );
		$do = new kcTermSettings( $term_options );
	}


	function usermeta_init() {
		$user_options = kc_meta( 'user', $this->kcsb['user'] );
		if ( !is_array($user_options) || empty($user_options) )
			return;

		$this->kcs_pages[] = 'profile';
		require_once( "{$this->paths['inc']}/user.php" );
		require_once( "{$this->paths['inc']}/form.php" );
		$do = new kcUserSettings( $user_options );
	}


	function scripts_n_styles_register() {
		# Common
		wp_register_script( 'modernizr', "{$this->paths['scripts']}/modernizr.2.0.6.min.js", false, '2.0.6', true );
		wp_register_script( 'kc-rowclone', "{$this->paths['scripts']}/kc-rowclone.js", array('jquery'), $this->version, true );
		wp_register_script( 'kc-settings', "{$this->paths['scripts']}/kc-settings.js", array('modernizr', 'jquery-ui-sortable', 'jquery-ui-datepicker', 'kc-rowclone', 'media-upload', 'thickbox'), $this->version, true );
		wp_register_style( 'kc-settings', "{$this->paths['styles']}/kc-settings.css", array('thickbox'), $this->version );

		wp_register_script( "kc-settings-upload", "{$this->paths['scripts']}/upload.js", array('jquery'), $this->version );

		# Builder script & style
		wp_register_script( 'kcsb', "{$this->paths['scripts']}/kcsb.js", array('jquery-ui-sortable'), $this->version, true );
		wp_register_style( 'kcsb', "{$this->paths['styles']}/kcsb.css", false, $this->version );
	}


	function js_globals() {
		$kcFiles_vars = array(
			'text' => array(
				'head' => __( 'KC Settings', 'kc-settings' ),
				'empty' => __( 'Please upload some files and then go back to this tab.', 'kc-settings' ),
				'checkAll' => __( 'Select all files', 'kc-settings' ),
				'clear' => __( 'Clear selections', 'kc-settings' ),
				'invert' => __( 'Invert selection', 'kc-settings' ),
				'addFiles' => __( 'Add files to collection', 'kc-settings' )
			)
		); ?>
<script>
	var kcFiles = <?php echo json_encode( $kcFiles_vars ) ?>;
</script>
	<?php }


	function scripts_n_styles_print() {
		if ( empty($this->kcs_pages) )
			return;

		global $hook_suffix;
		foreach ( $this->kcs_pages as $current_page ) {
			$kcspage = strpos($hook_suffix, $current_page);
			if ( $kcspage !== false ) {
				$this->js_globals();
				wp_enqueue_script( 'kc-settings' );
				wp_print_styles( 'kc-settings' );
				break;
			}
		}
	}


	function scripts_n_styles_uploader() {
		if ( isset($_REQUEST['kcsf']) && $_REQUEST['kcsf'] ) {
			wp_enqueue_script( "kc-settings-upload" );
			wp_print_styles( 'kc-settings' );
		}
	}


	function builder() {
		$properties = array(
			'version'		=> $this->version,
			'paths'			=> $this->paths,
			'settings'	=> $this->kcsb
		);
		require_once( "{$this->paths['inc']}/builder.php" );
		$kcsBuilder = new kcsBuilder;
		$kcsBuilder->init( $properties );
	}


	function dev() {
		echo '<pre>';

		global $current_screen;
		print_r( get_current_screen() );

		echo '</pre>';
	}
}

$kcSettings = new kcSettings;

?>

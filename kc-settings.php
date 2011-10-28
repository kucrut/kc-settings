<?php

/*
Plugin name: KC Settings
Plugin URI: http://kucrut.org/2010/10/kc-settings/
Description: Easily create plugin/theme settings page, custom fields metaboxes, term meta and user meta settings.
Version: 2.1.2
Author: Dzikri Aziz
Author URI: http://kucrut.org/
License: GPL v2

*/

class kcSettings {
	public static $version	= '2.1.2';
	public static $pages		= array();
	public static $paths;
	public static $settings;


	public static function init() {
		self::$paths = self::_paths();

		# Include samples (for development)
		self::_samples();

		# Get all settings
		self::$settings	= self::_bootsrap_settings();
		require_once( self::$paths['inc'].'/form.php' );
		require_once( self::$paths['inc'].'/helper.php' );
		require_once( self::$paths['inc'].'/_deprecated.php' );

		$ok = false;
		foreach ( array('plugin', 'post', 'term', 'user') as $type ) {
			if ( empty(self::$settings[$type]) )
				continue;

			$ok = true;
			call_user_func( array(__CLASS__, "_{$type}_init"), self::$settings[$type] );
		}

		self::_locale();
		self::_admin_actions();

		# Builder
		require_once( self::$paths['inc'].'/builder.php' );
		$kcsBuilder = new kcsBuilder;
		$kcsBuilder->init(array(
			'version'		=> self::$version,
			'paths'			=> self::$paths,
			'settings'	=> self::$settings
		));
	}


	/*
	 * Set plugin paths
	 */
	private static function _paths() {
		$paths = array();
		$inc_prefix = '/kc-settings-inc';
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

		return $paths;
	}


	/*
	 * Get all settings
	 */
	private static function _bootsrap_settings() {
		$kcsb = get_option( 'kcsb' );
		$settings = array(
			'_raw'		=> $kcsb,
			'plugin'	=> array(),
			'post'		=> array(),
			'term'		=> array(),
			'user'		=> array(),
			'_ids'		=> array(
				'settings'	=> array(),
				'sections'	=> array(),
				'fields'		=> array()
			)
		);

		if ( is_array($kcsb) && !empty($kcsb) ) {
			foreach ( $kcsb as $setting ) {
				$sID = $setting['id'];
				$settings['_ids']['settings'][] = $sID;
				$type = $setting['type'];
				$sections = array();

				foreach ( $setting['sections'] as $section ) {
					$settings['_ids']['sections'][] = $section['id'];
					$fields = array();
					foreach ( $section['fields'] as $field ) {
						$settings['_ids']['fields'][] = $field['id'];
						if ( in_array($field['type'], array('checkbox', 'radio', 'select', 'multiselect')) ) {
							$options = array();
							foreach ( $field['options'] as $option ) {
								$options[$option['key']] = $option['label'];
							}
							$field['options'] = $options;
						}
						$fields[$field['id']] = $field;
					}
					$section['fields'] = $fields;
					$sections[$section['id']] = $section;
				}

				$setting['options'] = $sections;
				unset ( $setting['sections'] );

				if ( $type == 'plugin' ) {
					$settings[$type][$sID] = $setting;
				}
				elseif ( $type == 'user' ) {
					$settings[$type][$sID] = array( $setting['options'] );
				}
				else {
					$object = ( $type == 'post') ? $setting['post_type'] : $setting['taxonomy'];
					$settings[$type][$sID] = array($object => $setting['options']);
				}
			}
		}

		// Add the others (from themes/plugins )
		$settings['plugin'] = apply_filters( 'kc_plugin_settings', $settings['plugin'] );
		$settings['post']	= self::_bootsrap_meta( 'post', $settings['post'] );
		$settings['term'] = self::_bootsrap_meta( 'term', $settings['term'] );
		$settings['user'] = self::_bootsrap_meta( 'user', $settings['user'] );

		return $settings;
	}


	/**
	 * Bootstrap Metadata
	 *
	 * Merge all the custom fields set by themes/plugins. Also rebuild the array.
	 *
	 * @param string $meta_type post|term|user Which meta?
	 * @return array $nu Our valid post/term/user meta options array
	 *
	 */

	private static function _bootsrap_meta( $meta_type, $others = array() ) {
		$old = apply_filters( "kc_{$meta_type}_settings", $others );
		if ( !is_array($old) || empty($old) )
			return array();

		$nu = array();
		foreach ( $old as $group ) {
			if ( !is_array($group) || empty($group) )
				return;

			# Loop through each taxonomy/post type to see if it has sections
			foreach ( $group as $object => $sections ) {
				# Skip this taxonomy/post type if it has no sections
				if ( !is_array($sections) )
					continue;

				# Loop through each section to see if it has fields
				foreach ( $sections as $section )
					# Skip the section if it doesn't have them
					if ( !isset($section['fields']) || !is_array($section['fields']) || empty($section['fields']) )
						continue 2;

				# Rebuild the array
				if ( isset($nu[$object]) )
					foreach ( $sections as $sk => $sv )
						$nu[$object][$sk] = $sv;
				else
					$nu[$object] = $sections;
			}
		}

		return $nu;
	}


	private static function _plugin_init( $settings ) {
		$plugin_settings = false;
		require_once( self::$paths['inc'].'/plugin.php' );

		# Loop through the array and pass each item to kcThemeSettings
		foreach ( $settings as $group ) {
			if ( !is_array($group) || empty($group) )
				continue;

			$plugin_settings = true;
			$do = new kcThemeSettings;
			$do->init( $group );

		}
		if ( $plugin_settings ) {
			self::$pages[] = 'kc-settings-';
		}
	}


	private static function _post_init( $settings ) {
		self::$pages[] = 'post';
		if ( array_key_exists('attachment', $settings) )
			self::$pages[] = 'media';
		require_once( self::$paths['inc'].'/post.php' );
		$do = new kcPostSettings( $settings );
	}


	private static function _term_init( $settings ) {
		self::$pages[] = 'edit-tags';
		require_once( self::$paths['inc'].'/term.php' );
		$do = new kcTermSettings( $settings );
	}


	private static function _user_init( $settings ) {
		self::$pages[] = 'profile';
		require_once( self::$paths['inc'].'/user.php' );
		$do = new kcUserSettings( $settings );
	}


	private static function _locale() {
		$mo_file = self::$paths['inc'].'/languages/kc-settings-'.get_locale().'.mo';
		if ( is_readable($mo_file) )
			load_textdomain( 'kc-settings', $mo_file );
	}


	private static function _admin_actions() {
		add_action( 'admin_init', array(__CLASS__, '_sns_register') );
		add_action( 'admin_enqueue_scripts', array(__CLASS__, '_sns_enqueue') );

		//add_action( 'admin_footer', array(__CLASS__, '_dev') );
	}


	public static function _sns_register() {
		# WP < 3.3
		if ( version_compare(get_bloginfo('version'), '3.3', '<') )
			wp_register_script( 'jquery-ui-datepicker', self::$paths['scripts']."/jquery.ui.datepicker.min.js", array('jquery-ui-core'), '1.8.11', true );

		# Common
		wp_register_script( 'modernizr',		self::$paths['scripts'].'/modernizr.2.0.6.min.js', false, '2.0.6', true );
		wp_register_script( 'kc-rowclone',	self::$paths['scripts'].'/kc-rowclone.js', array('jquery'), self::$version, true );
		wp_register_script( 'kc-settings',	self::$paths['scripts'].'/kc-settings.js', array('modernizr', 'jquery-ui-sortable', 'jquery-ui-datepicker', 'kc-rowclone', 'media-upload', 'thickbox'), self::$version, true );
		wp_register_style( 'kc-settings',		self::$paths['styles'].'/kc-settings.css', array('thickbox'), self::$version );

		wp_register_script( "kc-settings-upload", self::$paths['scripts'].'/upload.js', array('jquery'), self::$version );

		# Builder
		wp_register_script( 'kcsb', self::$paths['scripts'].'/kcsb.js', array('jquery-ui-sortable'), self::$version, true );
		wp_register_style( 'kcsb', self::$paths['styles'].'/kcsb.css', false, self::$version );
	}


	public static function _sns_enqueue( $hook_suffix ) {
		if ( $hook_suffix == 'media-upload-popup' ) {
			wp_enqueue_script( "kc-settings-upload" );
			wp_enqueue_style( 'kc-settings' );
		}

		foreach ( self::$pages as $current_page ) {
			$kcspage = strpos($hook_suffix, $current_page);
			if ( $kcspage !== false ) {
				self::js_globals();
				wp_enqueue_script( 'kc-settings' );
				wp_enqueue_style( 'kc-settings' );
				break;
			}
		}
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


	private static function _samples() {
		//require_once( self::$paths['inc'] . '/doc/sample/__theme_settings.php' );
		//require_once( self::$paths['inc'] . '/doc/sample/__settings2.php' );
		//require_once( self::$paths['inc'] . '/doc/sample/__term_settings.php' );
		//require_once( self::$paths['inc'] . '/doc/sample/__post_settings.php' );
		//require_once( self::$paths['inc'] . '/doc/sample/__post_settings2.php' );
		require_once( self::$paths['inc'] . '/doc/sample/__user_settings.php' );
	}


	public static function _dev() {
		echo '<pre>';

		echo '</pre>';
	}
}

add_action( 'init', array('kcSettings', 'init'), 11 );

?>

<?php

/**
 * @package KC_Settings
 * @version 2.2
 */


/*
Plugin name: KC Settings
Plugin URI: http://kucrut.org/2010/10/kc-settings/
Description: Easily create plugin/theme settings page, custom fields metaboxes, term meta and user meta settings.
Version: 2.2
Author: Dzikri Aziz
Author URI: http://kucrut.org/
License: GPL v2

*/

class kcSettings {
	protected static $pdata = array(
		'version'		=> '2.2',
		'paths'			=> '',
		'settings'	=> array(),
		'kcsb'			=> array()
	);

	public static $data	= array(
		'pages'			=> array('media-upload-popup'),
		'help'			=> array()
	);


	public static function init() {
		$paths = self::_paths( __FILE__ );
		if ( !is_array($paths) )
			return false;

		self::$data['options'] = get_option('kc_settings');
		self::$pdata['paths'] = $paths;

		require_once "{$paths['inc']}/form.php";
		require_once "{$paths['inc']}/helper.php";
		require_once "{$paths['inc']}/_deprecated.php";

		# Setup termmeta table
		self::_setup_termmeta_table();

		# Register scripts n styles
		self::_sns_register();

		# Include samples (for development)
		//self::_samples( array('theme') );

		# Settings bootstrap error messages
		self::$data['messages'] = array(
			'no_prefix'					=> __( "One of your settings doesn't have <b>prefix</b> set. Therefore it has NOT been added.", 'kc-settings'),
			'no_menu_title'			=> __( "One of your settings doesn't have <b>menu title</b> set. Therefore it has NOT been added.", 'kc-settings'),
			'no_page_title'			=> __( "One of your settings doesn't have <b>page title</b> set. Therefore it has NOT been added.", 'kc-settings'),
			'no_options'				=> __( "One of your settings doesn't have <b>options</b> set. Therefore it has NOT been added.", 'kc-settings'),
			'section_no_fields'	=> __( "One of your settings' section doesn't have <b>fields</b> set. Therefore it has NOT been added.", 'kc-settings'),
			'section_no_id'			=> __( "One of your settings' sections doesn't have <b>ID</b> set. Therefore it has NOT been added.", 'kc-settings'),
			'section_no_title'	=> __( "One of your settings' sections doesn't have <b>title</b> set. Therefore it has NOT been added.", 'kc-settings'),
			'field_no_id'				=> __( "One of your fields doesn't have <b>ID</b> set. Therefore it has NOT been added.", 'kc-settings'),
			'field_no_title'		=> __( "One of your fields doesn't have <b>title</b> set. Therefore it has NOT been added.", 'kc-settings'),
			'field_no_type'			=> __( "One of your fields doesn't have <b>type</b> set. Therefore it has NOT been added.", 'kc-settings'),
			'field_no_opt'			=> __( "One of your fields doesn't have the required <b>options</b> set. Therefore it has NOT been added.", 'kc-settings'),
			'field_no_cb'				=> __( "One of your fields doesn't have the required <b>callback</b> set. Therefore it has NOT been added.", 'kc-settings')
		);

		# Get all settings
		self::_bootstrap_settings();

		# Backend-only stuff
		add_action( 'init', array(__CLASS__, '_admin_init'), 100 );
	}


	public static function _admin_init() {
		if ( !is_admin() )
			return;

		# i18n
		$mo_file = self::$pdata['paths']['inc'].'/languages/kc-settings-'.get_locale().'.mo';
		if ( is_readable($mo_file) )
			load_textdomain( 'kc-settings', $mo_file );

		# Register settings
		if ( self::$pdata['settings'] ) {
			foreach ( array_keys(self::$pdata['settings']) as $type ) {
				require_once self::$pdata['paths']['inc']."/{$type}.php";

				if ( $type == 'plugin' ) {
					foreach ( self::$pdata['settings']['plugin'] as $group )
						$do = new kcSettings_plugin( $group );
				}
				else {
					call_user_func( array("kcSettings_{$type}", 'init') );
				}
			}
		}

		# Lock
		add_filter( 'plugin_action_links', array(__CLASS__, '_lock'), 10, 4 );

		# Admin scripts n styles
		add_action( 'admin_enqueue_scripts', array(__CLASS__, '_sns_admin') );

		# Admin notices
		self::$data['notices'] = array();
		add_action( 'admin_notices', array(__CLASS__, '_admin_notice') );

		# Builder
		require_once( self::$pdata['paths']['inc'].'/builder.php' );
		kcSettings_builder::init();

		# Contextual help
		add_action( 'admin_head', array(__CLASS__, '_help') );

		# Dev stuff
		add_action( 'admin_footer', array(__CLASS__, '_dev') );
	}


	/**
	 * Set plugin paths
	 */
	public static function _paths( $file, $inc_suffix = '-inc' ) {
		if ( !file_exists($file) )
			return false;

		$file_info = pathinfo( $file );
		$file_info['parent'] = basename( $file_info['dirname'] );
		$locations = array(
			'plugins'			=> array( WP_PLUGIN_DIR, plugins_url() ),
			'mu-plugins'	=> array( WPMU_PLUGIN_DIR, WPMU_PLUGIN_URL ),
			'themes'			=> array( get_theme_root(), get_theme_root_uri() )
		);

		$valid = false;
		foreach ( $locations as $key => $loc ) {
			$dir = $loc[0];
			if ( $file_info['parent'] != $key )
			$dir .= "/{$file_info['parent']}";
			if ( file_exists($dir) && is_dir( $dir ) ) {
				$valid = true;
				break;
			}
		}
		if ( !$valid )
			return false;

		$paths = array();
		$url = "{$locations[$key][1]}/{$file_info['parent']}";
		$inc_prefix = "{$file_info['filename']}{$inc_suffix}";

		$paths['file']		= $file;
		$paths['p_file']	= kc_plugin_file( $file );
		$paths['inc']			= "{$dir}/{$inc_prefix}";
		$paths['url']			= $url;
		$paths['scripts']	= "{$url}/{$inc_prefix}/scripts";
		$paths['styles']	= "{$url}/{$inc_prefix}/styles";

		return $paths;
	}


	/**
	 * Get all settings
	 */
	private static function _bootstrap_settings() {
		$kcsb = array(
			'settings'	=> get_option( 'kcsb' ),
			'_ids'			=> array(
				'settings'	=> array(),
				'sections'	=> array(),
				'fields'		=> array()
			)
		);

		$settings = array(
			'plugin'	=> array(),
			'post'		=> array(),
			'term'		=> array(),
			'user'		=> array()
		);

		# Process settings from the builder
		if ( is_array($kcsb['settings']) && !empty($kcsb['settings']) ) {
			foreach ( $kcsb['settings'] as $setting ) {
				$sID = $setting['id'];
				$kcsb['_ids']['settings'][] = $sID;
				$type = $setting['type'];
				$sections = array();

				foreach ( $setting['sections'] as $section ) {
					$kcsb['_ids']['sections'][] = $section['id'];
					$fields = array();
					foreach ( $section['fields'] as $field ) {
						$kcsb['_ids']['fields'][] = $field['id'];
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

			self::$pdata['kcsb'] = $kcsb;
		}

		$settings = self::_validate_settings( $settings );
		if ( empty($settings) )
			return;

		self::$pdata['settings'] = $settings;
	}


	# Validate settings
	private static function _validate_settings( $settings ) {
		$nu = array();

		foreach ( $settings as $type => $groups ) {
			$groups = apply_filters( "kc_{$type}_settings", $settings[$type] );
			if ( empty($groups) ) {
				unset( $settings[$type] );
				continue;
			}

			foreach ( $groups as $g_idx => $group ) {
				if ( !is_array($group) || empty($group) ) {
					trigger_error( self::$data['messages']['no_options'] );
					unset( $groups[$g_idx] );
					continue;
				}

				if ( $type == 'plugin' ) {
					foreach ( array('prefix', 'menu_title', 'page_title', 'options') as $c ) {
						if ( !isset($group[$c]) || empty($group[$c]) || ($c == 'options' && !is_array($group[$c])) ) {
							trigger_error( self::$data['messages']["no_{$c}"] );
							unset( $groups[$g_idx] );
							continue 2;
						}

						# Set page display type
						if ( !isset($group['display']) )
							$group['display'] = 'plain';
						# Set the location
						if ( !isset($group['menu_location']) )
							$group['menu_location'] = 'options-general.php';

					}

					$group['options'] = self::_validate_sections( $group['options'] );
					if ( empty($group['options']) )
						$group = null;
				}

				elseif ( in_array($type, array('post', 'term', 'user')) ) {
					foreach ( $group as $obj => $sections ) {
						$group[$obj] = self::_validate_sections( $sections );
						if ( empty($group[$obj]) )
							$group = null;
					}
				}

				if ( !empty($group) )
					$nu[$type][$g_idx] = $group;
			}

		}

		foreach ( array('post', 'term', 'user') as $type ) {
			if ( isset($nu[$type]) )
				$nu[$type] = self::_bootstrap_meta( $nu[$type] );
		}

		return $nu;
	}


	# Validate each setting's section
	private static function _validate_sections( $sections ) {
		foreach ( $sections as $s_idx => $section ) {
			foreach ( array('id', 'title', 'fields') as $c ) {
				if ( !isset($section[$c]) || empty($section[$c]) || ($c == 'fields' && !is_array($section[$c])) ) {
					trigger_error( self::$data['messages']["section_no_{$c}"] );
					unset( $sections[$s_idx] );
					continue 2;
				}
			}

			foreach ( $section['fields'] as $f_idx => $field ) {
				unset( $section['fields'][$f_idx] );
				foreach ( array('id', 'title', 'type') as $c ) {
					if ( !isset($field[$c]) || empty($field[$c]) ) {
						trigger_error( self::$data['messages']["field_no_{$c}"] );
						continue 2;
					}
				}
				$section['fields'][$field['id']] = $field;
			}

			unset( $sections[$s_idx] );

			if ( !empty($section['fields']) )
				$sections[$section['id']] = $section;
		}
		return $sections;
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

	private static function _bootstrap_meta( $settings ) {
		$nu = array();

		foreach ( $settings as $group ) {
			foreach ( $group as $object => $sections ) {
				if ( isset($nu[$object]) )
					foreach ( $sections as $sk => $sv )
						$nu[$object][$sk] = $sv;
				else
					$nu[$object] = $sections;
			}
		}

		return $nu;
	}


	public static function add_help( $page, $helps ) {
		if ( !is_array($helps) || empty($helps) )
			return;

		foreach ( $helps as $idx => $help ) {
			foreach ( array('id', 'title', 'content') as $c ) {
				if ( !isset($help[$c]) || empty($help[$c]) ) {
					unset( $helps[$idx] );
					continue 2;
				}
			}
		}

		if ( !empty($helps) )
			self::$data['help'][$page] = $helps;
	}


	/**
	 * Register contextual help
	 */
	public static function _help() {
		global $hook_suffix;
		$screen = get_current_screen();
		if ( empty(self::$data['help']) || !isset(self::$data['help'][$hook_suffix]) || !is_object($screen) )
			return;

		$helps = self::$data['help'][$hook_suffix];
		# WP >= 3.3
		if ( method_exists($screen, 'add_help_tab') ) {
			foreach ( $helps as $help ) {
				if ( isset($help['sidebar']) && $help['sidebar'] )
					$screen->set_help_sidebar( $help['content'] );
				else
					$screen->add_help_tab( $help );
			}
		}
		# WP < 3.3
		else {
			foreach ( $helps as $help )
				add_contextual_help( $screen, "<h2>{$help['title']}</h2>\n{$help['content']}" );
		}
	}

	public static function _sns_register() {
		# WP < 3.3
		if ( version_compare(get_bloginfo('version'), '3.3', '<') )
			wp_register_script( 'jquery-ui-datepicker', self::$pdata['paths']['scripts']."/jquery.ui.datepicker.min.js", array('jquery-ui-core'), '1.8.11', true );

		# Common
		wp_register_script( 'modernizr',		self::$pdata['paths']['scripts'].'/modernizr.2.0.6.min.js', false, '2.0.6', true );
		wp_register_script( 'kc-settings',	self::$pdata['paths']['scripts'].'/kc-settings.js', array('modernizr', 'jquery-ui-sortable', 'jquery-ui-datepicker', 'media-upload', 'thickbox'), self::$pdata['version'], true );
		wp_register_style( 'kc-settings',		self::$pdata['paths']['styles'].'/kc-settings.css', array('thickbox'), self::$pdata['version'] );

		# Uploader
		wp_register_script( 'kc-settings-upload', self::$pdata['paths']['scripts'].'/upload.js', array('jquery'), self::$pdata['version'] );

		# Misc
		# Lightbox Me http://buckwilson.me/lightboxme/
		wp_register_script( 'jquery-lightbox_me', self::$pdata['paths']['scripts'].'/jquery.lightbox_me.js', array('jquery'), '2.3', true );
	}


	public static function _sns_admin( $hook_suffix ) {
		if ( !in_array($hook_suffix, self::$data['pages']) )
			return;

		wp_enqueue_style( 'kc-settings' );
		wp_enqueue_script( 'kc-settings' );

		if ( $hook_suffix != 'media-upload-popup' )
			self::_js_globals();

		if ( $hook_suffix == 'media-upload-popup' &&
				( (isset($_REQUEST['kcsf']) && $_REQUEST['kcsf']) || strpos( wp_get_referer(), 'kcsf') !== false ) )
			wp_enqueue_script( 'kc-settings-upload' );
	}


	private static function _js_globals() {
		$kcSettings_vars = array(
			'upload'	=> array(
				'text'	=> array(
					'head'			=> __( 'KC Settings', 'kc-settings' ),
					'empty'			=> __( 'Please upload some files and then go back to this tab.', 'kc-settings' ),
					'checkAll'	=> __( 'Select all files', 'kc-settings' ),
					'clear'			=> __( 'Clear selections', 'kc-settings' ),
					'invert'		=> __( 'Invert selection', 'kc-settings' ),
					'addFiles'	=> __( 'Add files to collection', 'kc-settings' ),
					'info'			=> __( 'Click the "Media Library" tab to insert files that are already upload, or, upload your files, close this popup window, then click the "add files" button again to go to the "Media Library" tab to insert the files you just uploaded.', 'kc-settings' )
				)
			),
			'_ids'		=> isset( self::$pdata['kcsb']['_ids'] ) ? self::$pdata['kcsb']['_ids'] : '',
			'paths'		=> self::$pdata['paths']
		);

		?>
<script type="text/javascript">
	//<![CDATA[
	var kcSettings = <?php echo json_encode( $kcSettings_vars ) ?>;
	//]]>
</script>
	<?php }


	private static function _samples( $types ) {
		foreach ( $types as $type )
			require_once self::$pdata['paths']['inc'] . "/doc/sample/{$type}.php";
	}


	public static function _admin_notice() {
		if ( empty(self::$data['notices']) )
			return;

		foreach ( self::$data['notices'] as $notice ) {
			if ( !$notice['message'] )
				continue;
			if ( !isset($notice['class']) )
				$notice['class'] = 'updated';

			echo "<div class='message {$notice['class']}'>\n\t{$notice['message']}\n</div>\n";
		}
	}


	/**
	 * Create and/or set termmeta table
	 *
	 * @credit Simple Term Meta
	 * @link http://www.cmurrayconsulting.com/software/wordpress-simple-term-meta/
	 *
	 */
	private static function _setup_termmeta_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'termmeta';

		if ( $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name ) {
			$sql = "CREATE TABLE {$table_name} (
				meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				term_id bigint(20) unsigned NOT NULL DEFAULT '0',
				meta_key varchar(255) DEFAULT NULL,
				meta_value longtext,
				PRIMARY KEY (meta_id),
				KEY term_id (term_id),
				KEY meta_key (meta_key)
			);";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}

		$wpdb->termmeta = $table_name;
	}


	/**
	 * Lock plugin when there are other plugins/themes using it
	 */
	public static function _lock( $actions, $plugin_file, $plugin_data, $context ) {
		if ( $plugin_file == self::$pdata['paths']['p_file'] && !empty(self::$data['options']['kids']) )
			unset( $actions['deactivate'] );

		return $actions;
	}


	# Activation
	public static function _activate() {
		$options = get_option( 'kc_settings' );
		if ( !$options )
			$options = array();

		if ( !isset($options['kids']) )
			$options['kids'] = array();
		update_option( 'kc_settings', $options );
	}

	# Deactivation
	public static function _deactivate() {
		# TODO: Anything else?
		//delete_option( 'kc_settings' );
	}


	public static function _dev() {
		echo '<pre>';
		//print_r( self::get_data( 'settings', 'term', 'category', 'sample_section', 'priority' ) );
		//print_r( get_option('kc_settings') );
		echo '</pre>';
	}


	public static function get_data() {
		$data = self::$pdata;
		if ( !func_num_args() )
			return $data;

		return kcs_array_multi_get_value( $data, func_get_args() );
	}
}
add_action( 'init', array('kcSettings', 'init') );


# A hack for symlinks
if ( !function_exists('kc_plugin_file') ) {
	function kc_plugin_file( $file ) {
		if ( !file_exists($file) )
			return $file;

		$file_info = pathinfo( $file );
		$parent = basename( $file_info['dirname'] );

		$file = ( $parent == $file_info['filename'] ) ? "{$parent}/{$file_info['basename']}" : $file_info['basename'];

		return $file;
	}
}


$plugin_file = kc_plugin_file( __FILE__ );
register_activation_hook( $plugin_file, array('kcSettings', '_activate') );
register_deactivation_hook( $plugin_file, array('kcSettings', '_deactivate') );

?>

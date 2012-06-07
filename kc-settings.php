<?php

/**
 * @package KC_Settings
 * @version 2.6.7
 */


/*
Plugin name: KC Settings
Plugin URI: http://kucrut.org/kc-settings/
Description: Easily create plugin/theme settings page, custom fields metaboxes, term meta and user meta settings.
Version: 2.6.7
Author: Dzikri Aziz
Author URI: http://kucrut.org/
License: GPL v2
Text Domain: kc-settings
*/

class kcSettings {
	protected static $data = array(
		'paths'    => '',
		'pages'    => array('media-upload-popup'),
		'help'     => array(),
		'messages' => array(),
		'settings' => array(),
		'defaults' => array(),
		'kcsb'     => array()
	);


	public static function setup() {
		$paths = self::_paths( __FILE__ );
		if ( !is_array($paths) )
			return false;

		self::$data['status'] = get_option( 'kc_settings' );
		self::$data['paths'] = $paths;

		require_once "{$paths['inc']}/form.php";
		require_once "{$paths['inc']}/helper.php";

		# i18n
		$mo_file = $paths['inc'].'/languages/kc-settings-'.get_locale().'.mo';
		if ( is_readable($mo_file) )
			load_textdomain( 'kc-settings', $mo_file );

		add_action( 'init', array(__CLASS__, 'init'), 99 );

		# Debug bar extension
		require_once "{$paths['inc']}/debug-bar-ext.php";
		add_filter( 'debug_bar_panels', array(__CLASS__, 'debug_bar_ext') );
	}


	public static function init() {
		# Setup termmeta table
		self::_setup_termmeta_table();

		# Register scripts n styles
		self::_sns_register();

		# Options helpers
		require_once self::$data['paths']['inc'] . '/options.php';
		kcSettings_options::init();

		# Include samples (for development)
		//self::_samples( array('01_plugin') );

		# Get all settings
		self::_bootstrap_settings();

		# Backend-only stuff
		if ( is_admin() )
			self::_admin_init();
	}


	public static function _admin_init() {
		# Register settings
		if ( self::$data['settings'] ) {
			foreach ( array_keys(self::$data['settings']) as $type ) {
				require_once self::$data['paths']['inc']."/{$type}.php";

				if ( $type == 'plugin' ) {
					foreach ( self::$data['settings']['plugin'] as $group )
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

		# Builder
		require_once( self::$data['paths']['inc'].'/builder.php' );
		kcSettings_builder::init();

		# Contextual help
		add_action( 'admin_head', array(__CLASS__, '_register_help') );

		add_action( 'wp_ajax_kc_get_image_url', 'kc_ajax_get_image_url' );
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
			'plugins'    => array( WP_PLUGIN_DIR, plugins_url() ),
			'mu-plugins' => array( WPMU_PLUGIN_DIR, WPMU_PLUGIN_URL ),
			'themes'     => array( get_theme_root(), get_theme_root_uri() )
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

		$paths['file']    = $file;
		$paths['p_file']  = kc_plugin_file( $file );
		$paths['inc']     = "{$dir}/{$inc_prefix}";
		$paths['url']     = $url;
		$paths['scripts'] = "{$url}/{$inc_prefix}/scripts";
		$paths['styles']  = "{$url}/{$inc_prefix}/styles";

		return $paths;
	}


	/**
	 * Get all settings
	 */
	private static function _bootstrap_settings() {
		# Settings bootstrap error messages
		self::$data['messages']['bootstrap'] = array(
			'no_prefix'           => __( "One of your settings doesn't have <b>prefix</b> set.", 'kc-settings'),
			'no_menu_title'       => __( "One of your settings doesn't have <b>menu title</b> set.", 'kc-settings'),
			'no_page_title'       => __( "One of your settings doesn't have <b>page title</b> set.", 'kc-settings'),
			'no_options'          => __( "One of your settings doesn't have <b>options</b> set.", 'kc-settings'),
			'section_no_cb'       => __( "One of your section's callback is not callable.", 'kc-settings'),
			'section_no_fields'   => __( "One of your settings' section doesn't have <b>fields</b> set.", 'kc-settings'),
			'section_no_id'       => __( "One of your settings' sections doesn't have <b>ID</b> set.", 'kc-settings'),
			'section_no_title'    => __( "One of your settings' sections doesn't have <b>title</b> set.", 'kc-settings'),
			'section_metabox_old' => __( "One of your settings is still using the old format for metabox setting, please migrate it to the new one.", 'kc-settings'),
			'field_no_id'         => __( "One of your fields doesn't have <b>ID</b> set.", 'kc-settings'),
			'field_no_title'      => __( "One of your fields doesn't have <b>title</b> set.", 'kc-settings'),
			'field_no_type'       => __( "One of your fields doesn't have <b>type</b> set.", 'kc-settings'),
			'field_no_opt'        => __( "One of your fields doesn't have the required <b>options</b> set.", 'kc-settings'),
			'field_no_cb'         => __( "One of your fields doesn't have the required <b>callback</b> set, or is not callable.", 'kc-settings')
		);

		$kcsb = array(
			'settings' => get_option( 'kcsb' ),
			'_ids'     => array(
				'settings' => array(),
				'sections' => array(),
				'fields'   => array()
			)
		);

		$settings = array(
			'plugin' => array(),
			'post'   => array(),
			'term'   => array(),
			'user'   => array()
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

			self::$data['kcsb'] = $kcsb;
		}

		$settings = self::_validate_settings( $settings );
		if ( empty($settings) )
			return;

		self::$data['settings'] = $settings;
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
					trigger_error( self::$data['messages']['bootstrap']['no_options'] );
					unset( $groups[$g_idx] );
					continue;
				}

				if ( $type == 'plugin' ) {
					$g_idx = $group['prefix'];
					foreach ( array('prefix', 'menu_title', 'page_title', 'options') as $c ) {
						if ( !isset($group[$c]) || empty($group[$c]) || ($c == 'options' && !is_array($group[$c])) ) {
							trigger_error( self::$data['messages']['bootstrap']["no_{$c}"] );
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

					$group['options'] = self::_validate_sections( $type, $group['options'], $group );
					if ( empty($group['options']) )
						$group = null;
				}

				elseif ( in_array($type, array('post', 'term', 'user')) ) {
					foreach ( $group as $obj => $sections ) {
						$group[$obj] = self::_validate_sections( $type, $sections );
						if ( empty($group[$obj]) )
							$group = null;
					}
				}

				# Include this group only if it's valid
				if ( !empty($group) ) {
					# Plugin/themes only: Set page layout
					if ( isset($group['options']['has_sidebar']) ) {
						unset( $group['options']['has_sidebar'] );
						$group['has_sidebar'] = true;
					}

					$nu[$type][$g_idx] = $group;
				}
			}

		}

		foreach ( array('post', 'term', 'user') as $type ) {
			if ( isset($nu[$type]) )
				$nu[$type] = self::_bootstrap_meta( $nu[$type] );
		}

		return $nu;
	}


	# Validate each setting's section
	private static function _validate_sections( $type, $sections, $group = '' ) {
		$defaults = array();
		foreach ( $sections as $s_idx => $section ) {
			unset( $sections[$s_idx] );
			# Section check: id & title
			foreach ( array('id', 'title') as $c ) {
				if ( !isset($section[$c]) || empty($section[$c]) ) {
					trigger_error( self::$data['messages']['bootstrap']["section_no_{$c}"] );
					unset( $sections[$s_idx] );
					continue 2;
				}
			}

			# Custom callback for section?
			if ( $type == 'plugin' && isset($section['cb']) ) {
				if ( !is_callable($section['cb']) ) {
					trigger_error( self::$data['messages']['bootstrap']["section_no_cb"] );
					continue;
				}
			}
			else {
				if ( !isset($section['fields']) || !is_array($section['fields']) || empty($section['fields']) ) {
					trigger_error( self::$data['messages']['bootstrap']["section_no_fields"] );
					continue;
				}
				else {
					$fields = self::_validate_fields( $type, $section['fields'] );
					if ( empty($fields['fields']) )
						continue;
					$section['fields'] = $fields['fields'];

					if ( !empty($fields['defaults']) )
						$defaults['plugin'][$group['prefix']][$section['id']] = $fields['defaults'];
				}
			}

			# Plugin/theme/post only: Set metabox position & priority
			if ( $type == 'post' || ($type == 'plugin' && $group['display']) == 'metabox' ) {
				# TODO: remove in version 3.0
				if ( isset($section['priority']) ) {
					trigger_error( self::$data['messages']['bootstrap']["section_metabox_old"] );
					$metabox_priority = $section['priority'];
					unset( $section['priority'] );
				}
				$metabox_default = array(
					'context'  => 'normal',
					'priority' => isset($metabox_priority) ? $metabox_priority : 'default'
				);
				$metabox = isset($section['metabox']) ? $section['metabox'] : array();
				$section['metabox'] = wp_parse_args( $metabox, $metabox_default );
			}

			# Plugin/themes metabox position
			if ( $type == 'plugin' && $section['metabox']['context'] == 'side' )
				$sections['has_sidebar'] = true;

			$sections[$section['id']] = $section;
		}

		# Store default values
		if ( !empty($defaults) )
			self::$data['defaults'] = array_merge_recursive( self::$data['defaults'], $defaults );

		return $sections;
	}


	private static function _validate_fields( $type, $fields ) {
		$defaults = array();
		$need_options = array( 'select', 'radio', 'checkbox' );
		$file_modes = array('single', 'radio', 'checkbox');

		foreach ( $fields as $idx => $field ) {
			unset( $fields[$idx] );
			# Field check: id, title & type
			foreach ( array('id', 'title', 'type') as $c ) {
				if ( !isset($field[$c]) || empty($field[$c]) ) {
					trigger_error( self::$data['messages']['bootstrap']["field_no_{$c}"] );
					unset( $fields[$idx] );
					continue 2;
				}
			}
			# Field check: need options
			if ( in_array($field['type'], $need_options) && !isset($field['options']) ) {
				trigger_error( self::$data['messages']['bootstrap']['field_no_opt'] );
				unset( $fields[$idx] );
				continue;
			}
			# Field check: file mode
			if ( $field['type'] == 'file' ) {
				if ( !isset($field['mode']) || !in_array($field['mode'], $file_modes) )
					$fields[$idx]['mode'] = 'radio';
			}
			elseif ( $field['type'] == 'special' ) {
				if ( !isset($field['cb']) || !is_callable($field['cb']) ) {
					trigger_error( self::$data['messages']['bootstrap']['field_no_cb'] );
					unset( $fields[$idx] );
					continue;
				}
			}

			# Has default value?
			if ( $type == 'plugin' && isset($field['default']) )
				$defaults[$field['id']] = $field['default'];

			$fields[$field['id']] = $field;
		}

		return array('fields' => $fields, 'defaults' => $defaults );
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


	/**
	 * Register contextual help
	 */
	public static function _register_help() {
		global $hook_suffix;
		$screen = get_current_screen();
		if ( empty(self::$data['help']) || !isset(self::$data['help'][$hook_suffix]) || !is_object($screen) )
			return;

		foreach ( self::$data['help'][$hook_suffix] as $help ) {
			if ( isset($help['sidebar']) && $help['sidebar'] )
				$screen->set_help_sidebar( $help['content'] );
			else
				$screen->add_help_tab( $help );
		}
	}


	public static function _sns_register() {
		$path = self::$data['paths'];
		$version = self::$data['status']['version'];

		if ( !defined('KC_SETTINGS_SNS_DEBUG') )
			define( 'KC_SETTINGS_SNS_DEBUG', false );

		$suffix = KC_SETTINGS_SNS_DEBUG ? '.dev' : '';

		# Common
		wp_register_script( 'kc-settings-base', "{$path['scripts']}/kc-settings-base{$suffix}.js", array('jquery'), $version, true );
		wp_register_script( 'modernizr',        "{$path['scripts']}/modernizr-2.5.3{$suffix}.js", false, '2.5.3', true );
		wp_register_script( 'kc-settings',      "{$path['scripts']}/kc-settings{$suffix}.js", array('modernizr', 'kc-settings-base', 'jquery-ui-sortable', 'jquery-ui-datepicker', 'media-upload', 'thickbox'), $version, true );
		wp_register_style(  'kc-settings',      "{$path['styles']}/kc-settings{$suffix}.css", array('thickbox'), $version );

		# Uploader
		wp_register_script( 'kc-settings-upload',        "{$path['scripts']}/upload{$suffix}.js", array('jquery'), $version, true );
		wp_register_script( 'kc-settings-upload-single', "{$path['scripts']}/upload-single{$suffix}.js", array('jquery'), $version, true );
	}


	public static function _sns_admin( $hook_suffix ) {
		if ( !in_array($hook_suffix, self::$data['pages']) )
			return;

		wp_enqueue_style( 'kc-settings' );
		wp_enqueue_script( 'kc-settings' );

		if ( $hook_suffix != 'media-upload-popup' ) {
			self::_js_globals();
		}
		else {
			if ( (isset($_REQUEST['kcsfs']) && $_REQUEST['kcsfs']) || strpos( wp_get_referer(), 'kcsfs') !== false )
				wp_enqueue_script( 'kc-settings-upload-single' );
			elseif ( (isset($_REQUEST['kcsf']) && $_REQUEST['kcsf']) || strpos( wp_get_referer(), 'kcsf') !== false )
				wp_enqueue_script( 'kc-settings-upload' );
		}
	}


	private static function _js_globals() {
		$kcSettings_vars = array(
			'upload' => array(
				'text' => array(
					'head'     => __( 'KC Settings', 'kc-settings' ),
					'empty'    => __( 'Please upload some files and then go back to this tab.', 'kc-settings' ),
					'checkAll' => __( 'Select all files', 'kc-settings' ),
					'clear'    => __( 'Clear selections', 'kc-settings' ),
					'invert'   => __( 'Invert selection', 'kc-settings' ),
					'addFiles' => __( 'Add files to collection', 'kc-settings' ),
					'info'     => __( 'Click the "Media Library" tab to insert files that are already upload, or, upload your files and then go to the "Media Library" tab to insert the files you just uploaded.', 'kc-settings' ),
					'selFile'  => __( 'Select file', 'kc-settings' )
				)
			),
			'_ids'  => isset( self::$data['kcsb']['_ids'] ) ? self::$data['kcsb']['_ids'] : '',
			'paths' => self::$data['paths']
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
			require_once self::$data['paths']['inc'] . "/doc/sample/{$type}.php";
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
		if ( $plugin_file == self::$data['paths']['p_file'] && !empty(self::$data['status']['kids']) )
			unset( $actions['deactivate'] );

		return $actions;
	}


	public static function get_data() {
		$data = self::$data;
		if ( !func_num_args() )
			return $data;

		$args = func_get_args();
		return kc_array_multi_get_value( $data, $args );
	}


	public static function add_page( $page ) {
		if ( !in_array($page, self::$data['pages']) )
			self::$data['pages'][] = $page;
	}


	public static function add_help( $page, $helps ) {
		if ( !is_array($helps) || empty($helps) )
			return false;

		foreach ( $helps as $idx => $help ) {
			foreach ( array('id', 'title', 'content') as $c ) {
				if ( !isset($help[$c]) || empty($help[$c]) ) {
					unset( $helps[$idx] );
					continue 2;
				}
			}
		}

		if ( empty($helps) )
			return false;

		if ( !isset(self::$data['help'][$page]) )
			self::$data['help'][$page] = array();
		self::$data['help'][$page] = array_merge( self::$data['help'][$page], $helps );
	}


	# Plugin activation tasks
	public static function _activate() {
		if ( version_compare(get_bloginfo('version'), '3.3', '<') )
			wp_die( 'Please upgrade your WordPress to version 3.3 before using this plugin.' );

		$status = get_option( 'kc_settings' );
		if ( !$status )
			$status = array();

		if ( !isset($data['kids']) )
			$status['kids'] = array();

		$old_version = ( isset($status['version']) ) ? $status['version'] : '2.2';
		$status['version'] = '2.6.7';

		update_option( 'kc_settings', $status );

		if ( version_compare($old_version, '2.5', '<') )
			self::_upgrade( array('kcsb', 'kcsb_metabox') );
	}


	# Upgrade task(s)
	private static function _upgrade( $parts = array() ) {
		if ( in_array('kcsb', $parts) ) {
			$kcsb = get_option( 'kcsb' );
			if ( is_array($kcsb) ) {
				foreach ( $kcsb as $id => $item ) {
					foreach ($item['sections'] as $section_id => $section ) {
						# Metabox (20111215)
						if ( in_array('kcsb_metabox', $parts) ) {
							$mb_prio = isset($section['priority']) ? $section['priority'] : 'default';
							if ( isset($section['priority'])
										|| ( $item['type'] == 'plugin' && !isset($item['display']) )
										|| ( $item['display'] == 'metabox' && !isset($section['metabox']) ) ) {
								$section['metabox'] = array(
									'context'  => 'normal',
									'priority' => $mb_prio
								);
								$item['display'] = 'metabox';
								unset( $section['priority'] );
							}
						}

						# Return the section
						$item['sections'][$section_id] = $section;
					}

					# Return the item
					$kcsb[$id] = $item;
				}

				update_option( 'kcsb', $kcsb );
			}
		}
	}


	public static function debug_bar_ext( $panels ) {
		$panels[] = new kcDebug;
		return $panels;
	}

}
add_action( 'plugins_loaded', array('kcSettings', 'setup'), 7);


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

register_activation_hook( kc_plugin_file( __FILE__ ), array('kcSettings', '_activate') );

?>

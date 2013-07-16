<?php

class kcSettings_builder {
	protected static $data = array(
		'defaults' => array(
			'id'            => '',
			'status'        => '1',
			'type'          => 'post',
			'prefix'        => '',
			'menu_location' => 'options-general.php',
			'menu_title'    => '',
			'page_title'    => '',
			'display'       => 'plain',
			'post_type'     => 'post',
			'taxonomy'      => '',
			'sections'      => array(
				array(
					'id'      => '',
					'title'   => '',
					'desc'    => '',
					'metabox' => array(
						'context'  => 'normal',
						'priority' => 'default',
					),
					'fields'  => array(
						array(
							'id'                => '',
							'title'             => '',
							'desc'              => '',
							'type'              => 'text',
							'attr'              => '',
							'option_type'       => 'custom',
							'option_predefined' => 'yesno',
							'option_predefined_cb'     => '',
							'option_predefined_cb_tax' => '',
							'option_predefined_cb_pt'  => '',
							'options'           => array(
								array(
									'key'   => '',
									'label' => '',
								),
							),
							'editor_settings' => array( 'media_buttons', 'tinymce', 'quicktags' ),
						),
					),
				),
			),
		),
	);

	protected static $table;
	protected static $item_to_edit;
	protected static $item_to_export;
	protected static $update_message;


	public static function init() {
		$kcsb = kcSettings::get_data('kcsb');
		if ( !isset($kcsb['settings']) || !is_array($kcsb['settings']) )
			$kcsb['settings'] = array();
		self::$data['kcsb'] = $kcsb;

		add_action( 'admin_init', array(__CLASS__, 'register'), 21 );
		add_action( 'admin_menu', array(__CLASS__, 'create_page') );
		add_filter( 'plugin_row_meta', array(__CLASS__, 'builder_link'), 10, 3 );
		add_action( 'update_option_kcsb', array(__CLASS__, 'redirect'), 10, 2 );
	}


	private static function _options() {
		$options = array(
			'type' => array(
				'plugin' => __('Plugin / theme settings', 'kc-settings'),
				'post'   => __('Post metadata (custom fields)', 'kc-settings'),
				'term'   => __('Term metadata', 'kc-settings'),
				'user'   => __('User metadata', 'kc-settings'),
			),
			'menu_location' => array(
				'options-general.php' => __('Settings'),
				'themes.php'          => __('Appearance'),
				'index.php'           => __('Dashboard'),
				'plugins.php'         => __('Plugins'),
				'tools.php'           => __('Tools'),
				'plugins.php'         => __('Plugins'),
				'users.php'           => __('Users'),
				'upload.php'          => __('Media'),
				'link-manager.php'    => __('Links'),
				'edit-comments.php'   => __('Comments'),
				'edit.php'            => __('Posts'),
			),
			'display' => array(
				'metabox' => __('Metaboxes', 'kc-settings'),
				'plain'   => __('Plain', 'kc-settings'),
			),
			'field' => array(
				'text'     => __('Text', 'kc-settings'),
				'textarea' => __('Textarea', 'kc-settings'),
				'color'    => __('Color', 'kc-settings'),
				'date'     => __('Date', 'kc-settings'),
				'number'   => __('Number', 'kc-settings'),
				'email'    => __('Email', 'kc-settings'),
				'password' => __('Password', 'kc-settings'),
				'url'      => __('URL', 'kc-settings'),
				'tel'      => __('Telephone', 'kc-settings'),
				'month'    => __('Month', 'kc-settings'),
				'week'     => __('Week', 'kc-settings'),
				'time'     => __('Time', 'kc-settings'),
				'datetime-local' => __('Datetime (local)', 'kc-settings'),
				'datetime' => __('Datetime (with timezone)', 'kc-settings'),
				'editor'      => __('WYSIWYG Editor', 'kc-settings'),
				'media'       => __('Media', 'kc-settings'),
				'checkbox'    => __('Checkbox', 'kc-settings'),
				'radio'       => __('Radio', 'kc-settings'),
				'select'      => __('Select', 'kc-settings'),
				'multiselect' => __('Select (multiple)', 'kc-settings'),
				'multiinput'  => __('Multiinput', 'kc-settings'),
				'special'     => __('Special', 'kc-settings'),
				'file'        => __('File (deprecated)', 'kc-settings'),
			),
			'metabox' => array(
				'context'   => array(
					'label'   => __('Context', 'kc-settings'),
					'options' => array(
						'normal'   => __('Normal', 'kc-settings'),
						'advanced' => __('Advanced', 'kc-settings'),
						'side'     => __('Side', 'kc-settings'),
					),
				),
				'priority' => array(
					'label'   => __('Priority', 'kc-settings'),
					'options' => array(
						'high' => __('High', 'kc-settings'),
						'default' => __('Default', 'kc-settings'),
						'low' => __('Low', 'kc-settings'),
					),
				),
			),
			'option_type' => array(
				'predefined' => __('Predefined options', 'kc-settings'),
				'custom'     => __('Custom options', 'kc-settings'),
			),
			'status' => array(
				'1' => __('Active'),
				'0' => __('Inactive'),
			),
			'option_predefined' => array(
				'yesno'               => __('Yes / No', 'kc-settings'),
				'post_types'          => __('Post types (public)', 'kc-settings'),
				'post_types_all'      => __('Post types (all)', 'kc-settings'),
				'posts'               => __('Posts', 'kc-settings'),
				'taxonomies'          => __('Taxonomies (public)', 'kc-settings'),
				'taxonomies_all'      => __('Taxonomies (all)', 'kc-settings'),
				'terms'               => __('Terms', 'kc-settings'),
				'nav_menus'           => __('Nav menus', 'kc-settings'),
				'image_sizes'         => __('Image sizes (all)', 'kc-settings'),
				'image_sizes_custom'  => __('Image sizes (custom)', 'kc-settings'),
				'image_sizes_default' => __('Image sizes (default)', 'kc-settings'),
				'post_statuses'       => __('Post statuses', 'kc-settings'),
				'roles'               => __('User roles', 'kc-settings'),
				'sidebars'            => __('Sidebars', 'kc-settings'),
			),
			'filemode' => array(
				'single'   => __('Single file', 'kc-settings'),
				'radio'    => __('Single selection (radio)', 'kc-settings'),
				'checkbox' => __('Multiple selections (checkbox)', 'kc-settings'),
			),
			'post_types' => kcSettings_options::$post_types,
			'taxonomies' => kcSettings_options::$taxonomies,
			'role'       => kcSettings_options::$roles,
			'editor_settings' => array(
				'media_buttons' => __('Display media insert/upload buttons', 'kc-settings'),
				'tinymce'       => __('Load tinyMCE', 'kc-settings'),
				'quicktags'     => __('Load Quicktags', 'kc-settings'),
			),
		);

		asort( $options['field'] );

		return $options;
	}


	public static function create_page() {
		$page = add_options_page( __('KC Settings', 'kc-settings'), __('KC Settings', 'kc-settings'), 'manage_options', 'kcsb', array(__CLASS__, 'builder') );
		self::$data['url'] = admin_url('options-general.php?page=kcsb');

		add_action( "load-{$page}", array(__CLASS__, 'load') );
		add_action( "load-{$page}", array(__CLASS__, 'print_help') );
		add_action( "load-{$page}", array(__CLASS__, 'sns') );
	}


	public static function builder_link( $plugin_meta, $plugin_file, $plugin_data ) {
		if ( !empty(self::$data['url']) && $plugin_data['Name'] == 'KC Settings' )
			$plugin_meta[] = '<a href="'.self::$data['url'].'">'.__('KC Settings Builder', 'kc-settings').'</a>';

		return $plugin_meta;
	}


	public static function register() {
		register_setting( 'kcsb', 'kcsb', array(__CLASS__, 'validate') );
	}


	public static function load() {
		self::$data['options'] = self::_options();

		$temp = get_transient( 'kcsb' );
		if ( $temp ) {
			self::$item_to_edit = $temp;
			delete_transient( 'kcsb' );
		}

		require_once dirname( __FILE__ ) . '/builder-table.php';
		$table = new kcSettings_builder_table( array(
			'plural' => 'kcsb-table',
			'kcsb'   => array(
				'settings' => self::$data['kcsb']['settings'],
				'options'  => self::$data['options'],
			)
		) );
		self::$table = $table;

		$action = $table->current_action();
		if ( !$action || !in_array( $action, array('delete', 'edit', 'purge', 'empty', 'clone', 'activate', 'deactivate', 'export') ) )
			return;

		$update = false;

		# Singular
		if ( isset($_REQUEST['id']) && in_array($_REQUEST['id'], self::$data['kcsb']['settings'][$_REQUEST['id']]) ) {
			check_admin_referer( "__kcsb__{$_REQUEST['id']}" );
			$items = array( $_REQUEST['id'] );
		}
		# Plural
		elseif ( isset($_REQUEST['ids']) && is_array($_REQUEST['ids']) && !empty($_REQUEST['ids']) ) {
			check_admin_referer('bulk-kcsb-table');
			$items = $_REQUEST['ids'];
		}

		$single = count( $items ) < 2 ? true : false;

		switch ( $action ) {
			case 'activate' :
				foreach ( $items as $item )
					self::$data['kcsb']['settings'][$item]['status'] = '1';

				$update = true;
				self::$update_message = $single ? __('Setting succesfully activated.', 'kc-settings') : __('Settings succesfully activated.', 'kc-settings');
			break;

			case 'deactivate' :
				foreach ( $items as $item )
					self::$data['kcsb']['settings'][$item]['status'] = '0';

				$update = true;
				self::$update_message = $single ? __('Setting succesfully deactivated.', 'kc-settings') : __('Settings succesfully deactivated.', 'kc-settings');
			break;

			case 'delete' :
				foreach ( $items as $item )
					unset( self::$data['kcsb']['settings'][$item] );

				$update = true;
				self::$update_message = $single ? __('Setting succesfully deleted.', 'kc-settings') : __('Settings succesfully deleted.', 'kc-settings');
			break;

			case 'empty' :
				$result = 0;
				foreach ( $items as $item )
					if ( self::$data['kcsb']['settings'][$item]['type'] === 'plugin' && delete_option( self::$data['kcsb']['settings'][$item]['prefix'] . '_settings' ) )
					 $result++;

				if ( $result ) {
					$message = $result === 1 ? __('Setting values succesfully removed from database.', 'kc-settings') : __('Settings values succesfully removed from database.', 'kc-settings');
					kcSettings::add_notice( 'updated', "<strong>{$message}</strong>" );
				}
			break;

			case 'clone' :
				if ( isset($_REQUEST['new']) && !empty($_REQUEST['new']) ) {
					$new_id = sanitize_html_class( $_REQUEST['new'] );
					if ( $new_id && !isset(self::$data['kcsb']['settings'][$new_id]) ) {
						$new = self::$data['kcsb']['settings'][$_REQUEST['id']];
						$new['id'] = $new_id;
						self::$data['kcsb']['settings'][$new_id] = $new;

						$update = true;
						self::$update_message = __('Setting succesfully cloned.', 'kc-settings');
					}
				}
			break;

			case 'edit' :
				self::$item_to_edit = wp_parse_args( self::$data['kcsb']['settings'][$_REQUEST['id']], self::$data['defaults'] );
			break;

			case 'export' :
				if ( isset($_REQUEST['type']) && isset(self::$data['kcsb']['items'][$_REQUEST['type']][$_REQUEST['id']]) )
					self::$item_to_export = self::_exporter( $_REQUEST['type'], self::$data['kcsb']['items'][$_REQUEST['type']][$_REQUEST['id']] );
			break;
		}

		if ( $update )
			update_option( 'kcsb', self::$data['kcsb']['settings'] );
	}


	public static function print_help( $values ) {
		$screen = get_current_screen();
		$screen->add_help_tab( array(
			'id'      => 'kcsb',
			'title'   => __( 'KC Settings Builder', 'kc-settings' ),
			'content' =>
				'<ul>
					<li>'.__('All fields are required, unless stated otherwise.', 'kc-settings').'</li>
					<li>'.__('Some fields depend on other field(s), they will be shown when the dependency is selected/checked.', 'kc-settings').'</li>
					<li>'.__('Some fields (eg. ID, Prefix) can only be filled with alphanumerics, dashes and underscores, must be unique, and cannot begin with dashes or underscores.', 'kc-settings').'</li>
				</ul>'
		) );

		$screen->set_help_sidebar('
			<ul>
				<li><a href="http://kucrut.github.com/kc-settings/">'.__('Online documentation', 'kc-settings').'</a></li>
				<li><a href="http://wordpress.org/tags/kc-settings?forum_id=10">'.__('Support', 'kc-settings').'</a></li>
				<li><a href="https://github.com/kucrut/kc-settings/issues">'.__('Request new feature', 'kc-settings').'</a></li>
				<li><a href="http://kucrut.org/contact/">'.__('Donate/Contact', 'kc-settings').'</a></li>
			</ul>
		');
	}

	public static function validate( $values ) {
		/**
		 * Task: clone / delete an item
		 * Just return the values, assume it's valid
		 */
		if ( !isset($values['id']) ) {
			$settings = $values;
		}

		/**
		 * Task: Add / Edit item: get all items, and if:
		 * 0. Error: store the new item in the transient db
		 * 1. Sucess: add the new item
		 */
		else {
			$settings = self::$data['kcsb']['settings'];

			if ( isset($values['id']) && $values['id'] ) {
				$settings[$values['id']] = $values;
				if ( $settings === self::$data['kcsb']['settings'] )
					self::redirect();
				else
					self::$update_message = __('Setting succesfully updated.', 'kc-settings');
			}
			else {
				set_transient( 'kcsb', $values );
				add_settings_error('kcsb', 'not_saved', __('Setting was NOT saved! Please fill all the required fields.', 'kc-settings') );
				self::redirect();
			}
		}

		return $settings;
	}


	public static function redirect() {
		if ( self::$update_message )
			add_settings_error('kcsb', 'settings-updated', self::$update_message, 'updated' );
		set_transient('settings_errors', get_settings_errors(), 30);

		$sendback = remove_query_arg( array('action', 'action2', 'id', 'ids', '_wpnonce'), wp_get_referer() );
		$sendback = add_query_arg( 'settings-updated', 'true', $sendback );
		wp_redirect( $sendback );
		exit;
	}


	public static function sns() {
		wp_enqueue_style( 'kc-settings' );
		wp_enqueue_script( 'kc-settings-builder' );
		wp_localize_script( 'kc-settings-builder', 'kcsbIDs', isset( self::$data['kcsb']['_ids'] ) ? self::$data['kcsb']['_ids'] : '' );
	}


	private static function _fields( $fields, $s_val, $s_name, $s_id, $mode = 'fields' ) {
		$options = self::$data['options'];
		$ul_class = "kc-rows {$mode}";
		if ( $mode === 'subfields' ) {
			$texts = array(
				'head' => __('Sub-field #%s', 'kc-settings'),
				'drag' => __('Drag to reorder sub-field', 'kc-settings'),
				'add'  => __('Add new sub-field', 'kc-settings'),
				'del'  => __('Delete this sub-field', 'kc-settings')
			);
			unset($options['field']['multiinput']);
			# For transition
			if ( !isset($s_val[$mode][0]['id']) )
				$s_val[$mode][0] = self::$data['defaults']['sections'][0]['fields'][0];
		}
		else {
			$texts = array(
				'head' => __('Field #%s', 'kc-settings'),
				'drag' => __('Drag to reorder field', 'kc-settings'),
				'add'  => __('Add new field', 'kc-settings'),
				'del'  => __('Delete this field', 'kc-settings')
			);
		}

		$texts['head_o'] = __('Option #%s', 'kc-settings');
		$texts['add_o'] = __('Add new option', 'kc-settings');
		$texts['del_o'] = __('Delete this option', 'kc-settings');

		?>
		<ul class="<?php echo esc_attr($ul_class) ?>">
			<?php
				$field_idx = 0;
				$field_num = count( $fields );
				foreach ( $fields as $field_key => $field ) {
					$f_name = "{$s_name}[$mode][{$field_key}]";
					$f_val  = $s_val[$mode][$field_key];
					$f_id   = "{$s_id}-{$mode}-{$field_key}";
					$f_stat = ( $field_num === 1 && $field_idx === 0 ) ? ' open' : '';
			?>
			<li class="row" data-mode="<?php echo $mode ?>">
				<details<?php echo esc_attr($f_stat) ?>>
					<summary title="<?php echo $texts['drag'] ?>" class="actions">
						<h5><?php printf( $texts['head'], '<span class="count">'. ($field_idx + 1) .'</span>' ) ?></h5>
						<p>(
							<a class="add" title="<?php echo esc_attr($texts['add']) ?>"><?php _e('Add') ?></a>
							<a class="del" title="<?php echo esc_attr($texts['del']) ?>"><?php _e('Remove') ?></a>
						)</p>
					</summary>
					<ul class="main">
						<li>
							<label for="<?php echo esc_attr("{$f_id}-id") ?>" class="ml"><?php _e('ID', 'kc-settings') ?></label>
							<input id="<?php echo esc_attr("{$f_id}-id") ?>" class="mi kcsb-slug kcsb-ids required regular-text" type="text" name="<?php echo $f_name ?>[id]" value="<?php echo esc_attr($f_val['id']) ?>" data-ids="fields" />
						</li>
						<li>
							<label for="<?php echo esc_attr("{$f_id}-title") ?>" class="ml"><?php _e('Label') ?></label>
							<input id="<?php echo esc_attr("{$f_id}-title") ?>" class="mi required regular-text" type="text" name="<?php echo esc_attr($f_name) ?>[title]" value="<?php echo esc_attr($f_val['title']) ?>" />
						</li>
						<li>
							<label for="<?php echo esc_attr("{$f_id}-desc") ?>" class="ml"><?php _e('Description') ?></label>
							<div class="mi">
								<textarea id="<?php echo esc_attr("{$f_id}-desc") ?>" name="<?php echo esc_attr($f_name) ?>[desc]" cols="25" rows="4"><?php echo isset($f_val['desc']) ? esc_textarea($f_val['desc']) : '' // xss ok ?></textarea>
								<p class="description"><?php _e('Optional', 'kc-settings') ?></p>
							</div>
						</li>
						<li>
							<label for="<?php echo esc_attr("{$f_id}-type") ?>" class="ml"><?php _e('Type') ?></label>
							<?php
								echo kcForm::field(array(
									'type'    => 'select',
									'attr'    => array(
										'id'         => "{$f_id}-type",
										'name'       => "{$f_name}[type]",
										'class'      => 'hasdep mi',
										'data-child' => '.childFieldType',
										'data-scope' => 'ul.main',
									),
									'options' => $options['field'],
									'current' => !empty($f_val['type']) ? $f_val['type'] : 'text',
									'none'    => false,
								)); // xss ok
							?>
						</li>
						<li class="childFieldType" data-dep='file'>
							<label for="<?php echo esc_attr("{$f_id}-mode") ?>" class="ml"><?php _e('Mode', 'kc-settings') ?></label>
							<?php
								echo kcForm::field(array(
									'type'    => 'select',
									'attr'    => array(
										'id'         => "{$f_id}-mode",
										'name'       => "{$f_name}[mode]",
										'class'      => 'hasdep mi',
										'data-child' => '.childFileSize',
										'data-scope' => 'ul.main',
									),
									'options' => $options['filemode'],
									'current' => isset($f_val['mode']) ? $f_val['mode'] : 'single',
									'none'    => false,
								)); // xss ok
							?>
						</li>
						<li class="childFileSize" data-dep='single'>
							<label for="<?php echo esc_attr("{$f_id}-size") ?>" class="ml"><?php _e('Preview Size', 'kc-settings') ?></label>
							<?php
								echo kcForm::field(array(
									'type'    => 'select',
									'attr'    => array(
										'id'    => "{$f_id}-size",
										'name'  => "{$f_name}[size]",
										'class' => 'mi',
									),
									'options' => kcSettings_options::$image_sizes,
									'current' => isset($f_val['size']) ? $f_val['size'] : 'thumbnail',
									'none'    => false,
								)); // xss ok
							?>
						</li>
						<li class="childFieldType" data-dep='media'>
							<label for="<?php echo esc_attr("{$f_id}-multiple") ?>" class="ml"><?php _e('Multiple', 'kc-settings') ?></label>
							<?php
								echo kcForm::field(array(
									'type'    => 'select',
									'attr'    => array(
										'id'    => "{$f_id}-multiple",
										'name'  => "{$f_name}[multiple]",
										'class' => 'mi',
									),
									'options' => kcSettings_options::$yesno,
									'current' => isset($f_val['multiple']) ? $f_val['multiple'] : false,
									'none'    => false,
								)); // xss ok
							?>
						</li>
						<li class="childFieldType" data-dep='media'>
							<label for="<?php echo esc_attr("{$f_id}-preview_size") ?>" class="ml"><?php _e('Preview size', 'kc-settings') ?></label>
							<?php
								echo kcForm::field(array(
									'type'    => 'select',
									'attr'    => array(
										'id'    => "{$f_id}-preview_size",
										'name'  => "{$f_name}[preview_size]",
										'class' => 'mi',
									),
									'options' => kcSettings_options::$image_sizes,
									'current' => isset($f_val['preview_size']) ? $f_val['preview_size'] : 'thumbnail',
									'none'    => false,
								)); // xss ok
							?>
						</li>
						<li class="childFieldType" data-dep='["radio", "checkbox", "select", "multiselect"]'>
							<label for="<?php echo esc_attr("{$f_id}-option_type") ?>" class="ml"><?php _e('Options', 'kc-settings') ?></label>
							<?php
								echo kcForm::field(array(
									'type'    => 'select',
									'attr'    => array(
										'id'         => "{$f_id}-option_type",
										'name'       => "{$f_name}[option_type]",
										'class'      => 'hasdep mi',
										'data-child' => '.childFieldOptionType',
										'data-scope' => 'ul.main',
									),
									'options' => $options['option_type'],
									'current' => isset( $f_val['option_type'] ) ? $f_val['option_type'] : 'predefined',
									'none'    => false,
								)); // xss ok
							?>
						</li>
						<li class="childFieldOptionType" data-dep='predefined'>
							<label for="<?php echo esc_attr("{$f_id}-option_predefined") ?>" class="ml"><?php _e('Predefined option', 'kc-settings') ?></label>
							<?php
								echo kcForm::field(array(
									'type'    => 'select',
									'attr'    => array(
										'id'    => "{$f_id}-option_predefined",
										'name'  => "{$f_name}[option_predefined]",
										'class' => 'hasdep mi',
										'data-child' => '.childFieldOptionArg',
										'data-scope' => 'ul.main',
									),
									'options' => $options['option_predefined'],
									'current' => isset( $f_val['option_predefined'] ) ? $f_val['option_predefined'] : 'yesno',
									'none'    => false,
								)); // xss ok
							?>
						</li>
						<li class="childFieldOptionArg" data-dep='terms'>
							<label for="<?php echo esc_attr("{$f_id}-option_predefined_cb_tax") ?>" class="ml"><?php _e('Taxonomy', 'kc-settings') ?></label>
							<?php
								echo kcForm::field(array(
									'type'    => 'select',
									'attr'    => array(
										'id'    => "{$f_id}-option_predefined_cb_tax",
										'name'  => "{$f_name}[option_predefined_cb_tax]",
										'class' => 'mi',
									),
									'options' => $options['taxonomies'],
									'current' => isset( $f_val['option_predefined_cb_tax'] ) ? $f_val['option_predefined_cb_tax'] : 'category',
									'none'    => false,
								)); // xss ok
							?>
						</li>
						<li class="childFieldOptionArg" data-dep='posts'>
							<label for="<?php echo esc_attr("{$f_id}-option_predefined_cb_pt") ?>" class="ml"><?php _e('Post type', 'kc-settings') ?></label>
							<?php
								echo kcForm::field(array(
									'type'    => 'select',
									'attr'    => array(
										'id'    => "{$f_id}-option_predefined_cb_pt",
										'name'  => "{$f_name}[option_predefined_cb_pt]",
										'class' => 'mi',
									),
									'options' => $options['post_types'],
									'current' => isset( $f_val['option_predefined_cb_pt'] ) ? $f_val['option_predefined_cb_pt'] : 'page',
									'none'    => false,
								)); // xss ok
							?>
						</li>
						<li class="childFieldOptionType" data-dep='custom'>
							<label class="ml"><?php _e('Custom options', 'kc-settings') ?></label>
							<ul class="mi options kc-rows">
								<?php
									if ( !isset($f_val['options']) || !is_array($f_val['options']) )
										$f_val['options'] = array( array( 'key' => '', 'label' => '' ) );
									$option_num = count( $f_val['options'] );
									$option_idx = 0;
									foreach ( $f_val['options'] as $option_key => $option ) {
										$option_stat = ( $option_num === 1 && $option_idx === 0 ) ? ' open' : '';
								?>
								<li class="row" data-mode="options">
									<details<?php echo esc_attr($option_stat) ?>>
										<summary title="<?php echo esc_attr($texts['drag']) ?>" class="actions">
											<h5><?php printf( esc_html($texts['head_o']), '<span class="count">'. ($option_idx + 1) .'</span>' ) ?></h5>
											<p>(
												<a class="add" title="<?php echo esc_attr($texts['add_o']) ?>"><?php _e('Add') ?></a>
												<a class="del" title="<?php echo esc_attr($texts['del_o']) ?>"><?php _e('Remove') ?></a>
											)</p>
										</summary>
										<ul class="main">
											<li>
												<label for="<?php echo esc_attr("{$f_id}-options-{$option_key}-label") ?>"><?php _e('Label') ?></label>
												<input id="<?php echo esc_attr("{$f_id}-options-{$option_key}-label") ?>" class="kcsb-slug required regular-text" type="text" name="<?php echo esc_attr("{$f_name}[options][{$option_key}]") ?>[label]" value="<?php echo esc_attr($option['label']) ?>" />
											</li>
											<li>
												<label for="<?php echo esc_attr("{$f_id}-options-{$option_key}-value") ?>"><?php _e('Value', 'kc-settings') ?></label>
												<input id="<?php echo esc_attr("{$f_id}-options-{$option_key}-value") ?>" class="kcsb-slug required regular-text" type="text" name="<?php echo esc_attr("{$f_name}[options][{$option_key}]") ?>[key]" value="<?php echo esc_attr($option['key']) ?>" />
											</li>
										</ul>
									</details>
								</li>
								<?php $option_idx++; } ?>
							</ul>
						</li>
						<?php if ( !isset($f_val['cb']) ) $f_val['cb'] = ''; ?>
						<li class="childFieldType" data-dep='special'>
							<label for="<?php echo esc_attr("{$f_id}-cb") ?>" class="ml"><?php _e('Callback', 'kc-settings') ?></label>
							<input id="<?php echo esc_attr("{$f_id}-cb") ?>" class="mi kcsb-slug required regular-text" type="text" name="<?php echo esc_attr($f_name) ?>[cb]" value="<?php echo esc_attr($f_val['cb']) ?>" />
						</li>
						<?php if ( !isset($f_val['args']) ) $f_val['args'] = ''; ?>
						<li class="childFieldType" data-dep='special'>
							<label for="<?php echo esc_attr("{$f_id}-args") ?>" class="ml"><?php _e('Arguments', 'kc-settings') ?></label>
							<div class="mi">
								<input id="<?php echo esc_attr("{$f_id}-args") ?>" class="kcsb-slug regular-text" type="text" name="<?php echo esc_attr($f_name) ?>[args]" value="<?php echo esc_attr($f_val['args']) ?>" />
								<p class="description"><?php _e('String or function name, optional.', 'kc-settings') ?></p>
							</div>
						</li>
						<?php if ( $mode !== 'subfields' ) { ?>
						<li class="childFieldType" data-dep='multiinput'>
							<h5><?php _e('Sub-fields', 'kc-settings') ?></h5>
							<?php
								if ( !isset($f_val['subfields']) || !is_array($f_val['subfields']) || empty($f_val['subfields']) )
									$f_val['subfields'] = array( array( 'id' => '', 'title' => '', 'type' => 'text' ) );
								self::_fields( $f_val['subfields'], $f_val, $f_name, $f_id, 'subfields' );
							?>
						</li>
						<?php } ?>
						<li class="childFieldType" data-dep='editor'>
							<label class="ml"><?php _e('Editor settings', 'kc-settings') ?></label>
							<fieldset class="mi">
								<?php
									echo kcForm::field(array(
										'type'    => 'checkbox',
										'attr'    => array( 'name' => "{$f_name}[editor_settings][]" ),
										'options' => $options['editor_settings'],
										'current' => isset($f_val['editor_settings']) ? $f_val['editor_settings'] : self::$data['defaults']['sections'][0]['fields'][0]['editor_settings'],
										'none'    => false,
									)); // xss ok
								?>
								<input type="hidden" name="<?php echo esc_attr("{$f_name}[editor_settings][]") ?>" value="_kc-check" />
							</fieldset>
						</li>
					</ul>
				</details>
			</li>
			<?php $field_idx++; } ?>
		</ul>
	<?php }


	public static function builder() {
		$options = self::$data['options'];
		$url = self::$data['url'];
		if ( self::$item_to_edit ) {
			$url = add_query_arg( 'action', 'new', $url );
			$values = self::$item_to_edit;
			$form_class = 'editing';
			$submit_text = __('Save Changes');
		}
		else {
			$values = self::$data['defaults'];
			$form_class = ( self::$table->current_action() == 'new' ) ? '' : 'hidden';
			$submit_text = __('Create Setting', 'kc-settings');
		}

		?>
		<div class="wrap">
			<?php screen_icon('tools'); ?>
			<h2>
				<?php _e('KC Settings', 'kc-settings') ?>
				<a id="new-kcsb" class="add-new-h2" href="<?php esc_url($url) ?>"><?php _e('Add New') ?></a>
			</h2>
			<h3><?php _e('Saved Settings', 'kc-settings') ?></h3>
			<form id="kcsb-table" action="" method="post">
				<?php
					self::$table->prepare_items();
					self::$table->display();
				?>
			</form>

			<?php if ( self::$item_to_export ) { ?>
			<h3><?php _e('Export Data', 'kc-settings') ?> <small class="hide-if-no-js"><a class='kc-sh' data-target='.kcsb-export' href="#"><?php _e('Hide', 'kc-settings') ?></a></small></h3>
			<div class="kcsb-export">
				<textarea class="widefat" cols="30" rows="20"><?php echo esc_textarea(self::$item_to_export) ?></textarea>
			</div>
			<?php } ?>

			<!-- Start builder -->
			<p class="hide-if-js"><?php _e('To create a setting, please enable javascript in your browser and reload this page.', 'kc-settings') ?></p>
			<div id="kcsb" class="<?php echo $form_class ?> form-table">
				<h3><?php _e('KC Settings Builder', 'kc-settings') ?></h3>
				<p class="description"><?php _e('Please <a href="#" class="kc-help-trigger">read the guide</a> before creating a setting.', 'kc-settings') ?></p>

				<form class="kcsb" action="options.php" method="post">
					<?php settings_fields('kcsb') ?>
					<h4><?php _e('Main', 'kc-settings') ?></h4>
					<ul class="general main">
						<li>
							<label for="_kcsb-id" class="ml"><?php _e('ID', 'kc-settings') ?></label>
							<input id="_kcsb-id" class="mi kcsb-slug kcsb-ids required regular-text" type="text" name="kcsb[id]" value="<?php echo $values['id'] ?>" data-ids="settings" />
						</li>
						<li>
							<label for="_kcsb-status" class="ml"><?php _e('Status') ?></label>
							<?php
								echo kcForm::field(array(
									'type'   => 'select',
									'attr'   => array(
										'id'         => '_kcsb-status',
										'name'       => 'kcsb[status]',
										'class'      => 'mi',
									),
									'options' => $options['status'],
									'current' => isset( $values['status'] ) ? $values['status'] : 1,
									'none'    => false,
								)); // xss ok
							?>
						</li>
						<li>
							<label for="_kcsb-type" class="ml"><?php _e('Type') ?></label>
							<?php
								echo kcForm::field(array(
									'type'    => 'select',
									'attr'    => array(
										'id'         => '_kcsb-type',
										'name'       => 'kcsb[type]',
										'class'      => 'hasdep mi',
										'data-child' => '.childType',
									),
									'options' => $options['type'],
									'current' => $values['type'],
									'none'    => false,
								)); // xss ok
							?>
						</li>
						<li class="childType" data-dep="plugin">
							<label for="_kcsb-prefix" class="ml"><?php _e('Prefix', 'kc-settings') ?></label>
							<input id="_kcsb-prefix" class="mi kcsb-slug required regular-text" type="text" name="kcsb[prefix]" value="<?php echo esc_attr( $values['prefix'] ) ?>"/>
						</li>
						<li class="childType" data-dep='plugin'>
							<label for="_kcsb-menu_location" class="ml"><?php _e('Menu location', 'kc-settings') ?></label>
							<?php
								echo kcForm::field(array(
									'type'    => 'select',
									'attr'    => array(
										'id'    => '_kcsb-menu_location',
										'name'  => 'kcsb[menu_location]',
										'class' => 'mi',
									),
									'options' => $options['menu_location'],
									'current' => $values['menu_location'],
									'none'    => false,
								)); // xss ok
							?>
						</li>
						<li class="childType" data-dep='plugin'>
							<label for="_kcsb-menu_title" class="ml"><?php _e('Menu title', 'kc-settings') ?></label>
							<input id="_kcsb-menu_title" class="mi required regular-text" type="text" name="kcsb[menu_title]" value="<?php echo esc_attr( $values['menu_title'] ) ?>"/></li>
						<li class="childType" data-dep="plugin">
							<label for="_kcsb-page_title" class="ml"><?php _e('Page title', 'kc-settings') ?></label>
							<input id="_kcsb-page_title" class="mi required regular-text" type="text" name="kcsb[page_title]" value="<?php echo esc_attr( $values['page_title'] ) ?>" />
						</li>
						<li class="childType" data-dep='plugin'>
							<label for="_kcsb-display" class="ml"><?php _e('Page mode', 'kc-settings') ?></label>
							<?php
								echo kcForm::field(array(
									'type'    => 'select',
									'attr'    => array(
										'id'         => '_kcsb-display',
										'name'       => 'kcsb[display]',
										'class'      => 'hasdep mi',
										'data-child' => '.childDisplay',
									),
									'options' => $options['display'],
									'current' => $values['display'],
									'none'    => false,
								)); // xss ok
							?>
						</li>
						<li class="childType" data-dep='post'>
							<label for="_kcsb-post_type" class="ml"><?php _e('Post type', 'kc-settings') ?></label>
							<?php
								if ( empty($options['post_types']) ) {
								?>
								<p><?php __('No public post type found', 'kc-settings') ?></p>
								<?php
								}
								else {
									echo kcForm::field(array(
										'type'    => 'select',
										'attr'    => array(
											'id'    => '_kcsb-post_type',
											'name'  => 'kcsb[post_type]',
											'class' => 'mi',
										),
										'options' => $options['post_types'],
										'current' => $values['post_type'],
										'none'    => false,
									)); // xss ok
								}
							?>
						</li>
						<li class="childType" data-dep='term'>
							<label for="_kcsb-taxonomies" class="ml"><?php _e('Taxonomies', 'kc-settings') ?></label>
							<?php
								if ( empty($options['taxonomies']) ) {
								?>
								<p><?php __('No public taxonomy found', 'kc-settings') ?></p>
								<?php
								}
								else {
									echo kcForm::field(array(
										'type'    => 'select',
										'attr'    => array(
											'id'    => '_kcsb-taxonomies',
											'name'  => 'kcsb[taxonomy]',
											'class' => 'mi',
										),
										'options' => $options['taxonomies'],
										'current' => $values['taxonomy'],
										'none'    => false,
									)); // xss ok
								}
							?>
						</li>
					</ul>

					<h4><?php _e('Sections', 'kc-settings') ?></h4>
					<ul class="sections kc-rows">
						<?php
							$section_num = count( $values['sections'] );
							$section_idx = 0;
							foreach ( $values['sections']  as $section_key => $section ) {
								$s_name = "kcsb[sections][{$section_key}]";
								$s_val  = $values['sections'][$section_key];
								$s_id   = "_kcsb-sections-{$section_key}";
								$s_stat = ( $section_num === 1 && $section_idx === 0 ) ? ' open' : '';
						?>
						<li class="row" data-mode="sections">
							<details<?php echo esc_attr($s_stat) ?>>
								<summary title="<?php _e('Drag to reorder section', 'kc-settings') ?>" class="actions">
								<h5><?php printf( __('Section #%s', 'kc-settings'), '<span class="count">' .($section_idx + 1) .'</span>' ) ?></h5>
								<p>(
									<a class="add" title="<?php _e('Add new section', 'kc-settings') ?>"><?php _e('Add') ?></a>
									<a class="del" title="<?php _e('Remove this section', 'kc-settings') ?>"><?php _e('Remove') ?></a>
								)</p>
								</summary>
								<ul class="main">
									<li>
										<label for="<?php echo esc_attr("{$s_id}-id") ?>" class="ml"><?php _e('ID', 'kc-settings') ?></label>
										<input id="<?php echo esc_attr("{$s_id}-id") ?>" class="mi kcsb-slug kcsb-ids required regular-text" type="text" name="<?php echo esc_attr($s_name) ?>[id]" value="<?php echo esc_attr($s_val['id']) ?>" data-ids="sections" />
									</li>
									<li>
										<label for="<?php echo esc_attr("{$s_id}-title") ?>" class="ml"><?php _e('Title') ?></label>
										<input id="<?php echo esc_attr("{$s_id}-title") ?>" class="mi required regular-text" type="text" name="<?php echo esc_attr($s_name) ?>[title]" value="<?php echo esc_attr($s_val['title']) ?>" />
									</li>
									<li>
										<label for="<?php echo esc_attr("{$s_id}-desc") ?>" class="ml"><?php _e('Description', 'kc-settings') ?></label>
										<div class="mi">
											<textarea id="<?php echo esc_attr("{$s_id}-desc") ?>" name="<?php echo esc_attr($s_name) ?>[desc]" cols="25" rows="4"><?php echo esc_textarea($s_val['desc']) ?></textarea>
											<p class="description"><?php _e('Optional', 'kc-settings') ?></p>
										</div>
									</li>
									<?php if ( empty($options['role']) ) { ?>
									<li class="childType" data-dep='post'>
										<label class="ml"><?php _e('Roles', 'kc-settings') ?></label>
										<fieldset class="mi">
											<?php
												echo kcForm::field(array(
													'type'      => 'checkbox',
													'attr'      => array( 'name' => "{$s_name}[role][]" ),
													'options'   => $options['role'],
													'current'   => !empty($s_val['role']) ? $s_val['role'] : array(),
												)); // xss ok
											?>
											<p class="description"><?php _e('Check one or more to only show this section for certain user roles (optional).', 'kc-settings') ?></p>
										</fieldset>
									</li>
									<?php } if ( !isset($s_val['metabox']) ) $s_val['metabox'] = array('context' => 'normal', 'priority' => 'default'); ?>
									<li class="childType childDisplay" data-dep='["post", "metabox"]'>
										<label class="ml"><?php _e('Metabox', 'kc-settings') ?></label>
										<ul class="mi main">
											<?php foreach ( $options['metabox'] as $mb_prop => $prop ) { ?>
											<li class="kcsb-sub">
												<label for="<?php echo "{$s_id}-metabox-{$mb_prop}" ?>"><?php echo $prop['label'] ?></label>
												<?php
													echo kcForm::field(array(
														'type'    => 'select',
														'attr'    => array(
															'id'       => "{$s_id}-metabox-{$mb_prop}",
															'name'     => "{$s_name}[metabox][$mb_prop]",
															'required' => 'required',
														),
														'options' => $prop['options'],
														'current' => $s_val['metabox'][$mb_prop],
														'none'    => false,
													)); // xss ok
												?>
											</li>
											<?php } ?>
										</ul>
									</li>
								</ul>
								<h5><?php _e('Fields', 'kc-settings') ?></h5>
								<?php self::_fields( $section['fields'], $s_val, $s_name, $s_id ) ?>
							</details>
						</li>
						<?php $section_idx++; } ?>
					</ul>
					<div class="submit">
						<?php submit_button( $submit_text, 'primary', 'submit', false ) ?>
						<a href="#" class="button alignright kcsb-cancel"><?php _e('Cancel') ?></a>
					</div>
				</form>
			</div>
			<!-- End builder -->
		</div>
	<?php }


	private static function _exporter( $type, $entry ) {
		$func = 'my' . $type . '_options_' . date( 'YmdHis' );
		if ( $type == 'plugin' ) {
			unset( $entry['id'] );
			unset( $entry['status'] );
			unset( $entry['type'] );
			$content = kc_var_export( $entry, true, 2 );
		}
		else {
			$content = kc_var_export( $entry['options'], true, 4 );
		}
		$content = preg_replace( '/(\d+ => )/m', '', $content );

		$out = '
<?php

/**
 * KC Settings fields, generated by KC Settings Builder
 */
function '.$func.'( $groups ) {';
switch ( $type ) {
	case 'post' :
	case 'term' :
		$object_type = ( $type == 'post' ) ? $entry['post_type'] : $entry['taxonomy'];
		$out .= '
	$groups[] = array (
		\''.$object_type.'\' => '. $content .
		'
	);';
	break;
	case 'user' :
		$out .= '
	$groups[] = array (
		' . $content .
		'
	);';
	break;
	case 'plugin' :
	$out .= '
	$groups[] = '. $content .
		';';
	break;
}
	$out .= '

	return $groups;
}
add_filter( \'kc_'.$type.'_settings\', \''.$func.'\' );
';
		return $out;
	}
}

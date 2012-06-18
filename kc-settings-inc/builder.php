<?php

class kcSettings_builder {
	protected static $data = array(
		'defaults' => array(
			'id'            => '',
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
							'type'              => 'input',
							'attr'              => '',
							'option_type'       => 'custom',
							'option_predefined' => 'yesno',
							'options'           => array(
								array(
									'key'   => '',
									'label' => ''
								)
							)
						)
					)
				)
			)
		)
	);

	protected static $table;
	protected static $item_to_edit;
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
				'plugin' => array(
					'value' => 'plugin',
					'label' => __('Plugin / theme settings', 'kc-settings')
				),
				'post' => array(
					'value'   => 'post',
					'label'   => __('Post metadata (custom fields)', 'kc-settings'),
					'default' => true
				),
				'term' => array(
					'value' => 'term',
					'label' => __('Term metadata', 'kc-settings')
				),
				'user' => array(
					'value' => 'user',
					'label' => __('User metadata', 'kc-settings')
				)
			),
			'menu_location' => array(
				array(
					'value'   => 'options-general.php',
					'label'   => __('Settings'),
					'default' => true
				),
				array(
					'value' => 'themes.php',
					'label' => __('Appearance')
				),
				array(
					'value' => 'index.php',
					'label' => __('Dashboard')
				),
				array(
					'value' => 'plugins.php',
					'label' => __('Plugins')
				),
				array(
					'value' => 'tools.php',
					'label' => __('Tools')
				),
				array(
					'value' => 'plugins.php',
					'label' => __('Plugins')
				),
				array(
					'value' => 'users.php',
					'label' => __('Users')
				),
				array(
					'value' => 'upload.php',
					'label' => __('Media')
				),
				array(
					'value' => 'link-manager.php',
					'label' => __('Links')
				),
				array(
					'value' => 'edit-comments.php',
					'label' => __('Comments')
				),
				array(
					'value' => 'edit.php',
					'label' => __('Posts')
				),
			),
			'display' => array(
				array(
					'value' => 'metabox',
					'label' => __('Metaboxes', 'kc-settings')
				),
				array(
					'value' => 'plain',
					'label' => __('Plain', 'kc-settings')
				)
			),
			'string_fields' => array(
				array(
					'value'   => 'text',
					'label'   => __('Text', 'kc-settings'),
					'default' => true
				),
				array(
					'value' => 'textarea',
					'label' => __('Textarea', 'kc-settings')
				),
				array(
					'value' => 'color',
					'label' => __('Color', 'kc-settings')
				),
				array(
					'value' => 'date',
					'label' => __('Date', 'kc-settings')
				),
				array(
					'value' => 'number',
					'label' => __('Number', 'kc-settings')
				),
				array(
					'value' => 'email',
					'label' => __('Email', 'kc-settings')
				),
				array(
					'value' => 'password',
					'label' => __('Password', 'kc-settings')
				),
				array(
					'value' => 'url',
					'label' => __('URL', 'kc-settings')
				),
				array(
					'value' => 'tel',
					'label' => __('Telephone', 'kc-settings')
				),
				array(
					'value' => 'month',
					'label' => __('Month', 'kc-settings')
				),
				array(
					'value' => 'week',
					'label' => __('Week', 'kc-settings')
				),
				array(
					'value' => 'time',
					'label' => __('Time', 'kc-settings')
				),
				array(
					'value' => 'datetime-local',
					'label' => __('Datetime (local)', 'kc-settings')
				),
				array(
					'value' => 'datetime',
					'label' => __('Datetime (with timezone)', 'kc-settings')
				)
			),
			'metabox' => array(
				'context'   => array(
					'label'   => __('Context', 'kc-settings'),
					'options' => array(
						array(
							'label' => __('Normal', 'kc-settings'),
							'value' => 'normal'
						),
						array(
							'label' => __('Advanced', 'kc-settings'),
							'value' => 'advanced'
						),
						array(
							'label' => __('Side', 'kc-settings'),
							'value' => 'side'
						)
					)
				),
				'priority' => array(
					'label'   => __('Priority', 'kc-settings'),
					'options' => array(
						array(
							'label' => __('High', 'kc-settings'),
							'value' => 'high'
						),
						array(
							'label' => __('Default', 'kc-settings'),
							'value' => 'default'
						),
						array(
							'label' => __('Low', 'kc-settings'),
							'value' => 'low'
						)
					)
				)
			),
			'option_type' => array(
				'predefined' => __('Predefined options', 'kc-settings'),
				'custom'     => __('Custom options', 'kc-settings')
			),
			'option_predefined' => array(
				'yesno'               => __('Yes / No', 'kc-settings'),
				'post_types'          => __('Post types (public)', 'kc-settings'),
				'post_types_all'      => __('Post types (all)', 'kc-settings'),
				'taxonomies'          => __('Taxonomies (public)', 'kc-settings'),
				'taxonomies_all'      => __('Taxonomies (all)', 'kc-settings'),
				'nav_menus'           => __('Nav menus', 'kc-settings'),
				'image_sizes_all'     => __('Image sizes (all)', 'kc-settings'),
				'image_sizes_custom'  => __('Image sizes (custom)', 'kc-settings'),
				'image_sizes_default' => __('Image sizes (default)', 'kc-settings'),
				'post_statuses'       => __('Post statuses', 'kc-settings'),
				'roles'               => __('User roles', 'kc-settings'),
				'sidebars'            => __('Sidebars', 'kc-settings')
			)
		);

		$options['field'] = array_merge( $options['string_fields'], array(
			array(
				'value' => 'file',
				'label' => __('File', 'kc-settings')
			),
			array(
				'value' => 'checkbox',
				'label' => __('Checkbox', 'kc-settings')
			),
			array(
				'value' => 'radio',
				'label' => __('Radio', 'kc-settings')
			),
			array(
				'value' => 'select',
				'label' => __('Select', 'kc-settings')
			),
			array(
				'value' => 'multiselect',
				'label' => __('Select (multiple)', 'kc-settings')
			),
			array(
				'value' => 'multiinput',
				'label' => __('Multiinput', 'kc-settings')
			),
			array(
				'value' => 'special',
				'label' => __('Special', 'kc-settings')
			)
		) );
		$options['post_types'] = kcSettings_options::$post_types;
		$options['taxonomies'] = kcSettings_options::$taxonomies;
		$options['role'] = kcSettings_options::$roles;

		$options['filemode'] = array(
			array(
				'value' => 'single',
				'label' => __('Single file', 'kc-settings'),
				'default' => true
			),
			array(
				'value' => 'radio',
				'label' => __('Single selection', 'kc-settings')
			),
			array(
				'value' => 'checkbox',
				'label' => __('Multiple selections', 'kc-settings')
			)
		);

		return $options;
	}


	public static function create_page() {
		$page = add_options_page( __('KC Settings', 'kc-settings'), __('KC Settings', 'kc-settings'), 'manage_options', 'kcsb', array(__CLASS__, 'builder') );
		# Set scripts and styles
		kcSettings::add_page( $page );

		# Help
		kcSettings::add_help( $page, array(
			array(
				'id'      => 'kcsb',
				'title'   => __( 'KC Settings Builder', 'kc-settings' ),
				'content' =>
					'<ul>
						<li>'.__('All fields are required, unless the label is green.', 'kc-settings').'</li>
						<li>'.__('Some fields depend on other field(s), they will be shown when the dependency is selected/checked.', 'kc-settings').'</li>
						<li>'.__('Some fields (eg. ID, Prefix) can only be filled with alphanumerics, dashes and underscores, must be unique, and cannot begin with dashes or underscores. ', 'kc-settings').'</li>
					</ul>'
			),
			array(
				'id'      => 'kcsb-side',
				'title'   => __('Links'),
				'sidebar' => true,
				'content' =>
					'<ul>
						<li><a href="http://kucrut.github.com/kc-settings/">'.__('Online documentation', 'kc-settings').'</a></li>
						<li><a href="http://wordpress.org/tags/kc-settings?forum_id=10">'.__('Support', 'kc-settings').'</a></li>
						<li><a href="http://kucrut.org/contact/">'.__('Contact', 'kc-settings').'</a></li>
					</ul>'
			)
		) );

		add_action( "load-{$page}", array(__CLASS__, 'load') );
	}


	public static function builder_link( $plugin_meta, $plugin_file, $plugin_data ) {
		if ( $plugin_data['Name'] == 'KC Settings' )
			$plugin_meta[] = '<a href="'.admin_url('options-general.php?page=kcsb').'">'.__('KC Settings Builder', 'kc-settings').'</a>';

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
				'options'  => self::$data['options']
			)
		) );
		self::$table = $table;

		$action = $table->current_action();
		if ( !$action || !in_array($action, array('delete', 'edit', 'purge', 'empty', 'clone')) )
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
		}

		if ( $update )
			update_option( 'kcsb', self::$data['kcsb']['settings'] );
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


	public static function builder() {
		$options = self::$data['options'];
		if ( self::$item_to_edit ) {
			$values = self::$item_to_edit;
			$form_class = '';
			$button_txt = __('Save Changes');
		}
		else {
			$values = self::$data['defaults'];
			$form_class = ' class="hidden"';
			$button_txt = __('Create Setting', 'kc-settings');
		}

		?>
		<div class="wrap">
			<?php screen_icon('tools'); ?>
			<h2><?php echo __('KC Settings', 'kc-settings')." <a id='new-kcsb' class='add-new-h2' href='#'>".__('Add New')."</a>" ?></h2>
			<div class="kcsb-block">
				<h3><?php _e('Saved Settings', 'kc-settings') ?></h3>
				<form id="kcsb-table" action="" method="post">
					<?php
						self::$table->prepare_items();
						self::$table->display();
					?>
				</form>
			</div>


			<!-- Start builder -->
			<p class="hide-if-js"><?php _e('To create a setting, please enable javascript in your browser and reload this page.', 'kc-settings') ?></p>
			<div id="kcsb"<?php echo $form_class ?>>
				<h3><?php _e('KC Settings Builder', 'kc-settings') ?></h3>
				<p class="description"><?php _e('Please <a href="#" class="kc-help-trigger">read the guide</a> before creating a setting.', 'kc-settings') ?></p>

				<form class="kcsb" action="options.php" method="post">
					<?php settings_fields('kcsb') ?>
					<h4><?php _e('Main', 'kc-settings') ?></h4>
					<ul>
						<li>
							<label for="_kcsb-id" class="kcsb-ml"><?php _e('ID', 'kc-settings') ?></label>
							<input id="_kcsb-id" class="kcsb-mi kcsb-slug kcsb-ids required" type="text" name="kcsb[id]" value="<?php echo $values['id'] ?>" data-ids="settings" />
						</li>
						<li>
							<label for="_kcsb-type" class="kcsb-ml"><?php _e('Type') ?></label>
							<?php
								echo kcForm::select(array(
									'attr'         => array(
										'id'         => '_kcsb-type',
										'name'       => 'kcsb[type]',
										'class'      => 'hasdep kcsb-mi',
										'data-child' => '.childType'
									),
									'options' => $options['type'],
									'current' => $values['type'],
									'none'    => false
								));
							?>
						</li>
						<li class="childType" data-dep="plugin">
							<label for="_kcsb-prefix" class="kcsb-ml"><?php _e('Prefix', 'kc-settings') ?></label>
							<input id="_kcsb-prefix" class="kcsb-mi kcsb-slug required" type="text" name="kcsb[prefix]" value="<?php esc_attr_e( $values['prefix'] ) ?>"/>
						</li>
						<li class="childType" data-dep='plugin'>
							<label for="_kcsb-menu_location" class="kcsb-ml"><?php _e('Menu location', 'kc-settings') ?></label>
							<?php
								echo kcForm::select(array(
									'attr'    => array(
										'id'    => '_kcsb-menu_location',
										'name'  => 'kcsb[menu_location]',
										'class' => 'kcsb-mi'
									),
									'options' => $options['menu_location'],
									'current' => $values['menu_location'],
									'none'    => false
								));
							?>
						</li>
						<li class="childType" data-dep='plugin'>
							<label for="_kcsb-menu_title" class="kcsb-ml"><?php _e('Menu title', 'kc-settings') ?></label>
							<input id="_kcsb-menu_title" class="kcsb-mi required" type="text" name="kcsb[menu_title]" value="<?php esc_attr_e( $values['menu_title'] ) ?>"/></li>
						<li class="childType" data-dep="plugin">
							<label for="_kcsb-page_title" class="kcsb-ml"><?php _e('Page title', 'kc-settings') ?></label>
							<input id="_kcsb-page_title" class="kcsb-mi required" type="text" name="kcsb[page_title]" value="<?php esc_attr_e( $values['page_title'] ) ?>" />
						</li>
						<li class="childType" data-dep='plugin'>
							<label for="_kcsb-display" class="kcsb-ml"><?php _e('Page mode', 'kc-settings') ?></label>
							<?php
								echo kcForm::select(array(
									'attr'    => array(
										'id'         => '_kcsb-display',
										'name'       => 'kcsb[display]',
										'class'      => 'hasdep kcsb-mi',
										'data-child' => '.childDisplay'
									),
									'options' => $options['display'],
									'current' => $values['display'],
									'none'    => false
								));
							?>
						</li>
						<li class="childType" data-dep='post'>
							<label for="_kcsb-post_type" class="kcsb-ml"><?php _e('Post type', 'kc-settings') ?></label>
							<?php
								if ( empty($options['post_types']) )
									echo '<p>'.__('No public post type found', 'kc-settings').'</p>';
								else
									echo kcForm::field(array(
										'type'    => 'select',
										'attr'    => array(
											'id'    => '_kcsb-post_type',
											'name'  => 'kcsb[post_type]',
											'class' => 'kcsb-mi'
										),
										'options' => $options['post_types'],
										'current' => $values['post_type'],
										'none'    => false
									));
							?>
						</li>
						<li class="childType" data-dep='term'>
							<label for="_kcsb-taxonomies" class="kcsb-ml"><?php _e('Taxonomies', 'kc-settings') ?></label>
							<?php
								if ( empty($options['taxonomies']) )
									echo '<p>'.__('No public taxonomy found', 'kc-settings').'</p>';
								else
									echo kcForm::field(array(
										'type'    => 'select',
										'attr'    => array(
											'id'    => '_kcsb-taxonomies',
											'name'  => 'kcsb[taxonomy]',
											'class' => 'kcsb-mi'
										),
										'options' => $options['taxonomies'],
										'current' => $values['taxonomy'],
										'none'    => false
									));
								?>
						</li>
					</ul>

					<h4><?php _e('Sections', 'kc-settings') ?></h4>
					<ul class="sections kc-rows">
						<?php
							$count_s = 0;
							foreach ( $values['sections']  as $idxS => $section ) {
								$count_s++;
								$s_name = "kcsb[sections][{$idxS}]";
								$s_val  = $values['sections'][$idxS];
								$s_id   = "_kcsb-sections-{$idxS}";
						?>
						<li class="row" data-mode="sections">
							<details>
								<summary title="<?php _e('Drag to reorder section', 'kc-settings') ?>">
								<div class="actions">
									<h5><?php _e( sprintf('Section #%s', "<span class='count'>{$count_s}</span>"), 'kc-settings') ?></h5>
									<p>(
										<a class="add" title="<?php _e('Add new section', 'kc-settings') ?>"><?php _e('Add') ?></a>
										<a class="del" title="<?php _e('Remove this section', 'kc-settings') ?>"><?php _e('Remove') ?></a>
									)</p>
								</div>
								</summary>
								<ul>
									<li>
										<label for="<?php echo "{$s_id}-id" ?>" class="kcsb-ml"><?php _e('ID', 'kc-settings') ?></label>
										<input id="<?php echo "{$s_id}-id" ?>" class="kcsb-mi kcsb-slug kcsb-ids required" type="text" name="<?php echo $s_name ?>[id]" value="<?php esc_attr_e($s_val['id']) ?>" data-ids="sections" />
									</li>
									<li>
										<label for="<?php echo "{$s_id}-title" ?>" class="kcsb-ml"><?php _e('Title') ?></label>
										<input id="<?php echo "{$s_id}-title" ?>" class="kcsb-mi required" type="text" name="<?php echo $s_name ?>[title]" value="<?php esc_attr_e($s_val['title']) ?>" />
									</li>
									<li>
										<label for="<?php echo "{$s_id}-desc" ?>" class="kcsb-ml nr"><?php _e('Description') ?></label>
										<textarea id="<?php echo "{$s_id}-desc" ?>" class="kcsb-mi" name="<?php echo $s_name ?>[desc]" cols="25" rows="4"><?php echo esc_textarea($s_val['desc']) ?></textarea>
									</li>
									<li class="childType" data-dep='post'>
										<label class="kcsb-ml nr"><?php _e('Roles', 'kc-settings') ?></label>
										<ul class="kcsb-mi">
											<?php
												if ( empty($options['role']) )
													echo '<p>'.__('No role found.', 'kc-settings').'</p>';
												else
													echo kcForm::field(array(
														'type'      => 'checkbox',
														'attr'      => array('name' => "{$s_name}[role][]", 'class' => 'kcsb-mi'),
														'options'   => $options['role'],
														'current'   => isset($s_val['role']) ? $s_val['role'] : array(),
														'check_sep' => array("\t<li>", "</li>\n")
													));
											?>
										</ul>
									</li>
									<?php if ( !isset($s_val['metabox']) ) $s_val['metabox'] = array('context' => 'normal', 'priority' => 'default'); ?>
									<li class="childType childDisplay" data-dep='["post", "metabox"]'>
										<label class="kcsb-ml"><?php _e('Metabox', 'kc-settings') ?></label>
										<ul class="kcsb-mi">
											<?php foreach ( $options['metabox'] as $mb_prop => $prop ) { ?>
											<li class="kcsb-sub">
												<label for="<?php echo "{$s_id}-metabox-{$mb_prop}" ?>"><?php echo $prop['label'] ?> : </label>
												<?php
													echo kcForm::select(array(
														'attr'    => array(
															'id'       => "{$s_id}-metabox-{$mb_prop}",
															'name'     => "{$s_name}[metabox][$mb_prop]",
															'required' => 'required'
														),
														'options' => $prop['options'],
														'current' => $s_val['metabox'][$mb_prop],
														'none'    => false
													));
												?>
											</li>
											<?php } ?>
										</ul>
									</li>
									<li class="fields">
										<h4 class="kcsb-ml"><?php _e('Fields', 'kc-settings') ?></h4>
										<ul class="kcsb-mi kc-rows">
											<?php
												$count_f = 0;
												foreach ( $section['fields'] as $idxF => $field ) {
													$count_f++;
													$f_name = "{$s_name}[fields][{$idxF}]";
													$f_val  = $s_val['fields'][$idxF];
													$f_id   = "{$s_id}-fields-{$idxF}";
											?>
											<li class="row" data-mode="fields">
												<details>
													<summary title="<?php _e('Drag to reorder field', 'kc-settings') ?>">
														<div class="actions">
															<h5><?php _e( sprintf('Field #%s', "<span class='count'>{$count_f}</span>"), 'kc-settings') ?></h5>
															<p>(
																<a class="add" title="<?php _e('Add new field', 'kc-settings') ?>"><?php _e('Add') ?></a>
																<a class="del" title="<?php _e('Remove this field', 'kc-settings') ?>"><?php _e('Remove') ?></a>
															)</p>
														</div>
													</summary>
													<ul>
														<li>
															<label for="<?php echo "{$f_id}-id" ?>" class="kcsb-ml"><?php _e('ID', 'kc-settings') ?></label>
															<input id="<?php echo "{$f_id}-id" ?>" class="kcsb-mi kcsb-slug kcsb-ids required" type="text" name="<?php echo $f_name ?>[id]" value="<?php esc_attr_e($f_val['id']) ?>" data-ids="fields" />
														</li>
														<li>
															<label for="<?php echo "{$f_id}-title" ?>" class="kcsb-ml"><?php _e('Label') ?></label>
															<input id="<?php echo "{$f_id}-title" ?>" class="kcsb-mi required" type="text" name="<?php echo $f_name ?>[title]" value="<?php esc_attr_e($f_val['title']) ?>" />
														</li>
														<li>
															<label for="<?php echo "{$f_id}-desc" ?>" class="kcsb-ml nr"><?php _e('Description') ?></label>
															<textarea id="<?php echo "{$f_id}-desc" ?>" class="kcsb-mi" name="<?php echo $f_name ?>[desc]" cols="25" rows="4"><?php echo esc_textarea($f_val['desc']) ?></textarea>
														</li>
														<li>
															<label for="<?php echo "{$f_id}-type" ?>" class="kcsb-ml"><?php _e('Type') ?></label>
															<?php
																echo kcForm::select(array(
																	'attr'    => array(
																		'id'         => "{$f_id}-type",
																		'name'       => "{$f_name}[type]",
																		'class'      => 'hasdep kcsb-mi',
																		'data-child' => '.childFieldType',
																		'data-scope' => 'li.row'
																	),
																	'options' => $options['field'],
																	'current' => $f_val['type'],
																	'none'    => false
																));
															?>
														</li>
														<li class="childFieldType" data-dep='file'>
															<label for="<?php echo "{$f_id}-mode" ?>" class="kcsb-ml"><?php _e('Mode', 'kcsb') ?></label>
															<?php
																echo kcForm::select(array(
																	'attr'    => array(
																		'id'         => "{$f_id}-mode",
																		'name'       => "{$f_name}[mode]",
																		'class'      => 'hasdep kcsb-mi',
																		'data-child' => '.childFileSize',
																		'data-scope' => 'li.row'
																	),
																	'options' => $options['filemode'],
																	'current' => isset($f_val['mode']) ? $f_val['mode'] : 'single',
																	'none'    => false
																));
															?>
														</li>
														<li class="childFileSize" data-dep='single'>
															<label for="<?php echo "{$f_id}-size" ?>" class="kcsb-ml"><?php _e('Preview Size', 'kcsb') ?></label>
															<?php
																echo kcForm::field(array(
																	'type'    => 'select',
																	'attr'    => array(
																		'id'    => "{$f_id}-size",
																		'name'  => "{$f_name}[size]",
																		'class' => 'kcsb-mi'
																	),
																	'options' => kcSettings_options::$image_sizes,
																	'current' => isset($f_val['size']) ? $f_val['size'] : 'thumbnail',
																	'none'    => false
																));
															?>
														</li>
														<li class="childFieldType" data-dep='["radio", "checkbox", "select", "multiselect"]'>
															<label for="<?php echo "{$f_id}-option_type" ?>" class="kcsb-ml"><?php _e('Options', 'kcsb') ?></label>
															<?php
																echo kcForm::field(array(
																	'type'    => 'select',
																	'attr'    => array(
																		'id'         => "{$f_id}-option_type",
																		'name'       => "{$f_name}[option_type]",
																		'class'      => 'hasdep kcsb-mi',
																		'data-child' => '.childFieldOptionType',
																		'data-scope' => 'li.row'
																	),
																	'options' => $options['option_type'],
																	'current' => isset( $f_val['option_type'] ) ? $f_val['option_type'] : 'predefined',
																	'none'    => false
																));
															?>
														</li>
														<li class="childFieldOptionType" data-dep='predefined'>
															<label for="<?php echo "{$f_id}-option_predefined" ?>" class="kcsb-ml"><?php _e('Predefined option', 'kcsb') ?></label>
															<?php
																echo kcForm::field(array(
																	'type'    => 'select',
																	'attr'    => array(
																		'id'    => "{$f_id}-option_predefined",
																		'name'  => "{$f_name}[option_predefined]",
																		'class' => 'kcsb-mi'
																	),
																	'options' => $options['option_predefined'],
																	'current' => isset( $f_val['option_predefined'] ) ? $f_val['option_predefined'] : 'yesno',
																	'none'    => false
																));
															?>
														</li>
														<li class="childFieldOptionType" data-dep='custom'>
															<label class="kcsb-ml"><?php _e('Custom options', 'kcsb') ?></label>
															<ul class="kcsb-mi kcsb-options kc-rows kc-sortable">
																<?php
																	if ( !isset($f_val['options']) || !is_array($f_val['options']) )
																		$f_val['options'] = array( array( 'key' => '', 'label' => '' ) );
																	foreach ( $f_val['options'] as $o_idx => $option ) {
																?>
																<li class="row" data-mode="options">
																	<label>
																		<span><?php _e('Value', 'kcsb') ?></span>
																		<input class="kcsb-slug required" type="text" name="<?php echo "{$f_name}[options][{$o_idx}]" ?>[key]" value="<?php esc_attr_e($option['key']) ?>" />
																	</label>
																	<label>
																		<span><?php _e('Label') ?></span>
																		<input class="required" type="text" name="<?php echo "{$f_name}[options][{$o_idx}]" ?>[label]" value="<?php esc_attr_e($option['label']) ?>" />
																	</label>
																	<p class="actions">
																		<a class="add" title="<?php _e('Add new option', 'kc-settings') ?>"><?php _e('Add') ?></a>
																		<a class="del" title="<?php _e('Remove this option', 'kc-settings') ?>"><?php _e('Remove') ?></a>
																	</p>
																</li>
																<?php } ?>
															</ul>
														</li>
														<?php if ( !isset($f_val['cb']) ) $f_val['cb'] = ''; ?>
														<li class="childFieldType" data-dep='special'>
															<label for="<?php echo "{$f_id}-cb" ?>" class="kcsb-ml"><?php _e('Callback', 'kcsb') ?></label>
															<input id="<?php echo "{$f_id}-cb" ?>" class="kcsb-mi kcsb-slug required" type="text" name="<?php echo $f_name ?>[cb]" value="<?php esc_attr_e($f_val['cb']) ?>" />
														</li>
														<?php if ( !isset($f_val['args']) ) $f_val['args'] = ''; ?>
														<li class="childFieldType" data-dep='special'>
															<label for="<?php echo "{$f_id}-args" ?>" class="kcsb-ml">
																<span class="nr"><?php _e('Arguments', 'kcsb') ?></span>
																<br/><small><em>(<?php _e('String or function name', 'kc-settings') ?>)</em></small>
															</label>
															<input id="<?php echo "{$f_id}-args" ?>" class="kcsb-mi kcsb-slug" type="text" name="<?php echo $f_name ?>[args]" value="<?php esc_attr_e($f_val['args']) ?>" />
														</li>
														<li class="childFieldType" data-dep='multiinput'>
															<label class="kcsb-ml"><?php _e('Sub-fields', 'kcsb') ?></label>
															<ul class="kcsb-mi kcsb-options kc-rows kc-sortable">
																<?php
																	if ( !isset($f_val['subfields']) || !is_array($f_val['subfields']) || empty($f_val['subfields']) ) {
																		$f_val['subfields'] = array(
																			array( 'id' => 'key', 'title' => __('Key', 'kc-settings'), 'type' => 'text' ),
																			array( 'id' => 'value', 'title' => __('Value', 'kc-settings'), 'type' => 'textarea' )
																		);
																	}

																	foreach ( $f_val['subfields'] as $sf_idx => $sf ) {
																?>
																<li class="row" data-mode="subfields">
																	<label>
																		<span><?php _e('ID') ?></span>
																		<input class="required" type="text" name="<?php echo "{$f_name}[subfields][{$sf_idx}]" ?>[id]" value="<?php esc_attr_e($sf['id']) ?>" />
																	</label>
																	<label>
																		<span><?php _e('Label') ?></span>
																		<input class="required" type="text" name="<?php echo "{$f_name}[subfields][{$sf_idx}]" ?>[title]" value="<?php esc_attr_e($sf['title']) ?>" />
																	</label>
																	<label>
																		<span><?php _e('Type') ?></span>
																		<?php
																			echo kcForm::select(array(
																				'attr'    => array( 'name' => "{$f_name}[subfields][{$sf_idx}][type]" ),
																				'options' => $options['string_fields'],
																				'current' => $sf['type'],
																				'none'    => false
																			));
																		?>
																	</label>
																	<p class="actions">
																		<a class="add" title="<?php _e('Add new sub-field', 'kc-settings') ?>"><?php _e('Add') ?></a>
																		<a class="del" title="<?php _e('Remove this sub-field', 'kc-settings') ?>"><?php _e('Remove') ?></a>
																	</p>
																</li>
																<?php } ?>
															</ul>
														</li>
													</ul>
												</details>
											</li>
											<?php } unset( $count_f ); ?>
										</ul>
									</li>
								</ul>
							</details>
						</li>
						<?php } unset( $count_s ); ?>
					</ul>
					<div class="submit">
						<button class="button-primary" name="submit" type="submit"><?php echo $button_txt; ?></button>
						<a href="#" class="button alignright kcsb-cancel"><?php _e('Cancel') ?></a>
					</div>
				</form>
			</div>
			<!-- End builder -->
		</div>
	<?php }
}
?>

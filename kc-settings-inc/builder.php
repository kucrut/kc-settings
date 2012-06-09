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
							'id'      => '',
							'title'   => '',
							'desc'    => '',
							'type'    => 'input',
							'attr'    => '',
							'options' => array(
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


	public static function init() {
		self::$data['options'] = self::_options();
		self::$data['kcsb'] = kcSettings::get_data('kcsb');

		add_action( 'admin_init', array(__CLASS__, 'register'), 21 );
		add_action( 'admin_menu', array(__CLASS__, 'create_page') );
		add_filter( 'plugin_row_meta', array(__CLASS__, 'builder_link'), 10, 3 );
		add_action( 'update_option_kcsb', array(__CLASS__, 'after_save'), 10, 2 );

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
					'value' => 'checkbox',
					'label' => __('Checkbox', 'kc-settings')
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
			)
		);

		$options['field'] = array_merge( $options['string_fields'], array(
			array(
				'value' => 'file',
				'label' => __('File', 'kc-settings')
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
				'title'   => 'Links',
				'sidebar' => true,
				'content' =>
					'<ul>
						<li><a href="http://kucrut.github.com/kc-settings/">'.__('Online documentation', 'kc-settings').'</a></li>
						<li><a href="http://wordpress.org/tags/kc-settings?forum_id=10">'.__('Support', 'kc-settings').'</a></li>
						<li><a href="http://kucrut.org/contact/">'.__('Contact', 'kc-settings').'</a></li>
					</ul>'
			)
		) );

		add_action( "load-{$page}", array(__CLASS__, 'goback') );
	}


	public static function builder_link( $plugin_meta, $plugin_file, $plugin_data ) {
		if ( $plugin_data['Name'] == 'KC Settings' )
			$plugin_meta[] = '<a href="'.admin_url('options-general.php?page=kcsb').'">'.__('KC Settings Builder', 'kc-settings').'</a>';

		return $plugin_meta;
	}


	public static function register() {
		register_setting( 'kcsb', 'kcsb', array(__CLASS__, 'validate') );
	}


	public static function goback() {
		if ( isset($_GET['action']) && $_GET['action'] != 'edit' && isset($_GET['id']) && !empty($_GET['id']) ) {
			$sID = $_GET['id'];
			check_admin_referer( "__kcsb__{$sID}" );

			$action = $_GET['action'];
			$settings = self::$data['kcsb']['settings'];

			switch ( $action ) {
				case 'clone' :
					if ( isset($_GET['new']) && !empty($_GET['new']) ) {
						$nID = trim( $_GET['new'] );
						if ( $nID && !isset($settings[$nID]) ) {
							$new = $settings[$sID];
							$new['id'] = $nID;
							$settings[$nID] = $new;
							update_option( 'kcsb', $settings );
						}
					}
				break;
				case 'delete' :
					unset( $settings[$sID] );
					update_option( 'kcsb', $settings );
				break;
				case 'empty' :
					$o = "{$settings[$sID]['prefix']}_settings";
					if ( get_option($o) !== false )
						update_option( $o, '' );
				break;
				case 'purge' :
					delete_option( "{$settings[$sID]['prefix']}_settings" );
				break;
			}

			self::_success();
		}
		else {
			$er = get_transient( 'kcsb' );
			if ( !empty($er) && isset($er['new']) ) {
				set_transient( 'kcsb', $er['item'] );

				$goto = wp_get_referer();
				$goto = remove_query_arg( 'settings-updated', $goto );
				$goto = add_query_arg( 'action', 'edit', $goto );
				wp_redirect( $goto );
				exit;
			}
		}
	}


	public static function validate( $values ) {
		/**
		 * Task: clone / delete an item
		 * Just return the values, assume it's valid
		 */
		if ( !isset($values['id']) ) {
			return $values;
		}

		/**
		 * Task: Add / Edit item: get all items, and if:
		 * 0. Error: store the new item in the transient db
		 * 1. Sucess: add the new item
		 */
		else {
			$settings = self::$data['kcsb']['settings'];

			if ( empty($values['id']) || $values['id'] == 'id' ) {
				$values['id'] = '';
				set_transient( 'kcsb', array('new' => true, 'item' => $values) );
			}
			else {
				$settings[$values['id']] = $values;
			}

			return $settings;
		}
	}


	public static function after_save( $old, $new ) {
		# Delete
		if ( count($old) > count($new) )
			$message = __('Setting successfully deleted.', 'kc-settings');
		# Add
		elseif ( !is_array($old) || count($old) < count($new) )
			$message = __('Setting successfully created.', 'kc-settings');
		# Edit/Update
		else
			$message = __('Setting successfully updated.', 'kc-settings');

		if ( !count( get_settings_errors() ) )
			add_settings_error('general', 'settings_updated', $message, 'updated');
		set_transient('settings_errors', get_settings_errors(), 30);

		self::_success();
	}


	private static function _success() {
		$goto = wp_get_referer();
		$goto = remove_query_arg( array('id', 'action'), $goto );
		$goto = add_query_arg( 'settings-updated', 'true', $goto );
		wp_redirect( $goto );
		exit;
	}


	public static function sns() {
		wp_enqueue_script( 'kcsb' );
	}


	public static function builder() {
		$options    = self::$data['options'];
		$values     = self::$data['defaults'];
		$form_class = ' class="hidden"';
		$button_txt = __('Create Setting', 'kc-settings');
		$mode       = 'default';

		if ( isset($_GET['action']) ) {
			$action = $_GET['action'];
			if ( $action == 'edit' ) {
				if ( isset($_GET['id']) && !empty($_GET['id']) ) {
					$id = $_GET['id'];
					if ( isset(self::$data['kcsb']['settings'][$id]) ) {
						$mode   = 'edit';
						$values = wp_parse_args( self::$data['kcsb']['settings'][$id], $values );
					}
					else {
						add_settings_error('general', 'warning', sprintf( __("There's no setting with ID %s. Are you cheating? ;)", 'kc-settings'), "&#8220;{$id}&#8221;") );
					}
				}
				else {
					$er = get_transient( 'kcsb' );
					if ( !empty($er) ) {
						$mode   = 'edit';
						$values = wp_parse_args( $er, $values );
						delete_transient( 'kcsb' );
						add_settings_error('general', 'not_saved', __('Settings were NOT saved! Please fill all the required fields.', 'kc-settings') );
						set_transient('settings_errors', get_settings_errors(), 30);
					}
				}
			}
		}

		settings_errors( 'general' );

		if ( $mode == 'edit' ) {
			$form_class = '';
			$button_txt = __('Save Changes');
		}

		?>
		<div class="wrap">
			<?php screen_icon('tools'); ?>
			<h2><?php echo __('KC Settings', 'kc-settings')." <a id='new-kcsb' class='add-new-h2' href='#'>".__('Add New')."</a>" ?></h2>

			<div class="kcsb-block">
				<h3><?php _e('Saved Settings', 'kc-settings') ?></h3>
			<?php
				require_once dirname( __FILE__ ) . '/builder-table.php';
				$table = new kcSettings_builder_table( array(
					'kcsb' => array(
						'settings' => self::$data['kcsb']['settings'],
						'options'  => $options
					)
				) );
				$table->prepare_items();
				$table->display();
			?>
			</div>


			<!-- Start builder -->
			<p class="hide-if-js"><?php _e('To create a setting, please enable javascript in your browser and reload this page.', 'kc-settings') ?></p>
			<div id="kcsb"<?php echo $form_class ?>>
				<h3><?php _e('KC Settings Builder', 'kc-settings') ?></h3>
				<p class="description"><?php _e('Please <a href="#" class="kc-help-trigger">read the guide</a> before creating a setting.', 'kc-settings') ?></p>

				<form class="kcsb" action="options.php" method="post">
					<?php settings_fields('kcsb') ?>
					<h4>Main</h4>
					<ul>
						<li>
							<label class="kcsb-ml"><?php _e('ID', 'kc-settings') ?></label>
							<input class="kcsb-mi kcsb-slug kcsb-ids required" type="text" name="kcsb[id]" value="<?php echo $values['id'] ?>" data-ids="settings" />
						</li>
						<li>
							<label class="kcsb-ml"><?php _e('Type') ?></label>
							<?php
								echo kcForm::select(array(
									'attr'    => array('name' => 'kcsb[type]', 'class' => 'hasdep kcsb-mi', 'data-child' => '.childType'),
									'options' => $options['type'],
									'current' => $values['type'],
									'none'    => false
								));
							?>
						</li>
						<li class="childType" data-dep="plugin">
							<label class="kcsb-ml"><?php _e('Prefix', 'kc-settings') ?></label>
							<input class="kcsb-mi kcsb-slug required" type="text" name="kcsb[prefix]" value="<?php esc_attr_e( $values['prefix'] ) ?>"/>
						</li>
						<li class="childType" data-dep='plugin'>
							<label class="kcsb-ml"><?php _e('Menu location', 'kc-settings') ?></label>
							<?php
								echo kcForm::select(array(
									'attr'    => array('name' => 'kcsb[menu_location]', 'class' => 'kcsb-mi'),
									'options' => $options['menu_location'],
									'current' => $values['menu_location'],
									'none'    => false
								));
							?>
						</li>
						<li class="childType" data-dep='plugin'>
							<label class="kcsb-ml"><?php _e('Menu title', 'kc-settings') ?></label>
							<input class="kcsb-mi required" type="text" name="kcsb[menu_title]" value="<?php esc_attr_e( $values['menu_title'] ) ?>"/></li>
						<li class="childType" data-dep="plugin">
							<label class="kcsb-ml"><?php _e('Page title', 'kc-settings') ?></label>
							<input class="kcsb-mi required" type="text" name="kcsb[page_title]" value="<?php esc_attr_e( $values['page_title'] ) ?>" />
						</li>
						<li class="childType" data-dep='plugin'>
							<label class="kcsb-ml"><?php _e('Page mode', 'kc-settings') ?></label>
							<?php
								echo kcForm::select(array(
									'attr'    => array('name' => 'kcsb[display]', 'class' => 'hasdep kcsb-mi', 'data-child' => '.childDisplay'),
									'options' => $options['display'],
									'current' => $values['display'],
									'none'    => false
								));
							?>
						</li>
						<li class="childType" data-dep='post'>
							<label class="kcsb-ml"><?php _e('Post type', 'kc-settings') ?></label>
							<?php
								if ( empty($options['post_types']) )
									echo '<p>'.__('No public post type found', 'kc-settings').'</p>';
								else
									echo kcForm::field(array(
										'type'    => 'select',
										'attr'    => array('name' => 'kcsb[post_type]', 'class' => 'kcsb-mi'),
										'options' => $options['post_types'],
										'current' => $values['post_type'],
										'none'    => false
									));
							?>
						</li>
						<li class="childType" data-dep='term'>
							<label class="kcsb-ml"><?php _e('Taxonomies', 'kc-settings') ?></label>
							<?php
								if ( empty($options['taxonomies']) )
									echo '<p>'.__('No public taxonomy found', 'kc-settings').'</p>';
								else
									echo kcForm::field(array(
										'type'    => 'select',
										'attr'    => array('name' => 'kcsb[taxonomy]', 'class' => 'kcsb-mi'),
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
								$s_val = $values['sections'][$idxS];
						?>
						<li class="row" data-mode="sections">
							<div class="actions">
								<h5 title="<?php _e('Drag to reorder section', 'kc-settings') ?>"><?php _e( sprintf('Section #%s', "<span class='count'>{$count_s}</span>"), 'kc-settings') ?></h5>
								<p>(
									<a class="add" title="<?php _e('Add new section', 'kc-settings') ?>"><?php _e('Add') ?></a>
									<a class="del" title="<?php _e('Remove this section', 'kc-settings') ?>"><?php _e('Remove') ?></a>
								)</p>
							</div>
							<ul>
								<li>
									<label class="kcsb-ml"><?php _e('ID', 'kc-settings') ?></label>
									<input class="kcsb-mi kcsb-slug kcsb-ids required" type="text" name="<?php echo $s_name ?>[id]" value="<?php esc_attr_e($s_val['id']) ?>" data-ids="sections" />
								</li>
								<li>
									<label class="kcsb-ml"><?php _e('Title') ?></label>
									<input class="kcsb-mi required" type="text" name="<?php echo $s_name ?>[title]" value="<?php esc_attr_e($s_val['title']) ?>" />
								</li>
								<li>
									<label class="kcsb-ml nr"><?php _e('Description') ?></label>
									<textarea class="kcsb-mi" name="<?php echo $s_name ?>[desc]" cols="25" rows="4"><?php echo esc_textarea($s_val['desc']) ?></textarea>
								</li>
								<li class="childType" data-dep='post'>
									<label class="kcsb-ml nr"><?php _e('Roles', 'kc-settings') ?></label>
									<ul class="kcsb-mi">
										<?php
											if ( !isset($s_val['role']) )
												$s_val['role'] = array();

											if ( empty($options['role']) )
												echo '<p>'.__('No role found.', 'kc-settings').'</p>';
											else
												echo kcForm::field(array(
													'type'      => 'checkbox',
													'attr'      => array('name' => "{$s_name}[role][]", 'class' => 'kcsb-mi'),
													'options'   => $options['role'],
													'current'   => $s_val['role'],
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
											<label><?php echo $prop['label'] ?> : </label>
											<?php
												echo kcForm::select(array(
													'attr'    => array('name' => "{$s_name}[metabox][$mb_prop]", 'required' => 'required'),
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
										?>
										<li class="row" data-mode="fields">
											<div class="actions">
												<h5 title="<?php _e('Drag to reorder field', 'kc-settings') ?>"><?php _e( sprintf('Field #%s', "<span class='count'>{$count_f}</span>"), 'kc-settings') ?></h5>
												<p>(
													<a class="add" title="<?php _e('Add new field', 'kc-settings') ?>"><?php _e('Add') ?></a>
													<a class="del" title="<?php _e('Remove this field', 'kc-settings') ?>"><?php _e('Remove') ?></a>
												)</p>
											</div>
											<ul>
												<li>
													<label class="kcsb-ml"><?php _e('ID', 'kc-settings') ?></label>
													<input class="kcsb-mi kcsb-slug kcsb-ids required" type="text" name="<?php echo $f_name ?>[id]" value="<?php esc_attr_e($f_val['id']) ?>" data-ids="fields" />
												</li>
												<li>
													<label class="kcsb-ml"><?php _e('Label') ?></label>
													<input class="kcsb-mi required" type="text" name="<?php echo $f_name ?>[title]" value="<?php esc_attr_e($f_val['title']) ?>" />
												</li>
												<li>
													<label class="kcsb-ml nr"><?php _e('Description') ?></label>
													<textarea class="kcsb-mi" name="<?php echo $f_name ?>[desc]" cols="25" rows="4"><?php echo esc_textarea($f_val['desc']) ?></textarea>
												</li>
												<li>
													<label class="kcsb-ml"><?php _e('Type') ?></label>
													<?php
														echo kcForm::select(array(
															'attr'    => array(
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
												<?php
													if ( !isset($f_val['options']) || !is_array($f_val['options']) )
														$f_val['options'] = array( array( 'key' => '', 'label' => '' ) );
												?>
												<li class="childFieldType" data-dep='file'>
													<?php if ( !isset($f_val['mode']) ) $f_val['mode'] = ''; ?>
													<label class="kcsb-ml"><?php _e('Mode', 'kcsb') ?></label>
													<?php
														echo kcForm::select(array(
															'attr'    => array(
																'name'       => "{$f_name}[mode]",
																'class'      => 'hasdep kcsb-mi',
																'data-child' => '.childFileSize',
																'data-scope' => 'li.row'
															),
															'options' => $options['filemode'],
															'current' => $f_val['mode'],
															'none'    => false
														));
													?>
												</li>
												<li class="childFileSize" data-dep='single'>
													<?php if ( !isset($f_val['size']) ) $f_val['size'] = 'thumbnail'; ?>
													<label class="kcsb-ml"><?php _e('Preview Size', 'kcsb') ?></label>
													<?php
														echo kcForm::field(array(
															'type'    => 'select',
															'attr'    => array( 'name' => "{$f_name}[size]", 'class' => 'kcsb-mi' ),
															'options' => kcSettings_options::$image_sizes,
															'current' => $f_val['size'],
															'none'    => false
														));
													?>
												</li>
												<li class="childFieldType" data-dep='["radio", "checkbox", "select", "multiselect"]'>
													<label class="kcsb-ml"><?php _e('Options', 'kcsb') ?></label>
													<ul class="kcsb-mi kcsb-options kc-rows kc-sortable">
														<?php foreach ( $f_val['options'] as $o_idx => $option ) { ?>
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
													<label class="kcsb-ml"><?php _e('Callback', 'kcsb') ?></label>
													<input class="kcsb-mi kcsb-slug required" type="text" name="<?php echo $f_name ?>[cb]" value="<?php esc_attr_e($f_val['cb']) ?>" />
												</li>
												<?php if ( !isset($f_val['args']) ) $f_val['args'] = ''; ?>
												<li class="childFieldType" data-dep='special'>
													<label class="kcsb-ml">
														<span class="nr"><?php _e('Arguments', 'kcsb') ?></span>
														<br/><small><em>(<?php _e('String or function name', 'kc-settings') ?>)</em></small>
													</label>
													<input class="kcsb-mi kcsb-slug" type="text" name="<?php echo $f_name ?>[args]" value="<?php esc_attr_e($f_val['args']) ?>" />
												</li>
												<?php
													if ( !isset($f_val['subfields']) || !is_array($f_val['subfields']) || empty($f_val['subfields']) ) {
														$f_val['subfields'] = array(
															array( 'id' => 'key', 'title' => __('Key', 'kc-settings'), 'type' => 'text' ),
															array( 'id' => 'value', 'title' => __('Value', 'kc-settings'), 'type' => 'textarea' )
														);
													}
												?>
												<li class="childFieldType" data-dep='multiinput'>
													<label class="kcsb-ml"><?php _e('Sub-fields', 'kcsb') ?></label>
													<ul class="kcsb-mi kcsb-options kc-rows kc-sortable">
														<?php foreach ( $f_val['subfields'] as $sf_idx => $sf ) { ?>
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
										</li>
										<?php } unset( $count_f ); ?>
									</ul>
								</li>
							</ul>
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

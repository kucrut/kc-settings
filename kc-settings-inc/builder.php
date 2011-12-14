<?php

class kcSettings_builder {
	protected static $pdata = array(
		'defaults' => array(
			'id'								=> '',
			'type'							=> 'post',
			'prefix'						=> '',
			'menu_location'			=> 'options-general.php',
			'menu_title'				=> '',
			'page_title'				=> '',
			'display'						=> 'plain',
			'post_type'					=> 'post',
			'taxonomy'					=> '',
			'sections'					=> array(
				array(
					'id'						=> '',
					'title'					=> '',
					'desc'					=> '',
					'priority'			=> 'high',
					'fields'				=> array(
						array(
							'id'				=> '',
							'title'			=> '',
							'desc'			=> '',
							'type'			=> 'input',
							'attr'			=> '',
							'options'		=> array(
								array(
									'key'		=> '',
									'label'	=> ''
								)
							)
						)
					)
				)
			)
		)
	);


	public static function init() {
		self::$pdata['options'] = self::_options();
		self::$pdata['kcsb'] = kcSettings::get_data('kcsb');

		add_action( 'admin_init', array(__CLASS__, 'register'), 21 );
		add_action( 'admin_menu', array(__CLASS__, 'create_page') );
		add_filter( 'plugin_row_meta', array(__CLASS__, 'builder_link'), 10, 3 );
		add_action( 'update_option_kcsb', array(__CLASS__, 'after_save'), 10, 2 );

	}


	private static function _options() {
		$options = array(
			'yesno'		=> array(
				array(
					'value'	=> 1,
					'label'	=> __('Yes', 'kc-settings')
				),
				array(
					'value'	=> 0,
					'label'	=> __('No', 'kc-settings')
				)
			),
			'type'		=> array(
				'plugin' => array(
					'value'		=> 'plugin',
					'label'		=> __('Plugin / theme settings', 'kc-settings')
				),
				'post' => array(
					'value'		=> 'post',
					'label'		=> __('Post metadata (custom fields)', 'kc-settings'),
					'default'	=> true
				),
				'term' => array(
					'value'		=> 'term',
					'label'		=> __('Term metadata', 'kc-settings')
				),
				'user' => array(
					'value'		=> 'user',
					'label'		=> __('User metadata', 'kc-settings')
				)
			),
			'menu_location'	=> array(
				array(
					'value'		=> 'options-general.php',
					'label'		=> __('Settings'),
					'default'	=> true
				),
				array(
					'value'		=> 'themes.php',
					'label'		=> __('Appearance')
				),
				array(
					'value'		=> 'index.php',
					'label'		=> __('Dashboard')
				),
				array(
					'value'		=> 'plugins.php',
					'label'		=> __('Plugins')
				),
				array(
					'value'		=> 'tools.php',
					'label'		=> __('Tools')
				),
				array(
					'value'		=> 'plugins.php',
					'label'		=> __('Plugins')
				),
				array(
					'value'		=> 'users.php',
					'label'		=> __('Users')
				),
				array(
					'value'		=> 'upload.php',
					'label'		=> __('Media')
				),
				array(
					'value'		=> 'link-manager.php',
					'label'		=> __('Links')
				),
				array(
					'value'		=> 'edit-comments.php',
					'label'		=> __('Comments')
				),
				array(
					'value'		=> 'edit.php',
					'label'		=> __('Posts')
				),
			),
			'display'		=> array(
				array(
					'value'	=> 'metabox',
					'label'	=> __('Metaboxes', 'kc-settings')
				),
				array(
					'value'	=> 'plain',
					'label'	=> __('Plain', 'kc-settings')
				)
			),
			'field'		=> array(
				array(
					'value'		=> 'text',
					'label'		=> __('Text', 'kc-settings'),
					'default'	=> true
				),
				array(
					'value'		=> 'textarea',
					'label'		=> __('Textarea', 'kc-settings')
				),
				array(
					'value'		=> 'checkbox',
					'label'		=> __('Checkbox', 'kc-settings')
				),
				array(
					'value'		=> 'color',
					'label'		=> __('Color', 'kc-settings')
				),
				array(
					'value'		=> 'date',
					'label'		=> __('Date', 'kc-settings')
				),
				array(
					'value'		=> 'file',
					'label'		=> __('File', 'kc-settings')
				),
				array(
					'value'		=> 'radio',
					'label'		=> __('Radio', 'kc-settings')
				),
				array(
					'value'		=> 'select',
					'label'		=> __('Select', 'kc-settings')
				),
				array(
					'value'		=> 'multiselect',
					'label'		=> __('Select (multiple)', 'kc-settings')
				),
				array(
					'value'		=> 'multiinput',
					'label'		=> __('Multiinput', 'kc-settings')
				),
				array(
					'value'		=> 'special',
					'label'		=> __('Special', 'kc-settings')
				)
			),
			'priorities'	=> array(
				array(
					'value'		=> 'high',
					'label'		=> __('High', 'kc-settings'),
					'default'	=> true
				),
				array(
					'value'		=> 'special',
					'label'		=> __('Normal', 'kc-settings')
				)
			)
		);

		$taxonomies = kc_get_taxonomies();
		if ( !empty($taxonomies) ) {
			$options['taxonomies'] = $taxonomies;
		}
		else {
			$options['taxonomies'] = array(
				array(
					'value'	=> '',
					'label'	=> __('No public taxonomy found', 'kc-settings')
				)
			);
		}

		$post_types = kc_get_post_types();
		if ( !empty($post_types) ) {
			$options['post_types'] = $post_types;
		}
		else {
			$options['post_types'] = array(
				array(
					'value'	=> '',
					'label'	=> __('No public post type found', 'kc-settings')
				)
			);
		}

		$roles = kc_get_roles();
		if ( !empty($roles) ) {
			$options['role'] = $roles;
		}
		else {
			$options['role'] = array(
				array(
					'value'	=> '',
					'label'	=> __('No role found', 'kc-settings')
				)
			);
		}

		$options['filemode'] = array(
			array(
				'value' => 'radio',
				'label' => __('Single', 'kc-settings')
			),
			array(
				'value' => 'checkbox',
				'label' => __('Multiple', 'kc-settings'),
				'default' => true
			)
		);

		return $options;
	}


	public static function create_page() {
		$page = add_options_page( __('KC Settings', 'kc-settings'), __('KC Settings', 'kc-settings'), 'manage_options', 'kcsb', array(__CLASS__, 'builder') );

		# Set scripts and styles
		kcSettings::$data['pages'][] = $page;

		# Help
		kcSettings::$data['help'][$page] = array(
			array(
				'id'			=> 'kcsb',
				'title' 	=> __( 'KC Settings Builder', 'kc-settings' ),
				'content'	=>
					'<ul>
						<li>'.__('All fields are required, unless the label is green.', 'kc-settings').'</li>
						<li>'.__('Some fields depend on other field(s), they will be shown when the dependency is selected/checked.', 'kc-settings').'</li>
						<li>'.__('Some fields (eg. ID, Prefix) can only be filled with alphanumerics, dashes and underscores, must be unique, and cannot begin with dashes or underscores. ', 'kc-settings').'</li>
					</ul>'
			),
			array(
				'id'			=> 'kcsb-side',
				'title'		=> 'Links',
				'sidebar'	=> true,
				'content'	=>
					'<ul>
						<li><a href="http://wordpress.org/tags/kc-settings?forum_id=10">'.__('Support', 'kc-settings').'</a></li>
					</ul>'
			)
		);

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
			$settings = self::$pdata['kcsb']['settings'];

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


	public static function validate( $new ) {
		# Delete
		$kcsb = get_option( 'kcsb' );
		if ( !isset($new['id']) ) {
			return $kcsb;
		}
		# Add / Update
		else {
			if ( empty($new['id']) ) {
				set_transient( 'kcsb', array('new' => true, 'item' => $new) );
				return $kcsb;
			}
			else {
				$_temp = $kcsb;
				$kcsb[$new['id']] = $new;

				# No change?
				if ( $_temp == $kcsb )
					self::_success();
			}
		}

		return $kcsb;
	}


	public static function after_save( $old, $new ) {
		# Delete
		if ( count($old) > count($new) )
			$message = __('Setting successfully deleted.', 'kc-settings');
		# Add
		elseif ( !is_array($old) || count($old) < count($new) )
			$message = __('Setting successfully created.', 'kc-settings');
		# Update
		else
			$message = __('Setting successfully saved.', 'kc-settings');

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
		$options		= self::$pdata['options'];
		$values			= self::$pdata['defaults'];
		$form_class	= ' class="hidden"';
		$button_txt	= __('Create Setting', 'kc-settings');
		$mode				= 'default';

		if ( isset($_GET['action']) ) {
			$action = $_GET['action'];
			if ( $action == 'edit' ) {
				if ( isset($_GET['id']) && !empty($_GET['id']) ) {
					$id = $_GET['id'];
					if ( isset(self::$pdata['kcsb']['settings'][$id]) ) {
						$mode		= 'edit';
						$values = wp_parse_args( self::$pdata['kcsb']['settings'][$id], $values );
					}
					else {
						add_settings_error('general', 'warning', sprintf( __("There's no setting with ID %s. Are you cheating? ;)", 'kc-settings'), "&#8220;{$id}&#8221;") );
					}
				}
				else {
					$er = get_transient( 'kcsb' );
					if ( !empty($er) ) {
						$mode		= 'edit';
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
			$button_txt	= __('Save Changes');
		}

		?>
		<div class="wrap">
			<?php screen_icon('tools'); ?>
			<h2><?php echo __('KC Settings', 'kc-settings')." <a id='new-kcsb' class='add-new-h2' href='#'>".__('Add New')."</a>" ?></h2>

			<div class="kcsb-block">
				<h3><?php _e('Saved Settings', 'kc-settings') ?></h3>
				<table class="wp-list-table widefat fixed" cellspacing="0">
					<thead>
						<tr>
							<th scope="col" class="manage-column"><?php _e('ID', 'kc-settings') ?></th>
							<th scope="col" class="manage-column"><?php _e('Type') ?></th>
							<th scope="col" class="manage-column"><?php _e('Tools') ?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th scope="col" class="manage-column"><?php _e('ID', 'kc-settings') ?></th>
							<th scope="col" class="manage-column"><?php _e('Type') ?></th>
							<th scope="col" class="manage-column"><?php _e('Tools') ?></th>
						</tr>
					</tfoot>
					<tbody id="the-list">
						<?php
							if ( !empty(self::$pdata['kcsb']['settings']) ) {
								$i = 0;
								foreach( self::$pdata['kcsb']['settings'] as $sID => $sVal ) {
									++$i;
									$url_base = "options-general.php?page=kcsb&amp;id={$sVal['id']}&amp;action=";
						?>
						<tr valign="top"<?php if ($i % 2) echo ' class="alternate"' ?>>
							<td>
								<strong><a title="<?php _e('Edit this item') ?>" href="<?php echo admin_url("{$url_base}edit") ?>"><?php echo $sVal['id'] ?></a></strong>
								<div class="row-actions">
									<span class="edit"><a title="<?php _e('Edit this item') ?>" href="<?php echo admin_url("{$url_base}edit") ?>"><?php _e('Edit') ?></a> | </span>
									<?php
										if ( $sVal['type'] == 'plugin' ) {
											$o = get_option( "{$sVal['prefix']}_settings" );
											if ( $o !== false ) {
												if ( !empty($o) ) {
									?>
									<span class="trash"><a title="<?php _e('Reset options', 'kc-settings') ?>" href="<?php echo wp_nonce_url( admin_url("{$url_base}empty"), "__kcsb__{$sID}" ) ?>"><?php _e('Empty', 'kc-settings') ?></a> | </span>
									<?php } ?>
									<span class="trash"><a title="<?php _e('Remove all sections and fields', 'kc-settings') ?>" href="<?php echo wp_nonce_url( admin_url("{$url_base}purge"), "__kcsb__{$sID}" ) ?>"><?php _e('Purge', 'kc-settings') ?></a> | </span>
									<?php } } ?>
									<span class="trash"><a title="<?php _e('Remove this setting', 'kc-settings') ?>" href="<?php echo wp_nonce_url( admin_url("{$url_base}delete"), "__kcsb__{$sID}" ) ?>" title="<?php esc_attr_e('Delete') ?>" class="submitdelete"><?php _e('Delete') ?></a></span>
								</div>
							</td>
							<td><?php echo $options['type'][$sVal['type']]['label'] ?></td>
							<td class="kcsb-tools">
								<div class="hide-if-no-js">
									<!--span><a href="<?php echo admin_url("{$url_base}getcode") ?>"><?php _e('Get code', 'kc-settings') ?></a> |</span-->
									<a class="clone-open" href="#"><?php _e('Clone', 'kc-settings') ?></a>
									<div class="kcsb-clone hide-if-js">
										<input class="widefat kcsb-slug kcsb-ids clone-id" data-ids="settings" />
										<a class="clone-do" title="<?php _e('Clone', 'kc-settings') ?>"href="<?php echo wp_nonce_url( admin_url("{$url_base}clone"), "__kcsb__{$sID}" ) ?>"><span><?php _e('Clone', 'kc-settings') ?></span></a>
										<a class="close" title="<?php _e('Cancel') ?>" href="#"><span><?php _e('Cancel') ?></span></a><br />
										<em class="description"><?php _e("Don't forget to change the setting properties after cloning!", 'kc-settings') ?></em>
									</div>
								</div>
								<p class="hide-if-js"><em><?php _e('Please enable javascript to use the tool', 'kc-settings') ?></em></p>
							</td>
						</tr>
						<?php } } else { ?>
						<tr valign="top">
							<tr class="no-items"><td colspan="3" class="colspanchange"><?php _e('No setting found.', 'kc-settings') ?></td></tr>
						</tr>
						<?php } ?>
					</tbody>
				</table>
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
									'attr'		=> array('name' => 'kcsb[type]', 'class' => 'idep global kcsb-mi'),
									'options'	=> $options['type'],
									'current'	=> $values['type'],
									'none'		=> false
								));
							?>
						</li>
						<li class="idep_type plugin">
							<label class="kcsb-ml"><?php _e('Prefix', 'kc-settings') ?></label>
							<input class="kcsb-mi kcsb-slug required" type="text" name="kcsb[prefix]" value="<?php esc_attr_e( $values['prefix'] ) ?>"/>
						</li>
						<li class="idep_type plugin">
							<label class="kcsb-ml"><?php _e('Menu location', 'kc-settings') ?></label>
							<?php
								echo kcForm::select(array(
									'attr'		=> array('name' => 'kcsb[menu_location]', 'class' => 'kcsb-mi'),
									'options'	=> $options['menu_location'],
									'current'	=> $values['menu_location'],
									'none'		=> false
								));
							?>
						</li>
						<li class="idep_type plugin">
							<label class="kcsb-ml"><?php _e('Menu title', 'kc-settings') ?></label>
							<input class="kcsb-mi required" type="text" name="kcsb[menu_title]" value="<?php esc_attr_e( $values['menu_title'] ) ?>"/></li>
						<li class="idep_type plugin">
							<label class="kcsb-ml"><?php _e('Page title', 'kc-settings') ?></label>
							<input class="kcsb-mi required" type="text" name="kcsb[page_title]" value="<?php esc_attr_e( $values['page_title'] ) ?>" />
						</li>
						<li class="idep_type plugin">
							<label class="kcsb-ml"><?php _e('Page mode', 'kc-settings') ?></label>
							<?php
								echo kcForm::select(array(
									'attr'		=> array('name' => 'kcsb[display]', 'class' => 'kcsb-mi'),
									'options'	=> $options['display'],
									'current'	=> $values['display'],
									'none'		=> false
								));
							?>
						</li>
						<li class="idep_type post">
							<label class="kcsb-ml"><?php _e('Post type', 'kc-settings') ?></label>
							<?php
								echo kcForm::select(array(
									'attr'		=> array('name' => 'kcsb[post_type]', 'class' => 'kcsb-mi'),
									'options'	=> $options['post_types'],
									'current'	=> $values['post_type'],
									'none'		=> false
								));
							?>
						</li>
						<li class="idep_type term">
							<label class="kcsb-ml"><?php _e('Taxonomies', 'kc-settings') ?></label>
							<?php
								echo kcForm::select(array(
									'attr'		=> array('name' => 'kcsb[taxonomy]', 'class' => 'kcsb-mi'),
									'options'	=> $options['taxonomies'],
									'current'	=> $values['taxonomy'],
									'none'		=> false
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
								$s_name	= "kcsb[sections][{$idxS}]";
								$s_val	= $values['sections'][$idxS];
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
								<li class="global_idep_type post">
									<label class="kcsb-ml nr"><?php _e('Roles', 'kc-settings') ?></label>
									<ul class="kcsb-mi">
										<?php
											if ( !isset($s_val['role']) )
												$s_val['role'] = array();

											echo kcForm::checkbox(array(
												'attr'			=> array('name' => "{$s_name}[role][]", 'class' => 'kcsb-mi'),
												'options'		=> $options['role'],
												'current'		=> $s_val['role'],
												'check_sep'	=> array("\t<li>", "</li>\n")
											));
										?>
									</ul>
								</li>
								<?php if ( !isset($s_val['priority']) ) $s_val['priority'] = 'high' ?>
								<li class="global_idep_type post">
									<label class="kcsb-ml nr"><?php _e('Priority', 'kc-settings') ?></label>
									<?php
										echo kcForm::select(array(
											'attr'		=> array('name' => "{$s_name}[priority]", 'class' => 'kcsb-mi'),
											'options'	=> $options['priorities'],
											'current'	=> $s_val['priority'],
											'none'		=> false
										));
									?>
								</li>
								<li class="fields">
									<h4 class="kcsb-ml"><?php _e('Fields', 'kc-settings') ?></h4>
									<ul class="kcsb-mi kc-rows">
										<?php
											$count_f = 0;
											foreach ( $section['fields'] as $idxF => $field ) {
												$count_f++;
												$f_name	= "{$s_name}[fields][{$idxF}]";
												$f_val	= $s_val['fields'][$idxF];
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
															'attr'		=> array('name' => "{$f_name}[type]", 'class' => 'idep kcsb-mi'),
															'options'	=> $options['field'],
															'current'	=> $f_val['type'],
															'none'		=> false
														));
													?>
												</li>
												<?php
													if ( !isset($f_val['options']) || !is_array($f_val['options']) )
														$f_val['options'] = array( array( 'key' => '', 'label' => '' ) );
												?>
												<li class="idep_type file">
													<?php if ( !isset($f_val['mode']) ) $f_val['mode'] = ''; ?>
													<label class="kcsb-ml"><?php _e('Mode', 'kcsb') ?></label>
													<?php
														echo kcForm::select(array(
															'attr'		=> array('name' => "{$f_name}[mode]", 'class' => 'kcsb-mi'),
															'options'	=> $options['filemode'],
															'current'	=> $f_val['mode'],
															'none'		=> false
														));
													?>
												</li>
												<li class="idep_type radio checkbox select multiselect">
													<label class="kcsb-ml"><?php _e('Options', 'kcsb') ?></label>
													<ul class="kcsb-mi kcsb-options kc-rows kc-sortable">
														<?php
															foreach ( $f_val['options'] as $idxO => $option ) {
																$o_name	= "{$f_name}[options][{$idxO}]";
																$o_val	= $f_val['options'][$idxO];
														?>
														<li class="row" data-mode="options">
															<label>
																<span><?php _e('Value', 'kcsb') ?></span>
																<input class="kcsb-slug required" type="text" name="<?php echo $o_name ?>[key]" value="<?php esc_attr_e($o_val['key']) ?>" />
															</label>
															<label>
																<span><?php _e('Label') ?></span>
																<input class="required" type="text" name="<?php echo $o_name ?>[label]" value="<?php esc_attr_e($o_val['label']) ?>" />
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
												<li class="idep_type special">
													<label class="kcsb-ml"><?php _e('Callback', 'kcsb') ?></label>
													<input class="kcsb-mi kcsb-slug required" type="text" name="<?php echo $f_name ?>[cb]" value="<?php esc_attr_e($f_val['cb']) ?>" />
												</li>
												<?php if ( !isset($f_val['args']) ) $f_val['args'] = ''; ?>
												<li class="idep_type special">
													<label class="kcsb-ml">
														<span class="nr"><?php _e('Arguments', 'kcsb') ?></span>
														<br/><small><em>(<?php _e('String or function name', 'kc-settings') ?>)</em></small>
													</label>
													<input class="kcsb-mi kcsb-slug" type="text" name="<?php echo $f_name ?>[args]" value="<?php esc_attr_e($f_val['args']) ?>" />
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

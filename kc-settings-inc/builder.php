<?php

class kcsBuilder {
	var $kcsb_options;
	var $kcsb_page;
	var $defaults;


	function init( $properties ) {
		$this->properties = $properties;

		add_action( 'admin_init', array(&$this, 'register'), 21 );
		add_action( 'admin_menu', array(&$this, 'create_page') );
		add_action( 'update_option_kcsb', array($this, 'after_save'), 10, 2 );
	}


	function options() {
		$options = array(
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
			'field'		=> array(
				array(
					'value'		=> 'input',
					'label'		=> __('Input', 'kc-settings'),
					'default'	=> true
				),
				array(
					'value'		=> 'textarea',
					'label'		=> __('Textarea', 'kc-settings')
				),
				array(
					'value'		=> 'radio',
					'label'		=> __('Radio', 'kc-settings')
				),
				array(
					'value'		=> 'checkbox',
					'label'		=> __('Checkbox', 'kc-settings')
				),
				array(
					'value'		=> 'select',
					'label'		=> __('Select', 'kc-settings')
				),
				array(
					'value'		=> 'multiselect',
					'label'		=> __('Multiselect', 'kc-settings')
				),
				array(
					'value'		=> 'date',
					'label'		=> __('Date', 'kc-settings')
				),
				array(
					'value'		=> 'multiinput',
					'label'		=> __('Multiinput', 'kc-settings')
				),
				array(
					'value'		=> 'file',
					'label'		=> __('File', 'kc-settings')
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


	function create_page() {
		$this->kcsb_page = add_options_page( __('KC Settings', 'kc-settings'), __('KC Settings', 'kc-settings'), 'manage_options', 'kcsb', array(&$this, 'builder') );

		add_action( "load-{$this->kcsb_page}", array(&$this, 'goback') );
		add_action( "load-{$this->kcsb_page}", array(&$this, 'help') );

		// Script n style
		add_action( "admin_print_scripts-{$this->kcsb_page}", array(&$this, 'script'), 0);
		add_action( "admin_print_styles-{$this->kcsb_page}", array(&$this, 'style') );
	}


	function register() {
		register_setting( 'kcsb', 'kcsb', array(&$this, 'validate') );
		add_settings_section( 'kcsb', __('KC Settings Builder', 'kc-settings'), '', 'kcsb');
		add_settings_field( 'kcsb', 'Plugin Text Input', 'plugin_setting_string', 'plugin', 'plugin_main' );
	}


	function goback() {
		#echo wp_get_referer();
		#exit;
		if ( isset($_GET['action']) && $_GET['action'] != 'edit' && isset($_GET['id']) && !empty($_GET['id']) ) {
			$sID = $_GET['id'];
			check_admin_referer( "__kcsb__{$sID}" );

			$action = $_GET['action'];
			$settings = $this->properties['settings']['_raw'];

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

			$this->success();
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


	function validate( $new ) {
		# Delete
		$old = $this->properties['settings']['_raw'];
		if ( !isset($new['id']) ) {
			return $new;
		}
		# Add / Update
		else {
			if ( empty($new['id']) ) {
				set_transient( 'kcsb', array('new' => true, 'item' => $new) );
				return $old;
			}
			else {
				$_temp = $old;
				$old[$new['id']] = $new;

				# No change?
				if ( $_temp == $old )
					$this->success();
			}
		}

		return $old;
	}


	function after_save( $old, $new ) {
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

		$this->success();
	}


	function success() {
		$goto = wp_get_referer();
		$goto = remove_query_arg( array('id', 'action'), $goto );
		$goto = add_query_arg( 'settings-updated', 'true', $goto );
		wp_redirect( $goto );
		exit;
	}


	function script() { ?>
<script type='text/javascript'>
	// <![CDATA[
	var kcsbIDs = <?php echo json_encode( $this->properties['settings']['_ids'] ) ?>;
	// ]]>
</script>
		<?php
		wp_enqueue_script( 'kcsb' );
	}


	function style() {
		wp_enqueue_style( 'kcsb' );
	}

	function help() {
		$help  = "<h2>".__('Creating a setting', 'kc-settings')."</h2>\n";
		$help .= "<h3>".__('All Types', 'kc-settings')."</h3>\n";
		$help .= "<ul>\n";
		$help .= "\t<li>".__('All fields are required, unless the label is green.', 'kc-settings')."</li>\n";
		$help .= "\t<li>".__('Some fields depend on other field(s), they will be shown when the dependency is selected/checked.', 'kc-settings')."</li>\n";
		$help .= "\t<li>".__('Some fields (eg. ID, Prefix) can only be filled with alphanumerics, dashes and underscores, must be unique, and cannot begin with dashes or underscores. ', 'kc-settings')."</li>\n";
		$help .= "</ul>\n";
		$help .= "<h2>".__('Links', 'kc-settings')."</h2>\n";
		$help .= "<ul>\n";
		$help .= "\t<li><a href='http://wordpress.org/tags/kc-settings?forum_id=10'>".__('Support', 'kc-settings')."</a></li>\n";
		$help .= "</ul>\n";

		$screen = get_current_screen();
		if ( is_object($screen) ) {
			if ( method_exists($screen, 'add_help_tab') ) {
			$screen->add_help_tab(array(
				'id' => 'help-kcsb',
				'title' => __( 'KC Settings Builder', 'kc-settings' ),
				'content' => $help
			));
			} else {
				add_contextual_help( $screen, $help );
			}
		}
	}


	function builder() {
		$values	= kcsb_defaults();
		$form_class = ' class="hidden"';
		$bt_txt = __('Create Setting', 'kc-settings');
		$mode		= 'default';
		$options = $this->options();

		if ( isset($_GET['action']) ) {
			$action = $_GET['action'];
			if ( $action == 'edit' ) {
				if ( isset($_GET['id']) && !empty($_GET['id']) ) {
					$id = $_GET['id'];
					if ( isset($this->properties['settings']['_raw'][$id]) ) {
						$mode		= 'edit';
						$values = wp_parse_args( $this->properties['settings']['_raw'][$id], $values );
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
			$bt_txt	= __('Save Changes');
		}

		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php echo __('KC Settings', 'kc-settings')." <a id='new-kcsb' class='button add-new-h2' href='#'>".__('Add New')."</a>" ?></h2>

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
							if ( !empty($this->properties['settings']['_raw']) ) {
								$i = 0;
								foreach( $this->properties['settings']['_raw'] as $sID => $sVal ) {
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
				<p class="description"><?php _e('Please click the Help button to read the guide before creating a setting.', 'kc-settings')?></p>

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
									'current'	=> $values['type']
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
									'current'	=> $values['menu_location']
								));
							?>
						</li>
						<li class="idep_type plugin">
							<label class="kcsb-ml"><?php _e('Menu title', 'kc-settings') ?></label>
							<input class="kcsb-mi required" type="text" name="kcsb[menu_title]" value="<?php esc_attr_e( $values['menu_title'] ) ?>"/></li>
						<li class="idep_type plugin">
							<label class="kcsb-ml"><?php _e('Page title') ?></label>
							<input class="kcsb-mi required" type="text" name="kcsb[page_title]" value="<?php esc_attr_e( $values['page_title'] ) ?>" />
						</li>
						<li class="idep_type post">
							<label class="kcsb-ml"><?php _e('Post type', 'kc-settings') ?></label>
							<?php
								echo kcForm::select(array(
									'attr'		=> array('name' => 'kcsb[post_type]', 'class' => 'kcsb-mi'),
									'options'	=> $options['post_types'],
									'current'	=> $values['post_type']
								));
							?>
						</li>
						<li class="idep_type term">
							<label class="kcsb-ml"><?php _e('Taxonomies', 'kc-settings') ?></label>
							<?php
								echo kcForm::select(array(
									'attr'		=> array('name' => 'kcsb[taxonomy]', 'class' => 'kcsb-mi'),
									'options'	=> $options['taxonomies'],
									'current'	=> $values['taxonomy']
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
									<input class="kcsb-mi kcsb-slug required" type="text" name="<?php echo $s_name ?>[id]" value="<?php esc_attr_e($s_val['id']) ?>" data-ids="sections" />
								</li>
								<li>
									<label class="kcsb-ml"><?php _e('Title') ?></label>
									<input class="kcsb-mi required" type="text" name="<?php echo $s_name ?>[title]" value="<?php esc_attr_e($s_val['title']) ?>" />
								</li>
								<li>
									<label class="kcsb-ml nr"><?php _e('Description') ?></label>
									<input class="kcsb-mi" type="text" name="<?php echo $s_name ?>[desc]" value="<?php esc_attr_e($s_val['desc']) ?>" />
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
											'current'	=> $s_val['priority']
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
													<input class="kcsb-mi" type="text" name="<?php echo $f_name ?>[desc]" value="<?php esc_attr_e($f_val['desc']) ?>" />
												</li>
												<li>
													<label class="kcsb-ml"><?php _e('Type') ?></label>
													<?php
														echo kcForm::select(array(
															'attr'		=> array('name' => "{$f_name}[type]", 'class' => 'idep kcsb-mi'),
															'options'	=> $options['field'],
															'current'	=> $f_val['type']
														));
													?>
												</li>
												<?php if ( !isset($f_val['attr']) ) $f_val['attr'] = ''; ?>
												<li class="idep_type input textarea date">
													<label class="kcsb-ml nr"><?php _e('Attributes') ?></label>
													<input class="kcsb-mi" type="text" name="<?php echo $f_name ?>[attr]" value="<?php esc_attr_e($f_val['attr']) ?>" />
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
															'current'	=> $f_val['mode']
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
						<button class="button-primary" name="submit" type="submit"><?php echo $bt_txt; ?></button>
						<a href="#" class="button alignright kcsb-cancel"><?php _e('Cancel') ?></a>
					</div>
				</form>
			</div>
			<!-- End builder -->
		</div>
	<?php }
}
?>

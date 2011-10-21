<?php

class kcsBuilder {
	var $kcsb_options;
	var $kcsb_page;
	var $defaults;


	function init( $properties ) {
		$this->options();
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
					'label'		=> 'Input',
					'default'	=> true
				),
				array(
					'value'		=> 'textarea',
					'label'		=> 'Textarea'
				),
				array(
					'value'		=> 'radio',
					'label'		=> 'Radio'
				),
				array(
					'value'		=> 'checkbox',
					'label'		=> 'Checkbox'
				),
				array(
					'value'		=> 'select',
					'label'		=> 'Select'
				),
				array(
					'value'		=> 'multiselect',
					'label'		=> 'Multiselect'
				),
				array(
					'value'		=> 'date',
					'label'		=> 'Date'
				),
				array(
					'value'		=> 'multiinput',
					'label'		=> 'Multiinput'
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

		$this->kcsb_options = $options;
	}


	function create_page() {
		$this->kcsb_page = add_options_page( __('KC Settings', 'kc-settings'), __('KC Settings', 'kc-settings'), 'manage_options', 'kcsb', array(&$this, 'builder') );

		add_action( "load-{$this->kcsb_page}", array(&$this, 'goback') );
		add_action( "load-{$this->kcsb_page}", array(&$this, 'help') );

		// Script n style
		add_action( "admin_print_scripts-{$this->kcsb_page}", array(&$this, 'script'), 0);
		add_action( "admin_print_styles-{$this->kcsb_page}", function() {
			wp_enqueue_style('kcsb');
		}, 0);
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


	function help() {
		$help  = "<h3>".__('Creating a setting', 'kc-settings')."</h3>\n";
		$help .= "<h4>".__('All Types', 'kc-settings')."</h4>\n";
		$help .= "<ul>\n";
		$help .= "\t<li>".__('All fields are required, unless the label is green.', 'kc-settings')."</li>\n";
		$help .= "\t<li>".__('Some fields depend on other field, they will be shown when the dependency is selected/checked.', 'kc-settings')."</li>\n";
		$help .= "\t<li>".__('Some fields (eg. ID, Prefix) can only be filled with alphanumerics, dashes and underscores, must be unique, and cannot begin with dashes or underscores. ', 'kc-settings')."</li>\n";
		$help .= "</ul>\n";
		$help .= "<h3>".__('Links', 'kc-settings')."</h3>\n";
		$help .= "<ul>\n";
		$help .= "\t<li><a href='http://wordpress.org/tags/kc-settings?forum_id=10'>".__('Support', 'kc-settings')."</a></li>\n";
		$help .= "</ul>\n";

		add_contextual_help( $this->kcsb_page, $help );
	}


	function builder() {
		$values	= kcsb_defaults();
		$form_class = ' class="hidden"';
		$bt_txt = __('Create Setting', 'kc-settings');
		$mode		= 'default';

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
						add_settings_error('general', 'warning', sprintf( __("There's no setting with ID &#8220;%s&#8221; Are you cheating? ;)", 'kc-settings'), $id) );
					}
				}
				else {
					$er = get_transient( 'kcsb' );
					if ( !empty($er) ) {
						$mode		= 'edit';
						$values = wp_parse_args( $er, $values );
						delete_transient( 'kcsb' );
						add_settings_error('general', 'not_saved', __('Settings NOT saved! Please fill all the required fields.', 'kc-settings') );
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
									<span class="trash"><a href="<?php echo wp_nonce_url( admin_url("{$url_base}empty"), "__kcsb__{$sID}" ) ?>"><?php _e('Empty', 'kc-settings') ?></a> | </span>
									<?php } ?>
									<span class="trash"><a href="<?php echo wp_nonce_url( admin_url("{$url_base}purge"), "__kcsb__{$sID}" ) ?>"><?php _e('Purge', 'kc-settings') ?></a> | </span>
									<?php } } ?>
									<span class="trash"><a href="<?php echo wp_nonce_url( admin_url("{$url_base}delete"), "__kcsb__{$sID}" ) ?>" title="<?php esc_attr_e('Delete') ?>" class="submitdelete"><?php _e('Delete') ?></a></span>
								</div>
							</td>
							<td><?php echo $this->kcsb_options['type'][$sVal['type']]['label'] ?></td>
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
								<p class="hide-if-js"><em><?php _e('Please enable javascript to use the tools', 'kc-settings') ?></em></p>
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
				<p class="description"><?php _e('Please click the Help button on the top-right of the screen to read the guide before creating a setting.', 'kc-settings')?></p>

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
							<?php kcs_select( $this->kcsb_options['type'], $values['type'], array('name' => 'kcsb[type]', 'class' => 'idep global kcsb-mi') ); ?>
						</li>
						<li class="idep_type plugin">
							<label class="kcsb-ml"><?php _e('Prefix', 'kc-settings') ?></label>
							<input class="kcsb-mi kcsb-slug required" type="text" name="kcsb[prefix]" value="<?php esc_attr_e( $values['prefix'] ) ?>"/>
						</li>
						<li class="idep_type plugin">
							<label class="kcsb-ml"><?php _e('Menu location', 'kc-settings') ?></label>
							<?php kcs_select( $this->kcsb_options['menu_location'],  $values['menu_location'], array('name' => 'kcsb[menu_location]', 'class' => 'kcsb-mi') ); ?>
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
							<?php kcs_select( $this->kcsb_options['post_types'], $values['post_type'], array('name' => 'kcsb[post_type]', 'class' => 'kcsb-mi') ); ?>
						</li>
						<li class="idep_type term">
							<label class="kcsb-ml"><?php _e('Taxonomies', 'kc-settings') ?></label>
							<?php kcs_select( $this->kcsb_options['taxonomies'], $values['taxonomy'], array('name' => 'kcsb[taxonomy]', 'class' => 'kcsb-mi') ); ?>
						</li>
					</ul>

					<h4>Sections</h4>
					<ul class="sections kc-rows">
						<?php
							foreach ( $values['sections']  as $idxS => $section ) {
								$s_name	= "kcsb[sections][{$idxS}]";
								$s_val	= $values['sections'][$idxS];
						?>
						<li class="row">
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
											foreach ( $this->kcsb_options['role'] as $r ) {
										?>
										<li>
											<label><input class="optional" type="checkbox" name="<?php echo $s_name ?>[role][]" value="<?php esc_attr_e($r['value']) ?>" <?php checked(in_array($r['value'], $s_val['role'])) ?>/> <?php echo $r['label'] ?></label>
										</li>
										<?php } ?>
									</ul>
								</li>
								<?php if ( !isset($s_val['priority']) ) $s_val['priority'] = 'high' ?>
								<li class="global_idep_type post">
									<label class="kcsb-ml nr"><?php _e('Priority', 'kc-settings') ?></label>
									<?php kcs_select( $this->kcsb_options['priorities'], $s_val['priority'], array('name' => "{$s_name}[priority]", 'class' => 'kcsb-mi') ); ?>
								</li>
								<li class="fields">
									<label class="kcsb-ml"><?php _e('Fields', 'kc-settings') ?></label>
									<ul class="kcsb-mi kc-rows">
										<?php
											foreach ( $section['fields'] as $idxF => $field ) {
												$f_name	= "{$s_name}[fields][{$idxF}]";
												$f_val	= $s_val['fields'][$idxF];
										?>
										<li class="row">
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
													<?php kcs_select( $this->kcsb_options['field'], $f_val['type'], array('name' => "{$f_name}[type]", 'class' => 'idep kcsb-mi') ); ?>
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
												<li class="idep_type radio checkbox select multiselect">
													<label class="kcsb-ml"><?php _e('Options', 'kcsb') ?></label>
													<ul class="kcsb-mi kcsb-options kc-rows">
														<?php
															foreach ( $f_val['options'] as $idxO => $option ) {
																$o_name	= "{$f_name}[options][{$idxO}]";
																$o_val	= $f_val['options'][$idxO];
														?>
														<li class="row">
															<label>
																<span><?php _e('Value', 'kcsb') ?></span>
																<input class="kcsb-slug required" type="text" name="<?php echo $o_name ?>[key]" value="<?php esc_attr_e($o_val['key']) ?>" />
															</label>
															<label>
																<span><?php _e('Label') ?></span>
																<input class="required" type="text" name="<?php echo $o_name ?>[label]" value="<?php esc_attr_e($o_val['label']) ?>" />
															</label>
															<ul class="actions">
																<li><a class="add" href="#" rel="options" title="<?php _e('Add new option', 'kc-settings') ?>"><span><?php _e('Add') ?></span></a></li>
																<li><a class="del" href="#" rel="options" title="<?php _e('Remove this option', 'kc-settings') ?>"><span><?php _e('Remove') ?></span></a></li>
																<li><a class="move up" href="#" rel="options" title="<?php _e('Move this option up', 'kc-settings') ?>"><span><?php _e('Up', 'kc-settings') ?></span></a></li>
																<li><a class="move down" href="#" rel="options" title="<?php _e('Move this option down', 'kc-settings') ?>"><span><?php _e('Down', 'kc-settings') ?></span></a></li>
															</ul>
														</li>
														<?php } ?>
													</ul>
												</li>
												<?php if ( !isset($f_val['callback']) ) $f_val['callback'] = ''; ?>
												<li class="idep_type special">
													<label class="kcsb-ml"><?php _e('Callback', 'kcsb') ?></label>
													<input class="kcsb-mi kcsb-slug required" type="text" name="<?php echo $f_name ?>[callback]" value="<?php esc_attr_e($f_val['callback']) ?>" />
												</li>
											</ul>
											<ul class="actions">
												<li><a class="add" href="#" rel="fields" title="<?php _e('Add new field', 'kc-settings') ?>"><span><?php _e('Add') ?></span></a></li>
												<li><a class="del" href="#" rel="fields" title="<?php _e('Remove this field', 'kc-settings') ?>"><span><?php _e('Remove') ?></span></a></li>
												<li><a class="move up" href="#" rel="fields" title="<?php _e('Move this field up', 'kc-settings') ?>"><span><?php _e('Up', 'kc-settings') ?></span></a></li>
												<li><a class="move down" href="#" rel="fields" title="<?php _e('Move this field down', 'kc-settings') ?>"><span><?php _e('Down', 'kc-settings') ?></span></a></li>
											</ul>
										</li>
										<?php } ?>
									</ul>
								</li>
							</ul>
							<ul class="actions">
								<li><a class="add" href="#" rel="sections" title="<?php _e('Add new section', 'kc-settings') ?>"><span><?php _e('Add') ?></span></a></li>
								<li><a class="del" href="#" rel="sections" title="<?php _e('Remove this section', 'kc-settings') ?>"><span><?php _e('Remove') ?></span></a></li>
								<li><a class="move up" href="#" rel="sections" title="<?php _e('Move this section up', 'kc-settings') ?>"><span><?php _e('Up', 'kc-settings') ?></span></a></li>
								<li><a class="move down" href="#" rel="sections" title="<?php _e('Move this section down', 'kc-settings') ?>"><span><?php _e('Down', 'kc-settings') ?></span></a></li>
							</ul>
						</li>
						<?php } ?>
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

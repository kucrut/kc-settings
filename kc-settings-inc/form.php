<?php

/**
 * Form elements helper
 */
class kcForm {

	public static $i_text = array(
		'', 'text', 'url', 'search',
		'tel', 'number',
		'password',
		'email',
		'date', 'month', 'week', 'time', 'datetime', 'datetime-local',
		'color', 'range',
	);


	public static function field( $args = array() ) {
		$defaults = array(
			'type'    => 'text',
			'attr'    => '',
			'current' => '',
		);
		$args = wp_parse_args( $args, $defaults );

		$type = ( in_array( $args['type'], self::$i_text ) ) ? 'input' : $args['type'];
		if ( $type === 'multiselect' ) {
			$type = 'select';
			if ( !isset($args['attr']['multiple']) || !$args['attr']['multiple'] )
				$args['attr']['multiple'] = true;
		}

		if ( !method_exists( __CLASS__, $type ) )
			return false;

		if ( in_array( $type, array( 'select', 'radio', 'checkbox' ) ) ) {
			if ( !isset( $args['options'] ) || empty( $args['options'] ) )
				return false;

			if ( is_callable( $args['options'] ) )
				$args['options'] = call_user_func_array( $args['options'], isset( $args['args'] ) ? (array) $args['args'] : array() );
			if ( empty( $args['options'] ) || !is_array( $args['options'] ) )
				return false;
			elseif ( count( $args['options'] ) == count( $args['options'], COUNT_RECURSIVE ) )
				$args['options'] = self::_build_options( $args['options'] );
		}

		return call_user_func( array( __CLASS__, $type ), $args );
	}


	public static function input( $args ) {
		if ( empty( $args['type'] ) || in_array( $args['type'], array( '', 'input' ) ) )
			$args['type'] = 'text';

		$output  = '<input type="'. $args['type'] .'"';
		$output .= self::_build_attr( $args['attr'] );
		$output .= ' value="'. esc_attr( $args['current'] ) .'"';
		$output .= ' />';

		return $output;
	}


	public static function textarea( $args ) {
		$output  = '<textarea';
		$output .= self::_build_attr( $args['attr'] );
		$output .= '>';
		$output .= esc_textarea( $args['current'] );
		$output .= '</textarea>';

		return $output;
	}


	public static function radio( $args ) {
		$args['type'] = 'radio';

		return self::checkbox( $args );
	}


	public static function checkbox( $args ) {
		if ( empty( $args['type'] ) || !in_array( $args['type'], array( 'checkbox', 'radio') ) )
			$args['type'] = 'checkbox';

		unset( $args['attr']['id'] );

		if ( $args['type'] === 'checkbox' && !is_array( $args['current'] ) )
			$args['current'] = array($args['current']);

		if ( empty( $args['check_sep'] ) || !is_array( $args['check_sep'] ) || count( $args['check_sep'] ) < 2 )
			$args['check_sep'] = array( '', '<br />' );

		$attr = self::_build_attr( $args['attr'] );

		$output  = '';
		foreach ( $args['options'] as $option ) {
			$checked = (
				( $args['current'] == $option['value'] )
				|| ( is_array( $args['current'] ) && in_array( $option['value'], $args['current'] ) )
			) ? " checked='true'" : '';

			$output .= $args['check_sep'][0];
			$output .= '<label class="'. esc_attr( "kcs-check kcs-{$args['type']}" ) .'">';
			$output .= '<input';
			$output .= ' type="'. esc_attr( $args['type'] ) .'"';
			$output .= ' value="'. esc_attr( $option['value'] ) .'"';
			$output .= $attr;
			$output .= $checked;
			$output .= ' /> ';
			$output .= $option['label'];
			$output .= '</label>';
			$output .= $args['check_sep'][1] . PHP_EOL;
		}

		return $output;
	}


	public static function select( $args ) {
		if ( isset( $args['none'] ) ) {
			if ( $args['none'] )
				$none = $args['none'];
			else
				$none = false;
		}
		else {
			$none = sprintf( __( '%1$s Select %2$s', 'kc-settings' ), '&mdash;&nbsp;', '&nbsp;&mdash;' );
		}

		// for yes/no
		if (
			count( $args['options'] ) === 2
			&& array_keys( $args['options'] ) === array( 0, 1 )
		) {
			$none = false;
		}

		if ( $none ) {
			$args['options'] = array_merge(
				array( array('value' => '', 'label' => $none ) ),
				$args['options']
			);
		}

		if ( !is_array( $args['current'] ) )
			$args['current'] = array($args['current']);

		$output  = '<select';
		$output .= self::_build_attr( $args['attr'] );
		$output .= '>' . PHP_EOL;
		foreach ( $args['options'] as $option ) {
			$output .= "\t". '<option value="'. esc_attr( $option['value'] ) .'"';
			$output .= selected( in_array( $option['value'], $args['current'] ), true, false );
			$output .= '>'. $option['label'] .'</option>' . PHP_EOL;
		}
		$output .= '</select>' . PHP_EOL;

		return $output;
	}


	public static function editor( $args ) {
		if ( empty( $args['attr']['id'] ) )
			$args['attr']['id'] = 'wpeditor';

		$settings = !empty($args['editor_settings']) ? $args['editor_settings'] : array( 'media_buttons' => true, 'tinymce' => true, 'quicktags' => true );
		$settings['textarea_name'] = $args['attr']['name'];
		unset( $settings['_kc-check'] );

		ob_start();
		wp_editor(
			is_string( $args['current'] ) ? $args['current'] : '',
			strtolower( str_replace( array( '-', '_' ), '', $args['attr']['id'] ) ),
			$settings
		);

		return ob_get_clean();
	}


	public static function _build_attr( $attr, $q = '"' ) {
		if ( !is_array( $attr ) || empty( $attr ) )
			return '';

		foreach ( array( 'type', 'value', 'checked', 'selected' ) as $x )
			unset( $attr[$x] );

		$output = '';
		foreach ( $attr as $k => $v )
			$output .= " {$k}={$q}". esc_attr( $v ) ."{$q}";

		return $output;
	}


	private static function _build_options( $options ) {
		$out = array();
		foreach ( $options as $v => $l )
			$out[] = array( 'value' => $v, 'label' => $l );

		return $out;
	}
}


/**
 * Form Label
 *
 * Generate form label
 *
 * @param $title string Label text
 * @param $id string Input's id attribute this label corresponds to, default null
 * @param $wrap_th bool Wrap with th element, default false
 * @param $echo bool Echo or return the label element
 *
 * @return $output string HTML label element
 *
 */
function _kc_field_label( $title, $id = null, $wrap_th = false, $echo = true ) {
	$output  = '<label';
	if ( $id )
		$output .= ' for="'. esc_attr( $id ) .'"';
	$output .= '>'. esc_html( $title ) .'</label>';

	if ( $wrap_th )
		$output = '<th scope="row">'. $output .'</th>' . PHP_EOL;

	if ( $echo )
		echo $output; // xss ok
	else
		return $output;
}


/**
 * Settings field
 *
 * Generate HTML for settings field
 *
 * @param $args array
 * @return string HTML element
 *
 */
function _kc_field( $args ) {
	if ( empty( $args['field']['attr'] ) )
		$args['field']['attr'] = array();

	extract( $args, EXTR_OVERWRITE );

	$i_text = kcForm::$i_text;
	$field_types = array_merge(
		$i_text,
		array(
			'checkbox', 'radio', 'select', 'multiselect',
			'multiinput', 'special', 'file', 'media',
			'textarea', 'editor',
		)
	);
	$type = ( !empty( $field['type'] ) && in_array( $field['type'], $field_types ) ) ? $field['type'] : 'input';

	# setup the input id and name attributes, also get the current value from db
	switch ( $mode ) {
		# 0. Plugin / Theme
		case 'plugin' :
			$name = "{$prefix}_settings[{$section}][{$field['id']}]";
			$id = "{$section}__{$field['id']}";
			$db_value = kc_get_option( $prefix, $section, $field['id'] );
		break;

		# 1. Attachment
		case 'attachment' :
			$id = $field['id'];
			$name = "attachments[{$object_id}][{$id}]";
			$db_value = get_metadata( 'post', $object_id, "_{$id}", true );
		break;

		# 2. Subfields of multiinput field
		case 'subfield' :
			extract( $args['data'], EXTR_OVERWRITE );
		break;

		# 3. Menu item
		case 'menu_item' :
			$id = "edit-menu-item-{$section}-{$field['id']}-{$object_id}";
			$name = "kc-postmeta[{$section}][{$field['id']}][{$object_id}]";
			$key = "_{$field['id']}";
			$db_value = ( isset($object_id) && $object_id != '' ) ? get_metadata( 'post', $object_id, $key, true ) : null;
		break;

		# 4. Nav menu
		case 'menu_nav' :
			$id = "kc-menu_navmeta-{$section}-{$field['id']}";
			$name = "kc-termmeta[{$section}][{$field['id']}]";
			$key = $field['id'];
			$db_value = ( isset($object_id) && $object_id != '' ) ? get_metadata( 'term', $object_id, $key, true ) : null;
		break;

		# 5. Others: post, term & user meta
		default :
			$id = $field['id'];
			$name = "kc-{$mode}meta[{$section}][{$id}]";
			$key = ( $mode == 'post' ) ? "_{$id}" : $id;
			$db_value = ( isset($object_id) && $object_id != '' ) ? get_metadata( $mode, $object_id, $key, true ) : null;
		break;
	}

	$desc_tag   = ( isset($desc_tag) ) ? $desc_tag : 'p';
	$desc_class = ( $mode == 'attachment' ) ? 'help' : 'description';
	$desc       = ( isset($field['desc']) && !empty($field['desc']) ) ? "<{$desc_tag} class='{$desc_class}'>{$field['desc']}</{$desc_tag}>" : null;

	# Let user filter the output of the setting field
	$output = ( $mode !== 'subfield' ) ? apply_filters( 'kc_settings_field_before', '', $section, $field ) : '';

	# Special option with callback
	if ( $type == 'special' ) {
		$args['field']['name'] = $name;
		$cb_args = isset( $field['args'] ) ? $field['args'] : '';
		if ( !empty( $field['args'] ) && is_callable( $field['args'] ) )
			$cb_args = call_user_func_array( $field['args'], array( 'args' => $args, 'db_value' => $db_value ) );

		$output .= call_user_func_array( $field['cb'], array( 'args' => $args, 'db_value' => $db_value, 'cb_args' => $cb_args ) );
		$output .= $desc;
	}

	# File
	elseif ( $type == 'file' ) {
		$output .= _kc_field_file( array(
			'parent'    => ( $mode === 'post' || $mode === 'menu_item' ) ? $object_id : 0,
			'field'     => $field,
			'id'        => $id,
			'name'      => $name,
			'db_value'  => $db_value,
		) );
		$output .= "\t{$desc}\n";
	}

	# Media
	elseif ( $type == 'media' ) {
		$output .= _kc_field_media( array(
			'parent'    => ( $mode === 'post' || $mode === 'menu_item' ) ? $object_id : 0,
			'field'     => $field,
			'id'        => $id,
			'name'      => $name,
			'db_value'  => $db_value,
		) );
		$output .= "\t{$desc}\n";
	}

	# Multiinput
	elseif ( $type == 'multiinput' ) {
		$field['_id'] = $id;

		$output .= _kc_field_multiinput( $name, $db_value, $field );
		$output .= "\t{$desc}\n";
	}

	# Others
	else {
		// Attributes
		$field_attr = wp_parse_args( $field['attr'], array( 'name' => $name, 'class' => "kcs-{$type}" ) );

		if ( $type == 'multiselect' ) {
			$type = 'select';
			$field_attr['multiple'] = 'true';
			$field_attr['name'] .= '[]';
		}
		if ( $type == 'checkbox' ) {
			$field_attr['name'] .= '[]';
		}
		if ( !in_array( $type, array( 'checkbox', 'radio' ) ) ) {
			$field_attr['id'] = $id;
		}
		if ( $mode === 'attachment' ) {
			$field_attr['id'] = $name;
		}
		if ( in_array( $type, array_merge( $i_text, array( 'textarea' ) ) ) ) {
			$field_attr['class'] .= ' kcs-input';
			if ( $mode == 'menu_item' )
				$field_attr['class'] .= ' widefat';
		}


		$field_args = array(
			'type'    => $type,
			'attr'    => $field_attr,
			'current' => $db_value,
		);

		foreach ( array( 'options', 'none', 'editor_settings', 'args' ) as $key )
		if ( !empty($field[ $key ]) )
			$field_args[ $key ] = $field[ $key ];

		$output .= "\t" . kcForm::field( $field_args ) . "\n";
		$output .= "\t{$desc}\n";
	}

	# Let user filter the output of the setting field
	if ( $mode !== 'subfield' )
		$output = apply_filters( 'kc_settings_field_after', $output, $section, $field );

	if ( isset($args['echo']) && $args['echo'] === true )
		echo $output; // xss ok
	else
		return $output;
}


/**
 * Field: multiinput
 *
 * Generate html multiinput fields
 *
 * @param $name string Input name attribute
 * @param $db_value string|array Current value (from database) of this input
 * @param $type string Pair options type, defaults to multiinput
 *
 * @return $output string HTML Pair option row, with the required jQuery script
 *
 */
function _kc_field_multiinput( $name, $db_value, $field, $show_info = true ) {
	# Sanitize subfields
	foreach ( $field['subfields'] as $idx => $subfield ) {
		# 0. attributes
		if ( !empty( $subfield['attr'] ) && is_array( $subfield['attr'] ) ) {
			unset( $subfield['attr']['id'] );
			unset( $subfield['attr']['name'] );
		}
		else {
			$subfield['attr'] = array();
		}

		$field['subfields'][$idx] = $subfield;
	}

	if ( !is_array( $db_value ) || empty( $db_value ) ) {
		$_temp_value = array();
		foreach ( $field['subfields'] as $subfield )
			$_temp_value[ $subfield['id'] ] = '';
		$db_value = array( $_temp_value );
	}

	$output = '';
	if ( $show_info )
		$output .= "<p class='info'><em>". __( 'Info: Drag & drop to reorder.', 'kc-settings' ) ."</em></p>\n";
	$output .= "\n\t<ul class='kc-rows kcs-multiinput'>\n";

	foreach ( $db_value as $row_idx => $row_values ) {
		$output .= "\t\t<li class='row' data-mode='{$field['id']}'>\n";
		$output .= "\t\t\t<table class='form-table widefat'>\n";
		$output .= "\t\t\t\t<tbody>\n";
		# subfields
		foreach ( $field['subfields'] as $subfield ) {
			if ( $subfield['type'] == 'multiinput' )
				continue;

			$subfield_args = array(
				'mode' => 'subfield',
				'data' => array(
					'db_value' => !empty( $row_values[$subfield['id']] ) ? $row_values[$subfield['id']] : '',
					'name'     => "{$name}[$row_idx][{$subfield['id']}]",
					'id'       => "{$field['_id']}-{$row_idx}-{$subfield['id']}",
				),
				'field' => $subfield,
			);

			$output .= "\t\t\t\t\t<tr>\n";
			$output .= "\t\t\t\t\t\t<th><label for='{$field['_id']}-{$row_idx}-{$subfield['id']}'>{$subfield['title']}</label></th>\n";
			$output .= "\t\t\t\t\t\t<td>" . _kc_field( $subfield_args ) . "</td>\n";
			$output .= "\t\t\t</tr>\n";
		}
		$output .= "\t\t\t\t</tbody>\n";
		$output .= "\t\t\t</table>\n";

		# actions
		$output .= "\t\t\t<p class='actions'>";
		$output .= '<a class="add" title="'. __( 'Add new row', 'kc-settings' ) .'">'. __( 'Add', 'kc-settings' ) .'</a>';
		$output .= '<a class="del" title="'. __( 'Remove this row', 'kc-settings' ) .'">'. __( 'Remove', 'kc-settings' ) .'</a>';
		$output .= '<a class="clear" title="'. __( 'Clear', 'kc-settings' ) .'">'. __( 'Clear', 'kc-settings' ) .'</a>';
		$output .= "</p>\n";
		$output .= "\t\t</li>\n";
	}

	$output .= "\t</ul>\n";
	return $output;
}


/**
 * Field: file (back-end only)
 *
 * $args contents:
 * - parent: Post ID, if this field is used for post metadata, otherwise, set to 0
 * - field: The field array
 * - id: HTML `id` attribute
 * - name: HTML `name` attribute
 * - db_value: Current value
 *
 * @param array $args
 */
function _kc_field_file( $args ) {
	if ( $args['field']['mode'] === 'single' ) {
		$param = 'kcsfs';
		$fn = '_kc_field_file_single';
	}
	else {
		$param = 'kcsf';
		$fn = '_kc_field_file_multiple';
	}

	if ( isset($args['parent']) && $args['parent'] ) {
		$tab = 'gallery';
		$post_id = $args['parent'];
	}
	else {
		$tab = 'library';
		$post_id = 0;
	}
	$args['up_url'] = add_query_arg(
		array(
			$param      => 'true',
			'post_id'   => $post_id,
			'tab'       => $tab,
			'width'     => 640,
			'TB_iframe' => 1,
		),
		admin_url( '/media-upload.php' )
	);
	if ( !empty( $args['field']['mime_type'] ) )
		$args['up_url'] = add_query_arg( 'post_mime_type', $args['field']['mime_type'], $args['up_url'] );

	return call_user_func( $fn, $args );
}


/**
 * Field: Multiple files
 */
function _kc_field_file_multiple( $args ) {
	extract( $args, EXTR_OVERWRITE );

	#  Handle migration from single mode
	if ( is_numeric( $db_value ) ) {
		$db_value = array(
			'files' => array( $db_value ),
			'selected' => array( $db_value )
		);
	}

	# Set default value
	if ( empty($db_value) ) {
		$value = array(
			'files' => array(),
			'selected' => array(),
		);
	}
	else {
		$value = $db_value;
		if ( !isset( $value['files'] ) || !is_array( $value['files'] ) )
			$value['files'] = array();
		if ( !isset( $value['selected'] ) || !is_array( $value['selected'] ) )
			$value['selected'] = array();
	}
	$size = !empty($field['size']) ? $field['size'] : 'default';

	$output = "<div id='{$id}' class='kcs-file' data-mime-type='{$field['mime_type']}'>";

	# List files
	$lclass = empty($value['files']) ? ' hidden' : '';
	$output .= "<p class='info{$lclass}'><em>". __( 'Info: Drag & drop to reorder.', 'kc-settings' ) ."</em></p>\n";
	$output .= "\t<ul class='kc-rows sortable{$lclass}'>\n";

	if ( !empty($value['files']) ) {
		$q_args = array(
			'post__in'         => $value['files'],
			'post_type'        => 'attachment',
			'post_status'      => 'inherit',
			'posts_per_page'   => -1,
			'orderby'          => 'post__in',
			'suppress_filters' => false,
		);

		global $post;
		$tmp_post = $post;

		$files = get_posts( $q_args );
		if ( !empty($files) ) {
			foreach ( $files as $post ) {
				setup_postdata( $post );
				$attachment_id = get_the_ID();
				$output .= _kc_field_file_item(
					$name,
					$field['mode'],
					$attachment_id,
					get_the_title(),
					in_array( $attachment_id, $value['selected'] ),
					false,
					$size
				);
			}
			$post = $tmp_post;
		} else {
			$output .= _kc_field_file_item( $name, $field['mode'] );
		}
	}
	else {
		$output .= _kc_field_file_item( $name, $field['mode'] );
	}

	$output .= "\t</ul>\n";
	$output .= "\t<a href='". esc_url( $up_url ) ."' class='button kcsf-upload' title='". __( 'Add files to collection', 'kc-settings' )."'>". __( 'Add files', 'kc-settings' ) ."</a>\n";
	$output .= "</div>\n";

	return $output;
}


/**
 * File list item
 */
function _kc_field_file_item( $input_name, $input_type, $attachment_id = '', $attachment_title = '', $checked = false, $hidden = true, $size = 'default' ) {
	$checked = ( $checked ) ? "checked='checked' " : '';

	$output  = "\t<li title='". __( 'Drag to reorder the items', 'kc-settings' ) ."' class='row";
	if ( $hidden )
		$output .= ' hidden';
	$output .= "'>\n";
	$output .= "\t\t<img src='". kc_get_attachment_icon_src( $attachment_id ) ."' alt='' ";
	if ( is_numeric( $size ) )
		$output .= " style='width:{$size}px'";
	$output .= '/>';
	$output .= "\t\t<a class='rm mid' title='". __( 'Remove from collection', 'kc-settings' ) ."'><span>". __( 'Remove', 'kc-settings' )."</span></a>\n";
	$output .= "\t\t<label>";
	$output .= "<input class='mid include' type='{$input_type}' name='{$input_name}[selected][]' value='{$attachment_id}' {$checked}/> ";
	$output .= "<span class='title'>{$attachment_title}</span>";
	$output .= "</label>\n";
	$output .= "\t\t<input class='fileID' type='hidden' name='{$input_name}[files][]' value='{$attachment_id}'";
	if ( $hidden )
		$output .= " disabled='true'";
	$output .= '/> ';
	$output .= "\t</li>\n";

	return $output;
}


/**
 * Field: single file
 */
function _kc_field_file_single( $args ) {
	extract( $args, EXTR_OVERWRITE );
	$size = isset( $field['size'] ) ? $field['size'] : 'thumbnail';

	#  Handle migration from multiple mode
	if ( is_array( $db_value ) ) {
		if ( !empty($db_value['selected']) )
			$db_value = $db_value['selected'][0];
		elseif ( !empty($db_value['files']) )
			$db_value = $db_value['files'][0];
		else
			$db_value = '';
	}

	if ( get_post_type( absint( $db_value ) ) === 'attachment' && $attachment = get_post( absint( $db_value ) ) ) {
		$post_mime_types = get_post_mime_types();
		$keys  = array_keys( wp_match_mime_types( array_keys( $post_mime_types ), $attachment->post_mime_type ) );
		$type  = esc_attr( array_shift( $keys ) );
		$valid = true;
		$title = $attachment->post_title;
	}
	else {
		$type  = 'default';
		$valid = false;
		$title = '';
		$db_value = '';
	}

	$out  = "<div id='{$id}' class='kcs-file-single' data-type='{$type}' data-size='{$size}' data-mime-type='{$field['mime_type']}'>\n";
	$out .= "\t<p class='current";
	if ( !$valid )
		$out .= ' hidden';
	$out .= "'>\n";
	$out .= "<a href='". esc_url( $up_url ) ."' title='". __( 'Change file', 'kc-settings' ) ."' class='up'><img src='". kc_get_attachment_icon_src( $db_value, $size ) ."' alt=''";
	if ( !empty($field['size']) && is_numeric( $field['size'] ) )
		$out .= " style='width:{$field['size']}px'";
	$out .= ' /></a>';
	$out .= "<span>{$title}</span>";
	$out .= '<br /><a href="#" class="rm">'. __( 'Remove', 'kc-settings' ) .'</a>';
	$out .= "\t</p>\n";
	$out .= "\t<a href='". esc_url( $up_url ) ."' class='up";
	if ( $valid )
		$out .= ' hidden';
	$out .= "'>". __( 'Select file', 'kc-settings' ) .'</a>';
	$out .= "\t<input type='hidden' name='{$name}' value='{$db_value}' />\n";
	$out .= "</div>\n";

	return $out;
}


/**
 * Field: media
 */
function _kc_field_media( $args ) {
	$args['field']['multiple'] = (bool) $args['field']['multiple'];
	kcSettings::add_media_field( $args['id'], $args['field'] );

	if ( !is_array($args['db_value']) ) {
		$args['db_value'] = array( $args['db_value'] );
	}
	foreach ( $args['db_value'] as $idx => $attachment_id ) {
		if ( empty($attachment_id) ) {
			unset( $args['db_value'][ $idx ] );
			continue;
		}

		$attachment = get_post( $attachment_id );
		if ( empty($attachment) || !is_object($attachment) ) {
			unset( $args['db_value'][ $idx ] );
		}
	}

	$wrap_class = 'kc-media-selector';
	$list_class = 'kc-media-list attachments';

	if ( empty($args['db_value']) ) {
		$list_class .= ' hidden';
		$args['db_value'][] = ''; // Needed to print out the item template.
	}

	if ( $args['field']['multiple'] ) {
		$args['name'] .= '[]';
		$list_class .= ' multiple';
	}
	else {
		$wrap_class .= ' single-file';
	}

	$list_attr  = ' id="'. esc_attr( $args['id'] ) .'"';
	$list_attr .= ' class="'. esc_attr( $list_class ) .'"';
	$list_attr .= ' data-size="'. esc_attr( $args['field']['preview_size'] ) .'"';
	$list_attr .= ' data-animate="'. esc_attr( $args['field']['animate'] ) .'"';

	$did_once = false;
	ob_start();
?>
<div class="<?php echo esc_attr($wrap_class) ?>">
	<ul<?php echo $list_attr // xss ok ?>>
		<?php
			foreach ( $args['db_value'] as $attachment_id ) :
				$item_class = 'attachment';
				$thumb_style = '';

				if ( !empty($attachment_id) ) {
					$image       = wp_get_attachment_image( $attachment_id, $args['field']['preview_size'], true );
					$title       = get_the_title( $attachment_id );
					$mime_type   = substr( get_post_mime_type($attachment_id), 0, strpos($attachment->post_mime_type, '/') );
					$item_class .= sprintf(' type-%s', $mime_type);
					if (
						'image' === $mime_type
						&& $args['field']['preview_size'] !== 'thumbnail'
						&& $image_src = wp_get_attachment_image_src( $attachment_id, $args['field']['preview_size'], false )
					) {
						$thumb_style = ' style="width:'. $image_src[1] .'px;height:'. $image_src[2] .'px"';
					}
				}
				else {
					if ( $did_once ) {
						// Skip, we already printed the template.
						continue;
					}

					$image = '<img />';
					$title = '';
				}

				$did_once = true;
		?>
			<li class="<?php echo esc_attr($item_class) ?>">
				<div class="attachment-preview"<?php echo $thumb_style // xss ok ?>>
					<div class="thumbnail"<?php echo $thumb_style // xss ok ?>>
						<div class="centered">
							<?php echo $image // xss ok ?>
						</div>
						<div class="filename">
							<div><?php echo esc_html($title) ?></div>
						</div>
					</div>
					<a title="<?php esc_attr_e('Deselect') ?>" href="#" class="check"><div class="media-modal-icon"></div></a>
				</div>
				<input type="hidden" name="<?php echo esc_attr( $args['name'] ) ?>" value="<?php echo esc_attr( $attachment_id ) ?>" />
			</li>
		<?php endforeach; ?>
	</ul>
	<p><a href="#" class="button-primary kc-media-select" data-fieldid="<?php echo esc_attr( $args['id'] ) ?>"><?php echo esc_html( $args['field']['select_button'] ) ?></a></p>
</div>
<?php
	$output = ob_get_contents();
	ob_end_clean();

	return $output;
}

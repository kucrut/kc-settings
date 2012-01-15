<?php

/*
 * Form elements helper
 */
class kcForm {

	public static $i_text = array(
		'', 'text', 'url', 'search',
		'tel', 'number',
		'password',
		'email',
		'date', 'month', 'week', 'time', 'datetime', 'datetime-local',
		'color', 'range'
	);

	public static function field( $args = array() ) {
		$defaults = array(
			'type'    => 'text',
			'attr'    => '',
			'current' => ''
		);
		$args = wp_parse_args( $args, $defaults );

		$type = ( in_array($args['type'], self::$i_text) ) ? 'input' : $args['type'];

		if ( !method_exists(__CLASS__, $type) )
			return false;

		if ( in_array($type, array('select', 'radio', 'checkbox')) ) {
			if ( !isset($args['options']) || !is_array($args['options']) )
				return false;
			elseif ( count($args['options']) == count($args['options'], COUNT_RECURSIVE) )
				$args['options'] = self::_build_options( $args['options'] );
		}

		return call_user_func( array(__CLASS__, $type), $args );
	}


	public static function input( $args ) {
		if ( !isset($args['type']) || in_array($args['type'], array('', 'input')) )
			$args['type'] = 'text';

		$output  = "<input type='{$args['type']}'";
		$output .= self::_build_attr( $args['attr'] );
		$output .= "value='".esc_attr($args['current'])."' ";
		$output .= " />";

		return $output;
	}


	public static function textarea( $args ) {
		$output  = "<textarea";
		$output .= self::_build_attr( $args['attr'] );
		$output .= ">";
		$output .= esc_textarea( $args['current'] );
		$output .= "</textarea>";

		return $output;
	}


	public static function radio( $args ) {
		$args['type'] = 'radio';
		return self::checkbox( $args );
	}


	public static function checkbox( $args ) {
		if ( !isset($args['type']) || !$args['type'] )
			$args['type'] = 'checkbox';
		unset( $args['attr']['id'] );

		if ( !is_array($args['current']) )
			$args['current'] = array($args['current']);
		if ( !isset($args['check_sep']) || !is_array($args['check_sep']) || count($args['check_sep']) < 2 )
			$args['check_sep'] = array('', '<br />');
		$attr = self::_build_attr( $args['attr'] );

		$output  = '';
		foreach ( $args['options'] as $o ) {
			$output .= "{$args['check_sep'][0]}<label class='kcs-check kcs-{$args['type']}'><input type='{$args['type']}' value='{$o['value']}'{$attr}";
			if ( in_array($o['value'], $args['current']) || ( isset($args['current'][$o['value']]) && $args['current'][$o['value']]) )
				$output .= " checked='true'";
			$output .= " /> {$o['label']}</label>{$args['check_sep'][1]}\n";
		}

		return $output;
	}


	public static function select( $args ) {
		if ( !isset($args['none']) || ( isset($args['none']) && $args['none'] !== false ) ) {
			$args['none'] = array(
				'value'   => '',
				'label'   => '&mdash;&nbsp;'.__('Select', 'kc-settings').'&nbsp;&mdash;'
			);
			$args['options'] = array_merge( array($args['none']), $args['options'] );
		}

		if ( !is_array($args['current']) )
			$args['current'] = array($args['current']);

		$output  = "<select";
		$output .= self::_build_attr( $args['attr'] );
		$output .= ">\n";
		foreach ( $args['options'] as $o ) {
			$output .= "\t<option value='".esc_attr($o['value'])."'";
			if ( $o['value'] == $args['current'] || in_array($o['value'], $args['current']) )
				$output .= " selected='true'\n";
			$output .= ">{$o['label']}</option>\n";
		}
		$output .= "</select>";

		return $output;
	}


	private static function _build_attr( $attr ) {
		if ( !is_array($attr) || empty($attr) )
			return '';

		foreach ( array('type', 'value', 'checked', 'selected') as $x )
			unset( $attr[$x] );

		$output = '';
		foreach ( $attr as $k => $v )
			$output .= " {$k}='".esc_attr($v)."'";

		return $output;
	}


	private static function _build_options( $options ) {
		$out = array();
		foreach ( $options as $v => $l )
			$out[] = array( 'value' => $v, 'label'	=> $l );

		return $out;
	}
}


/**
 * Form Label
 *
 * Generate form label
 *
 * @param $title string Label text
 * @param $id string Input's id attribute this label corresponds to, defaul null
 * @param $ft bool Wrap with th element, default false
 * @param $echo bool Echo or return the label element
 *
 * @return $output string HTML label element
 *
 */
function kc_form_label( $title, $id = null, $ft = false, $echo = true  ) {
	$output  = "<label";
	if ( $id )
		$output .= " for='{$id}' ";
	$output .= ">{$title}</label>";

	if ( $ft )
		$output = "<th scope='row'>{$output}</th>\n";

	if ( $echo )
		echo $output;
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

function kc_settings_field( $args ) {
	if ( !isset($args['field']['attr']) )
		$args['field']['attr'] = array();

	extract($args, EXTR_OVERWRITE);

	$i_text = kcForm::$i_text;
	$field_types = array_merge( $i_text, array(
		'checkbox', 'radio', 'select', 'multiselect',
		'multiinput', 'special', 'file',
		'textarea'
	) );
	$type = ( isset($field['type']) && in_array($field['type'], $field_types) ) ? $field['type'] : 'input';

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

		# 2. Others: post, term & user meta
		default :
			$id = $field['id'];
			$name = "kc-{$mode}meta[{$section}][{$id}]";
			$key = ( $mode == 'post' ) ? "_{$id}" : $id;
			$db_value = ( isset($object_id) && $object_id != '' ) ? get_metadata( $mode, $object_id, $key, true ) : null;
		break;
	}


	$desc_tag = ( isset($desc_tag) ) ? $desc_tag : 'p';
	$desc = ( $mode != 'attachment' && isset($field['desc']) && !empty($field['desc']) ) ? "<{$desc_tag} class='description'>{$field['desc']}</{$desc_tag}>" : null;

	# Let user filter the output of the setting field
	$output = apply_filters( 'kc_settings_field_before', '', $section, $field );

	# Special option with callback
	if ( $type == 'special' ) {
		$args['field']['name'] = $name;
		$cb_args = isset($field['args']) ? $field['args'] : '';
		if ( isset($field['args']) && is_callable($field['args']) )
			$cb_args = call_user_func_array( $field['args'], array( 'args' => $args, 'db_value' => $db_value) );

		$output .= call_user_func_array( $field['cb'], array( 'args' => $args, 'db_value' => $db_value, 'cb_args' => $cb_args) );
		$output .= $desc;
	}

	# File
	elseif ( $type == 'file' ) {
		$file_field_args = array(
			'mode'      => $mode,
			'field'     => $field,
			'id'        => $id,
			'name'      => $name,
			'db_value'  => $db_value,
			'object_id' => $object_id
		);
		if ( in_array($field['mode'], array('radio', 'checkbox')) ) {
			$output .= kc_field_file_multiple( $file_field_args );
		}
	}

	# Multiinput
	elseif ( $type == 'multiinput' ) {
		$output .= "<p class='info'><em>". __('Info: Drag & drop to reorder.', 'kc-settings') ."</em></p>\n";
		$output .= kc_field_multiinput( $name, $db_value, $field );
		$output .= "\t{$desc}\n";
	}

	# Others
	else {
		// Attributes
		$field_attr = wp_parse_args( $field['attr'], array('name' => $name, 'class' => "kcs-{$type}" ));

		if ( $type == 'multiselect' ) {
			$type = 'select';
			$field_attr['multiple'] = 'true';
			$field_attr['name'] .= '[]';
		}
		if ( $type == 'checkbox' ) {
			$field_attr['name'] .= '[]';
		}
		if ( !in_array($type, array('checkbox', 'radio')) ) {
			$field_attr['id'] = $id;
		}
		if ( in_array($type, array_merge($i_text, array('textarea'))) ) {
			$field_attr['class'] .= ' kcs-input';
		}


		$field_args = array(
			'type'    => $type,
			'attr'    => $field_attr,
			'current' => $db_value
		);

		if ( isset($field['options']) )
			$field_args['options'] = $field['options'];
		if ( isset($field['none']) )
			$field_args['none'] = $field['none'];

		$output .= "\t" . kcForm::field( $field_args ) . "\n";
		$output .= "\t{$desc}\n";
	}

	# Let user filter the output of the setting field
	$output = apply_filters( 'kc_settings_field_after', $output, $section, $field );

	if ( isset($args['echo']) && $args['echo'] )
		echo $output;
	else
		return $output;
}


/**
 * Field: Multiple files
 */
function kc_field_file_multiple( $args ) {
	extract( $args, EXTR_OVERWRITE );

	# Set default value
	if ( empty($db_value) ) {
		$value = array(
			'files' => array(),
			'selected' => array()
		);
	} else {
		$value = $db_value;
		if ( !isset($value['files']) || !is_array($value['files']) )
			$value['files'] = array();
		if ( !isset($value['selected']) || !is_array($value['selected']) )
			$value['selected'] = array();
	}

	$output = "<div id='{$id}' class='kcs-file'>";

	# List files
	$lclass = empty($value['files']) ? ' hidden' : '';
	$output .= "<p class='info{$lclass}'><em>". __('Info: Drag & drop to reorder.', 'kc-settings') ."</em></p>\n";
	$output .= "\t<ul class='kc-rows sortable{$lclass}'>\n";

	if ( !empty($value['files']) ) {
		$q_args = array(
			'post__in'         => $value['files'],
			'post_type'        => 'attachment',
			'post_status'      => 'inherit',
			'posts_per_page'   => -1,
			'orderby'          => 'post__in',
			'suppress_filters' => false
		);

		add_filter( 'posts_orderby', 'kc_sort_query_by_post_in', 10, 2 );

		global $post;
		$tmp_post = $post;

		$files = get_posts( $q_args );
		if ( !empty($files) ) {
			foreach ( $files as $post ) {
				setup_postdata( $post );
				$attachment_id = get_the_ID();
				$output .= kc_field_file_item( $name, $field['mode'], $attachment_id, get_the_title(), in_array($attachment_id, $value['selected']), false );
			}
			$post = $tmp_post;
		} else {
			$output .= kc_field_file_item( $name, $field['mode'] );
		}

		remove_filter( 'posts_orderby', 'kc_sort_query_by_post_in' );

	} else {
		$output .= kc_field_file_item( $name, $field['mode'] );
	}

	$output .= "\t</ul>\n";

	if ( $mode == 'post' ) {
		$attachments_parent = $object_id;
		$popup_tab = 'gallery';
	}
	else {
		$attachments_parent = 0;
		$popup_tab = 'library';
	}
	# Add files button
	$output .= "<a href='media-upload.php?kcsf=true&amp;post_id={$attachments_parent}&amp;tab={$popup_tab}&amp;TB_iframe=1' class='button kcsf-upload' title='".__('Add files to collection', 'kc-settings')."'>".__('Add files', 'kc-settings')."</a>\n";

	if ( isset($field['desc']) && !empty($field['desc']) )
		$output .= wpautop( $field['desc'] );
	$output .= "</div>\n";

	return $output;
}


/**
 * Pair option row
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

function kc_field_multiinput( $name, $db_value, $field ) {
	if ( !is_array($db_value) || empty($db_value) )
		$db_value = array(array('key' => '', 'value' => ''));

	$rownum = 0;
	$output = "\n\t<ul class='sortable kc-rows kcs-multiinput'>\n";

	foreach ( $db_value as $k => $v ) {
		$r_key = ( isset($v['key']) ) ? esc_attr( $v['key'] ) : '';
		$r_val = ( isset($v['value']) ) ? esc_textarea( $v['value'] ) : '';

		$output .= "\t\t<li class='row' data-mode='{$field['id']}'>\n";
		$output .= "\t\t\t<ul>\n";
		# key
		$output .= "\t\t\t<li>\n";
		$output .= "\t\t\t\t<label>".__('Key', 'kc-settings')."</label>\n";
		$output .= "\t\t\t\t<input class='regular-text' type='text' name='{$name}[{$k}][key]' value='{$r_key}' />\n";
		$output .= "\t\t\t</li>\n";
		# value
		$output .= "\t\t\t<li>\n";
		$output .= "\t\t\t\t<label>".__('Value', 'kc-settings')."</label>\n";
		$output .= "\t\t\t\t<textarea name='{$name}[{$k}][value]' cols='100' rows='3'>{$r_val}</textarea>\n";
		$output .= "\t\t\t</li>\n";
		$output .= "\t\t\t</ul>\n";
		# actions
		$output .= "\t\t\t<p class='actions'>";
		$output .= "<a class='add' title='".__('Add new row', 'kc-settings')."'>".__('Add', 'kc-settings')."</a>";
		$output .= "<a class='del' title='".__('Remove this row', 'kc-settings')."'>".__('Remove', 'kc-settings')."</a>";
		$output .= "<a class='clear' title='".__('Clear', 'kc-settings')."'>".__('Clear', 'kc-settings')."</a>";
		$output .= "</p>\n";
		$output .= "\t\t</li>\n";

		++$rownum;
	}

	$output .= "\t</ul>\n";
	return $output;
}


/**
 * File list item
 */
function kc_field_file_item( $input_name, $input_type, $attachment_id = '', $attachment_title = '', $checked = false, $hidden = true ) {
	$checked = ( $checked ) ? "checked='checked' " : '';

	$output  = "\t<li title='".__('Drag to reorder the items', 'kc-settings')."' class='row";
	if ( $hidden )
		$output .= " hidden";
	$output .= "'>\n";
	# Image thumb or mime type icon
	if ( $attachment_id ) {
		# Image
		if ( wp_attachment_is_image($attachment_id) ) {
			$icon = wp_get_attachment_image_src($attachment_id, array(46, 46));
			$icon = $icon[0];
		}
		# Other types
		else {
			$icon = wp_mime_type_icon( get_post_mime_type($attachment_id) );
		}
	}
	else {
		$icon = wp_mime_type_icon(0);
	}
	$output .= "\t\t<img src='{$icon}' alt=''/>";
	$output .= "\t\t<a class='rm mid' title='".__('Remove from collection', 'kc-settings')."'><span>".__('Remove', 'kc-settings')."</span></a>\n";
	$output .= "\t\t<label>";
	$output .= "<input class='mid include' type='{$input_type}' name='{$input_name}[selected][]' value='{$attachment_id}' {$checked}/> ";
	$output .= "<span class='title'>{$attachment_title}</span>";
	$output .= "</label>\n";
	$output .= "\t\t<input class='fileID' type='hidden' name='{$input_name}[files][]' value='{$attachment_id}'";
	if ( $hidden )
		$output .= " disabled='true'";
	$output .= "/> ";
	$output .= "\t</li>\n";

	return $output;
}

?>

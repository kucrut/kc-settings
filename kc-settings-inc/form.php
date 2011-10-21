<?php

/**
 * Prints out all settings sections added to a particular settings page
 *
 */
function kc_do_settings_sections( $prefix, $group ) {
	$page = "{$prefix}_settings";
	global $wp_settings_sections, $wp_settings_fields;

	if ( !isset($wp_settings_sections) || !isset($wp_settings_sections[$page]) )
		return;

	foreach ( (array) $wp_settings_sections[$page] as $section ) {
		if ( !strpos($section['title'], '-section-') )
			echo "<h3>{$section['title']}</h3>\n";
		call_user_func( $section['callback'], $section );
		if ( !isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section['id']]) )
			continue;

		$opt_section = $group['options'][$section['id']];

		# Wanna do something before the options table?
		do_action( 'kc_settings_section_before', $prefix, $opt_section );

		# Call user callback function for printing the section when specified
		if ( isset($opt_section['cb']) && is_callable($opt_section['cb']) ) {
			call_user_func( $opt_section['cb'], $opt_section );
		}
		# Defaults to WordPress' Settings API
		else {
			echo '<table class="form-table">';
			do_settings_fields( $page, $section['id'] );
			echo '</table>';
		}

		# Wanna do something after the options table?
		do_action( 'kc_settings_section_after', $prefix, $section );
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
	$defaults = array(
		'mode'			=> 'plugin',
		'section'		=> null,
		'field'			=> array(),
		'label_for'	=> null
	);
	//$r = wp_parse_args( $args, $defaults );
	extract($args, EXTR_OVERWRITE);

	$input_types = array('input', 'textarea', 'checkbox', 'radio', 'select', 'multiselect', 'multiinput', 'date', 'special');
	$type = ( isset($field['type']) && in_array($field['type'], $input_types) ) ? $field['type'] : 'input' ;
	$br = ( isset($tabled) && $tabled ) ? '<br />' : null;

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

	if ( in_array($type, array('multiselect')) )
		$name .= '[]';
	$name_id = "name='{$name}' id='{$id}'";

	$desc_tag = ( isset($desc_tag) ) ? $desc_tag : 'p';
	$desc = ( $mode != 'attachment' && isset($field['desc']) && !empty($field['desc']) ) ? "<{$desc_tag} class='description'>{$field['desc']}</{$desc_tag}>" : null;

	# Let user filter the output of the setting field
	$output = apply_filters( 'kc_settings_field_before', '', $section, $field );

	# Special option with callback
	if ( $type == 'special' && function_exists($field['cb']) ) {
		$output .= call_user_func( $field['cb'], $args, $db_value );
		$output .= $desc;
	}

	# Input
	elseif ( $type == 'input' ) {
		$value = ( !empty($db_value) ) ? esc_html( stripslashes($db_value) ) : '';
		$attr = ( isset($field['attr']) ) ? $field['attr'] : '';
		$output .= "\n\t<input type='text' {$name_id} value='{$value}' class='kcs-{$type}' {$attr}/> {$desc}\n";
	}

	# Input
	elseif ( $type == 'date' ) {
		$value = ( !empty($db_value) ) ? esc_html( stripslashes($db_value) ) : '';
		$attr = ( isset($field['attr']) ) ? $field['attr'] : '';
		$output .= "\n\t<input type='date' {$name_id} value='{$value}' class='widefat kcs-{$type}' {$attr}/> {$desc}\n";
	}

	# Textarea
	elseif ( $type == 'textarea' ) {
		$value = ( !empty($db_value) ) ? esc_html( stripslashes($db_value) ) : '';
		$attr = ( isset($field['attr']) ) ? $field['attr'] : 'cols="40" rows="4"';
		$output .= "\n\t<textarea {$name_id} class='kcs-{$type}' {$attr}>{$value}</textarea> {$desc}\n";
	}

	# Checkboxes, Radioboxes, Dropdown options
	elseif ( in_array($type, array('checkbox', 'radio', 'select', 'multiselect')) ) {
		if ( !is_array($field['options']) || empty($field['options']) )
			return;

		$options = $field['options'];

		switch ( $type ) {
			# Checkboxes
			case 'checkbox' :
				foreach ( $options as $c_id => $c_lbl ) {
					$checked = ( is_array($db_value) && isset($db_value[$c_id]) && $db_value[$c_id] ) ? 'checked="checked" ' : null;
					$output .= "\n\t<label class='kcs-{$type}'><input type='checkbox' name='{$name}[{$c_id}]' value='1' {$checked}/> {$c_lbl}</label>{$br}\n";
				}
			break;

			# Radioboxes
			case 'radio' :
				foreach ( $options as $c_val => $c_lbl ) {
					$db_value = ( empty($db_value) && isset($field['default']) ) ? $field['default'] : $db_value;
					$checked = ( $db_value == $c_val ) ? 'checked="checked" ' : null;
					$output .= "\n\t<label class='kcs-{$type}'><input type='radio' name='{$name}' value='{$c_val}' {$checked}/> {$c_lbl}</label>{$br}\n";
				}
			break;

			# Dropdown
			case 'select' :
				$output  = "\n\t<select {$name_id} class='kcs-{$type}'>\n";
				$output .= "\t\t<option value=''>&mdash;".__('Select')."&mdash;</option>\n";
				foreach ( $options as $c_val => $c_lbl ) {
					$selected = ( $db_value == $c_val ) ? ' selected="selected"' : null;
					$output .= "\t\t<option value='{$c_val}'{$selected}>{$c_lbl}</option>\n";
				}
				$output .= "\t</select>\n";
			break;

			# Dropdown (multi)
			case 'multiselect' :
				$output  = "\n\t<select {$name_id} multiple='multiple' size='3' class='kcs-{$type}'>\n";
				$output .= "\t\t<option value='0'>&mdash;".__('Select')."&mdash;</option>\n";
				foreach ( $options as $c_val => $c_lbl ) {
					//$selected = ( $db_value == $c_val ) ? ' selected="selected" ' : null;
					$selected = ( is_array($db_value) && in_array($c_val, $db_value) ) ? ' selected="selected" ' : null;
					$output .= "\t\t<option value='{$c_val}'{$selected}>{$c_lbl}</option>\n";
				}
				$output .= "\t</select>\n";
			break;
		}
		$output .= "\t{$desc}\n";

	}

	# pair Input
	elseif ( $type == 'multiinput' ) {
		$output .= kc_pair_option_row( $name, $db_value, $type );
		$output .= "\t{$desc}\n";
	}

	# Let user filter the output of the setting field
	if ( isset($args['echo']) && $args['echo'] )
		echo apply_filters( 'kc_settings_field_after', $output, $section, $field );
	else
		return apply_filters( 'kc_settings_field_after', $output, $section, $field );
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

function kc_pair_option_row( $name, $db_value, $type = 'multiinput' ) {
	preg_match_all('/\[(\w+)\]/', $name, $rel);
	$rel = end( end($rel) );
	$output = '';
	$rownum = 0;
	#print_r( $db_value );
	if ( !is_array($db_value) || empty($db_value) )
		$db_value = array(
			array('key' => '', 'value' => '')
		);

	$output .= "\n\t<ul class='kcs-rows'>\n";

	# If there's an array already, print it
	if ( is_array($db_value) && !empty($db_value) ) {
		foreach ( $db_value as $k => $v ) {
			$p_lbl = ( isset($v['key']) ) ? esc_attr( $v['key'] ) : '';
			$p_val = ( isset($v['value']) ) ? esc_html( stripslashes($v['value']) ) : '';
			$output .= "\t\t<li class='row'>\n";
			$output .= "\t\t\t<ul>\n";
			# label/key
			$output .= "\t\t\t<li><label>".__('Key', 'kc-settings')."</label>&nbsp;<input type='text' name='{$name}[{$k}][key]' value='{$p_lbl}' /></li>\n";
			# value
			$output .= "\t\t\t<li><label>".__('Value', 'kc-settings')."</label>&nbsp;<textarea name='{$name}[{$k}][value]' cols='100' rows='3'>{$p_val}</textarea></li>\n";
			$output .= "\t\t\t</ul>\n";
			# remove button
			$output .= "\t\t\t<ul class='actions'>\n";
			$output .= "\t\t\t\t<li><a href='#' class='add' rel='{$rel}' title='".__('Add new row', 'kc-settings')."'><span>".__('Add', 'kc-settings')."</span></li></a>";
			$output .= "\t\t\t\t<li><a href='#' class='del' rel='{$rel}' title='".__('Remove this row', 'kc-settings')."'><span>".__('Remove', 'kc-settings')."</span></li></a>";
			$output .= "\t\t\t\t<li><a href='#' class='move up' rel='{$rel}' title='".__('Move this row up', 'kc-settings')."'><span>".__('Up', 'kc-settings')."</span></li></a>";
			$output .= "\t\t\t\t<li><a href='#' class='move down' rel='{$rel}' title='".__('Move this row down', 'kc-settings')."'><span>".__('Down', 'kc-settings')."</span></a></li>";
			$output .= "\t\t\t\t<li><a href='#' class='clear' rel='{$rel}' title='".__('Clear', 'kc-settings')."'><span>".__('Clear', 'kc-settings')."</span></a></li>";
			$output .= "\t\t\t</ul>\n";
			$output .= "\t\t</li>\n";

			++$rownum;
		}
	}

	$output .= "\t</ul>\n";
	return $output;
}


?>
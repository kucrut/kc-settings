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

	$type = ( isset($field['type']) ) ? $field['type'] : 'input' ;
	$br = ( isset($tabled) && $tabled ) ? '<br />' : null;

	# setup the input id and name attributes, also get the current value from db
	if ( $mode == 'plugin' ) {
		$name = "{$prefix}_settings[{$section}][{$field['id']}]";
		$id = "{$section}__{$field['id']}";
		$db_value = kc_get_option( $prefix, $section, $field['id'] );
	}
	else {
		$name = "kc-{$mode}meta[{$section}][{$field['id']}]";
		$id = $field['id'];
		# prefix with underscore to hide it from the default custom fields metabox
		if ( $mode == 'post' )
			$id = "_{$id}";
		$db_value = ( isset($object_id) && $object_id != '' ) ? get_metadata( $mode, $object_id, $id, true ) : null;
	}

	if ( in_array($type, array('multiselect')) )
		$name .= '[]';
	$name_id = "name='{$name}' id='{$id}'";

	$desc_tag = ( isset($desc_tag) ) ? $desc_tag : 'span';
	$desc = ( isset($field['desc']) && !empty($field['desc']) ) ? "<{$desc_tag} class='description'>{$field['desc']}</{$desc_tag}>" : null;

	# Let user filter the output of the setting field
	$output = apply_filters( 'kc_settings_field_before', '', $section, $field );

	# Special option with callback
	if ( $type == 'special' && function_exists($field['cb']) ) {
		if ( isset($field['args']) )
			$output .= call_user_func( $field['cb'], $field['args'] );
		else
			$output .= call_user_func( $field['cb'] );
		$output .= $desc;
	}

	# Input
	elseif ( $type == 'input' ) {
		$value = ( !empty($db_value) ) ? esc_html( stripslashes($db_value) ) : '';
		$attr = ( isset($field['attr']) ) ? $field['attr'] : 'style="min-width:234px" ';
		$output .= "\n\t<input type='text' {$name_id} value='{$value}' {$attr}/> {$desc}\n";
	}

	# Textarea
	elseif ( $type == 'textarea' ) {
		$value = ( !empty($db_value) ) ? esc_html( stripslashes($db_value) ) : '';
		$attr = ( isset($field['attr']) ) ? $field['attr'] : ' cols="40" rows="4" style="min-width:98%"';
		$output .= "\n\t<textarea {$name_id}{$attr}>{$value}</textarea> {$desc}\n";
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
					$output .= "\n\t<label><input type='checkbox' name='{$name}[{$c_id}]' value='1' {$checked}/> {$c_lbl}</label>{$br}\n";
				}
			break;

			# Radioboxes
			case 'radio' :
				foreach ( $options as $c_val => $c_lbl ) {
					$db_value = ( empty($db_value) && isset($field['default']) ) ? $field['default'] : $db_value;
					$checked = ( $db_value == $c_val ) ? 'checked="checked" ' : null;
					$output .= "\n\t<label><input type='radio' name='{$name}' value='{$c_val}' {$checked}/> {$c_lbl}</label>{$br}\n";
				}
			break;

			# Dropdown
			case 'select' :
				$output  = "\n\t<select {$name_id}>\n";
				$output .= "\t\t<option value=''>&mdash;".__('Select')."&mdash;</option>\n";
				foreach ( $options as $c_val => $c_lbl ) {
					$selected = ( $db_value == $c_val ) ? ' selected="selected"' : null;
					$output .= "\t\t<option value='{$c_val}'{$selected}>{$c_lbl}</option>\n";
				}
				$output .= "\t</select>\n";
			break;

			# Dropdown (multi)
			case 'multiselect' :
				$output  = "\n\t<select {$name_id} multiple='multiple' size='3' style='height:7.5em;padding-right:.5em'>\n";
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
		$output .= "\t<p>{$desc}</p>\n";
	}

	# Upload
	elseif ( $type == 'upload' ) {
		$output .= kc_setting_upload( $name, $db_value, $type );
		$output .= "\t<p>{$desc}</p>\n";
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

	$output  = "\n<script type='text/javascript'>\n";
	$output .= "//<![CDATA[\n";
	$output .= 'jQuery("button.kc-rem").live("click", function() {jQuery(this).parent().remove();return false;} );'."\n";
	$output .= 'jQuery("button.kc-add").live("click", function() {'."\n";
	$output .= '	var $this = jQuery(this);'."\n";
	$output .= '	var $lastRow = jQuery("div.kc-rows").children(":last-child");'."\n";
	$output .= '	var $rowNum = $lastRow.index() + 1;'."\n";
	$output .= '	var $nuRow = $lastRow.clone();'."\n";
	$output .= '	$nuRow.removeAttr("class").addClass("row-"+$rowNum);'."\n";
	$output .= '	var $name = "'.$name.'["+$rowNum+"]";'."\n";
	$output .= '	jQuery("input", $nuRow).attr("value", "").removeAttr("name").attr("name", $name+"[0]");'."\n";
	$output .= '	jQuery("textarea", $nuRow).empty().removeAttr("name").attr("name", $name+"[1]");'."\n";
	$output .= '	$nuRow.appendTo(jQuery(".kc-rows"));'."\n";
	$output .= '	return false;'."\n";
	$output .= '});'."\n";
	$output .= "//]]>\n";
	$output .= "</script>\n";
	$rownum = 0;

	$output .= "\n\t<div class='kc-rows'>\n";

	# If there's an array already, print it
	if ( is_array($db_value) && !empty($db_value) ) {
		foreach ( $db_value as $k => $v ) {
			$p_lbl = ( isset($v[0]) ) ? esc_html( stripslashes($v[0]) ) : '';
			$p_val = ( isset($v[1]) ) ? esc_html( stripslashes($v[1]) ) : '';
			$output .= "\t\t<div class='row-{$rownum}' style='overflow:hidden;padding:2px 0'>\n";
			# label/key
			$output .= "\t\t\t<input type='text' name='{$name}[{$k}][0]' value='{$p_lbl}' style='float:left;width:20%'/>&nbsp;\n";
			# value
			$output .= "\t\t\t<textarea name='{$name}[{$k}][1]' cols='100' rows='3' style='float:right;width:77%'>{$p_val}</textarea>\n";
			# remove button
			$output .= "\t\t\t<button class='kc-rem button' style='float:left;margin-top:7px'>".__('Delete', 'kc-settings')."</button>";
			$output .= "\t\t</div>\n";

			++$rownum;
		}
	}

	# empty row
	$output .= "\t\t<div class='row-{$rownum}' style='overflow:hidden;padding:2px 0'>\n";
	$output .= "\t\t\t<input type='text' name='{$name}[{$rownum}][0]' value='' style='float:left;;width:20%'/>&nbsp;\n";
	$output .= "\t\t\t<textarea name='{$name}[{$rownum}][1]' cols='100' rows='3' style='float:right;width:76%'></textarea>\n";
	$output .= "\t\t</div>\n";

	$output .= "\t</div>\n";

	# add button
	$output .= "\t<button class='kc-add button'>".__('Add new row', 'kc-settings')."</button>";

	return $output;
}


/**
 * Upload field
 */
function kc_setting_upload( $name, $db_value, $type ) {
	$output  = "\n\t<p id='upload-input'>\n";
	$output .= "\t<input type='file' />\n";
	$output .= "\t<button class='button'>Upload</button>\n";
	$output .= "\t</p>\n";
	$output .= "\t<div id='upload-files'>\n";
	$output .= "\t</div>\n";

	return $output;
}

?>
<?php

/**
 * Rebuild array indices
 *
 * @param $arr array
 * @param $cleanup bool Trim the value, defaults to true
 *
 * @return $nu_arr array
 */

function kc_array_rebuild_index( $arr, $cleanup = true ) {
	$nu_arr = array();
	$rownum = 0;
	foreach ( $arr as $row ) {
		foreach ( $row as $l => $v ) {
			if ( $cleanup )
				$v = trim( $v );
			$nu_arr[$rownum][$l] = $v;
		}
		++$rownum;
	}

	return $nu_arr;
}


/* Search haystack for needle and return an array of the key path,
 * FALSE otherwise. If $key is given, return only for this key
 *
 * @param $needle The searched value
 * @param $haystack The array
 * @param $needlekey Optional key
 * @mixed array_search_recursive(mixed $needle, array $haystack [,$key [,bool $trict[,array $path]]])
 *
 * @credit ob at babcom dot biz
 * @link http://www.php.net/manual/en/function.array-search.php#69232
 */

function array_search_recursive( $needle, $haystack, $needlekey = '', $strict = false, $path = array() ) {
	if( !is_array($haystack) )
		return false;

	foreach( $haystack as $key => $val ) {
		if ( is_array($val) && $subpath = array_search_recursive( $needle, $val, $needlekey, $strict, $path) ) {
			$path = array_merge( $path, array($key), $subpath );
			return $path;
		}
		elseif ( (!$strict && $val == $needle && $key == (strlen($needlekey) > 0 ? $needlekey : $key)) || ($strict && $val === $needle && $key == (strlen($needlekey) > 0 ? $needlekey : $key)) ) {
			$path[] = $key;
			return $path;
		}
	}
	return false;
}


/**
 * Cleanup array
 *
 * @credit Jonas John
 * @link http://www.jonasjohn.de/snippets/php/array-remove-empty.htm
 * @param $arr Array to cleanup
 * @return array
 */

function kc_array_remove_empty( $arr, $rm_zero = true ) {
	$narr = array();
	while ( list($key, $val) = each($arr) ) {
		if ( is_array($val) ) {
			$val = kc_array_remove_empty( $val );
			if ( count($val) != 0 )
				$narr[$key] = $val;
		}
		else {
			if ( trim($val) != '' ) {
				if ( $rm_zero && $val )
				$narr[$key] = $val;
			}
		}
	}
	unset( $arr );
	return $narr;
}


/**
 * Dropdown options (pages/categories), to be used in the theme settings page
 *
 * @param $opt_id ID for the select element
 * @param $type Optional. pages or categories
 *
 * @return dropdown menu
 */

function kc_dropdown_options( $args = array() ) {
	$defaults =  array(
		'prefix'	=> 'kc',
		'mode'		=> 'pages',
		'section'	=> '',
		'id'			=> 'pages'
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	$kc_settings = get_option( "{$prefix}_settings" );

	$dd_args = array(
		'echo'							=> 0,
		'hide_empty'				=> 0,
		'hierarchical'			=> 1,
		'id'								=> "{$section}__{$id}",
		'name'							=> "{$prefix}_settings[{$section}][{$id}]",
		'show_option_none'	=> '&mdash;'.__('Select').'&mdash;'
	);

	if ( is_array($kc_settings) && isset($kc_settings[$section][$id]) )
		$dd_args['selected'] = $kc_settings[$section][$id];

	return ( $mode == 'pages' ) ? wp_dropdown_pages( $dd_args ) : wp_dropdown_categories( $dd_args );
}


/**
 * Get theme option
 *
 * @param string $group (Optional) Theme options group, default null
 * @param string $option (Optional) Theme option, default null
 *
 * @return array|string
 *
 */

function kc_get_option( $prefix, $section = null, $field = null ) {
	$kc_settings = get_option( "{$prefix}_settings" );
	if ( empty($kc_settings) )
		return;

	if ( !$section ) {
		return $kc_settings;
	}
	else {
		if ( !empty($section) && isset($kc_settings[$section]) ) {
			if ( $field && isset($kc_settings[$section][$field]) )
				return $kc_settings[$section][$field];
			elseif ( !$field )
				return $kc_settings[$section];
		}
	}
}


/**
 * Prints out all settings sections added to a particular settings page
 *
 */
function kc_do_settings_sections( $prefix, $group ) {
	$page = "{$prefix}_settings";
	global $wp_settings_sections, $wp_settings_fields;

	if ( !isset($wp_settings_sections) || !isset($wp_settings_sections[$page]) )
		return;

	//$all_options = apply_filters( 'kc_plugin_settings', array() );

	foreach ( (array) $wp_settings_sections[$page] as $section ) {
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
	switch ( $mode ) {
		case 'cfields';
			$name = "kc-postmeta[{$section}][{$field['id']}]";
			$id = $field['id'];
			$db_value = get_post_meta( $post_id, "_{$id}", true );
		break;

		case 'term';
			$name = "kc-termmeta[{$section}][{$field['id']}]";
			$id = $field['id'];
			$db_value = ( isset($term) ) ? get_metadata( 'term', $term, $id, true ) : '';
			$desc_tag = 'p';
		break;

		case 'plugin' :
			$name = "{$prefix}_settings[{$section}][{$field['id']}]";
			$id = "{$section}__{$field['id']}";
			$db_value = kc_get_option( $prefix, $section, $field['id'] );
		break;
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


/**
 * KC Meta
 *
 * Merge all the custom fields set by themes/plugins. Also rebuild the array.
 *
 * @param $object_type string post|term Which meta?
 * @return $nu array Our valid post/term meta options array
 *
 */

function kc_meta( $object_type ) {
	$old = apply_filters( "kc_{$object_type}_settings", array() );
	if ( !is_array($old) || empty($old) )
		return;

	$nu = array();
	foreach ( $old as $group ) {
		if ( !is_array($group) || empty($group) )
			return;

		# Loop through each taxonomy to see if it has sections
		foreach ( $group as $pt_tax => $sections ) {
			# Skip this taxonomy if it has no sections
			if ( !is_array($sections) )
				continue;

			# Loop through each section to see if it has fields
			foreach ( $sections as $section )
				# Skip the section if it doesnt have them
				if ( !isset($section['fields']) || !is_array($section['fields']) || empty($section['fields']) )
					continue 2;

			# Rebuild the array
			if ( !isset($nu[$pt_tax]) )
				$nu[$pt_tax] = $sections;
			else
				foreach ( $sections as $sk => $sv )
					$nu[$pt_tax][$sk] = $sv;
		}
	}

	return $nu;
}


/**
 * Save post custom fields values
 *
 * @param int $post_id
 *
 */

function kc_save_cfields( $post_id ) {
	if ( isset($_POST['action']) && $_POST['action'] == 'inline-save' )
		return $post_id;

	$cfields = kc_meta( 'post' );

	if ( isset($_POST['post_type']) )
		$post_type = $_POST['post_type'];
	$post_cfields = ( isset($post_type) ) ? $cfields[$post_type] : null;

	# empty options array? abort!
	if ( empty($post_cfields) )
		return $post_id;

	# Verify the nonce before preceding.
	if ( !wp_verify_nonce( $_POST["{$post_type}_kc_meta_box_nonce"], '___kc_meta_box_nonce___' ) )
		return $post_id;

	# Get the post type object.
	$post_type_obj = get_post_type_object( $post_type );

	# Check if the current user has permission to edit the post.
	if ( !current_user_can( $post_type_obj->cap->edit_post, $post_id ) )
		return $post_id;

	global $post;


	# Loop through all of post meta box arguments.
	foreach ( $post_cfields as $section ) {
		# no fields? abort!
		if ( !isset($section['fields']) || empty($section['fields']) )
			return $post_id;

		foreach ( $section['fields'] as $field ) {
			kc_update_meta( 'post', $post_type, $post_id, $section, $field );
		}
	}
}


/**
 * Update posts & terms metadata
 *
 * @param $meta_type (string) post|term The type of metadata, post or term
 * @param $object_type (string) The taxonomy or post type name
 * @param $object_id (int) The ID of the object (post/term) that we're gonna update
 * @param $section (array) The meta section array
 * @param $field (array) The meta field array
 */

function kc_update_meta( $meta_type = 'post', $object_type_name, $object_id, $section, $field ) {
	if ( isset($_POST['action']) && $_POST['action'] == 'inline-save' )
		return;

	# Set the meta key and get the value based on the $meta_type and screen
	switch( $meta_type ) {
		case 'post' :
			$meta_key = "_{$field['id']}";
			$action = 'editpost';
		break;

		case 'term' :
			$meta_key = $field['id'];
			$action = 'editedtag';
		break;
	}

	$db_val = ( $_POST['action'] == $action ) ? get_metadata( $meta_type, $object_id, $meta_key, true ) : '';

	# Get the new meta value from user
	$nu_val = $_POST["kc-{$meta_type}meta"][$section['id']][$field['id']];

	# default sanitation
	if ( $field['type'] == 'multiinput' ) {
		$nu_val = kc_array_remove_empty( $nu_val );
		$nu_val = kc_array_rebuild_index( $nu_val );
		if ( empty($nu_val) )
			$nu_val = '';
	}
	elseif ( !is_array($nu_val) ) {
		$nu_val = trim( $nu_val );
	}

	# apply validation/sanitation filters on the new values
	# 	0. Taxonomy / Post type
	$nu_val = apply_filters( "kcv_{$meta_type}meta_{$object_type_name}", $nu_val, $section, $field );
	# 	1. Field type
	$nu_val = apply_filters( "kcv_{$meta_type}meta_{$object_type_name}_{$field['type']}", $nu_val, $section, $field );
	#		2. Section
	$nu_val = apply_filters( "kcv_{$meta_type}meta_{$object_type_name}_{$section['id']}", $nu_val, $section, $field );
	# 	3. Field
	$nu_val = apply_filters( "kcv_{$meta_type}meta_{$object_type_name}_{$section['id']}_{$field['id']}", $nu_val, $section, $field );

	# If a new meta value was added and there was no previous value, add it.
	if ( $nu_val && '' == $db_val )
		add_metadata( $meta_type, $object_id, $meta_key, $nu_val, true );

	# If the new meta value does not match the old value, update it.
	elseif ( $nu_val && $nu_val != $db_val )
		update_metadata( $meta_type, $object_id, $meta_key, $nu_val );

	# If there is no new meta value but an old value exists, delete it.
	elseif ( '' == $nu_val && $db_val )
		delete_metadata( $meta_type, $object_id, $meta_key, $nu_val );
}


/**
 * Generate term meta field HTML.
 *
 * $param $args This could be a taxonomy name (string) or a term (object), depending on which screen we're at.
 * $return string $output Input field HTML
 *
 */

function kc_term_meta_field( $args ) {
	# Get the options array
	$terms_meta = kc_meta( 'term' );

	# Where are we? add/edit
	#	a. Edit screen
	if ( is_object($args) ) {
		$edit_mode = true;
		$taxonomy = $args->taxonomy;
		$term_id = $args->term_id;
		$tabled = true;
	}
	# b. Add screen
	else {
		$edit_mode = false;
		$taxonomy = $args;
		$term_id = null;
		$tabled = false;
	}

	# Set the field wrapper tag? Why the incosistencies WP? :P
	$row_tag = ( $tabled ) ? 'tr' : 'div';

	foreach ( $terms_meta as $tax => $sections ) {
		if ( $taxonomy != $tax )
			continue;

		$output = '';
		foreach ( $sections as $section ) {
			if ( !isset($section['fields']) || !is_array($section['fields']) || empty($section['fields']) )
				return;

			foreach ( $section['fields'] as $field ) {
				$args = array(
					'mode' 		=> 'term',
					'section' => $section['id'],
					'field' 	=> $field,
					'tabled'	=> $tabled,
					'echo' 		=> false
				);
				if ( isset($term_id) )
					$args['term'] = $term_id;

				//if ( !in_array($field['type'], array('checkbox', 'radio', 'multiinput')) )
					//$args['label_for'] = "{$section['id']}__{$field['id']}";
				$label_for = ( !in_array($field['type'], array('checkbox', 'radio')) ) ? $field['id'] : null;

				$output .= "<{$row_tag} class='form-field'>\n";

				$the_label = "\t".kc_form_label( $field['title'], $label_for, false, false  )."\n";
				# Wrap the field with <tr> if we're in edit mode
				if ( $edit_mode )
					$the_label = "\t<th scope='row'>\n{$the_label}\t</th>\n";
				$output .= $the_label;

				$the_field = "\t\t".kc_settings_field( $args )."\n";
				# Wrap the field with <tr> if we're in edit mode
				if ( $edit_mode )
					$the_field = "\t<td>\n{$the_field}\t</td>\n";
				$output .= $the_field;

				$output .= "</{$row_tag}>";
			}
		}

		echo $output;
	}
}


/**
 * Save term meta value
 *
 * @param int $term_id Term ID
 * @param int $tt_id Term Taxonomy ID
 * @param string $taxonomy Taxonomy name
 *
 */

function kc_save_termmeta( $term_id, $tt_id, $taxonomy ) {
	if ( isset($_POST['action']) && $_POST['action'] == 'inline-save-tax' )
		return $term_id;

	$terms_meta = kc_meta( 'term' );
		if ( !is_array($terms_meta) || empty($terms_meta) )
			return;

	foreach ( $terms_meta as $tax => $sections ) {
		if ( $taxonomy != $tax )
			continue;

		foreach ( $sections as $section ) {
			foreach ( $section['fields'] as $field )
				kc_update_meta( 'term', $tax, $term_id, $section, $field );
		}
	}
}


/**
 * Create termmeta table
 *
 * @credit Simple Term Meta
 * @link http://www.cmurrayconsulting.com/software/wordpress-simple-term-meta/
 *
 */
function kc_termmeta_table() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'termmeta';

	if ( $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name ) {
		$sql = "CREATE TABLE {$table_name} (
			meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			term_id bigint(20) unsigned NOT NULL DEFAULT '0',
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext,
			PRIMARY KEY (meta_id),
			KEY term_id (term_id),
			KEY meta_key (meta_key)
		);";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	$wpdb->termmeta = $wpdb->prefix . 'termmeta';
}

?>
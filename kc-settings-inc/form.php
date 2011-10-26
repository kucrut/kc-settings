<?php

/*
 * Form elements helper
 */
class kcForm {

  public static function field( $args = array() ) {
    $defaults = array(
      'type'    => 'text',
      'attr'    => '',
      'current' => ''
    );
    $args = wp_parse_args( $args, $defaults );

    if ( in_array($args['type'], array('', 'text', 'date')) ) {
      $type = 'input';
    }
    else {
      $type = $args['type'];
    }


    if ( !method_exists(__CLASS__, $type) )
      return false;

    if ( in_array($type, array('select', 'radio', 'checkbox'))
          && (!isset($args['options']) || !is_array($args['options'])) )
      return false;

    return call_user_func( array(__CLASS__, $type), $args );
  }


  public static function input( $args ) {
    $output  = "<input type='{$args['type']}'";
    $output .= self::_build_attr( $args['attr'] );
    $output .= "value='{$args['current']}' ";
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
    $args['type'] = 'checkbox';
    if ( !is_array($args['current']) )
      $args['current'] = array($args['current']);
    if ( !isset($args['check_sep']) || !is_array($args['check_sep']) || count($args['check_sep']) < 2 )
      $args['check_sep'] = array('', '<br />');
    $attr = self::_build_attr( $args['attr'] );

    $output  = '';
    foreach ( $args['options'] as $o ) {
      $output .= "{$args['check_sep'][0]}<label><input type='{$args['type']}' value='{$o['value']}'{$attr}";
      if ( in_array($o['value'], $args['current']) || ( isset($args['current'][$o['value']]) && $args['current'][$o['value']]) )
        $output .= " checked='true'";
      $output .= " /> {$o['label']}</label>{$args['check_sep'][1]}\n";
    }

    return $output;
  }


  public static function select( $args ) {
    if ( !isset($args['none']) || !is_array($args['none']) || empty($args['none']) ) {
      $args['none'] = array(
        'value'   => '-1',
        'label'   => '&mdash;&nbsp;'.__('Select', 'kc-settings').'&nbsp;&mdash;'
      );
    }
    $options = array_merge( array($args['none']), $args['options'] );

    if ( !is_array($args['current']) )
      $args['current'] = array($args['current']);

    $output  = "<select";
    $output .= self::_build_attr( $args['attr'] );
    $output .= ">\n";
    foreach ( $options as $o ) {
      $output .= "\t<option value='".esc_attr($o['value'])."'";
      if ( in_array($o['value'], $args['current']) && ($o['value'] != $args['none']['value']) )
        $output .= " selected='true'\n";
      $output .= ">{$o['label']}</option>\n";
    }
    $output .= "</select>";

    return $output;
  }


  private static function _build_attr( $attr ) {
    if ( !is_array($attr) || empty($attr) )
      return;

    foreach ( array('type', 'value', 'checked', 'selected') as $x )
      unset( $attr[$x] );

    $output = '';
    foreach ( $attr as $k => $v )
      $output .= " {$k}='".esc_attr($v)."'";

    return $output;
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
function kcs_form_label( $title, $id = null, $ft = false, $echo = true  ) {
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

function kcs_settings_field( $args ) {
	extract($args, EXTR_OVERWRITE);

	$input_types = array('special', 'date', 'text', 'textarea',
		'checkbox', 'radio', 'select', 'multiselect', 'multiinput', 'file'
	);
	$type = ( isset($field['type']) && in_array($field['type'], $input_types) ) ? $field['type'] : 'input';

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
	if ( $type == 'special' && function_exists($field['cb']) && is_callable($field['cb']) ) {
		$args['field']['name'] = $name;
		$cb_args = '';
		if ( isset($field['args']) && !empty($field['args']) ) {
			$cb_args = $field['args'];
			// Is it a function?
			if ( is_string($cb_args) && function_exists($cb_args) && is_callable($cb_args) ) {
				$cb_args = call_user_func( $cb_args, $args, $db_value );
			}
		}

		$output .= call_user_func( $field['cb'], $args, $db_value, $cb_args );
		$output .= $desc;
	}

	# File
	elseif ( $type == 'file' ) {
		# Set mode
		if ( !isset($field['mode']) || !in_array($field['mode'], array('radio', 'checkbox')) ) {
			$field['mode'] = 'radio';
		}

		# Post ID (for post meta only)
		$p__id = ( isset($object_id) && $object_id != '' ) ? $object_id : '';

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

		$output .= "<div id='{$id}' class='kcs-file'>";

		# List files
		$output .= "\t<ul class='kc-sortable'>\n";

		if ( !empty($value['files']) ) {
			$q_args = array(
				'post__in' => $value['files'],
				'post_type' => 'attachment',
				'post_status' => 'inherit',
				'orderby' => 'post__in',
				'suppress_filters' => false
			);

			add_filter( 'posts_orderby', 'kcs_sort_query_by_post_in', 10, 2 );

			global $post;
			$tmp_post = $post;

			$files = get_posts( $q_args );
			if ( !empty($files) ) {
				foreach ( $files as $post ) {
					setup_postdata( $post );
					$f__id = get_the_ID();
					$output .= kcs_filelist_item( $name, $field['mode'], $p__id, $f__id, get_the_title(), in_array($f__id, $value['selected']), false );
				}
				$post = $tmp_post;
			} else {
				$output .= kcs_filelist_item( $name, $field['mode'] );
			}

			remove_filter( 'posts_orderby', 'kcs_sort_query_by_post_in' );

		} else {
			$output .= kcs_filelist_item( $name, $field['mode'] );
		}

		$output .= "\t</ul>\n";

		$output .= "<a href='media-upload.php?kcsf=true&amp;post_id={$p__id}&amp;TB_iframe=1' class='button kcsf-upload' title='".__('Add files to collection', 'kc-settings')."'>".__('Add files', 'kc-settings')."</a>\n";
		$output .= "<input type='hidden' class='kcsf-holder'>\n";
		$output .= "</div>\n";
	}

	# pair Input
	elseif ( $type == 'multiinput' ) {
		$output .= kc_pair_option_row( $name, $db_value, $type );
		$output .= "\t{$desc}\n";
	}

	# Others
	else {
		// Attributes
		$field_attr = array(
			'name'		=> $name,
			'class'		=> "kcs-{$type}"
		);

		if ( $type == 'multiselect' ) {
			$type = 'select';
			$field_attr['multiple'] = true;
		}
		if ( in_array($type, array('checkbox', 'select')) ) {
			$field_attr['name'] .= '[]';
		}
		if ( !in_array($type, array('checkbox', 'radio')) ) {
			$field_attr['id'] = $id;
		}
		if ( in_array($type, array('text', 'date', 'input', 'textarea')) ) {
			$field_attr['class'] .= ' widefat kcs-input';
		}


		$field_args = array(
			'type'		=> $type,
			'attr'		=> $field_attr,
			'current'	=> $db_value
		);

		if ( isset($field['options']) ) {
			$field_options = array();
			foreach ( $field['options'] as $v => $l )
				$field_options[] = array(
					'value' => $v,
					'label'	=> $l
				);
			$field_args['options'] = $field_options;
		}

		$output .= "\t" . kcForm::field( $field_args ) . "\n";
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


function kcs_filelist_item( $name, $type, $pid = '', $fid = '', $title = '', $checked = false, $hidden = true ) {
	if ( $checked ) {
		$checked = "checked='checked' ";
	} else {
		$checked = '';
	}

	$output  = "\t<li title='".__('Drag to reorder the items', 'kc-settings')."'";
	if ( $hidden )
		$output .= " class='hidden'";
	$output .= ">\n";
	// Image thumb or mime type icon
	if ( $fid && wp_attachment_is_image($fid) ) {
		$icon = wp_get_attachment_image_src($fid, array(46, 46));
		$icon = $icon[0];
	} else {
		$icon = wp_mime_type_icon($fid);
	}
	$output .= "\t\t<img src='{$icon}' alt=''/>";
	$output .= "\t\t<a class='del mid' title='".__('Remove from collection', 'kc-settings')."'><span>".__('Remove', 'kc-settings')."</span></a>\n";
	$output .= "\t\t<label>";
	$output .= "<input class='mid' type='{$type}' name='{$name}[selected][]' value='{$fid}' {$checked}/> ";
	$output .= "<span class='title'>{$title}</span>";
	$output .= "</label>\n";
	$output .= "\t\t<input type='hidden' name='{$name}[files][]' value='{$fid}'/> ";
	$output .= "\t</li>\n";

	return $output;
}

?>

<?php

/**
 * Dropdown options (pages/categories), to be used in the theme settings page
 *
 * @param $opt_id ID for the select element
 * @param $type Optional. pages or categories
 *
 * @return dropdown menu
 */

function kc_dropdown_options( $args = array() ) {
	_deprecated_function( __FUNCTION__, '2.2', 'wp_dropdown_pages()' );

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


function kcs_select($arr = array(), $val, $atts = array(), $echo = true) {
	_deprecated_function( __FUNCTION__, '2.2', 'kcForm::field()' );

	if ( empty($arr) )
		return false;

	$output  = "<select";
	if ( !empty($atts) )
		foreach ( $atts as $k => $v )
			$output .= " {$k}='".esc_attr($v)."'";
	$output .= ">\n";
	foreach ( $arr as $i ) {
		$output .= "\t<option value='".esc_attr($i['value'])."'";
		if ( ($val == $i['value'] ) || (empty($val) && isset($i['default']) && $i['default'] === true) )
			$output .= " selected='selected'";
		$output .= ">{$i['label']}</option>\n";
	}
	$output .= "</select>\n";

	if ( $echo )
		echo $output;
	else
		return $output;
}

?>

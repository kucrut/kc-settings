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
 * Check role of current user
 *
 * Will determine if current user is allowed to do the task.
 *
 * @param array $roles Array containing roles, could be a string for single role.
 * @return bool $allowed Permission
 */
function kcs_check_roles( $roles = array() ) {
	if ( empty($roles) )
		return true;

	if ( !is_array($roles) )
		$roles = array( $roles );

	# get current user data
	global $current_user;

	# if current user is not within the roles, abort
	$allowed = false;
	foreach ( $roles as $r ) {
		if ( in_array($r, $current_user->roles) ) {
			$allowed = true;
			break;
		}
	}

	return $allowed;
}

?>
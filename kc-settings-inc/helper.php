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



function kc_get_taxonomies( $args = array('public' => true) ) {
	$taxonomies = array();
	$arr = get_taxonomies( $args, 'object' );
	if ( !empty($arr) )
		foreach ( $arr as $tax )
			$taxonomies[] = array( 'value' => $tax->name, 'label' => $tax->label );

	return $taxonomies;
}


function kc_get_post_types( $args = array('publicly_queryable' => true) ) {
	$post_types = array();
	$arr = get_post_types( $args, 'object' );
	if ( !empty($arr) )
		foreach ( $arr as $pt )
			$post_types[] = array( 'value' => $pt->name, 'label' => $pt->label );

	return $post_types;
}


function kc_get_roles() {
	$roles = array();

	global $wp_roles;
	if ( !is_object($wp_roles) )
		return $roles;

	foreach ( $wp_roles->roles as $k => $v ) {
		$roles[] = array(
			'value'	=> $k,
			'label'	=> $v['name']
		);
	}

	return $roles;
}


function kcsb_defaults() {
	$defaults = array(
		'id'								=> '',
		'type'							=> 'post',
		'prefix'						=> '',
		'menu_location'			=> 'options-general.php',
		'menu_title'				=> '',
		'page_title'				=> '',
		'post_type'					=> 'post',
		'taxonomy'					=> '',
		'sections'					=> array(
			array(
				'id'						=> '',
				'title'					=> '',
				'desc'					=> '',
				'priority'			=> 'high',
				'fields'				=> array(
					array(
						'id'				=> '',
						'title'			=> '',
						'desc'			=> '',
						'type'			=> 'input',
						'attr'			=> '',
						'options'		=> array(
							array(
								'key'		=> '',
								'label'	=> ''
							)
						)
					)
				)
			)
		)
	);

	return $defaults;
}


function kcsb_settings_bootsrap() {
	$all = get_option( 'kcsb' );
	$output = array(
		'_raw'		=> $all,
		'plugin'	=> array(),
		'post'		=> array(),
		'term'		=> array(),
		'user'		=> array(),
		'_ids'		=> array(
			'settings'	=> array(),
			'sections'	=> array(),
			'fields'		=> array()
		),
	);

	if ( empty($all) )
		return $output;

	foreach ( $all as $setting ) {
		$sID = $setting['id'];
		$output['_ids']['settings'][] = $sID;
		$type = $setting['type'];
		$sections = array();

		foreach ( $setting['sections'] as $section ) {
			$output['_ids']['sections'][] = $section['id'];
			$fields = array();
			foreach ( $section['fields'] as $field ) {
				$output['_ids']['fields'][] = $field['id'];
				if ( in_array($field['type'], array('checkbox', 'radio', 'select', 'multiselect')) ) {
					$options = array();
					foreach ( $field['options'] as $option ) {
						$options[$option['key']] = $option['label'];
					}
					$field['options'] = $options;
				}
				$fields[$field['id']] = $field;
			}
			$section['fields'] = $fields;
			$sections[$section['id']] = $section;
		}

		$setting['options'] = $sections;
		unset ( $setting['sections'] );

		if ( $type == 'plugin' ) {
			$output[$type][$sID] = $setting;
		}
		elseif ( $type == 'user' ) {
			$output[$type][$sID] = array( $setting['options'] );
		}
		else {
			$object = ( $type == 'post') ? $setting['post_type'] : $setting['taxonomy'];
			$output[$type][$sID] = array($object => $setting['options']);
		}
	}

	return $output;
}


function kc_create_code( $arr ) {
	$output = '';

	foreach ( $arr as $k => $v ) {
		$output .= "'{$k}' =&gt; ";
		if ( is_array($v) ) {
			$output .= " array(\n";
			$output .= "\t".kc_create_code( $v );
			$output .= "),\n";
		}
		else
			$output .= "'{$v}',\n";
	}

	return $output;
}


/**
 * Sort query order by 'post__in'
 *
 * @credit Jake Goldman (Oomph, Inc)
 * @links http://www.thinkoomph.com
 * @links http://wordpress.org/extend/plugins/sort-query-by-post-in/
 */
function kcs_sort_query_by_post_in( $sortby, $query ) {
	if ( isset($query->query['post__in']) && !empty($query->query['post__in']) && isset($query->query['orderby']) && $query->query['orderby'] == 'post__in' )
		$sortby = "find_in_set(ID, '" . implode( ',', $query->query['post__in'] ) . "')";

	return $sortby;
}





?>

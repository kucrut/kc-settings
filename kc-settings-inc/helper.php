<?php

/**
 * Rebuild array indices
 *
 * @param array $arr Array
 * @param bool $cleanup Trim the value, defaults to true
 *
 * @return array $nu_arr array
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


/**
 * Search array recursively
 *
 * Search haystack for needle and return an array of the key path,
 * FALSE otherwise. If $key is given, return only for this key
 *
 * @param string $needle The searched value
 * @param array $haystack The array
 * @param string $needlekey Optional key
 * @param bool $strict
 * @param array $path
 * @mixed kc_array_search_recursive(mixed $needle, array $haystack [,string $key [,bool $strict[,array $path]]])
 *
 * @credit ob at babcom dot biz
 * @link http://www.php.net/manual/en/function.array-search.php#69232
 */
function kc_array_search_recursive( $needle, $haystack, $needlekey = '', $strict = false, $path = array() ) {
	if( !is_array($haystack) )
		return false;

	foreach( $haystack as $key => $val ) {
		if ( is_array($val) && $subpath = kc_array_search_recursive( $needle, $val, $needlekey, $strict, $path) ) {
			$path = array_merge( $path, array($key), $subpath );
			return $path;
		}
		elseif ( (!$strict && $val == $needle && $key == (strlen($needlekey) > 0 ? $needlekey : $key))
		         || ($strict && $val === $needle && $key == (strlen($needlekey) > 0 ? $needlekey : $key)) ) {
			$path[] = $key;
			return $path;
		}
	}
	return false;
}


/**
 * Cleanup array
 *
 * @param array $arr Array to cleanup
 * @param bool $rm_zero
 * @return array
 *
 * @credit Jonas John
 * @link http://www.jonasjohn.de/snippets/php/array-remove-empty.htm
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
 * Get value of multidimensional array
 *
 * @param array $array Source array.
 * @param array $keys Array of keys of the $array value to get
 *
 * @return mixed
 */
function kc_array_multi_get_value( $array, $keys ) {
	foreach ( $keys as $idx => $key ) {
		unset( $keys[$idx] );
		if ( !isset($array[$key]) )
			return false;

		if ( count($keys) )
			$array = $array[$key];
	}

	if ( !isset($array[$key]) )
		return false;

	return $array[$key];
}


/**
 * Get theme/plugin option
 *
 * @param string $prefix Options prefix, required
 * @param string $section Section id, optional
 * @param string $field Field id, optional
 *
 * @return bool|array|string
 */
function kc_get_option( $prefix, $section = '', $field = '') {
	$values = get_option( "{$prefix}_settings" );
	if ( !is_array($values) || func_num_args() < 2 )
		return $values;

	$keys = func_get_args();
	unset( $keys[0] );
	return kc_array_multi_get_value( $values, $keys );
}


/**
 * Get default value
 *
 * @param string $type Options type, required
 * @param string $prefix Options prefix, required
 * @param string $section Section id, optional
 * @param string $field Field id, optional
 *
 * @return bool|array|string
 *
 * @since 2.5
 */
function kc_get_default( $type, $prefix, $section = '', $field = '' ) {
	$defaults = kcSettings::get_data( 'defaults', $type, $prefix );
	if ( !$defaults || func_num_args() < 3 )
		return $defaults;

	$keys = func_get_args();
	unset( $keys[0] );
	unset( $keys[1] );
	return kc_array_multi_get_value( $defaults, $keys );
}


/**
 * Check role of current user
 *
 * Will determine if current user is allowed to do the task.
 *
 * @param array $roles Array containing roles, could be a string for single role.
 * @return bool $allowed Permission
 */
function kc_check_roles( $roles = array() ) {
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


/**
 * Sort query order by 'post__in'
 *
 * @credit Jake Goldman (Oomph, Inc)
 * @links http://www.thinkoomph.com
 * @links http://wordpress.org/extend/plugins/sort-query-by-post-in/
 */
function kc_sort_query_by_post_in( $sortby, $query ) {
	if ( isset($query->query['post__in']) && !empty($query->query['post__in']) && isset($query->query['orderby']) && $query->query['orderby'] == 'post__in' )
		$sortby = "find_in_set(ID, '" . implode( ',', $query->query['post__in'] ) . "')";

	return $sortby;
}


/**
 * Sanitize user input
 */
function _kc_sanitize_value( $value, $type ) {
	# default sanitation
	if ( $value != '' && $type === 'multiinput' ) {
		$value = kc_array_remove_empty( $value );
		$value = kc_array_rebuild_index( $value );
		if ( empty($value) )
			$value = '';
	}
	elseif ( !is_array($value) ) {
		$value = trim( $value );
	}

	return $value;
}


/**
 * Update posts & terms metadata
 *
 * @param string $meta_type post|term|user The type of metadata, post, term or user
 * @param string $object_type_name The taxonomy or post type name
 * @param int $object_id The ID of the object (post/term) that we're gonna update
 * @param array $section The meta section array
 * @param array $field The meta field array
 * @param bool $is_attachment Are we updating attachment metadata?
 */
function _kc_update_meta( $meta_type = 'post', $object_type_name, $object_id, $section, $field, $is_attachment = false ) {
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

		case 'user' :
			$meta_key = $field['id'];
			$action = 'update';
		break;
	}

	# Current value
	$db_val = get_metadata( $meta_type, $object_id, $meta_key, true );
	# Hold new value
	$nu_val = '';

	# Get the new meta value from user
	if ( $is_attachment && isset($_POST['attachments'][$object_id][$field['id']]) ) {
		$nu_val = $_POST['attachments'][$object_id][$field['id']];
	}
	elseif ( isset($_POST["kc-{$meta_type}meta"][$section['id']][$field['id']]) ) {
		$nu_val = $_POST["kc-{$meta_type}meta"][$section['id']][$field['id']];
	}

	# default sanitation
	$nu_val = _kc_sanitize_value( $nu_val, $field['type'] );

	$filter_prefix = "kcv_{$meta_type}meta";
	if ( $meta_type != 'user' && $object_type_name != '' )
		$filter_prefix .= "_{$object_type_name}";

	# apply validation/sanitation filters on the new values
	# 	0. Taxonomy / Post type
	$nu_val = apply_filters( $filter_prefix, $nu_val, $section, $field );
	# 	1. Field type
	$nu_val = apply_filters( "{$filter_prefix}_{$field['type']}", $nu_val, $section, $field );
	#		2. Section
	$nu_val = apply_filters( "{$filter_prefix}_{$section['id']}", $nu_val, $section, $field );
	# 	3. Field
	$nu_val = apply_filters( "{$filter_prefix}_{$section['id']}_{$field['id']}", $nu_val, $section, $field );

	if ( !$nu_val )
		delete_metadata( $meta_type, $object_id, $meta_key );
	else
		update_metadata( $meta_type, $object_id, $meta_key, $nu_val );
}


/**
 * Get registered image sizes
 *
 * @param string $type Sizes to get: all, default, or custom
 * @return array $sizes Array of image sizes
 */
function kc_get_image_sizes( $type = 'all' ) {
	$sizes = array();

	# Default sizes
	if ( $type !== 'custom' ) {
		foreach ( array('thumbnail', 'medium', 'large') as $size ) {
			$sizes[$size] = array(
				'width'  => get_option( "{$size}_size_w" ),
				'height' => get_option( "{$size}_size_h" )
			);
		}
	}

	if ( $type !== 'default' ) {
		global $_wp_additional_image_sizes;
		if ( is_array($_wp_additional_image_sizes) )
			$sizes = array_merge( $sizes, $_wp_additional_image_sizes );
	}

	ksort( $sizes );
	return $sizes;
}


/**
 * Get attachment icon
 *
 * @param int $id Attachment ID
 * @param $size Icon size (for image)
 */
function kc_get_attachment_icon_src( $id = '', $size = 'thumbnail' ) {
	if ( !$id )
		return wp_mime_type_icon();

	# Image
	if ( $thumb = wp_get_attachment_image_src( $id, $size, true ) )
		$icon = $thumb[0];
	else
		$icon = wp_mime_type_icon( get_post_mime_type($id) );

	return $icon;
}


/**
 * Get image URL via AJAX
 *
 * The request should contains 'id' and 'size'
 */
function kc_ajax_get_image_url() {
	$id = (int) $_REQUEST['id'];
	$size = isset($_REQUEST['size']) ? $_REQUEST['size'] : 'thumbnail';
	if ( $thumb = wp_get_attachment_image_src( $id, $size ) )
		$result = $thumb[0];
	else
		$result = false;

	echo $result;
	die();
}


/**
 * Prettify var_export()
 */
function kc_var_export( $data, $use_tabs = false, $pad = 0 ) {
	$data = var_export( $data, true );
	$pad = (int) $pad;
	if ( $pad )
		$data = preg_replace( '/^/m', str_repeat(' ', $pad), $data );
	$data = preg_replace('/^(\s+array)/m', 'array', $data );
	$data = str_replace( "=> \n", '=> ', $data );

	if ( $use_tabs )
		$data = preg_replace_callback('/^(\s+)/m', 'kc_var_export_tabs', $data );

	return $data;
}


function kc_var_export_tabs( $data ) {
	return str_repeat( "\t", intval( strlen($data[1])/2 ) );
}


/**
 * Get current URL
 * @return string Current URL
 * @since 2.7.7
 * @link http://kovshenin.com/2012/current-url-in-wordpress/
 */
function kc_get_current_url() {
	global $wp;
	if ( get_option('permalink_structure') )
		$current_url = home_url( $wp->request );
	else
		$current_url = add_query_arg( $wp->query_string, '', trailingslashit(home_url()) );

	return $current_url;
}


if ( !function_exists('kc_get_sns') ) {
/**
 * Get scripts and styles sources
 *
 * @since 2.7.7
 *
 * @param array|string $handles Registered script/style handle(s)
 * @param string $type js|css Defaults to 'js'
 * @param array $_output Internal
 *
 * @return array Scripts/styles sources and status
 */
function kc_get_sns( $handles, $type = 'js', $_output = array() ) {
	if ( $type == 'css' ) {
		global $wp_styles;
		$sources = $wp_styles;
	}
	else {
		global $wp_scripts;
		$sources = $wp_scripts;
	}

	foreach ( (array) $handles as $id ) {
		if ( isset($_output[$id]) || !isset($sources->registered[$id]) )
			continue;

		$src = $sources->registered[$id]->src;
		if ( substr( $sources->registered[$id]->src, 0, 1 ) === '/' )
			$src = home_url($src);

		$_id = str_replace( '-', '_', $id );
		$_output[$_id] = array(
			'src'   => $src,
			'queue' => (int) wp_script_is( $id )
		);
		if ( isset($sources->registered[$id]->extra['data']) )
			$_output[$_id]['data'] = $sources->registered[$id]->extra['data'];

		if ( empty($sources->registered[$id]->deps) )
			continue;

		$_x = clone $sources;
		$_x->all_deps( $id );
		$_output[$_id]['deps'] = array_map(
			function( $id ) {
				return str_replace( '-', '_', $id );
			},
			$_x->to_do
		);

		$_output = kc_get_sns( $sources->registered[$id]->deps, $type, $_output );
	}

	return $_output;
}
}
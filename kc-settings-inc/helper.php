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
 * Get theme option
 *
 * @param string $prefix Options prefix, required
 * @param string $section Section id, optional
 * @param string $field Field id, optional
 *
 * @return bool|array|string
 *
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
 * Get default values
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
 * Update posts & terms metadata
 *
 * @param string $meta_type post|term|user The type of metadata, post, term or user
 * @param string $object_type_name The taxonomy or post type name
 * @param int $object_id The ID of the object (post/term) that we're gonna update
 * @param array $section The meta section array
 * @param array $field The meta field array
 * @param bool $is_attachment Are we updating attachment metadata?
 */
function kc_update_meta( $meta_type = 'post', $object_type_name, $object_id, $section, $field, $is_attachment = false ) {
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
	if ( $nu_val != '' && $field['type'] == 'multiinput' ) {
		$nu_val = kc_array_remove_empty( $nu_val );
		$nu_val = kc_array_rebuild_index( $nu_val );
		if ( empty($nu_val) )
			$nu_val = '';
	}
	elseif ( !is_array($nu_val) ) {
		$nu_val = trim( $nu_val );
	}

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
		delete_metadata( $meta_type, $object_id, $meta_key, $nu_val );
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
 * Options
 */
class kcSettings_options {
	public static $nav_menus;
	public static $image_sizes;
	public static $image_sizes_default;
	public static $image_sizes_custom;
	public static $post_types;
	public static $post_types_all;
	public static $taxonomies;
	public static $taxonomies_all;
	public static $roles;
	public static $yesno;


	public static function init() {
		foreach ( get_class_methods(__CLASS__) as $method )
			if ( !in_array($method, array('init', 'channels')) )
				call_user_func( array(__CLASS__, $method) );

		# Trivial ones

		# User roles
		global $wp_roles;
		if ( is_object($wp_roles) )
			self::$roles = $wp_roles->role_names;

		# Yes/No
		self::$yesno = array(
			'1' => __('Yes', 'kc-settings'),
			'0' => __('No', 'kc-settings')
		);
	}


	public static function nav_menus() {
		$_menus = wp_get_nav_menus();
		if ( !$_menus )
			return;

		$menus = array();
		foreach ( $_menus as $menu )
			$menus[$menu->term_id] = $menu->name;

		self::$nav_menus = $menus;
	}


	public static function image_sizes( $store = true, $dims = true ) {
		$sizes = array();
		foreach ( kc_get_image_sizes() as $id => $dim ) {
			if ( $dims )
				$sizes[$id] = "{$id} ({$dim['width']} x {$dim['height']})";
			else
				$sizes[$id] = $id;
		}

		if ( !$store )
			return $sizes;

		self::$image_sizes = $sizes;

		$defaults = array();
		foreach ( array('thumbnail', 'medium', 'large') as $ds ) {
			$defaults[$ds] = $sizes[$ds];
			unset( $sizes[$ds] );
		}
		self::$image_sizes_default = $defaults;

		if ( !empty($sizes) )
			self::$image_sizes_custom = $sizes;
	}


	public static function taxonomies( $store = true, $public_only = false, $detail = true ) {
		return self::channels( 'taxonomies', $store, $public_only, $detail );
	}


	public static function post_types( $store = true, $public_only = false, $detail = true ) {
		return self::channels( 'post_types', $store, $public_only, $detail );
	}


	public static function channels( $type, $store = true, $public_only = false, $detail = true ) {
		$_objects = ( $type === 'post_types' ) ? get_post_types( array(), 'object' ) : get_taxonomies( array(), 'object' );
		if ( empty($_objects) )
			return false;

		$objects_all = $objects = array();
		foreach ( $_objects as $object ) {
			$label = $detail ? "{$object->label} <code>({$object->name})</code>" : $object->label;
			$objects_all[$object->name] = $label;

			if ( $object->show_ui )
				$objects[$object->name] = $label;
		}

		if ( !$store ) {
			if ( $public_only )
				return $objects;
			else
				return $objects_all;
		}
		else {
			switch ( $type ) {
				case 'post_types' :
					self::$post_types = $objects;
					self::$post_types_all = $objects_all;
				break;

				default :
					self::$taxonomies = $objects;
					self::$taxonomies_all = $objects_all;
				break;
			}
		}
	}
}

?>

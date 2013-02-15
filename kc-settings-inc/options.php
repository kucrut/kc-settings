<?php

/**
 * Options helpers
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
	public static $post_statuses;
	public static $sidebars;
	public static $roles;
	public static $yesno;


	public static function init() {
		foreach ( get_class_methods( __CLASS__ ) as $method )
			if ( !in_array( $method, array('init', 'channels') ) )
				call_user_func( array(__CLASS__, $method) );

		# Trivial ones

		# User roles
		global $wp_roles;
		if ( is_object( $wp_roles ) )
			self::$roles = $wp_roles->role_names;

		# ALL post statuses
		$post_statuses = array_merge(
			get_post_statuses(),
			array(
				'auto-draft' => __( 'Auto Draft' ),
				'inherit'    => __( 'Inherit', 'kc-settings' ),
				'trash'      => __( 'Trash' ),
				'future'     => __( 'Scheduled' ),
			)
		);
		asort( $post_statuses );
		self::$post_statuses = $post_statuses;

		# Yes/No
		self::$yesno = array(
			'0' => __( 'No', 'kc-settings' ),
			'1' => __( 'Yes', 'kc-settings' ),
		);
	}


	public static function nav_menus() {
		$_menus = wp_get_nav_menus();
		if ( !$_menus )
			return;

		$menus = array();
		foreach ( $_menus as $menu )
			$menus[ $menu->term_id ] = $menu->name;

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
		$sizes['full'] = __( 'Full (original size)', 'kc-settings' );

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

			if ( $object->public || $object->show_ui )
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


	public static function sidebars() {
		global $wp_registered_sidebars;
		if ( empty($wp_registered_sidebars) )
			return;

		$sidebars = array();
		foreach ( $wp_registered_sidebars as $sb )
			$sidebars[$sb['id']] = $sb['name'];
		self::$sidebars = $sidebars;
	}
}


class kcWalker_Terms extends Walker {
	var $tree_type = 'category';
	var $pad = '';
	var $db_fields = array( 'parent' => 'parent', 'id' => 'term_id' );

	function start_el( &$output, $term, $depth, $args, $id = 0 ) {
		$indent = ( $depth && !empty($this->pad) ) ? str_repeat( $this->pad, $depth ) .'&nbsp;' : '';
		$output[$term->term_id] = $indent . $term->name;
	}
}

class kcWalker_Posts extends Walker {
	var $tree_type = 'page';
	var $pad = '';
	var $db_fields = array( 'parent' => 'post_parent', 'id' => 'ID' );

	function start_el( &$output, $post, $depth, $args, $id = 0 ) {
		$indent = ( $depth && !empty($this->pad) ) ? str_repeat( $this->pad, $depth ) .'&nbsp;' : '';
		$output[ $post->ID ] = $indent . apply_filters( 'the_title', $post->post_title );
	}
}

if ( !class_exists( 'kcWalker_Menu' ) ) {
	class kcWalker_Menu extends Walker {
		var $tree_type = array( 'post_type', 'taxonomy', 'custom' );
		var $db_fields = array( 'parent' => 'menu_item_parent', 'id' => 'db_id' );
		var $pad = '';

		function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
			$indent = ( $depth && !empty($this->pad) ) ? str_repeat( $this->pad, $depth ) .'&nbsp;' : '';
			$output[ $item->ID ] = $indent . apply_filters( 'the_title', $item->title, $item->ID );
		}
	}
}


class kcSettings_options_cb {
	public static function terms( $taxonomy = 'category', $args = array(), $pad = '&mdash;' ) {
		$none = array( '' => __( 'Nothing found', 'kc-settings' ) );
		if ( !taxonomy_exists( $taxonomy ) )
			return $none;

		$args = wp_parse_args( $args, array(
			'depth'        => 0,
			'hide_empty'   => false,
			'hierarchical' => true,
		) );

		$result = get_terms( $taxonomy, $args );
		if ( !$result || is_wp_error( $result ) )
			return $none;

		$walk = new kcWalker_Terms;
		$walk->pad = $pad;
		return $walk->walk( $result, $args['depth'], $args );
	}


	public static function posts( $post_type = 'post', $args = array(), $pad = '&mdash;' ) {
		$args = wp_parse_args( $args, array(
			'depth'          => 0,
			'post_type'      => $post_type,
			'hierarchical'   => true,
			'posts_per_page' => -1,
		) );
		if ( $args['post_type'] === 'attachment' && !isset($args['post_status']) )
			$args['post_status'] = 'inherit';

		$q = new WP_Query( $args );
		if ( $q->have_posts() )
			$result = $q->posts;
		wp_reset_postdata();

		if ( !isset($result) )
			return array( '' => __( 'Nothing found', 'kc-settings' ) );

		$walk = new kcWalker_Posts;
		$walk->pad = $pad;
		return $walk->walk( $result, $args['depth'], $args );
	}
}

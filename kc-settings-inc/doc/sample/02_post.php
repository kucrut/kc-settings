<?php

/**
 * Sample attachment metadata options.
 * Make sure to change the post type name as needed.
 * Built-in WordPress post types are: post, page, attachment
 *
 * @todo Change post type name
 * @see 01_plugin.php for other field types.
 */

add_filter( 'kc_post_settings', 'mypost_options2' );
function mypost_options2( $groups ) {
	$my_group = array(
		'post'	=> array(		// Post type name
			array(
				'id'				=> 'sample_section',
				'title'			=> 'Section title',
				'desc'			=> '<p>Some description about this options group</p>',
				'role'			=> array('administrator', 'editor'),
				'fields'		=> array(
					array(
						'id'		=> 'text',
						'title'	=> 'Single line input',
						'type'	=> 'text'
					)
				)
			)
		)
	);

	$groups[] = $my_group;
	return $groups;
}

?>
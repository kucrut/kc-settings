<?php

/**
 * Sample user metadata options.
 * This is very similiar to post/term metadata options,
 * except that it doesn't have a post type/taxonomy name
 *
 * @see 01_plugin.php for other field types.
 */

add_filter( 'kc_user_settings', 'my_user_options' );
function my_user_options( $groups ) {
	$my_group = array(
		array(
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
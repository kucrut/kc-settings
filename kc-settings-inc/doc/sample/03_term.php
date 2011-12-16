<?php

/**
 * Sample attachment metadata options.
 * This is very similiar to post metadata options,
 * just make sure to use 'attachment' as the post type.
 *
 * @todo: Change taxonomy name
 * @see 01_plugin.php for other field types.
 */

add_filter( 'kc_term_settings', 'myterm_options' );
function myterm_options( $groups ) {
	$my_group = array(
		'category'	=> array(			// TODO: Change this to the desired taxonomy name
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
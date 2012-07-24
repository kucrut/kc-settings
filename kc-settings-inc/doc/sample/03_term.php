<?php

/**
 * Sample attachment metadata options.
 * This is very similiar to post metadata options,
 * just make sure to use 'attachment' as the post type.
 *
 * @see 00_fields.php for complete field types.
 */

add_filter( 'kc_term_settings', 'myterm_options' );
function myterm_options( $groups ) {
	// TODO: Please copy/paste/edit the fields you need, then remove the require_once line.
	// This is only for simplifying the development.
	require_once dirname(__FILE__) . '/00_fields.php';

	$my_group = array(
		'category' => array( // TODO: Change this to the desired taxonomy name
			array(
				'id'     => 'sample_section',
				'title'  => 'Section title',
				'desc'   => '<p>Some description about this options group</p>',
				'role'   => array('administrator', 'editor'),
				'fields' => kc_sample_fields() // TODO: See 00_fields.php and paste the fields you need here.
			)
		)
	);

	$groups[] = $my_group;
	return $groups;
}

?>

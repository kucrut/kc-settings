<?php

/**
 * Sample attachment metadata options.
 * Make sure to change the post type name as needed.
 * Built-in WordPress post types are: post, page, attachment
 *
 * @see 00_fields.php for complete field types.
 */

add_filter( 'kc_post_settings', 'mypost_options2' );
function mypost_options2( $groups ) {
	// TODO: Please copy/paste/edit the fields you need, then remove the require_once line.
	// This is only for simplifying the development.
	require_once dirname(__FILE__) . '/00_fields.php';

	$my_group = array(
		'post' => array( // TODO: Change this to the desired post type name
			array(
				'id'     => 'sample_section',
				'title'  => 'Section title',
				'desc'   => '<p>Some description about this options group</p>',
				'role'   => array('administrator', 'editor'), // Optional. Remove this to display the metadata for all user roles.
				'fields' => kc_sample_fields() // TODO: See 00_fields.php and paste the fields you need here.
			)
		)
	);

	$groups[] = $my_group;
	return $groups;
}

?>

<?php

/**
 * Sample user metadata options.
 * This is very similiar to post/term metadata options,
 * except that it doesn't have a post type/taxonomy name
 *
 * @see 00_fields.php for complete field types.
 */

add_filter( 'kc_user_settings', 'kc_settings_sample_user' );
function kc_settings_sample_user( $groups ) {
	/**
	 * NOTE: Please copy/paste/edit the fields you need, then remove the require_once line.
	 * This is only for simplifying the development.
	 */
	require_once dirname(__FILE__) . '/00_fields.php';

	$my_group = array(
		array(
			array(
				'id'     => 'sample_section',
				'title'  => 'Section title',
				'desc'   => '<p>Some description about this options group</p>',
				/**
				 * Optional. Uncomment this to only display the metadata settings for
				 * certain user roles.
				 */
				// 'role'   => array('administrator', 'editor'),
				'fields' => kc_settings_sample_fields() // NOTE: See 00_fields.php
			)
		)
	);

	$groups[] = $my_group;
	return $groups;
}

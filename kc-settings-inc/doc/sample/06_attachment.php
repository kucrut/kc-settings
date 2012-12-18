<?php

/**
 * Sample attachment metadata options.
 * Make sure to change the post type name as needed.
 * Built-in WordPress post types are: post, page, attachment
 *
 * @see 00_fields.php for complete field types.
 */

add_filter( 'kc_post_settings', 'kc_settings_sample_attachment' );
function kc_settings_sample_attachment( $groups ) {
	/**
	 * NOTE: Please copy/paste/edit the fields you need, then remove the require_once line.
	 * This is only for simplifying the development.
	 */
	require_once dirname(__FILE__) . '/00_fields.php';

	$my_group = array(
		'attachment' => array( // NOTE: Change this key to the desired post type name
			array(
				'id'     => 'sample_section',
				'title'  => 'Section title',
				'desc'   => '<p>Some description about this options group</p>',
				/**
				 * Optional. Uncomment this to only display the metabox for
				 * certain user roles.
				 * Accepts string / array.
				 */
				//'role' => array( 'administrator', 'editor' ),
				'fields' => kc_settings_sample_fields(), // NOTE: See 00_fields.php
				/**
				 * Optional. Uncomment this to only display the metabox for
				 * certain mime types.
				 * Accepts string / array.
				 */
				'post_mime_types' => array( 'image', 'audio' ),
			)
		)
	);

	$groups[] = $my_group;
	return $groups;
}

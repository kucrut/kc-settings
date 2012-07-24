<?php

/**
 * Sample plugin/theme options.
 *
 * @TODO Make sure to use unique and only alphanumerics/dashes/underscores string for the prefix and section field IDs!
 * @see 00_fields.php for complete field types.
 *
 */


add_filter( 'kc_plugin_settings', 'mytheme_options' );
function mytheme_options( $settings ) {
	// TODO: Please copy/paste/edit the fields you need, then remove the require_once line.
	// This is only for simplifying the development.
	require_once dirname(__FILE__) . '/00_fields.php';

	$my_settings = array(
		'prefix'        => 'anything',    // Use only alphanumerics, dashes and underscores here!
		'menu_location' => 'themes.php',  // options-general.php | index.php | edit.php | upload.php | link-manager.php | edit-comments.php | themes.php | users.php | tools.php
		'menu_title'    => 'My Theme Settings',
		'page_title'    => 'My Theme Settings Page',
		'display'       => 'metabox',     // plain|metabox. If you chose to use metabox, don't forget to set their settings too
		'metabox'       => array(
			'context'   => 'normal',  // normal | advanced | side
			'priority'  => 'default', // default | high | low
		),
		'options'       => array(
			array(
				'id'     => 'sample_section',
				'title'  => 'Sample Options',
				'desc'   => '<p>Some description about this options group</p>',
				'fields' => kc_sample_fields() // TODO: See 00_fields.php and paste the fields you need here.
			)
			// You can add more sections here...
		),
		'help'          => array(   // Here goes the contextual helps
			array(
				'id'      => 'help_1',
				'title'   => 'Help title',
				'content' => 'Something....'
			),
			array(
				'id'      => 'help_2',
				'title'   => 'Another Help',
				'content' => 'Something the user needs to know....'
			)
		)
	);

	$settings[] = $my_settings;
	return $settings;
}

?>

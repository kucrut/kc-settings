<?php

/**
 * Sample plugin/theme options.
 *
 * @todo Make sure to use unique and only alphanumerics/dashes/underscores string for the prefix and section field IDs!
 *
 */


add_filter( 'kc_plugin_settings', 'mytheme_options' );
function mytheme_options( $settings ) {
	$options = array(
		array(
			'id'			=> 'sample_section',
			'title'		=> 'Sample Options',
			'desc'		=> '<p>Some description about this options group</p>',
			'fields'	=> array(
				array(
					'id'			=> 'sample_input',
					'title'		=> 'Simple input',
					'desc'		=> 'Just a simple input field',
					'type'		=> 'input',
					'default'	=> 'Default value'
				),
				array(
					'id'			=> 'date',
					'title'		=> 'Date input',
					'type'		=> 'date',
					'default'	=> '2011-12-17'
				),
				array(
					'id'			=> 'color',
					'title'		=> 'Color input',
					'type'		=> 'color',
					'default'	=> '#000000'
				),
				array(
					'id'			=> 'textarea',
					'title'		=> 'Textarea',
					'desc'		=> 'An ordinary text area where you can write some long texts',
					'type'		=> 'textarea',
					'default'	=> 'Default value...'
				),
				array(
					'id'			=> 'sample_checkbox',
					'title'		=> 'Checkboxes (checkbox)',
					'desc'		=> 'You can select one or more',
					'type'		=> 'checkbox',
					'options'	=> array(
						'cbox1'	=> 'Option #1',
						'cbox2'	=> 'Option #2'
					),
					'default'	=> 'cbox1'
				),
				array(
					'id'			=> 'sample_radio',
					'title'		=> 'Radioboxes (radio)',
					'desc'		=> 'You can only select one here',
					'type'		=> 'radio',
					'options'	=> array(
						'radio1'	=> 'Option #1',
						'radio2'	=> 'Option #2 (Default)',
						'radio3'	=> 'Option #3'
					),
					'default'	=> 'radio2'
				),
				array(
					'id'			=> 'sample_select',
					'title'		=> 'Dropdown options (select)',
					'desc'		=> 'You can only select one option here',
					'type'		=> 'select',
					'options'	=> array(
						'select3'	=> 'Option #1',
						'select2'	=> 'Option #2',
						'select1'	=> 'Option #3'
					),
					'default'	=> 'select1'
				),
				array(
					'id'			=> 'sample_multiselect',
					'title'		=> 'Dropdown options (multiple select)',
					'desc'		=> 'You can select more than one option here',
					'type'		=> 'multiselect',
					'options'	=> array(
						'select3'	=> 'Option #1',
						'select2'	=> 'Option #2',
						'select1'	=> 'Option #3',
						'select4'	=> 'Option #4',
						'select5'	=> 'Option #5',
						'select6'	=> 'Option #6'
					),
					'default'	=> 'select6'
				),
				array(
					'id'		=> 'sample_multiinput',
					'title'	=> 'Multi input (multiinput)',
					'desc'	=> 'Input field with your own custom label, to create an array',
					'type'	=> 'multiinput'
				),
				array(
					'id'		=> 'sample_file',
					'title'	=> 'File selection',
					'type'	=> 'file',
					'mode'	=> 'radio' // radio (single) | checkbox (multiple)
				),
				array(
					'id'			=> 'sample_callback_3',
					'title'		=> 'Callback',
					'desc'		=> 'Callback with static argument',
					'type'		=> 'special',
					'cb'			=> 'kcs_sample_callback_static',	// See how to handle the arguments passed at the bottom of this file
					'args'		=> "Hey, I'm the static callback argument",
					'default'	=> 'Some default value'
				),
				array(
					'id'		=> 'sample_callback_4',
					'title'	=> 'Another Callback',
					'desc'	=> 'Callback with dynamic argument (function return value)',
					'type'	=> 'special',
					'cb'		=> 'kcs_sample_callback_dynamic',	// See how to handle the arguments passed at the bottom of this file
					'args'	=> 'kcs_sample_callback_dynamic_args',
					'default'	=> 'Some default value'
				)
			)
		)
		// You can add more sections here...
	);

	$my_settings = array(
		'prefix'				=> 'anything',		// Use only alphanumerics, dashes and underscores here!
		'menu_location'	=> 'themes.php',	// options-general.php | index.php | edit.php | upload.php | link-manager.php | edit-comments.php | themes.php | users.php | tools.php
		'menu_title'		=> 'My Theme Settings',
		'page_title'		=> 'My Theme Settings Page',
		'display'				=> 'metabox',		// plain|metabox. If you chose to use metabox, don't forget to set their settings too
		'metabox'				=> array(
			'context'		=> 'normal',	// normal | advanced | side
			'priority'	=> 'default',	// default | high | low
		),
		'options'				=> $options,
		'help'					=> array(		// Here goes the contextual helps
			array(
				'id'			=> 'help_1',
				'title'		=> 'Help title',
				'content'	=> 'Something....'
			),
			array(
				'id'			=> 'help_2',
				'title'		=> 'Another Help',
				'content'	=> 'Something the user needs to know....'
			)
		)
	);

	$settings[] = $my_settings;
	return $settings;
}


function kcs_sample_callback_static( $field, $db_value, $args ) {
	return $args;
}


function kcs_sample_callback_dynamic( $field, $db_value, $args ) {
	$output  = "I'm gonna give you the value from your argument function.<br />";
	$output .= "Your field name is <b>{$args}</b>, right?";
	return $output;
}


function kcs_sample_callback_dynamic_args( $field, $db_value ) {
	// You can do whatever you want here and then return in
	// So your callback function can process it.

	return $field['field']['name'];
}

?>

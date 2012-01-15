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
			'id'     => 'sample_section',
			'title'  => 'Sample Options',
			'desc'   => '<p>Some description about this options group</p>',
			'fields' => array(
				array(
					'id'      => 'sample_text',
					'title'   => 'Text input',
					'desc'    => 'Just a simple text field',
					'type'    => 'text',
					'default' => 'Default value'
				),
				array(
					'id'      => 'sample_email',
					'title'   => 'Email input',
					'type'    => 'email',
					'default' => 'email@domain.tld'
				),
				array(
					'id'      => 'sample_tel',
					'title'   => 'Telephone input',
					'type'    => 'tel',
					'default' => 'Default value'
				),
				array(
					'id'      => 'sample_number',
					'title'   => 'Number input',
					'type'    => 'number',
					'default' => '1'
				),
				array(
					'id'      => 'sample_password',
					'title'   => 'Password input',
					'type'    => 'password'
				),
				array(
					'id'      => 'sample_date',
					'title'   => 'Date input',
					'type'    => 'date',
					'default' => date('Y-m-d'),
					'desc'    => 'Format: <code>'.date('Y-m-d').'</code>'
				),
				array(
					'id'      => 'sample_month',
					'title'   => 'Month input',
					'type'    => 'month',
					'default' => date('Y-m'),
					'desc'    => 'Format: <code>'.date('Y-m').'</code>'
				),
				array(
					'id'      => 'sample_week',
					'title'   => 'Week input',
					'type'    => 'week',
					'default' => date('Y-\WW'),
					'desc'    => 'Format: <code>'.date('Y-\WW').'</code>'
				),
				array(
					'id'      => 'sample_time',
					'title'   => 'Time input',
					'type'    => 'time',
					'default' => date('H:i'),
					'desc'    => 'Format: <code>'.date('H:i').'</code>'
				),
				array(
					'id'      => 'sample_datetime',
					'title'   => 'Datetime input',
					'type'    => 'datetime',
					'default' => date('Y-m-d\TH:i\Z'),
					'desc'    => 'Format: <code>'.date('Y-m-d\TH:i\Z').'</code>'
				),
				array(
					'id'      => 'sample_datetime-local',
					'title'   => 'Datetime (local) input',
					'type'    => 'datetime-local',
					'default' => date('Y-m-d\TH:i'),
					'desc'    => 'Format: <code>'.date('Y-m-d\TH:i').'</code>'
				),
				array(
					'id'      => 'sample_color',
					'title'   => 'Color input',
					'type'    => 'color',
					'default' => '#000000',
					'desc'    => 'Format: <code>#000000</code>'
				),
				array(
					'id'      => 'sample_textarea',
					'title'   => 'Textarea',
					'type'    => 'An ordinary text area where you can write some long texts',
					'default' => 'textarea',
					'desc'    => 'Default value...'
				),
				array(
					'id'      => 'sample_checkbox',
					'title'   => 'Checkboxes (checkbox)',
					'desc'    => 'You can select one or more',
					'type'    => 'checkbox',
					'options' => array(
						'cbox1' => 'Option #1',
						'cbox2' => 'Option #2'
					),
					'default'	=> 'cbox1'
				),
				array(
					'id'      => 'sample_radio',
					'title'   => 'Radioboxes (radio)',
					'desc'    => 'You can only select one here',
					'type'    => 'radio',
					'options' => array(
						'radio1'  => 'Option #1',
						'radio2'  => 'Option #2 (Default)',
						'radio3'  => 'Option #3'
					),
					'default' => 'radio2'
				),
				array(
					'id'      => 'sample_select',
					'title'   => 'Dropdown options (select)',
					'desc'    => 'You can only select one option here',
					'type'    => 'select',
					'options' => array(
						'select3' => 'Option #1',
						'select2' => 'Option #2',
						'select1' => 'Option #3'
					),
					'default' => 'select1'
				),
				array(
					'id'      => 'sample_multiselect',
					'title'   => 'Dropdown options (multiple select)',
					'desc'    => 'You can select more than one option here (hold control or shift key)',
					'type'    => 'multiselect',
					'options' => array(
						'select3' => 'Option #1',
						'select2' => 'Option #2',
						'select1' => 'Option #3',
						'select4' => 'Option #4',
						'select5' => 'Option #5',
						'select6' => 'Option #6'
					),
					'default' => 'select6'
				),
				array(
					'id'    => 'sample_multiinput',
					'title' => 'Multi input (multiinput)',
					'desc'  => 'Input field with your own custom label, to create an array',
					'type'  => 'multiinput'
				),
				array(
					'id'    => 'sample_file1',
					'title' => 'File selection (single)',
					'desc'	=> 'File list with single selection',
					'type'  => 'file',
					'mode'  => 'radio'
				),
				array(
					'id'    => 'sample_file2',
					'title' => 'File selection (multiple)',
					'desc'	=> 'File list with multiple selection',
					'type'  => 'file',
					'mode'  => 'checkbox'
				),
				array(
					'id'      => 'sample_callback_3',
					'title'   => 'Callback',
					'desc'    => 'Callback with static argument',
					'type'    => 'special',
					'cb'      => 'kc_sample_callback_static',  // See how to handle the arguments passed at the bottom of this file
					'args'    => "Hey, I'm the static callback argument",
					'default' => 'Some default value'
				),
				array(
					'id'      => 'sample_callback_4',
					'title'   => 'Another Callback',
					'desc'    => 'Callback with dynamic argument (function return value)',
					'type'    => 'special',
					'cb'      => 'kc_sample_callback_dynamic',  // See how to handle the arguments passed at the bottom of this file
					'args'    => 'kc_sample_callback_dynamic_args',
					'default' => 'Some default value'
				)
			)
		)
		// You can add more sections here...
	);

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
		'options'       => $options,
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


function kc_sample_callback_static( $field, $db_value, $args ) {
	return $args;
}


function kc_sample_callback_dynamic( $field, $db_value, $args ) {
	$output  = "I'm gonna give you the value from your argument function.<br />";
	$output .= "Your field name is <b>{$args}</b>, right?";
	return $output;
}


function kc_sample_callback_dynamic_args( $field, $db_value ) {
	// You can do whatever you want here and then return in
	// So your callback function can process it.

	return $field['field']['name'];
}

?>

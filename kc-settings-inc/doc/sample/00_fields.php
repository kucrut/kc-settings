<?php

function kc_settings_sample_fields() {
	return array(
		array(
			'id'      => 'sample_text',
			'title'   => 'Text input',
			'desc'    => 'Just a simple text field',
			'type'    => 'text',
			'default' => 'Default value',
			/**
			 * Optional. Uncomment this to only display the metabox for
			 * certain mime types.
			 * Accepts string / array.
			 */
			//'post_mime_types' => array( 'image', 'audio' ),
		),
		array(
			'id'      => 'sample_email',
			'title'   => 'Email input',
			'type'    => 'email',
			'default' => 'email@domain.tld',
			'attr'    => array('placeholder' => 'eg. email@domain.tld')
		),
		array(
			'id'      => 'sample_tel',
			'title'   => 'Telephone input',
			'type'    => 'tel',
			'default' => '+123456789',
			'attr'    => array('placeholder' => 'eg. +123456789')
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
			'type'    => 'date', // Not supported in theme customizer
			'default' => date('Y-m-d'),
			'desc'    => 'Format: <code>'.date('Y-m-d').'</code>'
		),
		array(
			'id'      => 'sample_month',
			'title'   => 'Month input',
			'type'    => 'month', // Not supported in theme customizer
			'default' => date('Y-m'),
			'desc'    => 'Format: <code>'.date('Y-m').'</code>'
		),
		array(
			'id'      => 'sample_week',
			'title'   => 'Week input',
			'type'    => 'week', // Not supported in theme customizer
			'default' => date('Y-\WW'),
			'desc'    => 'Format: <code>'.date('Y-\WW').'</code>'
		),
		array(
			'id'      => 'sample_time',
			'title'   => 'Time input',
			'type'    => 'time', // Not supported in theme customizer
			'default' => date('H:i'),
			'desc'    => 'Format: <code>'.date('H:i').'</code>'
		),
		array(
			'id'      => 'sample_datetime',
			'title'   => 'Datetime input',
			'type'    => 'datetime', // Not supported in theme customizer
			'default' => date('Y-m-d\TH:i\Z'),
			'desc'    => 'Format: <code>'.date('Y-m-d\TH:i\Z').'</code>'
		),
		array(
			'id'      => 'sample_datetime-local',
			'title'   => 'Datetime (local) input',
			'type'    => 'datetime-local', // Not supported in theme customizer
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
			'type'    => 'textarea',
			'desc'    => 'An ordinary text area where you can write some long texts',
			'default' => 'Some text for default value here...'
		),
		array(
			'id'      => 'sample_editor',
			'title'   => 'WYSIWYG Editor',
			'type'    => 'editor', // Not supported in theme customizer
			/**
			 * Optional, these are the defaults
			 * NOTE: Attachment metadata will only use QuickTags
			 */
			'editor_settings' => array(
				'media_buttons' => true,
				'tinymce'       => true,
				'quicktags'     => true
			),
			'desc'    => "Wordpress' builtin WYSIWYG Editor"
		),
		/**
		 * checkbox, radio, select, and multiselect require options
		 * options could be an array or a callback (function/class method)
		 * that returns an array
		 */
		array(
			'id'      => 'sample_checkbox',
			'title'   => 'Checkboxes (checkbox)',
			'desc'    => 'You can select one or more',
			'type'    => 'checkbox', // Not supported in theme customizer
			'options' => array(
				'cbox1' => 'Option #1',
				'cbox2' => 'Option #2'
			),
			'default'	=> 'cbox1'
		),
		array(
			'id'      => 'sample_checkbox2',
			'title'   => 'Categories',
			'desc'    => 'These options are the return value of <code>kcSettings_options_cb::terms("category")</code>',
			'type'    => 'checkbox', // Not supported in theme customizer
			'options' => array('kcSettings_options_cb', 'terms'),
			'args'    => array(
				'taxonomy' => 'category',
				'args' => array( 'depth' => 0 )
			)
			/**
			 * To modify the arguments of get_terms(), for example:
			 * 'args' => array( 'taxonomy' => 'category', 'args' => array('parent' => 1, 'exclude_tree' => 3) )
			 */
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
			'type'    => 'multiselect', // Not supported in theme customizer
			'options' => array(
				'select1' => 'Option #1',
				'select2' => 'Option #2',
				'select3' => 'Option #3',
				'select4' => 'Option #4',
				'select5' => 'Option #5',
				'select6' => 'Option #6'
			),
			'default' => 'select6'
		),
		array(
			'id'      => 'sample_select2',
			'title'   => 'Dropdown options (select)',
			'desc'    => 'These options are the return value of <code>kc_settings_sample_options()</code>',
			'type'    => 'select',
			'options' => 'kc_settings_sample_options',
			'args'    => 'some_argument',
			'default' => 'select1'
		),
		array(
			'id'      => 'sample_select3',
			'title'   => 'Dropdown page',
			'desc'    => 'These options are the return value of <code>kcSettings_options_cb::posts()</code>',
			'type'    => 'select',
			'options' => array('kcSettings_options_cb', 'posts'),
			'args'    => array(
				'post_type' => 'post',
				'args' => array('posts_per_page' => 2) // This is where the arguments for WP_Query goes
			)
		),
		array(
			'id'     => 'sample_multiinput',
			'title'  => 'Multi input (multiinput)',
			'desc'   => 'Input field with your own custom label, to create an array',
			'type'   => 'multiinput', // Not supported in theme customizer
			/**
			 * subfields are optional and will default to text and textarea if not set.
			 * Each sub-field should have id, title and type.
			 * Obviously, you cannot use 'multiinput' as the type here :)
			 */
			'subfields' => array(
				array(
					'id'    => 'key1',
					'title' => 'Single line text',
					'type'  => 'text'
				),
				array(
					'id'    => 'key2',
					'title' => 'Multiple-lines text',
					'type'  => 'textarea'
				),
				array(
					'id'    => 'key3',
					'title' => 'Date',
					'type'  => 'date'
				),
				array(
					'id'    => 'key4',
					'title' => 'Color',
					'type'  => 'color'
				),
				array(
					'id'    => 'key5',
					'title' => 'Single file',
					'type'  => 'file'
				),
				array(
					'id'    => 'key6',
					'title' => 'WP Editor',
					'type'  => 'editor'
				)
			)
		),
		array(
			'id'    => 'sample_file0',
			'title' => 'Single file',
			'desc'  => 'This is useful for multiple tumbnails, logo, background, etc.',
			'type'  => 'file', // Not supported in theme customizer
			'mode'  => 'single',
			/**
			 * This is the image size that will be used for the preview in the backend.
			 * You can use 'full', 'large', 'medium', 'thumbnail' or any other
			 * registered custom image size here.
			 */
			'size'  => 'full'
		),
		array(
			'id'    => 'sample_file1',
			'title' => 'File selection (single)',
			'desc'	=> 'File list with single selection',
			'type'  => 'file', // Not supported in theme customizer
			'mode'  => 'radio',
			/**
			 * Uncomment the next line to customize the file icon width
			 */
			//'size'  => 46
		),
		array(
			'id'    => 'sample_file2',
			'title' => 'File selection (multiple)',
			'desc'	=> 'File list with multiple selection',
			'type'  => 'file', // Not supported in theme customizer
			'mode'  => 'checkbox',
			/**
			 * Uncomment the next line to customize the file icon width
			 */
			//'size'  => 46
		),
		array(
			'id'          => 'sample_media0',
			'title'       => 'Media (single)',
			'desc'        => 'Select media/attachment',
			'type'        => 'media', // Not supported in theme customizer
			'multiple'    => false,
			/**
			 * This is the image size that will be used for the preview in the backend.
			 * You can use 'full', 'large', 'medium', 'thumbnail' or any other
			 * registered custom image size here.
			 */
			'preview_size' => 'thumbnail',
		),
		array(
			'id'          => 'sample_media1',
			'title'       => 'Media (multiple)',
			'desc'        => 'Select media/attachment',
			'type'        => 'media', // Not supported in theme customizer
			'multiple'    => true,
			/**
			 * This is the image size that will be used for the preview in the backend.
			 * You can use 'full', 'large', 'medium', 'thumbnail' or any other
			 * registered custom image size here.
			 */
			'preview_size' => 'thumbnail',
		),
		array(
			'id'      => 'sample_callback_3',
			'title'   => 'Callback',
			'desc'    => 'Callback with static argument',
			'type'    => 'special', // Not supported in theme customizer
			'cb'      => 'kc_settings_sample_callback_static',  // See how to handle the arguments passed at the bottom of this file
			'args'    => "Hey, I'm the static callback argument",
			'default' => 'Some default value'
		),
		array(
			'id'      => 'sample_callback_4',
			'title'   => 'Another Callback',
			'desc'    => 'Callback with dynamic argument (function return value)',
			'type'    => 'special', // Not supported in theme customizer
			'cb'      => 'kc_settings_sample_callback_dynamic',  // See how to handle the arguments passed at the bottom of this file
			'args'    => 'kc_settings_sample_callback_dynamic_args',
			'default' => 'Some default value'
		)
	);
}


function kc_settings_sample_callback_static( $field, $db_value, $args ) {
	return $args;
}


function kc_settings_sample_callback_dynamic( $field, $db_value, $args ) {
	$output  = "This is the value of your argument function.<br />";
	$output .= "Your field name is <b>{$args}</b>, right?";
	return $output;
}


function kc_settings_sample_callback_dynamic_args( $field, $db_value ) {
	return $field['field']['name'];
}


function kc_settings_sample_options_static( $args = '' ) {
	// You can process the $args here ...
	return array(
		'select3' => 'Option #1',
		'select2' => 'Option #2',
		'select1' => 'Option #3',
		'select4' => 'Option #4',
		'select5' => 'Option #5',
		'select6' => 'Option #6'
	);
}

function kc_settings_sample_options( $args = '' ) {
	// You can process the $args here ...
	return array(
		'select1' => 'Option #1',
		'select2' => 'Option #2',
		'select3' => 'Option #3',
		'select4' => 'Option #4',
		'select5' => 'Option #5',
		'select6' => 'Option #6'
	);
}

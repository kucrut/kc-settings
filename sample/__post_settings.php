<?php
add_filter( 'kc_post_settings', 'mypost_options' );
function mypost_options( $groups ) {
	$my_group = array(
		'post'	=> array(		// post_type
			'sample_section' => array(
				'id'				=> 'sample_section',		// section ID for each metabox
				'title'			=> 'Sample Options',		// section title
				'desc'			=> '<p>Some description about this options group</p>',	// section description (optional, default null)
				'priority'	=> 'high',							// section priority, low|high (optional, default high)
				'role'			=> array('administrator', 'editor'),			// user role, only user in this role will get this metabox. use an array for more than one role (optional, default none)
				'fields'		=> array(								// here are the options for this metabox
					'sample_input' => array(
						'id'		=> 'sample_input',			// option ID
						'title'	=> 'Simple input',			// option title/label
						'desc'	=> 'Just a simple input field',		// option description (optional, default null)
						'type'	=> 'input'							// option type, callback|input|textarea|checkbox|radio|select|multiselect (optional, default input)
					),
					'sample_textarea' => array(
						'id'		=> 'sample_textarea',
						'title'	=> 'Textarea',
						'desc'	=> 'An ordinary text area where you can write some long texts',
						'type'	=> 'textarea'
					),
					'sample_checkbox' => array(
						'id'			=> 'sample_checkbox',
						'title'		=> 'Checkboxes (checkbox)',
						'desc'		=> 'You can select one or more',
						'type'		=> 'checkbox',
						'options'	=> array(					// checkbox, radio, select and multiselect option type must have options array
							'cbox1'	=> 'Option #1',
							'cbox2'	=> 'Option #2'
						)
					),
					'sample_radio' => array(
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
					'sample_select' => array(
						'id'			=> 'sample_select',
						'title'		=> 'Dropdown options (select)',
						'desc'		=> 'You can only select one option here',
						'type'		=> 'select',
						'options'	=> array(
							'select3'	=> 'Option #1',
							'select2'	=> 'Option #2',
							'select1'	=> 'Option #3'
						)
					),
					'sample_multiselect' => array(
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
						)
					),
					'sample_multiinput' => array(
						'id'		=> 'sample_multiinput',
						'title'	=> 'Pair input',
						'desc'	=> 'Input field with your own custom label, to create an array',
						'type'	=> 'multiinput'
					)
				)
			)
		)
	);

	$groups[] = $my_group;
	return $groups;
}

?>
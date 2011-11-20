<?php

add_filter( 'kc_user_settings', 'my_user_options' );
function my_user_options( $groups ) {
	$my_group = array(
		array(
			array(
				'id'				=> 'sample_section',
				'title'			=> 'Sample Options',
				'desc'			=> '<p>Some description about this options group</p>',
				'role'			=> array('administrator', 'editor'),
				'fields'		=> array(
					array(
						'id'		=> 'sample_input',
						'title'	=> 'Simple input',
						'desc'	=> 'Just a simple input field',
						'type'	=> 'input'
					),
					array(
						'id'		=> 'sample_input',
						'title'	=> 'Simple input',
						'desc'	=> 'Just a simple input field',
						'type'	=> 'input'
					),
					array(
						'id'		=> 'anything',
						'title'	=> 'Textarea',
						'desc'	=> 'An ordinary text area where you can write some long texts',
						'type'	=> 'textarea'
					),
					array(
						'id'		=> 'date',
						'title'	=> 'Date input',
						'desc'	=> 'Birtdate?',
						'type'	=> 'date'
					)
				)
			)
		)
	);

	$groups[] = $my_group;
	return $groups;
}

add_filter( 'kc_user_settings', 'my_user_options2' );
function my_user_options2( $groups ) {
	$my_group = array(
		array(
			array(
				'id'				=> 'sample_section2',
				'title'			=> 'Sample Options 2',
				'desc'			=> '<p>Some description about this options group</p>',
				'priority'	=> 'high',
				'role'			=> array('administrator', 'editor'),
				'fields'		=> array(
					array(
						'id'		=> 'sample_input2',
						'title'	=> 'Simple input 2',
						'desc'	=> 'Just a simple input field',
						'type'	=> 'input'
					),
					array(
						'id'		=> 'sample_textarea2',
						'title'	=> 'Textarea 2',
						'desc'	=> 'An ordinary text area where you can write some long texts',
						'type'	=> 'textarea'
					),
					array(
						'id'			=> 'sample_checkbox',
						'title'		=> 'Checkboxes (checkbox)',
						'desc'		=> 'You can select one or more',
						'type'		=> 'checkbox',
						'options'	=> array(
							'cbox1'	=> 'Option #1',
							'cbox2'	=> 'Option #2'
						)
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
						)
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
						)
					),
					array(
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
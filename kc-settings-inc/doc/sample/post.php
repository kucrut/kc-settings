<?php
add_filter( 'kc_post_settings', 'mypost_options' );
function mypost_options( $groups ) {
	$my_group = array(
		'post'	=> array(
			array(
				'id'				=> 'sample_section',
				'title'			=> 'Sample Options',
				'desc'			=> '<p>Some description about this options group</p>',
				'priority'	=> 'high',
				'role'			=> array('administrator', 'editor'),
				'fields'		=> array(
					array(
						'id'		=> 'sample_input',
						'title'	=> 'Simple input',
						'desc'	=> 'Just a simple input field',
						'type'	=> 'input'
					),
					array(
						'id'		=> 'date',
						'title'	=> 'Date input',
						'type'	=> 'date'
					),
					array(
						'id'		=> 'sample_textarea',
						'title'	=> 'Textarea',
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
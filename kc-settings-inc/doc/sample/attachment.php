<?php
add_filter( 'kc_post_settings', 'mypost_options2' );
function mypost_options2( $groups ) {
	$my_group = array(
		'attachment'	=> array(		// post_type
			array(
				'id'				=> 'sample_section2',
				'title'			=> 'Another Options',
				'desc'			=> '<p>Some description about this options group</p>',
				'priority'	=> 'high',
				'role'			=> array('administrator', 'editor'),
				'fields'		=> array(
					array(
						'id'		=> 'date2',
						'title'	=> 'Date input',
						'type'	=> 'date'
					),
					array(
						'id'		=> 'sample_textarea2',
						'title'	=> 'Textarea',
						'desc'	=> 'An ordinary text area where you can write some long texts',
						'type'	=> 'textarea'
					)
				)
			),
			array(
				'id'				=> 'sample_section3',
				'title'			=> 'Another Options 3',
				'desc'			=> '<p>Some description about this options group</p>',
				'priority'	=> 'high',
				'role'			=> array('administrator', 'editor'),
				'fields'		=> array(
					array(
						'id'		=> 'sample_input3',
						'title'	=> 'Simple input',
						'desc'	=> 'Just a simple input field',
						'type'	=> 'input'
					),
					array(
						'id'		=> 'sample_textarea3',
						'title'	=> 'Textarea',
						'desc'	=> 'An ordinary text area where you can write some long texts',
						'type'	=> 'textarea'
					)
				)
			)
		)
	);

	$groups[] = $my_group;
	return $groups;
}

?>
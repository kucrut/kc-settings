<?php
add_filter( 'kc_post_settings', 'mypost_options2' );
function mypost_options2( $groups ) {
	$my_group = array(
		'post'	=> array(		// post_type
			'sample_section2' => array(
				'id'				=> 'sample_section2',		// section ID for each metabox
				'title'			=> 'Another Options',		// section title
				'desc'			=> '<p>Some description about this options group</p>',	// section description (optional, default null)
				'priority'	=> 'high',							// section priority, low|high (optional, default high)
				'role'			=> array('administrator', 'editor'),			// user role, only user in this role will get this metabox. use an array for more than one role (optional, default none)
				'fields'		=> array(								// here are the options for this metabox
					'sample_input2' => array(
						'id'		=> 'sample_input2',			// option ID
						'title'	=> 'Simple input',			// option title/label
						'desc'	=> 'Just a simple input field',		// option description (optional, default null)
						'type'	=> 'input'							// option type, callback|input|textarea|checkbox|radio|select|multiselect (optional, default input)
					),
					'sample_textarea2' => array(
						'id'		=> 'sample_textarea2',
						'title'	=> 'Textarea',
						'desc'	=> 'An ordinary text area where you can write some long texts',
						'type'	=> 'textarea'
					)
				)
			),
			'sample_section3' => array(
				'id'				=> 'sample_section3',		// section ID for each metabox
				'title'			=> 'Another Options 3',		// section title
				'desc'			=> '<p>Some description about this options group</p>',	// section description (optional, default null)
				'priority'	=> 'high',							// section priority, low|high (optional, default high)
				'role'			=> array('administrator', 'editor'),			// user role, only user in this role will get this metabox. use an array for more than one role (optional, default none)
				'fields'		=> array(								// here are the options for this metabox
					'sample_input2' => array(
						'id'		=> 'sample_input3',			// option ID
						'title'	=> 'Simple input',			// option title/label
						'desc'	=> 'Just a simple input field',		// option description (optional, default null)
						'type'	=> 'input'							// option type, callback|input|textarea|checkbox|radio|select|multiselect (optional, default input)
					),
					'sample_textarea2' => array(
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
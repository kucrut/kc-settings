<?php
add_filter( 'kc_plugin_settings', 'mytheme_options2' );
function mytheme_options2( $settings ) {
	$klean_options = array(
		'sample_section' => array(
			'id'			=> 'sample_section',
			'title'		=> 'Sample Options',
			'desc'		=> '<p>Some description about this options group</p>',
			'fields'	=> array(
				'sample_input' => array(
					'id'		=> 'sample_input',
					'title'	=> 'Simple input',
					'desc'	=> 'Just a simple input field',
					'type'	=> 'input'
				),
				'sample_textarea' => array(
					'id'		=> 'sample_textarea',
					'title'	=> 'Textarea',
					'desc'	=> 'An ordinary text area where you can write some long texts',
					'type'	=> 'textarea'
				)
			)
		)
	);

	$my_settings = array(
		'prefix'				=> 'whatever',
		'menu_title'		=> 'More Settings',
		'page_title'		=> 'Additional Klean Theme Settings',
		'options'				=> $klean_options
	);

	$settings[] = $my_settings;
	return $settings;
}

?>
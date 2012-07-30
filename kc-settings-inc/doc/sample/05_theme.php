<?php

/**
 * Sample theme customizer options.
 *
 * For section description, don't use ANY HTML tags, they will be stripped
 * because it will be used as a tooltip (title attribute).
 *
 * Not all fields are supported for the theme customizer.
 * Blacklisted fields are: multiinput, multiselect, special, editor,
 * checkbox, file, date, datetime, datetime-local, week, month, time
 *
 * @TODO Make sure to use unique and only alphanumerics/dashes/underscores string for the prefix and section field IDs!
 * @see 00_fields.php for complete field types.
 *
 */


add_filter( 'kc_theme_settings', 'mytheme_options' );
function mytheme_options( $settings ) {
	// TODO: Please copy/paste/edit the fields you need, then remove the require_once line.
	// This is only for simplifying the development.
	require_once dirname(__FILE__) . '/00_fields.php';

	$my_settings = array(
		'prefix'  => 'anything', // Use only alphanumerics, dashes and underscores here!
		'options' => array(
			array(
				'id'     => 'sample_section',
				'title'  => 'Sample Options',
				'desc'   => 'Some description about this options group',
				'fields' => kc_sample_fields() // TODO: See 00_fields.php and paste the fields you need here.
			),
			array(
				'id'     => 'title_tagline', // This will inject new fields under the default "Site Title & Tagline" section
				'title'  => '-', // You'll still have to put some text here eventhough it will not be used
				'fields' => array(
					array(
						'id' => 'another_text',
						'type' => 'text',
						'title' => 'Another text'
					)
				)
			),
			array(
				'id'     => 'colors', // This will inject new fields under the default "Colors" section
				'title'  => '-', // You'll still have to put some text here eventhough it will not be used
				'fields' => array(
					array(
						'id'      => 'title_color',
						'type'    => 'color',
						'default' => '#555',
						'title'   => 'Site Title text color',
						'script'  => "$('#site-title a').css('color', to ? to : '');" // a jQuery line. 'to' is the current value
					),
					array(
						'id'      => 'page_bg',
						'type'    => 'color',
						'default' => '#fff',
						'title'   => 'Page background color',
						'script'  => "$('#page').css('backgroundColor', to ? to : '');" // a jQuery line. 'to' is the current value
					)
				)
			)
			// You can add more sections here...
		)
	);

	$settings[] = $my_settings;
	return $settings;
}

?>

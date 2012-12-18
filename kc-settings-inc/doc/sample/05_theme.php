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
 * @NOTE Make sure to use unique and only alphanumerics/dashes/underscores string for the prefix and section field IDs!
 * @see 00_fields.php for complete field types.
 *
 */


add_filter( 'kc_theme_settings', 'kc_settings_sample_theme' );
function kc_settings_sample_theme( $settings ) {
	/**
	 * NOTE: Please copy/paste/edit the fields you need, then remove the require_once line.
	 * This is only for simplifying the development.
	 */
	require_once dirname(__FILE__) . '/00_fields.php';

	$my_settings = array(
		/**
		 * Only alphanumerics, dashes and underscores are allowed here.
		 */
		'prefix'  => 'anything',
		/**
		 * Copy the customizer.js to your theme's directory file then
		 * uncomment the line below if you want to load a javascript file
		 * when viewing the theme customizer page.
		 */
		// 'script'  => get_stylesheet_directory_uri() . '/customizer.js',
		'options' => array(
			array(
				'id'     => 'sample_section',
				'title'  => 'Sample Options',
				/**
				 * Any HTML tags in the description will be stripped because
				 * this will be used as the tooltip for the section title.
				 */
				'desc'   => 'Some description about this options group',
				'fields' => kc_settings_sample_fields() // NOTE: See 00_fields.php
			),
			array(
				/**
				 * This will inject new fields under the default "Site Title & Tagline" section
				 * The title will not be used, but you still need to put something there to pass
				 * the validator.
				 */
				'id'     => 'title_tagline',
				'title'  => '-',
				'fields' => array(
					array(
						'id' => 'another_text',
						'type' => 'text',
						'title' => 'Another text'
					)
				)
			),
			array(
				/**
				 * This will inject new fields under the default "Colors" section
				 * The title will not be used, but you still need to put something there to pass
				 * the validator.
				 */
				'id'     => 'colors',
				'title'  => '-',
				'fields' => array(
					array(
						'id'      => 'title_color',
						'type'    => 'color',
						'default' => '#555',
						'title'   => 'Site Title text color'
					),
					array(
						'id'      => 'page_bg',
						'type'    => 'color',
						'default' => '#fff',
						'title'   => 'Page background color',
						/**
						 * Below is a sample jQuery line to modify the preview page in realtime.
						 * 'to' is the current value of a setting field.
						 * You don't need this if you already have a JS file loaded and did
						 * your stuff there.
						 */
						'script'  => "$('#page').css('backgroundColor', to ? to : '');"
					)
				)
			)
			// You can add more sections here...
		)
	);

	$settings[] = $my_settings;
	return $settings;
}

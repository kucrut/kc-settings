<?php

/**
 * Display additional fields on profile edit page
 *
 * @credit Justin Tadlock
 * @links http://justintadlock.com/archives/2009/09/10/adding-and-using-custom-user-profile-fields
 *
 * @param int $user_id User ID
 * @return null
 */
function kc_user_meta_field( $user ) {
	$metadata = kc_meta( 'user' );

	if ( !is_array($metadata) || empty($metadata) )
		return false;

	$output = '';
	foreach ( $metadata as $group ) {
		if ( !is_array($group) || empty($group) )
			continue;

		foreach ( $group as $section ) {
			if ( !isset($section['fields']) || !is_array($section['fields']) || empty($section['fields']) )
				continue 2;

			# Section title
			if ( isset($section['title']) && !empty($section['title']) )
				$output .= "<h3>{$section['title']}</h3>\n";

			# The section
			$output .= "<table class='form-table'>\n";
			$output .= "\t<tbody>\n";
			foreach ( $section['fields'] as $field ) {
				extract( $field, EXTR_OVERWRITE );

				# don't use label's for attribute for these types of options
				$label_for = ( !in_array($type, array('checkbox', 'radio')) ) ? $id : null;
				$args = array( 'mode' => 'user', 'object_id' => $user->ID, 'section' => $section['id'], 'field' => $field );

				$output .= "\t\t<tr>\n";
				$output .= "\t\t\t<th>".kc_form_label($title, $label_for, false, false)."</th>\n";
				$output .= "\t\t\t<td>".kc_settings_field( $args )."</td>\n";
				$output .= "\t\t</tr>\n";
			}
			$output .= "\t</tbody>\n";
			$output .= "</table>\n";
		}
	}

	echo $output;
}


/**
 * Save additional user metadata
 *
 * @credit Justin Tadlock
 * @links http://justintadlock.com/archives/2009/09/10/adding-and-using-custom-user-profile-fields
 *
 * @param int $user_id User ID
 * @return null
 */
function kc_user_meta_save( $user_id ) {
	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;

	$metadata = kc_meta( 'user' );
	if ( !is_array($metadata) || empty($metadata) )
		return false;

	# Loop through all of post meta box arguments.
	foreach ( $metadata as $sections ) {
		foreach ( $sections as $section ) {
			# no fields? abort!
			if ( !isset($section['fields']) || empty($section['fields']) )
				return false;

			foreach ( $section['fields'] as $field )
				kc_update_meta( 'user', null, $user_id, $section, $field );
		}
	}

}

?>
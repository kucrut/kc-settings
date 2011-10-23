<?php


class kcUserSettings {
	function __construct( $metadata ) {
		$this->metadata = $metadata;

		# Display additional fields in user profile page
		add_action( 'show_user_profile', array(&$this, 'fields') );
		add_action( 'edit_user_profile', array(&$this, 'fields') );

		# Save the additional data
		add_action( 'personal_options_update', array(&$this, 'save') );
		add_action( 'edit_user_profile_update', array(&$this, 'save') );
	}

	/**
	 * Display additional fields on profile edit page
	 *
	 * @credit Justin Tadlock
	 * @links http://justintadlock.com/archives/2009/09/10/adding-and-using-custom-user-profile-fields
	 *
	 * @param int $user_id User ID
	 * @return null
	 */
	function fields( $user ) {
		$output = '';
		foreach ( $this->metadata as $group ) {
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
	function save( $user_id ) {
		if ( !current_user_can( 'edit_user', $user_id ) )
			return false;

		# Loop through all of post meta box arguments.
		foreach ( $this->metadata as $sections ) {
			foreach ( $sections as $section ) {
				# no fields? abort!
				if ( !isset($section['fields']) || empty($section['fields']) )
					return false;

				foreach ( $section['fields'] as $field )
					kc_update_meta( 'user', null, $user_id, $section, $field );
			}
		}
	}

}


?>
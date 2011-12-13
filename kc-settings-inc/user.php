<?php

class kcSettings_user {
	protected static $settings;

	public static function init() {
		self::$settings = kcSettings::get_data('settings', 'user' );
		kcSettings::$data['pages'][] = 'profile.php';
		kcSettings::$data['pages'][] = 'user-edit.php';

		# Display additional fields in user profile page
		add_action( 'show_user_profile', array(__CLASS__, '_fields') );
		add_action( 'edit_user_profile', array(__CLASS__, '_fields') );

		# Save the additional data
		add_action( 'personal_options_update', array(__CLASS__, '_save') );
		add_action( 'edit_user_profile_update', array(__CLASS__, '_save') );
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
	public static function _fields( $user ) {
		$output = '';
		foreach ( self::$settings as $group ) {
			foreach ( $group as $section ) {
				# Section title & desc
				$output .= "<h3>{$section['title']}</h3>\n";
				if ( isset($section['desc']) && !empty($section['desc']) )
					$output .= "{$section['desc']}\n";

				# The section
				$output .= "<table class='form-table'>\n";
				$output .= "\t<tbody>\n";
				foreach ( $section['fields'] as $field ) {
					$label_for = ( !in_array($field['type'], array('checkbox', 'radio', 'multiinput')) ) ? $field['id'] : null;
					$args = array( 'mode' => 'user', 'object_id' => $user->ID, 'section' => $section['id'], 'field' => $field );

					$output .= "\t\t<tr>\n";
					$output .= "\t\t\t<th>".kcs_form_label($field['title'], $label_for, false, false)."</th>\n";
					$output .= "\t\t\t<td>".kcs_settings_field( $args )."</td>\n";
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
	public static function _save( $user_id ) {
		if ( !current_user_can( 'edit_user', $user_id ) )
			return $user_id;

		foreach ( self::$settings as $group ) {
			foreach ( $group as $section )
				foreach ( $section['fields'] as $field )
					kcs_update_meta( 'user', null, $user_id, $section, $field );
		}
	}

}


?>

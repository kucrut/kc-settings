<?php

class kcPostSettings {

	function init( $cfields ) {
		$this->cfields = $cfields;

		# Create metabox(es)
		add_action( 'admin_menu', array($this, 'create_meta_box') );
		# Save the custom fields values
		add_action( 'save_post', 'kc_save_cfields' );
	}


	# Create metabox
	function create_meta_box() {

		# loop trough the post options array
		foreach ( $this->cfields as $post_type => $sections ) {
			if ( is_array($sections) && !empty($sections) ) {
				foreach ( $sections as $section ) {
					# does this section have options?
					if ( !isset($section['fields']) || empty($section['fields']) )
						return;

					# does this section have role set?
					if ( isset($section['role']) || $section['role'] != '' ) {
						if ( !is_array($section['role']) )
							$roles = array( $section['role'] );
						else
							$roles = $section['role'];

						# get current user data
						global $current_user;

						# if current user is not within the roles, abort
						$allowed = false;
						foreach ( $roles as $r ) {
							if ( in_array($r, $current_user->roles) )
								$allowed = true;
						}
						if ( !$allowed )
							return;
					}

					# set metabox priority
					$priority = ( isset($section['priority']) && in_array($section['priority'], array('low', 'high')) ) ? $section['priority'] : 'high';

					# add metabox
					add_meta_box( "kc-metabox-{$post_type}-{$section['id']}", $section['title'], array($this, 'fill_meta_box'), $post_type, 'normal', $priority, $section['fields'] );
				}
			}
		}
	}


	# Populate metabox
	function fill_meta_box( $object, $box ) {
		$section = str_replace( "kc-metabox-{$object->post_type}-", '', $box['id'] );

		$output  = "<input type='hidden' name='{$object->post_type}_kc_meta_box_nonce' value='".wp_create_nonce( '___kc_meta_box_nonce___' )."' />";
		$output .= "<table class='form-table'>\n";

		$fields = $box['args'];

		foreach ( $fields as $field ) {
			$output .= "\t<tr>\n";

			# don't use label's for attribute for these types of options
			$label_for = ( !in_array($field['type'], array('checkbox', 'radio')) ) ? $field['id'] : null;
			# label for each option field
			$output .= kc_form_label( $field['title'], $label_for, true, false );

			# print the option field
			$output .= "\t\t<td>";
			$output .= kc_settings_field( array( 'mode' => 'cfields', 'post_id' => $object->ID, 'section' => $section, 'field' => $field ) );
			$output .= "\t\t</td>\n";

			$output .= "\t</tr>\n";
		}

		$output .= "</table>\n";

		echo $output;
	}

}


?>
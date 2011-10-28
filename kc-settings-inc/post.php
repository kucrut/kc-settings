<?php

class kcPostSettings {

	function __construct( $metadata ) {
		$this->metadata = $metadata;

		# Create metabox(es)
		add_action( 'admin_menu', array(&$this, 'create_meta_box') );
		# Save the custom fields values
		add_action( 'save_post', array(&$this, 'save') );

		# Attachment
		if ( isset($this->metadata['attachment']) && is_array($this->metadata['attachment']) && !empty($this->metadata['attachment']) )
			$this->attachment_init( $this->metadata['attachment'] );
	}


	# Create metabox
	function create_meta_box() {

		# loop trough the post options array
		foreach ( $this->metadata as $post_type => $sections ) {
			$post_type_obj = get_post_type_object( $post_type );
			# skip if no sections found
			if ( !is_array($sections) || empty($sections) )
				continue;

			foreach ( $sections as $section ) {
				# skip if no options found
				if ( !isset($section['fields']) || empty($section['fields']) )
					continue;

				# does this section have role set?
				if ( isset($section['role']) && !empty($section['role']) )
					if ( !kcs_check_roles($section['role']) )
						continue;

				# set metabox priority
				$priority = ( isset($section['priority']) && in_array($section['priority'], array('low', 'high')) ) ? $section['priority'] : 'high';

				# add metabox
				$title = ( !isset($section['title']) ) ? sprintf( __('%s Settings', 'kc-settings'), $post_type_obj->label ) : $section['title'];
				add_meta_box( "kc-metabox-{$post_type}-{$section['id']}", $title, array(&$this, 'fill_meta_box'), $post_type, 'normal', $priority, $section['fields'] );
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
			$output .= kcs_form_label( $field['title'], $label_for, true, false );

			# print the option field
			$output .= "\t\t<td>";
			$output .= kcs_settings_field( array( 'mode' => 'post', 'object_id' => $object->ID, 'section' => $section, 'field' => $field ) );
			$output .= "\t\t</td>\n";

			$output .= "\t</tr>\n";
		}

		$output .= "</table>\n";

		echo $output;
	}


	/**
	 * Save post custom fields values
	 *
	 * @param int $post_id
	 *
	 */

	function save( $post_id ) {
		if ( isset($_POST['action']) && $_POST['action'] == 'inline-save' )
			return $post_id;

		if ( isset($_POST['post_type']) )
			$post_type = $_POST['post_type'];
		$post_metadata = ( isset($post_type) ) ? $this->metadata[$post_type] : null;

		# empty options array? abort!
		if ( empty($post_metadata) )
			return $post_id;

		$post_type_obj = get_post_type_object( $post_type );
		if ( ( wp_verify_nonce($_POST["{$post_type}_kc_meta_box_nonce"], '___kc_meta_box_nonce___') && current_user_can($post_type_obj->cap->edit_post) ) !== true )
			return $post_id;

		# Loop through all of post meta box arguments.
		foreach ( $post_metadata as $section ) {
			# no fields? abort!
			if ( !isset($section['fields']) || empty($section['fields']) )
				return $post_id;

			foreach ( $section['fields'] as $field ) {
				kcs_update_meta( 'post', $post_type, $post_id, $section, $field );
			}
		}
	}


	function attachment_init( $sections ) {
		$this->attachment_sections = array();

		foreach ( $sections as $section ) {
			# skip if no options found
			if ( !isset($section['fields']) || empty($section['fields']) )
				continue;

			# does this section have role set?
			if ( isset($section['role']) && !empty($section['role']) )
				if ( !kcs_check_roles($section['role']) )
					continue;

			$this->attachment_sections[] = $section;
		}

		if ( empty($this->attachment_sections) )
			return;

		add_filter( 'attachment_fields_to_edit', array($this, 'attachment_fields_to_edit'), 10, 2 );
		add_filter( 'attachment_fields_to_save', array($this, 'attachment_fields_to_save'), 10, 2 );
	}


	function attachment_fields_to_edit( $fields, $post ) {
		foreach ( $this->attachment_sections as $section ) {
			foreach ( $section['fields'] as $field ) {
				extract( $field, EXTR_OVERWRITE );

				if ( !empty($field['file_type']) && !strstr($post->post_mime_type, $field['file_type']) )
					continue;

				$input_args = array(
					'mode'			=> 'attachment',
					'object_id'	=> $post->ID,
					'section'		=> $section['id'],
					'field'			=> $field
				);

				$nu_field = array(
					'label' => $title,
					'input' => 'html',
					'html'  => kcs_settings_field( $input_args )
				);
				if ( isset($desc) && !empty($desc) )
					$nu_field['helps'] = $desc;

				$fields[$id] = $nu_field;
			}
		}

		return $fields;
	}


	function attachment_fields_to_save( $post, $attachment ) {
		foreach ( $this->attachment_sections as $section ) {
			foreach ( $section['fields'] as $field ) {
				kcs_update_meta( 'post', 'attachment', $post['ID'], $section, $field, true );
			}
		}

		return $post;
	}

}


?>

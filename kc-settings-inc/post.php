<?php

class kcSettings_post {

	public static function init() {
		add_action( 'add_meta_boxes', array(__CLASS__, '_create_meta_box'), 11, 2 );
		add_action( 'save_post', array(__CLASS__, '_save'), 11, 2 );

		if ( isset(kcSettings::$data['settings']['post']['attachment']) ) {
			kcSettings::$data['pages'][] = 'media.php';
			kcSettings::$data['pages'][] = 'media-upload-popup';

			add_filter( 'attachment_fields_to_edit', array(__CLASS__, '_attachment_fields_to_edit'), 10, 2 );
			add_filter( 'attachment_fields_to_save', array(__CLASS__, '_attachment_fields_to_save'), 10, 2 );
		}
	}


	# Create metabox
	public static function _create_meta_box( $post_type, $post ) {
		if ( !isset(kcSettings::$data['settings']['post'][$post_type]) )
			return;

		kcSettings::$data['pages'][] = 'post.php';
		kcSettings::$data['pages'][] = 'post-new.php';

		foreach ( kcSettings::$data['settings']['post'][$post_type] as $section ) {
			# does this section have role set?
			if ( (isset($section['role']) && !empty($section['role'])) && !kcs_check_roles($section['role']) )
				continue;

			# set metabox priority
			$priority = ( isset($section['priority']) && in_array($section['priority'], array('low', 'high')) ) ? $section['priority'] : 'high';

			# add metabox
			add_meta_box( "kc-metabox-{$post_type}-{$section['id']}", $section['title'], array(__CLASS__, '_fill_meta_box'), $post_type, 'normal', $priority, $section );
		}
	}


	# Populate metabox
	public static function _fill_meta_box( $object, $box ) {
		$section = $box['args'];

		$output  = "<input type='hidden' name='{$object->post_type}_kc_meta_box_nonce' value='".wp_create_nonce( '___kc_meta_box_nonce___' )."' />";
		$output .= "<table class='form-table'>\n";

		foreach ( $section['fields'] as $field ) {
			$label_for = ( !in_array($field['type'], array('checkbox', 'radio')) ) ? $field['id'] : null;
			$output .= "\t<tr>\n";
			$output .= kcs_form_label( $field['title'], $label_for, true, false );
			$output .= "\t\t<td>";
			$output .= kcs_settings_field( array( 'mode' => 'post', 'object_id' => $object->ID, 'section' => $section['id'], 'field' => $field ) );
			$output .= "\t\t</td>\n";
			$output .= "\t</tr>\n";
		}

		$output .= "</table>\n";

		echo $output;
	}


	# Save post metadata/custom fields values
	public static function _save( $post_id, $post ) {
		if ( !isset(kcSettings::$data['settings']['post'][$post->post_type])
					|| ( isset($_POST['action']) && $_POST['action'] == 'inline-save' )
					|| $post->post_status == 'auto-draft' )
			return $post_id;

		$post_type_obj = get_post_type_object( $post->post_type );
		if ( ( wp_verify_nonce($_POST["{$post->post_type}_kc_meta_box_nonce"], '___kc_meta_box_nonce___') && current_user_can($post_type_obj->cap->edit_post) ) !== true )
			return $post_id;

		foreach ( kcSettings::$data['settings']['post'][$post->post_type] as $section ) {
			foreach ( $section['fields'] as $field )
				kcs_update_meta( 'post', $post->post_type, $post_id, $section, $field );
		}
	}


	public static function _attachment_fields_to_edit( $fields, $post ) {
		foreach ( kcSettings::$data['settings']['post']['attachment'] as $section ) {
			foreach ( $section['fields'] as $field ) {
				if ( !empty($field['file_type']) && !strstr($post->post_mime_type, $field['file_type']) )
					continue;

				$input_args = array(
					'mode'			=> 'attachment',
					'object_id'	=> $post->ID,
					'section'		=> $section['id'],
					'field'			=> $field
				);

				$nu_field = array(
					'label' => $field['title'],
					'input' => 'html',
					'html'  => kcs_settings_field( $input_args )
				);
				if ( isset($desc) && !empty($desc) )
					$nu_field['helps'] = $field['desc'];

				$fields[$field['id']] = $nu_field;
			}
		}

		return $fields;
	}


	public static function _attachment_fields_to_save( $post, $attachment ) {
		foreach ( kcSettings::$data['settings']['post']['attachment'] as $section ) {
			foreach ( $section['fields'] as $field )
				kcs_update_meta( 'post', 'attachment', $post['ID'], $section, $field, true );
		}

		return $post;
	}
}
?>

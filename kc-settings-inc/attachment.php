<?php

class kcSettings_attachment {
	protected static $settings;


	public static function init( $settings ) {
		self::$settings = $settings;

		kcSettings::add_page( 'media.php' );
		kcSettings::add_page( 'media-upload-popup' );

		add_filter( 'attachment_fields_to_edit', array(__CLASS__, '_edit'), 10, 2 );
		add_filter( 'attachment_fields_to_save', array(__CLASS__, '_save'), 10, 2 );
	}


	public static function _edit( $fields, $post ) {
		foreach ( self::$settings as $section ) {
			foreach ( $section['fields'] as $field ) {
				if ( !empty($field['file_type']) && !strstr($post->post_mime_type, $field['file_type']) )
					continue;

				$input_args = array(
					'mode'      => 'attachment',
					'object_id' => $post->ID,
					'section'   => $section['id'],
					'field'     => $field
				);

				$nu_field = array(
					'label' => $field['title'],
					'input' => 'html',
					'html'  => _kc_field( $input_args )
				);
				if ( isset($desc) && !empty($desc) )
					$nu_field['helps'] = $field['desc'];

				$fields[$field['id']] = $nu_field;
			}
		}

		return $fields;
	}


	public static function _save( $post, $attachment ) {
		foreach ( self::$settings as $section ) {
			foreach ( $section['fields'] as $field )
				_kc_update_meta( 'post', 'attachment', $post['ID'], $section, $field, true );
		}

		return $post;
	}
}

?>

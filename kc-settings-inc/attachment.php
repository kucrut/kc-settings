<?php

class kcSettings_attachment {
	protected static $settings;
	public static $sns_done = false;


	public static function init( $settings ) {
		self::$settings = $settings;

		kcSettings::add_page( 'media.php' );
		kcSettings::add_page( 'media-upload-popup' );

		add_action( 'admin_head', array(__CLASS__, 'sns'), 9 );
		add_filter( 'attachment_fields_to_edit', array(__CLASS__, '_edit'), 10, 2 );
		add_filter( 'attachment_fields_to_save', array(__CLASS__, '_save'), 10, 2 );
	}


	public static function _edit( $fields, $post ) {
		foreach ( self::$settings as $section ) {
			foreach ( $section['fields'] as $field ) {
				if ( !empty($field['file_type']) && !strstr($post->post_mime_type, $field['file_type']) )
					continue;

				$nu_field = array(
					'label' => $field['title'],
					'input' => 'html'
				);

				if ( $field['type'] === 'editor' ) {
					$nu_field['html'] = self::field_editor( $field['id'], $post->ID, get_post_meta($post->ID, "_{$field['id']}", true) );
				}
				else {
					$nu_field['html'] = _kc_field( array(
						'mode'      => 'attachment',
						'object_id' => $post->ID,
						'section'   => $section['id'],
						'field'     => $field
					) );
				}

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


	public static function field_editor( $field_id, $post_id, $db_value ) {
		ob_start();
		wp_editor(
			$db_value,
			"attachments{$post_id}{$field_id}",
			array(
				'textarea_name' => "attachments[{$post_id}][{$field_id}]",
				'textarea_rows' => 7,
				'media_buttons' => false,
				'tinymce'       => false,
				'editor_class'  => 'kcss',
				'wpautop'       => false,
				'quicktags'     => array( 'buttons' => 'strong,em,link,block,ul,ol,li,code,ins,del,close' )
			)
		);

		return ob_get_clean();
	}


	public static function sns() {
		global $pagenow;
		if ( !in_array( $pagenow, array('media-upload.php', 'media.php') ) )
			return;

		wp_enqueue_script( 'quicktags' ); ?>

<script>window.tinymce = false;</script>
<style>.wp-editor-wrap {width:460px} .wp-editor-container textarea.wp-editor-area.kcss {width:457px} #media-upload .wp-editor-area {border:0;margin:0}</style>
	<?php
		self::$sns_done = true;
	}
}

?>

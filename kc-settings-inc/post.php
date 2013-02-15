<?php

class kcSettings_post {
	protected static $settings;

	public static function init() {
		$settings = kcSettings::get_data( 'settings', 'post' );
		if ( empty($settings) )
			return;

		self::$settings = $settings;
		add_action( 'add_meta_boxes', array(__CLASS__, '_create'), 11, 2 );
		add_action( 'save_post', array(__CLASS__, '_save'), 11 );
		add_action( 'edit_attachment', array(__CLASS__, '_save'), 11 );
	}


	# Filter settings
	private static function _bootstrap_sections( $sections, $post ) {
		foreach ( $sections as $section_index => $section ) {
			# does this section have role set?
			if ( !empty($section['role']) && !kc_check_roles( $section['role'] ) ) {
				unset( $sections[$section_index] );
				continue;
			}

			# Attachments:
			if ( $post->post_type == 'attachment' ) {
				# 0. Check if this section is meant only for a certain mime types
				if ( !empty($section['post_mime_types']) ) {
					$section_mime_type_matches = array_filter(
						(array) $section['post_mime_types'],
						array( new _kc_Array_Filter_Helper( $post->post_mime_type ), 'match_mime_types' )
					);
					if ( empty($section_mime_type_matches) ) {
						unset( $sections[$section_index] );
						continue;
					}
				}

				# 1. Check if fields are meant only for certain mime types
				foreach ( $section['fields'] as $field_index => $field ) {
					if ( !empty($field['post_mime_types']) ) {
						$field_mime_type_matches = array_filter(
							(array) $field['post_mime_types'],
							array( new _kc_Array_Filter_Helper( $post->post_mime_type ), 'match_mime_types' )
						);
						if ( empty($field_mime_type_matches) ) {
							unset( $sections[$section_index]['fields'][$field_index] );
							continue;
						}
					}
				}

				if ( empty($sections[$section_index]['fields']) ) {
					unset( $sections[$section_index] );
				}
			}
		}

		return $sections;
	}

	# Create metabox
	public static function _create( $post_type, $post ) {
		if ( empty(self::$settings[$post_type]) )
			return;

		$sections = self::_bootstrap_sections( self::$settings[$post_type], $post );
		if ( empty($sections) )
			return;

		kcSettings::add_page( 'post.php' );
		kcSettings::add_page( 'post-new.php' );

		foreach ( $sections as $section_index => $section ) {
			add_meta_box(
				"kc-metabox-{$post_type}-{$section['id']}",
				$section['title'],
				array( __CLASS__, '_fill' ),
				$post_type,
				$section['metabox']['context'],
				$section['metabox']['priority'],
				$section
			);
		}
	}


	# Populate metabox
	public static function _fill( $object, $box ) {
		$section = $box['args'];
		if ( !empty($section['desc']) ) {
			echo wpautop( $section['desc'] ) . PHP_EOL; // xss ok
		}

		$on_side = $section['metabox']['context'] == 'side' ? true : false;
		if ( $on_side ) {
			$wraps = array(
				'block' => array('<ul class="kcs-sideform">', '</ul>'),
				'row'   => array('<li>', '</li>')
			);
		}
		else {
			$wraps = array(
				'block' => array('<table class="form-table">', '</table>'),
				'row'   => array('<tr>', '</tr>')
			);
		}
		?>
		<?php wp_nonce_field( '___kc_meta_box_nonce___', "{$object->post_type}_kc_meta_box_nonce" ) ?>
		<?php echo $wraps['block'][0] . PHP_EOL; ?>
		<?php
			foreach ( $section['fields'] as $field ) :
				if ( !in_array( $field['type'], array( 'checkbox', 'radio', 'multiinput', 'file' ) ) ) {
					$label_for = $field['id'];
					if ( $field['type'] === 'editor' )
						$label_for = strtolower( str_replace( array( '-', '_' ), '', $label_for ) );
				}
				else {
					$label_for = '';
				}

				$f_label = _kc_field_label( $field['title'], $label_for, !$on_side, false );
				$f_input = _kc_field( array( 'mode' => 'post', 'object_id' => $object->ID, 'section' => $section['id'], 'field' => $field ) );
		?>
			<?php echo $wraps['row'][0] . PHP_EOL; // xss ok ?>
			<?php if ( $on_side ) : ?>
			<span class="side-label"><?php echo $f_label; // xss ok ?></span>
			<?php echo $f_input; // xss ok ?>
			<?php else : ?>
			<?php echo $f_label; // xss ok ?>
			<td>
				<?php echo $f_input; // xss ok ?>
			</td>
			<?php endif; ?>
			<?php echo $wraps['row'][1] . PHP_EOL; // xss ok ?>
		<?php endforeach; ?>
		<?php echo $wraps['block'][1] . PHP_EOL; // xss ok ?>
		<?php
	}


	# Save post metadata/custom fields values
	public static function _save( $post_id ) {
		if ( !current_user_can( 'edit_post', $post_id ) )
			return $post_id;

		$post = get_post( $post_id );
		if ( empty(self::$settings[$post->post_type]) )
			return $post_id;

		$sections = self::_bootstrap_sections( self::$settings[$post->post_type], $post );
		if (
			empty( $sections )
			|| ( isset($_POST['action']) && in_array( $_POST['action'], array('inline-save', 'trash', 'untrash') ) )
			|| $post->post_status == 'auto-draft'
			|| empty( $_POST["{$post->post_type}_kc_meta_box_nonce"] )
			|| !wp_verify_nonce( $_POST[ "{$post->post_type}_kc_meta_box_nonce" ], '___kc_meta_box_nonce___' )
		) {
			return $post_id;
		}

		foreach ( $sections as $section ) {
			foreach ( $section['fields'] as $field )
				_kc_update_meta( 'post', $post->post_type, $post_id, $section, $field );
		}

		return $post_id;
	}
}

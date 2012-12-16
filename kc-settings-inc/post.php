<?php

class kcSettings_post {
	protected static $settings;

	public static function init() {
		self::$settings = kcSettings::get_data('settings', 'post' );
		if ( empty(self::$settings) )
			return;

		add_action( 'add_meta_boxes', array(__CLASS__, '_create'), 11, 2 );
		add_action( 'save_post', array(__CLASS__, '_save'), 11 );
		add_action( 'edit_attachment', array(__CLASS__, '_save'), 11 );
	}


	# Create metabox
	public static function _create( $post_type, $post ) {
		if ( !isset(self::$settings[$post_type]) )
			return;

		kcSettings::add_page( 'post.php' );
		kcSettings::add_page( 'post-new.php' );

		foreach ( self::$settings[$post_type] as $section ) {
			# does this section have role set?
			if ( (isset($section['role']) && !empty($section['role'])) && !kc_check_roles($section['role']) )
				continue;

			# add metabox
			add_meta_box( "kc-metabox-{$post_type}-{$section['id']}", $section['title'], array(__CLASS__, '_fill'), $post_type, $section['metabox']['context'], $section['metabox']['priority'], $section );
		}
	}


	# Populate metabox
	public static function _fill( $object, $box ) {
		$output = '';
		$section = $box['args'];
		if ( isset($section['desc']) && !empty($section['desc']) )
			$output .= wpautop( $section['desc'] );

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
		<?php echo wp_nonce_field( '-1', "{$object->post_type}_kc_meta_box_nonce" ) . PHP_EOL ?>
		<?php echo $wraps['block'][0] . PHP_EOL; ?>
		<?php
			foreach ( $section['fields'] as $field ) :
				$label_for = ( !in_array($field['type'], array('checkbox', 'radio', 'multiinput', 'file')) ) ? $field['id'] : null;
				$f_label = _kc_field_label( $field['title'], $label_for, !$on_side, false );
				$f_input = _kc_field( array( 'mode' => 'post', 'object_id' => $object->ID, 'section' => $section['id'], 'field' => $field ) );
		?>
			<?php echo $wraps['row'][0] . PHP_EOL; ?>
			<?php if ( $on_side ) : ?>
			<span class="side-label"><?php echo $f_label ?></span>
			<?php echo $f_input; ?>
			<?php else : ?>
			<?php echo $f_label; ?>
			<td>
				<?php echo $f_input ?>
			</td>
			<?php endif; ?>
			<?php echo $wraps['row'][1] . PHP_EOL; ?>
		<?php endforeach; ?>
		<?php echo $wraps['block'][1] . PHP_EOL; ?>
		<?php
	}


	# Save post metadata/custom fields values
	public static function _save( $post_id ) {
		$post = get_post( $post_id );
		if (
			!current_user_can( 'edit_post', $post_id )
			|| !isset( self::$settings[$post->post_type] )
			|| ( isset($_POST['action']) && in_array($_POST['action'], array('inline-save', 'trash', 'untrash')) )
			|| $post->post_status == 'auto-draft'
			|| empty( $_POST["{$post->post_type}_kc_meta_box_nonce"] )
			|| !wp_verify_nonce( $_POST["{$post->post_type}_kc_meta_box_nonce"], '___kc_meta_box_nonce___' )
		) {
			return $post_id;
		}

		foreach ( self::$settings[$post->post_type] as $section ) {
			foreach ( $section['fields'] as $field )
				_kc_update_meta( 'post', $post->post_type, $post_id, $section, $field );
		}

		return $post_id;
	}
}

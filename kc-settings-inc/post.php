<?php

class kcSettings_post {
	protected static $settings;

	public static function init() {
		self::$settings = kcSettings::get_data('settings', 'post' );

		if ( isset(self::$settings['attachment']) ) {
			require_once dirname( __FILE__ ) . '/attachment.php';
			kcSettings_attachment::init( self::$settings['attachment'] );
			unset( self::$settings['attachment'] );
		}

		if ( empty(self::$settings) )
			return;

		add_action( 'add_meta_boxes', array(__CLASS__, '_create'), 11, 2 );
		add_action( 'save_post', array(__CLASS__, '_save'), 11, 2 );
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
				'block' => array("<ul class='kcs-sideform'>\n", "</ul>\n"),
				'row'   => array("\t<li>\n", "\t</li>\n")
			);
		}
		else {
			$wraps = array(
				'block' => array("<table class='form-table'>\n", "</table>\n"),
				'row'   => array("\t<tr>\n", "\t</tr>\n")
			);
		}

		$output .= "<input type='hidden' name='{$object->post_type}_kc_meta_box_nonce' value='".wp_create_nonce( '___kc_meta_box_nonce___' )."' />";
		$output .= $wraps['block'][0];

		foreach ( $section['fields'] as $field ) {
			$label_for = ( !in_array($field['type'], array('checkbox', 'radio', 'multiinput', 'file')) ) ? $field['id'] : null;
			$output .= $wraps['row'][0];
			$f_label = _kc_field_label( $field['title'], $label_for, !$on_side, false );
			$output .= ( $on_side ) ? "\t\t<span class='side-label'>{$f_label}</span>\n" : $f_label;
			$f_input = _kc_field( array( 'mode' => 'post', 'object_id' => $object->ID, 'section' => $section['id'], 'field' => $field ) );
			$output .= ( $on_side ) ? $f_input : "\t\t<td>\n\t\t\t{$f_input}\n\t\t</td>\n";
			$output .= $wraps['row'][1];
		}

		$output .= $wraps['block'][1];

		echo $output;
	}


	# Save post metadata/custom fields values
	public static function _save( $post_id, $post ) {
		if ( !isset(self::$settings[$post->post_type])
		      || ( isset($_POST['action']) && in_array($_POST['action'], array('inline-save', 'trash', 'untrash')) )
		      || $post->post_status == 'auto-draft'
		      || !isset($_POST["{$post->post_type}_kc_meta_box_nonce"]) )
			return $post_id;

		$post_type_obj = get_post_type_object( $post->post_type );
		if ( ( wp_verify_nonce($_POST["{$post->post_type}_kc_meta_box_nonce"], '___kc_meta_box_nonce___') && current_user_can($post_type_obj->cap->edit_post) ) !== true )
			return $post_id;

		foreach ( self::$settings[$post->post_type] as $section ) {
			foreach ( $section['fields'] as $field )
				_kc_update_meta( 'post', $post->post_type, $post_id, $section, $field );
		}

		return $post_id;
	}
}
?>

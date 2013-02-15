<?php
/**
 * Menu item metadata
 * @since 2.7.8
 * @credit Weston Ruter https://twitter.com/westonruter
 * @link https://gist.github.com/3802459
 */

final class kcSettings_menu_item {
	protected static $sections = array();
	
	public static function init() {
		self::$sections = kcSettings::get_data( 'settings', 'menu_item' );
		kcSettings::add_page( 'nav-menus.php' );
		
		add_filter( 'wp_edit_nav_menu_walker', array(__CLASS__, '_walker') );
		add_action( 'wp_update_nav_menu_item', array(__CLASS__, '_save'), 10, 3 );
	}


	public static function _walker() {
		require_once dirname( __FILE__ ) . '/menu_item_ext.php';
		return 'KC_Walker_Nav_Menu_Edit';
	}

	public static function get_fields( $item, $depth, $args ) {
		$wide_fields = array( 'textarea', 'checkbox' );
		$out = '';
		foreach ( self::$sections as $section ) {
			$out .= '<div class="kc-menu-item-section">';
			$out .= "<h2>{$section['title']}</h2>\n";
			foreach ( $section['fields'] as $field ) {
				$field_html = _kc_field( array(
					'mode'      => 'menu_item',
					'object_id' => $item->ID,
					'section'   => $section['id'],
					'field'     => $field,
					'desc_tag'  => 'span',
				) );

				if ( $field['type'] == 'special' ) {
					$out .= $field_html;
					continue;
				}

				$out .= "<p class='description description-wide'>\n";
				$out .= "<label for='edit-menu-item-{$section['id']}-{$field['id']}-{$item->ID}'>{$field['title']}</label><br />\n";
				$out .= "<span class='kcs-field-wrap kcs-{$field['type']}-wrap'>{$field_html}</span>\n";
				$out .= "</p>\n";
			}
			$out .= '</div>';
		}
		
		return $out; 
	}
	

	public static function _save( $menu_id, $menu_item_db_id, $args ) {
		foreach ( self::$sections as $section )
			foreach ( $section['fields'] as $field )
				_kc_update_meta( 'post', 'nav_menu_item', $menu_item_db_id, $section, $field );
	}
}

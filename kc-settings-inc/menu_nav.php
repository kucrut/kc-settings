<?php
/**
 * Nav menu metadata
 * @since 2.7.9
 */

final class kcSettings_menu_nav {
	protected static $sections = array();
	
	public static function init() {
		self::$sections = kcSettings::get_data( 'settings', 'menu_nav' );
		kcSettings::add_page( 'nav-menus.php' );

		add_action( 'admin_footer', array(__CLASS__, 'meta_box_print'), 0 );
		add_action( 'wp_update_nav_menu', array(__CLASS__, '_save'), 10, 2 );
	}


	public static function meta_box_fill( $menu_id, $args ) {
		foreach ( $args['args']['fields'] as $field ) { ?>
<div class="kc-field">
	<label for="<?php echo esc_attr( "kc-menu_navmeta-{$args['args']['id']}-{$field['id']}" ) ?>"><?php echo esc_html( $field['title'] ) ?></label>
	<div class="<?php echo esc_attr( "kc-field-wrap kc-field-{$field['type']}-wrap" ) ?>">
		<?php
			echo _kc_field( array(
				'mode'      => 'menu_nav',
				'object_id' => $menu_id,
				'section'   => $args['args']['id'],
				'field'     => $field,
			) ); // xss ok
		?>
	</div>
</div>
		<?php }
	}


	public static function meta_box_print() {
		global $nav_menu_selected_id, $hook_suffix;
		if ( $hook_suffix !== 'nav-menus.php' || !$nav_menu_selected_id )
			return;
		
		foreach ( self::$sections as $section )
			add_meta_box( "kc-nav-menus-metabox-{$section['id']}", $section['title'], array(__CLASS__, 'meta_box_fill'), 'nav-menus', 'kcmenunavmeta', 'default', $section );
		?>
<div id="kc-menu_navmeta" class="metabox-holder">
	<h2><?php _e( 'Menu metadata', 'kc-settings' ) ?></h2>
	<?php do_meta_boxes( 'nav-menus', 'kcmenunavmeta', $nav_menu_selected_id ) ?>
</div>
	<?php }


	public static function _save( $menu_id, $menu_data = '' ) {
		foreach ( self::$sections as $section )
			foreach ( $section['fields'] as $field )
				_kc_update_meta( 'term', 'nav_menu', $menu_id, $section, $field );
	}
}


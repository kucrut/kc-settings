<?php
/**
 * Menu item metadata walker
 * @since 2.7.8
 * @credit Weston Ruter https://twitter.com/westonruter
 * @link https://gist.github.com/3802459
 */

class KC_Walker_Nav_Menu_Edit extends Walker_Nav_Menu_Edit {
	function start_el( &$output, $item, $depth, $args ) {
		$item_output = '';
		parent::start_el( $item_output, $item, $depth, $args );
		
		if ( $new_fields = kcSettings_menu_item::get_fields( $item, $depth, $args ) )
			$item_output = preg_replace( '/(?=<div[^>]+class="[^"]*submitbox)/', $new_fields, $item_output );
		
		$output .= $item_output;
	}
}

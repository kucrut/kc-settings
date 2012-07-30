/**
 * For each field, we need to append it with the settings' prefix
 * and section ID, just like we would when we're getting its value
 * using get_theme_mod()
 */

(function($) {
	/**
	 * Example:
	 * Prefix: anything
	 * Section ID: colors (2nd section in the sample file)
	 * Field ID: title_color (2nd field of the 2nd section in the sample file)
	 */
	wp.customize( 'anything_colors_title_color', function( value ){
		value.bind( function( to ) {
			$( '#site-title a' ).css( 'color', to ? to : '' );
		} );
	});
})(jQuery);

<?php

/**
 * Options helpers
 *
 * @package KC_Settings
 *
 * All functions here should only be used as field callback functions
 */


/**
 * Multi featured images
 *
 */
function kcs_multi_featured_images( $args, $db_value, $cb_args ) {
	if ( !is_array($cb_args) || !$cb_args )
		return '<p class="description"><span class="impo">'.__('Please fix your callback args!', 'kc-settings').'</span></p>';

	$url = 'media-upload.php?kcmfi=true&amp;post_id='.$args['object_id'].'&amp;TB_iframe=1';
	$out = "<ul class='kc-mfi'>\n";
	foreach ( $cb_args as $idx => $item ) {
		$out .= "\t<li>\n";

		if ( isset($db_value[$idx]) ) {
			if ( $img = wp_get_attachment_image_src($db_value[$idx]) )
				$out .= "\t\t<a href='{$url}' class='add' title='{$cb_args[$idx]['add']}'><img src='{$img[0]}' alt='' /></a>\n";
			$img_id = $db_value[$idx];
		}
		else {
			$out .= "\t\t<a href='{$url}' class='add thickbox'>{$cb_args[$idx]['add']}</a>\n";
			$img_id = '';
		}

		$out .= "\t\t<a href='#' class='del'>".__('Remove', 'kc-settings')."</a>\n";
		$out .= "\t\t<input type='hidden' name='{$args['field']['name']}[{$cb_args[$idx]['id']}]' value='{$img_id}'/>\n";
		$out .= "\t</li>\n";
	}
	$out .= "</ul>\n";

	return $out;
}

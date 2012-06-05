<?php

/**
 * Debug
 *
 * Inject our own debug info into Debug Bar
 */
class kcDebug {
	var $content = '';


	function title() {
		return __( 'KC Debug' );
	}


	function prerender() {
		$this->content = apply_filters( 'kc_debug', '' );
	}


	function is_visible() {
		if ( empty($this->content) )
			return false;

		return true;
	}


	function render() {
		echo $this->content;
	}
}

?>

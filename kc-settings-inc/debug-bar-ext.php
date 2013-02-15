<?php

/**
 * Debug
 *
 * Inject our own debug info into Debug Bar
 */
class kcDebug {
	public $content = '';


	function title() {
		return sprintf( __('%s Debug', 'kc-settings'), 'KC' );
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

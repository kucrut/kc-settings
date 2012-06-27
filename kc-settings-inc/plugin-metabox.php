<?php

class kcSettings_plugin_metabox {
	var $_parent;

	function __construct( $_parent ) {
		$this->_parent = $_parent;

		add_action( "load-{$_parent->page}", array($this, 'create') );
	}


	function create() {
		wp_enqueue_script( 'post' );
		add_screen_option('layout_columns', array('max' => 4, 'default' => isset($this->_parent->group['has_sidebar']) ? 2 : 1) );
		foreach ( $this->_parent->group['options'] as $section )
			add_meta_box( "kc-metabox-{$this->_parent->page}-{$section['id']}", $section['title'], array($this, 'fill'), $this->_parent->page, $section['metabox']['context'], $section['metabox']['priority'], $section );
	}


	function fill( $object, $box ) {
		$this->_parent->settings_section( $box['args'] );
		echo "<p><input class='button-primary' name='submit' type='submit' value='".esc_attr( __('Save Changes') )."' /></p>";
	}


	function display() {
		wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false );
		wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false );

		global $screen_layout_columns;
		$hide2 = $hide3 = $hide4 = '';
		switch ( $screen_layout_columns ) {
			case 4:
				$width = 'width:25%;';
			break;
			case 3:
				$width = 'width:33.333333%;';
				$hide4 = 'display:none;';
			break;
			case 2:
				$width = 'width:50%;';
				$hide3 = $hide4 = 'display:none;';
			break;
			default:
				$width = 'width:100%;';
				$hide2 = $hide3 = $hide4 = 'display:none;';
		}

		echo "<div class='metabox-holder' id='kc-metabox-{$this->_parent->page}'>\n";
		echo "\t<div id='postbox-container-1' class='postbox-container' style='$width'>\n";
		do_meta_boxes( $this->_parent->page, 'normal', $this->_parent->group );
		do_meta_boxes( $this->_parent->page, 'advanced', $this->_parent->group );

		echo "\t</div>\n\t<div id='postbox-container-2' class='postbox-container' style='{$hide2}$width'>\n";
		do_meta_boxes( $this->_parent->page, 'side', $this->_parent->group );

		echo "\t</div>\n\t<div id='postbox-container-3' class='postbox-container' style='{$hide3}$width'>\n";
		do_meta_boxes( $this->_parent->page, 'column3', $this->_parent->group );

		echo "\t</div>\n\t<div id='postbox-container-4' class='postbox-container' style='{$hide4}$width'>\n";
		do_meta_boxes( $this->_parent->page, 'column4', $this->_parent->group );
		echo "</div>\n";
	}
}

?>

<?php

class kcSettings_plugin_metabox {
	protected $_parent;

	function __construct( $_parent ) {
		$this->_parent = $_parent;

		add_action( "load-{$_parent->page}", array($this, 'create') );
	}


	function create() {
		wp_enqueue_script( 'postbox' );
		add_screen_option( 'layout_columns', array('max' => 4, 'default' => isset($this->_parent->group['has_sidebar']) ? 2 : 1) );
		foreach ( $this->_parent->group['options'] as $section ) {
			add_meta_box(
				"kc-metabox-{$this->_parent->page}-{$section['id']}",
				$section['title'],
				array( $this, 'fill' ),
				$this->_parent->page,
				$section['metabox']['context'],
				$section['metabox']['priority'],
				$section
			);
		}
	}


	function fill( $object, $box ) {
		$this->_parent->settings_section( $box['args'] );
		echo '<p>'. get_submit_button( $box['args']['metabox']['button_text'], 'primary', 'submit', false ) .'</p>'; // xss ok
	}


	function display() {
		$class = 'metabox-holder columns-' . get_current_screen()->get_columns();

		wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
		wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
		?>
		<div id="<?php echo esc_attr( "kc-metabox-{$this->_parent->page}" ) ?>" class="<?php echo esc_attr( $class ) ?>">
			<div id="postbox-container-1" class="postbox-container">
				<?php
					do_meta_boxes( $this->_parent->page, 'normal', $this->_parent->group );
					do_meta_boxes( $this->_parent->page, 'advanced', $this->_parent->group );
				?>
			</div>
			<div id="postbox-container-2" class="postbox-container">
				<?php do_meta_boxes( $this->_parent->page, 'side', $this->_parent->group ); ?>
			</div>
			<div id="postbox-container-3" class="postbox-container">
				<?php do_meta_boxes( $this->_parent->page, 'column3', $this->_parent->group ); ?>
			</div>
			<div id="postbox-container-4" class="postbox-container">
				<?php do_meta_boxes( $this->_parent->page, 'column4', $this->_parent->group ); ?>
			</div>
		</div>
		<?php
	}
}

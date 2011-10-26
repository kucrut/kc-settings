<?php


class kcTermSettings {

	function __construct( $metadata ) {
		$this->metadata = $metadata;
		# Create & set termmeta table
		add_action( 'init', array(&$this, 'create_table'), 12 );

		# Add every term fields to its taxonomy add & edit screen
		foreach ( $this->metadata as $tax => $sections ) {
			add_action( "{$tax}_add_form_fields", array(&$this, 'fields') );
			add_action( "{$tax}_edit_form_fields", array(&$this, 'fields'), 20, 2 );
		}
		# Also add the saving routine
		add_action( 'edit_term', array(&$this, 'save'), 10, 3);
		add_action( 'create_term', array(&$this, 'save'), 10, 3);

	}

	/**
	 * Create termmeta table
	 *
	 * @credit Simple Term Meta
	 * @link http://www.cmurrayconsulting.com/software/wordpress-simple-term-meta/
	 *
	 */
	function create_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'termmeta';

		if ( $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name ) {
			$sql = "CREATE TABLE {$table_name} (
				meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				term_id bigint(20) unsigned NOT NULL DEFAULT '0',
				meta_key varchar(255) DEFAULT NULL,
				meta_value longtext,
				PRIMARY KEY (meta_id),
				KEY term_id (term_id),
				KEY meta_key (meta_key)
			);";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}

		$wpdb->termmeta = $table_name;
	}

	/**
	 * Generate term meta field HTML.
	 *
	 * $param $args This could be a taxonomy name (string) or a term (object), depending on which screen we're at.
	 * $return string $output Input field HTML
	 *
	 */
	function fields( $args ) {
		# Where are we? add/edit
		#	a. Edit screen
		if ( is_object($args) ) {
			$edit_mode = true;
			$taxonomy = $args->taxonomy;
			$term_id = $args->term_id;
			$tabled = true;
		}
		# b. Add screen
		else {
			$edit_mode = false;
			$taxonomy = $args;
			$term_id = null;
			$tabled = false;
		}

		# Set the field wrapper tag? Why the incosistencies WP? :P
		$row_tag = ( $tabled ) ? 'tr' : 'div';

		foreach ( $this->metadata as $tax => $sections ) {
			if ( $taxonomy != $tax )
				continue;

			$output = '';
			foreach ( $sections as $section ) {
				if ( !isset($section['fields']) || !is_array($section['fields']) || empty($section['fields']) )
					continue 2;

				foreach ( $section['fields'] as $field ) {
					$args = array(
						'mode' 		=> 'term',
						'section' => $section['id'],
						'field' 	=> $field,
						'tabled'	=> $tabled,
						'echo' 		=> false
					);
					if ( isset($term_id) )
						$args['object_id'] = $term_id;

					$label_for = ( !in_array($field['type'], array('checkbox', 'radio')) ) ? $field['id'] : null;

					$output .= "<{$row_tag} class='form-field'>\n";

					$the_label = "\t".kcs_form_label( $field['title'], $label_for, false, false  )."\n";
					# Wrap the field with <tr> if we're in edit mode
					if ( $edit_mode )
						$the_label = "\t<th scope='row'>\n{$the_label}\t</th>\n";
					$output .= $the_label;

					$the_field = "\t\t".kc_settings_field( $args )."\n";
					# Wrap the field with <tr> if we're in edit mode
					if ( $edit_mode )
						$the_field = "\t<td>\n{$the_field}\t</td>\n";
					$output .= $the_field;

					$output .= "</{$row_tag}>";
				}
			}

			echo $output;
		}
	}


	/**
	 * Save term meta value
	 *
	 * @param int $term_id Term ID
	 * @param int $tt_id Term Taxonomy ID
	 * @param string $taxonomy Taxonomy name
	 *
	 */
	function save( $term_id, $tt_id, $taxonomy ) {
		if ( isset($_POST['action']) && $_POST['action'] == 'inline-save-tax' )
			return $term_id;

		foreach ( $this->metadata as $tax => $sections ) {
			if ( $taxonomy != $tax )
				continue;

			foreach ( $sections as $section ) {
				foreach ( $section['fields'] as $field )
					kc_update_meta( 'term', $tax, $term_id, $section, $field );
			}
		}
	}
}

?>

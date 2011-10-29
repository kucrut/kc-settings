<?php


class kcSettings_term {

	public static function init() {
		# Create and/or set termmeta table
		add_action( 'init', array(__CLASS__, '_create_table'), 12 );

		foreach ( array_keys(kcSettings::$data['settings']['term']) as $tax ) {
			add_action( "{$tax}_add_form_fields", array(__CLASS__, '_fields'), 20, 1 );
			add_action( "{$tax}_edit_form_fields", array(__CLASS__, '_fields'), 20, 2 );
		}

		add_action( 'edit_term', array(__CLASS__, '_save'), 10, 3);
		add_action( 'create_term', array(__CLASS__, '_save'), 10, 3);
	}


	/**
	 * Create termmeta table
	 *
	 * @credit Simple Term Meta
	 * @link http://www.cmurrayconsulting.com/software/wordpress-simple-term-meta/
	 *
	 */
	public static function _create_table() {
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
	public static function _fields( $args ) {
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

		if ( !isset(kcSettings::$data['settings']['term'][$taxonomy]) )
			return $args;

		# Set the field wrapper tag? Why the incosistencies WP? :P
		$row_tag = ( $tabled ) ? 'tr' : 'div';
		$output = '';

		foreach ( kcSettings::$data['settings']['term'][$taxonomy] as $section ) {

			$section_head = "\t\t\t\t<h4>{$section['title']}</h4>\n";
			if ( isset($section['desc']) && $section['desc'] )
				$section_head .= "\t\t\t\t{$section['desc']}\n";
			if ( $tabled )
				$section_head = "<tr class='form-field'>\n\t\t\t<th colspan='2'>\n{$section_head}\t\t\t</th>\n\t\t</tr>\n";
			$output .= $section_head;

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

				$label_for = ( !in_array($field['type'], array('checkbox', 'radio', 'multiinput')) ) ? $field['id'] : null;

				$output .= "\t\t<{$row_tag} class='form-field'>\n";

				$the_label = kcs_form_label( $field['title'], $label_for, false, false  );
				# Wrap the field with <tr> if we're in edit mode
				if ( $edit_mode )
					$the_label = "\t\t\t<th scope='row'>{$the_label}</th>\n";
				$output .= $the_label;

				$the_field = "\t\t\t\t".kcs_settings_field( $args )."\n";
				# Wrap the field with <tr> if we're in edit mode
				if ( $edit_mode )
					$the_field = "\t\t\t<td>\n{$the_field}\t\t\t</td>\n";
				$output .= $the_field;

				$output .= "\t\t</{$row_tag}>\n";
			}

		}
		echo $output;
	}


	/**
	 * Save term meta value
	 *
	 * @param int $term_id Term ID
	 * @param int $tt_id Term Taxonomy ID
	 * @param string $taxonomy Taxonomy name
	 *
	 */
	public static function _save( $term_id, $tt_id, $taxonomy ) {
		if ( !isset(kcSettings::$data['settings']['term'][$taxonomy])
					|| ( isset($_POST['action']) && $_POST['action'] == 'inline-save-tax' ) )
			return $term_id;

		foreach ( kcSettings::$data['settings']['term'][$taxonomy] as $section ) {
			foreach ( $section['fields'] as $field )
				kcs_update_meta( 'term', $tax, $term_id, $section, $field );
		}
	}
}

?>

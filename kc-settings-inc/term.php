<?php


class kcSettings_term {
	protected static $settings;

	public static function init() {
		self::$settings = kcSettings::get_data( 'settings', 'term' );
		kcSettings::add_page( 'edit-tags.php' );

		foreach ( array_keys( self::$settings ) as $tax ) {
			add_action( "{$tax}_add_form_fields", array(__CLASS__, '_fields'), 20, 1 );
			add_action( "{$tax}_edit_form_fields", array(__CLASS__, '_fields'), 20, 2 );
		}

		add_action( 'edit_term',   array(__CLASS__, '_save'),   10, 3 );
		add_action( 'create_term', array(__CLASS__, '_save'),   10, 3 );
		add_action( 'delete_term', array(__CLASS__, '_delete'), 10, 3 );
	}


	/**
	 * Generate term meta field HTML.
	 *
	 * @param string|object $args This could be a taxonomy name (string) or a term (object), depending on which screen we're at.
	 *
	 */
	public static function _fields( $args ) {
		# Where are we? add/edit
		#	a. Edit screen
		if ( is_object( $args ) ) {
			$edit_mode = true;
			$taxonomy  = $args->taxonomy;
			$term_id   = $args->term_id;
			$tabled    = true;
		}
		# b. Add screen
		else {
			$edit_mode = false;
			$taxonomy  = $args;
			$term_id   = null;
			$tabled    = false;
		}

		if ( !isset(self::$settings[ $taxonomy ]) )
			return $args;

		# New term: table. Edit term: div
		$row_tag = ( $tabled ) ? 'tr' : 'div';
		$output = '';

		foreach ( self::$settings[ $taxonomy ] as $section ) {
			$section_head = "\t\t\t\t<h4>{$section['title']}</h4>\n";
			if ( isset($section['desc']) && $section['desc'] )
				$section_head .= "\t\t\t\t".wpautop( $section['desc'] )."\n"; // xss ok
			if ( $tabled )
				$section_head = "<tr class='form-field'>\n\t\t\t<th colspan='2'>\n{$section_head}\t\t\t</th>\n\t\t</tr>\n";
			$output .= $section_head;

			foreach ( $section['fields'] as $field ) {
				$args = array(
					'mode'    => 'term',
					'section' => $section['id'],
					'field'   => $field,
					'tabled'  => $tabled,
					'echo'    => false,
				);
				if ( isset($term_id) )
					$args['object_id'] = $term_id;

				if ( !in_array( $field['type'], array( 'checkbox', 'radio', 'multiinput', 'file' ) ) ) {
					$label_for = $field['id'];
					if ( $field['type'] === 'editor' )
						$label_for = strtolower( str_replace( array( '-', '_' ), '', $label_for ) );
				}
				else {
					$label_for = '';
				}

				$output .= "\t\t<{$row_tag} class='form-field kcs-field'>\n";

				$the_label = _kc_field_label( $field['title'], $label_for, false, false  );
				# Wrap the field with <tr> if we're in edit mode
				if ( $edit_mode )
					$the_label = "\t\t\t<th scope='row'>{$the_label}</th>\n";
				$output .= $the_label;

				$the_field = "\t\t\t\t"._kc_field( $args )."\n";
				# Wrap the field with <tr> if we're in edit mode
				if ( $edit_mode )
					$the_field = "\t\t\t<td>\n{$the_field}\t\t\t</td>\n";
				$output .= $the_field;

				$output .= "\t\t</{$row_tag}>\n";
			}
		}

		echo $output; // xss ok
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
		if ( !isset(self::$settings[ $taxonomy ])
			   || ( isset($_POST['action']) && $_POST['action'] == 'inline-save-tax' ) )
			return $term_id;

		foreach ( self::$settings[ $taxonomy ] as $section )
			foreach ( $section['fields'] as $field )
				_kc_update_meta( 'term', $taxonomy, $term_id, $section, $field );
	}


	/**
	 * Delete term meta upon term deletion
	 *
	 * @param int $term_id Term ID
	 * @param int $tt_id Term Taxonomy ID
	 * @param string $taxonomy Taxonomy name
	 *
	 * @since 2.7.7
	 */
	public static function _delete( $term_id, $tt_id, $taxonomy ) {
		if ( !isset(self::$settings[ $taxonomy ]) )
			return;

		foreach ( self::$settings[ $taxonomy ] as $section )
			foreach ( $section['fields'] as $field )
				delete_metadata( 'term', $term_id, $field['id'] );
	}
}

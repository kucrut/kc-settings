<?php

class kcSettings_user {
	protected static $settings;

	public static function init() {
		self::$settings = kcSettings::get_data( 'settings', 'user' );
		kcSettings::add_page( 'profile.php' );
		kcSettings::add_page( 'user-edit.php' );

		# Display additional fields in user profile page
		add_action( 'show_user_profile', array(__CLASS__, '_fields') );
		add_action( 'edit_user_profile', array(__CLASS__, '_fields') );

		# Save the additional data
		add_action( 'personal_options_update', array(__CLASS__, '_save') );
		add_action( 'edit_user_profile_update', array(__CLASS__, '_save') );
	}


	/**
	 * Display additional fields on profile edit page
	 *
	 * @credit Justin Tadlock
	 * @link http://justintadlock.com/archives/2009/09/10/adding-and-using-custom-user-profile-fields
	 *
	 * @param stdClass $user User object
	 * @return void
	 */
	public static function _fields( $user ) {
		foreach ( self::$settings as $group_id => $group ) : ?>
			<?php foreach ( $group as $section ) : ?>
				<?php
					/**
					* Do something before printing the section
					*
					* @param array $section
					*/
					do_action( 'kc_settings_before_user_meta_section', $section );
				?>
				<?php printf(
					'<h3 id="%s" data-target="%s" class="kcs-section-title">%s</h3>',
					esc_attr( "kcs-section-title-{$section['id']}" ),
					esc_attr( "#kcs-section-{$section['id']}" ),
					esc_html( $section['title'] )
				); ?>
				<div id="kcs-section-<?php echo esc_attr( $section['id'] ) ?>" class="kcs-section">
					<?php if ( ! empty( $section['desc'] ) ) : ?>
						<?php echo $section['desc']; // xss ok ?>
					<?php endif; ?>
					<table class="form-table">
						<tbody>
							<?php foreach ( $section['fields'] as $field ) : ?>
								<?php
									if ( ! in_array( $field['type'], array( 'checkbox', 'radio', 'multiinput', 'file' ) ) ) {
										$label_for = $field['id'];
										if ( $field['type'] === 'editor' ) {
											$label_for = strtolower(
												str_replace(
													array( '-', '_' ),
													'',
													$label_for
												)
											);
										}
									}
									else {
										$label_for = '';
									}

									$row_class = sprintf( 'kcs-field-%s-%s', $section['id'], $field['id'] );
									$args      = array(
										'mode'      => 'user',
										'object_id' => $user->ID,
										'section'   => $section['id'],
										'field'     => $field
									);
								?>
								<tr class="<?php echo esc_attr( $row_class ) ?>">
									<th><?php _kc_field_label( $field['title'], $label_for, false ); ?></th>
									<td>
										<?php echo _kc_field( $args ); // xss ok ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<?php
					/**
					* Do something before printing the section
					*
					* @param array $section
					*/
					do_action( 'kc_settings_after_user_meta_section', $section );
				?>
			<?php endforeach; ?>
		<?php endforeach;
	}


	/**
	 * Save additional user metadata
	 *
	 * @credit Justin Tadlock
	 * @links http://justintadlock.com/archives/2009/09/10/adding-and-using-custom-user-profile-fields
	 *
	 * @param int $user_id User ID
	 * @return null
	 */
	public static function _save( $user_id ) {
		if ( !current_user_can( 'edit_user', $user_id ) )
			return $user_id;

		foreach ( self::$settings as $group ) {
			foreach ( $group as $section )
				foreach ( $section['fields'] as $field )
					_kc_update_meta( 'user', null, $user_id, $section, $field );
		}
	}

}

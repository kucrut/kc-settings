<?php

/**
 * Theme customizer module
 *
 * @package KC_Settings
 * @since 2.7.4
 * @link http://ottopress.com/2012/how-to-leverage-the-theme-customizer-in-your-own-themes/
 * @link http://ottopress.com/2012/theme-customizer-part-deux-getting-rid-of-options-pages/
 */

class kcSettings_theme {
	protected static $settings;
	protected static $defaults;
	protected static $controls = array(
		'color'    => 'WP_Customize_Color_Control',
		'image'    => 'WP_Customize_Image_Control',
		'email'    => 'WP_Customize_KC_Common_Control',
		'number'   => 'WP_Customize_KC_Common_Control',
		'password' => 'WP_Customize_KC_Common_Control',
		'tel'      => 'WP_Customize_KC_Common_Control',
		'textarea' => 'WP_Customize_KC_Common_Control'
	);
	protected static $count = 999;

	public static function init() {
		self::$settings = kcSettings::get_data( 'settings', 'theme' );
		self::$defaults = kcSettings::get_data( 'defaults', 'theme' );

		# Add menu under Appearance
		add_action( 'admin_menu', array(__CLASS__, 'create_menu') );
		add_action( 'customize_register', array(__CLASS__, 'register') );
	}


	public static function create_menu() {
		add_theme_page( __('Customizer', 'kc-settings'), __('Customizer', 'kc-settings'), 'edit_theme_options', 'customize.php' );
	}


	public static function register( $wp_customize ) {
		foreach ( self::$settings as $group ) {
			extract( $group, EXTR_OVERWRITE );
			foreach( $group['options'] as $section ) {
				# Add the section
				$section_id = "{$prefix}_{$section['id']}";
				$section_args = array(
					'title'    => $section['title'],
					'priority' => self::$count++,
				);
				if ( isset($section['desc']) && !empty($section['desc']) )
					$section_args['description'] = strip_tags( $section['desc'] );
				$wp_customize->add_section( $section_id, $section_args);

				# Add fields
				foreach ( $section['fields'] as $field ) {
					$field_id = "{$section_id}_{$field['id']}";
					$setting_args = array(
						'type'       => 'theme_mod',
						'capability' => 'edit_theme_options',
					);
					if ( isset(self::$defaults[$prefix][$section['id']][$field['id']]) )
						$setting_args['default'] = self::$defaults[$prefix][$section['id']][$field['id']];

					$wp_customize->add_setting( $field_id, $setting_args );

					# 0. WP Custom controls
					if ( isset(self::$controls[$field['type']]) ) {
						$_class = self::$controls[$field['type']];
						$wp_customize->add_control( new $_class( $wp_customize, $field_id, array(
							'label'   => $field['title'],
							'section' => $section_id,
							'type'    => $field['type']
						) ) );
					}
					# 1. Default controls
					else {
						$control_args = array(
							'label'   => $field['title'],
							'section' => $section_id,
							'type'    => $field['type'],
						);
						if ( $field['type'] == 'radio' || $field['type'] == 'select' ) {
							if ( is_callable($field['options']) )
								$control_args['choices'] = call_user_func_array( $field['options'], isset($field['args']) ? (array) $field['args'] : array() );
							else
								$control_args['choices'] = $field['options'];
						}

						$wp_customize->add_control( $field_id, $control_args );
					}
				}
			}
		}
	}
}


require_once ABSPATH . WPINC . '/class-wp-customize-control.php';
class WP_Customize_KC_Common_Control extends WP_Customize_Control {
	public $type = 'email';

	public function render_content() {
		switch ( $this->type ) {
			case 'textarea':
				?>
				<label>
					<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
					<textarea cols="30" rows="5" style="width:97%;min-height:8em;resize:vertical"<?php $this->link(); ?>><?php echo esc_textarea( $this->value() ); ?></textarea>
				</label>
				<?php
			break;
			default :
				?>
				<label>
					<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
					<input type="text" value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); ?> />
				</label>
				<?php
			break;
		}
	}
}

?>

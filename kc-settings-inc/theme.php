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
	protected static $controls = array(
		'color'    => 'WP_Customize_Color_Control',
		'image'    => 'WP_Customize_Image_Control',
		'email'    => 'WP_Customize_KC_Common_Control',
		'number'   => 'WP_Customize_KC_Common_Control',
		'password' => 'WP_Customize_KC_Common_Control',
		'tel'      => 'WP_Customize_KC_Common_Control',
		'textarea' => 'WP_Customize_KC_Common_Control',
	);
	protected static $wp_sections = array(
		'title_tagline',
		'colors',
		'background_image',
		'static_front_page',
	);
	protected static $count = 999;
	protected static $script_files = array();
	protected static $scripts = array();

	public static function init() {
		if ( class_exists( 'kcSettings' ) )
			$settings = kcSettings::get_data( 'settings', 'theme' );
		else
			$settings = apply_filters( 'kc_theme_settings', array() );

		if ( empty($settings) )
			return false;

		self::$settings = $settings;

		# Add menu under Appearance
		add_action( 'admin_menu', array(__CLASS__, 'create_menu') );
		add_action( 'customize_register', array(__CLASS__, 'register') );
	}


	public static function create_menu() {
		add_theme_page( __( 'Customize' ), __( 'Customize' ), 'edit_theme_options', 'customize.php' );
	}


	public static function register( $wp_customize ) {
		foreach ( self::$settings as $group ) {
			extract( $group, EXTR_OVERWRITE );
			if ( isset($script) && !empty($script) ) {
				$has_script_file = true;
				self::$script_files[ $prefix ] = $script;
			}
			else {
				$has_script_file = false;
			}
			foreach ( $group['options'] as $section ) {
				$field_prefix = "{$prefix}_{$section['id']}";
				# Add the section
				# 0. Inject to WP's section
				if ( in_array( $section['id'], self::$wp_sections ) ) {
					$section_id = $section['id'];
				}
				# 1. Create new section
				else {
					$section_id = $field_prefix;
					$section_args = array(
						'title'    => $section['title'],
						'priority' => self::$count++,
					);
					if ( isset($section['desc']) && !empty($section['desc']) )
						$section_args['description'] = strip_tags( $section['desc'] );
					$wp_customize->add_section( $section_id, $section_args );
				}

				# Add fields
				foreach ( $section['fields'] as $field ) {
					$field_id = "{$field_prefix}_{$field['id']}";
					$field_args = array(
						'type'       => 'theme_mod',
						'capability' => 'edit_theme_options',
					);
					if ( isset($field['default']) )
						$field_args['default'] = $field['default'];

					if ( $has_script_file )
						$field_args['transport'] = 'postMessage';

					if ( isset($field['script']) && !empty($field['script']) ) {
						self::$scripts[$field_id] = $field['script'];
						$field_args['transport'] = 'postMessage';
					}

					$wp_customize->add_setting( $field_id, $field_args );

					# 0. WP Custom controls
					if ( isset(self::$controls[$field['type']]) ) {
						$_class = self::$controls[$field['type']];
						$wp_customize->add_control( new $_class( $wp_customize, $field_id, array(
							'label'   => $field['title'],
							'section' => $section_id,
							'type'    => $field['type'],
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
							if ( is_callable( $field['options'] ) )
								$control_args['choices'] = call_user_func_array( $field['options'], isset($field['args']) ? (array) $field['args'] : array() );
							else
								$control_args['choices'] = $field['options'];
						}

						$wp_customize->add_control( $field_id, $control_args );
					}
				}
			}
		}

		if ( $wp_customize->is_preview() && !is_admin() ) {
			if ( !empty(self::$script_files) )
				add_action( 'wp_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'), 999 );
			if ( !empty(self::$scripts) )
				add_action( 'wp_footer', array(__CLASS__, 'print_scripts'), 999 );
		}
	}


	public static function enqueue_scripts() {
		foreach ( self::$script_files as $prefix => $url )
			wp_enqueue_script( "kctc-{$prefix}", $url, array( 'customize-preview' ), 'latest', true );
	}


	public static function print_scripts() {
		$out = '';
		foreach ( self::$scripts as $field_id => $script ) {
			$out .= "
wp.customize( '{$field_id}', function( value ) {
	value.bind( function( to ) {
		{$script}
	} );
} );
";
		} ?>
<script>
	(function($) {
		<?php echo $out // xss ok ?>
	})(jQuery);
</script>
	<?php }
}
kcSettings_theme::init();


if ( class_exists( 'WP_Customize_Control' ) ) {
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
}

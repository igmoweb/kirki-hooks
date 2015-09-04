<?php

add_action( 'customize_register', 'kirki_hooks_customize_register' );
function kirki_hooks_customize_register( $wp_customize ) {

	class Kirki_Customize_Repeater_Button_Setting extends WP_Customize_Setting {

	}

	class Kirki_Customize_Repeater_Control extends WP_Customize_Control {
		public $type = 'kirki-repeater';

		public function __construct( $manager, $id, $args = array() ) {
			parent::__construct( $manager, $id, $args );
		}

		public function render_content() {
			foreach ( $this->settings as $setting_key => $setting ) {
				var_dump( $setting->id);
				var_dump( $setting->type);
				?>
				<label>
					<?php if ( ! empty( $this->label ) ) : ?>
						<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
					<?php endif;
					if ( ! empty( $this->description ) ) : ?>
						<span class="description customize-control-description"><?php echo $this->description; ?></span>
					<?php endif; ?>
					<input type="text" <?php $this->input_attrs(); ?> value="<?php echo esc_attr( $this->value( $setting_key ) ); ?>" <?php echo $this->get_link( $setting_key ); ?> />
				</label>
				<?php
			}


		}

	}
		// All this will be passed to a new Kirki class/function
	$repeater_id = 'my-repeater';
	$section_id = 'repeater_test';

	$theme_mod = false;

	$structure = array(
		'subsetting_1' => array(
			'type' => 'text',
			'default' => 'Yeah'
		),
		'subsetting_2' => array(
			'type' => 'text'
		)
	);

	$wp_customize->add_section( $section_id, array(
		'title'          => __( 'Repeater Test', 'themename' ),
		'priority'       => 35,
		'transport'     => 'refresh'
	) );

	if ( ! is_array( $theme_mod ) || empty( $theme_mod ) )
		$theme_mod = array();

	$repeater_settings = array();


	$button_setting_id = $repeater_id . '-button';
	$repeater_settings[] = $button_setting_id ;
	$wp_customize->add_setting( new Kirki_Customize_Repeater_Button_Setting( $wp_customize, $button_setting_id, array(
		'default' => 'Add new Row',
		'type' => 'repeater-button'
	) ) );


	$wp_customize->add_control( new Kirki_Customize_Repeater_Control( $wp_customize, $repeater_id, array(
		'section' => $section_id,
		'settings' => $repeater_settings
	) ) );
}
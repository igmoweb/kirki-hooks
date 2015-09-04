<?php

add_action( 'customize_register', 'kirki_hooks_customize_register' );
function kirki_hooks_customize_register( $wp_customize ) {

	class Kirki_Customize_Repeater_Button_Setting extends WP_Customize_Setting {

	}

	class Kirki_Customize_Repeater_Control extends WP_Customize_Control {
		public $type = 'kirki-repeater';
		public $fields = array();
		public $button_label = "";

		public function __construct( $manager, $id, $args = array() ) {
			parent::__construct( $manager, $id, $args );
		}

		public function to_json() {
			parent::to_json();

			$this->json['id'] = $this->id;
			$this->json['fields'] = $this->fields;
			$this->json['buttonSetting'] = $this->button_setting_id;
			$this->json['buttonLabel'] = $this->button_label;
		}

		public function set_button_setting_id( $id ) {
			$this->button_setting_id = $id;
		}

		public function enqueue() {
			wp_enqueue_script( 'kirki-repeater-customize-controls', plugin_dir_url( __FILE__ ) . 'kirki-hooks.js', array( 'jquery', 'customize-base' ), '123', true );
		}


		public function render_content() {
			foreach ( $this->settings as $setting_key => $setting ) {

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

		public function content_template() {
			?>
				<#
					console.log(data);
					if ( ! data )
						return;

					var api = wp.customize;
					for ( var i = 0; i < data.settings.length; i++ ) {
						var setting_key = data.settings[i];
						var setting = api.settings.settings[ setting_key ];

						if ( setting_key === data.buttonSetting ) {
							// Render the button
							#>
							<input type="text" value="{{{ setting.value }}}" data-customize-setting-link="{{{ setting_key }}}" />
							<button class="repeater-add-new">{{ data.buttonLabel }}</button>
							<#
						}
						else {
							// Render the field

						}
						console.log(setting_key);
						console.log(wp.customize.settings);

					}

				#>

			<?php
		}

	}
		// All this will be passed to a new Kirki class/function
	$repeater_id = 'my-repeater';
	$section_id = 'repeater_test';
	$button_label = "Add new Row";

	$theme_mod = false;

	$structure = array(
		'subsetting_1' => array(
			'type' => 'text',
			'label' => 'Setting A',
			'default' => 'Yeah'
		),
		'subsetting_2' => array(
			'type' => 'text',
			'label' => 'Setting B'
		)
	);







	foreach ( $structure as $key => $value ) {
		$structure[ $key ]['id'] = $key;
	}

	$wp_customize->register_control_type( 'Kirki_Customize_Repeater_Control' );

	$wp_customize->add_section( $section_id, array(
		'title'          => __( 'Repeater Test', 'themename' ),
		'priority'       => 35,
		'transport'     => 'refresh'
	) );

	if ( ! is_array( $theme_mod ) || empty( $theme_mod ) )
		$theme_mod = array();

	$total_rows = count( $theme_mod );
	$repeater_settings = array();


	$button_setting_id = $repeater_id . '-button';
	$repeater_settings[] = $button_setting_id ;
	$wp_customize->add_setting( new Kirki_Customize_Repeater_Button_Setting( $wp_customize, $button_setting_id, array(
		'default' => $total_rows // We pass the number of current rows to the button setting. It's the one that will control the number of rows
	) ) );

	$control = new Kirki_Customize_Repeater_Control( $wp_customize, $repeater_id, array(
		'section' => $section_id,
		'settings' => $repeater_settings,
		'fields' => $structure,
		'button_label' => $button_label
	) );
	$control->set_button_setting_id( $button_setting_id );
	$wp_customize->add_control( $control );
}
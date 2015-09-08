<?php

add_action( 'customize_register', 'kirki_hooks_customize_register', 500 );
function kirki_hooks_customize_register( $wp_customize ) {

	class Kirki_Customize_Repeater_Setting extends WP_Customize_Setting {

		public function __construct( $manager, $id, $args = array() ) {
			parent::__construct( $manager, $id, $args );

			// Will onvert the setting from JSON to array. Must be triggered very soon
			add_filter( "customize_sanitize_{$this->id}", array( $this, 'sanitize_repeater_setting' ), 10, 1 );
		}

		public function value() {
			$value = parent::value();
			if ( ! is_array( $value ) )
				$value = array();

			return $value;
		}

		/**
		 * Convert the JSON encoded setting coming from Customizer to an Array
		 *
		 * @param $value URL Encoded JSON Value
		 *
		 * @return array
		 */
		public function sanitize_repeater_setting( $value ) {
			$value = json_decode( urldecode( $value ) );

			if ( empty( $value ) || ! is_array( $value ) )
				$sanitized = array();
			else
				$sanitized = $value;

			// Make sure that every row is an array, not an object
			foreach ( $sanitized as $key => $_value ) {
				if ( empty( $_value ) ) {
					unset( $sanitized[ $key ] );
				}
				else {
					$sanitized[ $key ] = (array)$_value;
				}

			}

			// Reindex array
			$sanitized = array_values( $sanitized );

			return $sanitized;
		}


	}

	class Kirki_Customize_Repeater_Control extends WP_Customize_Control {
		public $type = 'repeater';
		public $fields = array();
		public $button_label = "";

		public function __construct( $manager, $id, $args = array() ) {
			parent::__construct( $manager, $id, $args );

			if ( empty( $this->button_label ) )
				$this->button_label = 'Add new row';
		}

		public function to_json() {
			parent::to_json();

			$this->json['fields'] = $this->fields;
			$this->json['value'] = $this->value();
		}



		public function enqueue() {
			wp_enqueue_script( 'kirki-repeater-customize-controls', plugin_dir_url( __FILE__ ) . 'kirki-hooks.js', array( 'jquery', 'customize-base' ), '123', true );
			wp_enqueue_style( 'kirki-repeater-customize-controls', plugin_dir_url( __FILE__ ) . 'kirki-hooks.css' );
		}


		public function render_content() {
			$value = json_encode( $this->value() );
			$id = $this->id;
			?>
			<label>
				<?php if ( ! empty( $this->label ) ) : ?>
					<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php endif;
				if ( ! empty( $this->description ) ) : ?>
					<span class="description customize-control-description"><?php echo $this->description; ?></span>
				<?php endif; ?>
				<input type="hidden" <?php $this->input_attrs(); ?> value="" <?php echo $this->get_link(); ?> />
			</label>

			<div class="repeater-fields"></div>

			<button class="button-secondary repeater-add"><?php echo esc_html( $this->button_label ); ?></button>
			<?php

			$this->repeater_js_template();

		}

		public function repeater_js_template() {
			?>
			<script type="text/html" class="customize-control-repeater-content">
				<#

					var field;
					var index = data['index'];

					#>
					<div class="repeater-row" data-row="{{{ index }}}">
						<div class="repeater-row-header">
							<span class="repeater-row-number"></span>
							<span class="repeater-row-close" data-row="{{{ index }}}"><i class="dashicons dashicons-no-alt repeater-remove"></i></span>
						</div>
					<#

					for ( i in data ) {
						if ( ! data.hasOwnProperty( i ) )
							continue;

						field = data[i];

						if ( ! field.type )
							continue;

						#><div class="repeater-field repeater-field-{{{ field.type }}}"><#
						if ( field.type === 'text' ) {
							#>
								<label>
									<# if ( field.label ) { #>
										<span class="customize-control-title">{{ field.label }}</span>
									<# } #>
									<# if ( field.description ) { #>
										<span class="description customize-control-description">{{ field.description }}</span>
									<# } #>
									<input type="text" name="" value="{{{ field.default }}}" data-field="{{{ field.id }}}" data-row="{{{ index }}}">
								</label>
							<#
						}
						else if ( field.type === 'checkbox' ) {
							#>
							<label>
								<input type="checkbox" value="true" data-field="{{{ field.id }}}" data-row="{{{ index }}}" <# if ( field.default ) { #> checked="checked" <# } #> />
								<# if ( field.description ) { #>
									{{ field.description }}
								<# } #>
							</label>
							<#
						}
						else if ( field.type === 'select' ) {
							#>
							<label>
								<# if ( field.label ) { #>
									<span class="customize-control-title">{{ field.label }}</span>
								<# } #>
								<# if ( field.description ) { #>
									<span class="description customize-control-description">{{ field.description }}</span>
								<# } #>
								<select data-field="{{{ field.id }}}" data-row="{{{ index }}}">
									<# for ( i in field.choices ) { #>
										<# if ( field.choices.hasOwnProperty( i ) ) { #>
											<option value="{{{ i }}}" <# if ( field.default == i ) { #> selected="selected" <# } #>>{{ field.choices[i] }}</option>
										<# } #>
									<# } #>
								</select>

							</label>
							<#
						}
						else if ( field.type === 'radio' ) {
							#>
							<label>
								<# if ( field.label ) { #>
									<span class="customize-control-title">{{ field.label }}</span>
								<# } #>
								<# if ( field.description ) { #>
									<span class="description customize-control-description">{{ field.description }}</span>
								<# } #>

								<# for ( i in field.choices ) { #>
									<# if ( field.choices.hasOwnProperty( i ) ) { #>
										<label>
											<input type="radio" data-field="{{{ field.id }}}" data-row="{{{ index }}}" name="{{{ data.controlId }}}-{{{ field.id }}}-{{{ index }}}" value="{{{ i }}}" <# if ( field.default == i ) { #> checked="checked" <# } #>> {{ field.choices[i] }} <br/>
										</label>
									<# } #>
								<# } #>

							</label>
							<#
						}
						else if ( field.type == 'textarea' ) {
							#>
								<# if ( field.label ) { #>
									<span class="customize-control-title">{{ field.label }}</span>
								<# } #>
								<# if ( field.description ) { #>
									<span class="description customize-control-description">{{ field.description }}</span>
								<# } #>
								<textarea rows="5" data-field="{{{ field.id }}}" data-row="{{{ index }}}">{{ field.default }}</textarea>
							<#
						}

						#></div><#
					}

					#>
					</div>
					<#
				#>

			</script>
			<?php
		}


	}

	// All this will be passed to a new Kirki class/function
	$repeater_id = 'my-repeater';
	$section_id = 'repeater_test';

	$fields = array(
		'subsetting_1' => array(
			'type' => 'text',
			'label' => 'Setting A',
			'description' => 'lalala',
			'default' => 'Yeah'
		),
		'subsetting_2' => array(
			'type' => 'text',
			'label' => 'Setting B',
			'description' => 'lalala',
			'default' => ''
		),
		'subsetting_3' => array(
			'type' => 'checkbox',
			'description' => 'A checkbox',
			'default' => true
		),
		'subsetting_4' => array(
			'label' => 'A selector',
			'type' => 'select',
			'description' => 'lalala',
			'default' => '',
			'choices' => array(
				'' => 'None',
				'choice_1' => 'Choice 1',
				'choice_2' => 'Choice 2'
			)
		),
		'subsetting_5' => array(
			'type' => 'textarea',
			'label' => 'A textarea',
			'description' => 'lalalala',
			'default' => ''
		),
		'subsetting_6' => array(
			'label' => 'A radio',
			'type' => 'radio',
			'description' => 'yipiyai',
			'default' => 'choice-1',
			'choices' => array(
				'choice-1' => 'First choice',
				'choice-2' => 'Second choice'
			)
		),
	);
	// End of data. Everything will be done by Kirki from now on

	$wp_customize->add_section( $section_id, array(
		'title'          => __( 'Repeater Test', 'themename' ),
		'priority'       => 35,
		'transport'     => 'refresh'
	) );

	$args = array(
		'type' => 'theme_mod',
		'capability' => 'manage_options',
		'theme_supports' => '',
		'transport' => 'refresh',
		'sanitize_callback' => '',
		'sanitize_js_callback' => '',
		'section' => $section_id,
		'fields' => $fields,
		'button_label' => 'Add new Row',
		'label' => '',
		'description' => '',
		'priority' => ''
	);

	kirki_repeater_black_box( $repeater_id, $args );

}


function kirki_repeater_black_box( $repeater_id, $args = array() ) {
	global $wp_customize;

	$defaults = array(
		'type' => 'theme_mod',
		'capability' => 'manage_options',
		'theme_supports' => '',
		'transport' => 'refresh',
		'sanitize_callback' => '',
		'sanitize_js_callback' => '',
		'section' => '',
		'fields' => array(),
		'button_label' => __( 'Add new Row', 'kirki' ),
		'label' => '',
		'description' => '',
		'priority' => ''
	);

	$args = wp_parse_args( $args, $defaults );

	foreach ( $args['fields'] as $key => $value ) {
		if ( ! isset( $value['default'] ) )
			$args['fields'][ $key ]['default'] = '';

		if ( ! isset( $value['label'] ) )
			$args['fields'][ $key ]['label'] = '';
		$args['fields'][ $key ]['id'] = $key;
	}


	$wp_customize->add_setting( new Kirki_Customize_Repeater_Setting( $wp_customize, $repeater_id, array(
		'type' => $args['type'],
		'capability' => $args['capability'],
		'theme_supports' => $args['theme_supports'],
		'transport' => $args['transport'],
		'sanitize_callback' => $args['sanitize_callback'],
		'sanitize_js_callback' => $args['sanitize_js_callback']
	) ) );

	$control = new Kirki_Customize_Repeater_Control( $wp_customize, $repeater_id, array(
		'section' => $args['section'],
		'fields' => $args['fields'],
		'button_label' => $args['button_label'],
		'label' => $args['label'],
		'description' => $args['description'],
		'priority' => $args['priority']
	) );

	$wp_customize->add_control( $control );
}
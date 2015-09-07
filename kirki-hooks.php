<?php

add_action( 'customize_register', 'kirki_hooks_customize_register', 500 );
function kirki_hooks_customize_register( $wp_customize ) {

	class Kirki_Customize_Repeater_Setting extends WP_Customize_Setting {
		public function __construct( $manager, $id, $args = array() ) {
			parent::__construct( $manager, $id, $args );
		}

		public function value() {
			$value = parent::value();
			if ( ! is_array( $value ) )
				$value = array();

			return $value;
		}

		public function _preview_filter( $original ) {
			if ( ! $this->is_current_blog_previewed() ) {
				return $original;
			}

			$undefined = new stdClass(); // symbol hack
			$post_value = json_decode( urldecode( $this->post_value( $undefined ) ) );
			if ( $undefined === $post_value ) {
				$value = $this->_original_value;
			} else {
				$value = $post_value;
			}

			$return = $this->multidimensional_replace( $original, $this->id_data['keys'], $value );
			return $return;
		}

		protected function update( $value ) {
			$value = json_decode( urldecode( $value ) );

			if ( empty( $value ) || ! is_array( $value ) )
				$value = array();

			// Customizer will send up an array of objects,
			// we are going to cast those to arrays
			foreach ( $value as $row => $object ) {
				$value[ $row ] = (array)$object;
			}

			switch( $this->type ) {
				case 'theme_mod' :
					return $this->_update_theme_mod( $value );

				case 'option' :
					return $this->_update_option( $value );

				default :

					/**
					 * Fires when the {@see WP_Customize_Setting::update()} method is called for settings
					 * not handled as theme_mods or options.
					 *
					 * The dynamic portion of the hook name, `$this->type`, refers to the type of setting.
					 *
					 * @since 3.4.0
					 *
					 * @param mixed                $value Value of the setting.
					 * @param WP_Customize_Setting $this  WP_Customize_Setting instance.
					 */
					return do_action( 'customize_update_' . $this->type, $value, $this );
			}
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
						<i class="dashicons dashicons-no-alt repeater-remove" data-row="{{{ index }}}"></i>
					<#

					for ( i in data ) {
						if ( ! data.hasOwnProperty( i ) )
							continue;

						field = data[i];

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
								console.log(field.default);
							#>
							<label>
								<# if ( field.label ) { #>
									<span class="customize-control-title">{{ field.label }}</span>
								<# } #>
								<# if ( field.description ) { #>
									<span class="description customize-control-description">{{ field.description }}</span>
								<# } #>

								<# if ( field.default ) { #>
									<input type="checkbox" name="" data-field="{{{ field.id }}}" data-row="{{{ index }}}" checked="checked" />
								<# } else { #>
									<input type="checkbox" name="" data-field="{{{ field.id }}}" data-row="{{{ index }}}" />
								<# } #>

							</label>
							<#
						}
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
	$button_label = "Add new Row";

	$structure = array(
		'subsetting_1' => array(
			'type' => 'text',
			'label' => 'Setting A',
			'default' => 'Yeah'
		),
		'subsetting_2' => array(
			'type' => 'text',
			'label' => 'Setting B',
			'default' => ''
		)
	);
	// End of data. Everything will be done by Kirki from now on




	foreach ( $structure as $key => $value ) {
		if ( ! isset( $value['default'] ) )
			$structure[ $key ]['default'] = '';

		if ( ! isset( $value['label'] ) )
			$structure[ $key ]['default'] = '';
		$structure[ $key ]['id'] = $key;
	}


	$wp_customize->add_section( $section_id, array(
		'title'          => __( 'Repeater Test', 'themename' ),
		'priority'       => 35,
		'transport'     => 'refresh'
	) );


	$wp_customize->add_setting( new Kirki_Customize_Repeater_Setting( $wp_customize, $repeater_id, array(
		'type' => 'theme_mod',
		'capability' => 'manage_options',
		'theme_supports' => '',
		'transport' => 'refresh',
		'sanitize_callback' => '',
		'sanitize_js_callback' => ''
	) ) );

	$control = new Kirki_Customize_Repeater_Control( $wp_customize, $repeater_id, array(
		'section' => $section_id,
		'fields' => $structure,
		'button_label' => $button_label,
		'label' => '',
		'description' => '',
		'priority' => ''
	) );

	$wp_customize->add_control( $control );



}
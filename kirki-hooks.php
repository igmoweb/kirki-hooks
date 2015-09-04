<?php

add_action( 'customize_register', 'kirki_hooks_customize_register' );

function kirki_hooks_customize_register( $wp_customize ) {
	class Kirki_Customize_Control_Repeater_Button_Setting extends WP_Customize_Setting  {

	}

	class Kirki_Customize_Control_Repeater_Setting extends WP_Customize_Setting {

		/**
		 * In Repeater, one setting is one row full of settings
		 * fields save all the fields and their types
		 *
		 * @var array
		 */
		public $fields = array();


		public function get_fields() {
			return $this->fields;
		}
	}

	class Kirki_Customize_Control_Repeater_Control extends WP_Customize_Control {
		public $type = 'kirki-repeater';
		public $fields_properties = array();

		public function __construct( $manager, $id, $args = array() ) {
			$keys = array_keys( get_object_vars( $this ) );
			foreach ( $keys as $key ) {
				if ( isset( $args[ $key ] ) ) {
					$this->$key = $args[ $key ];
				}
			}

			$this->manager = $manager;
			$this->id = $id;
			if ( empty( $this->active_callback ) ) {
				$this->active_callback = array( $this, 'active_callback' );
			}
			self::$instance_count += 1;
			$this->instance_number = self::$instance_count;

			// Process settings.
			if ( empty( $this->settings ) ) {
				$this->settings = array();
			}

			$settings = array();

			foreach ( $this->settings as $key => $setting ) {
				$settings[ $key ] = $this->manager->get_setting( $setting );
			}

			$settings['button'] = $this->manager->get_setting( $args['button_setting'] );
			$this->settings = $settings;
		}


		public function enqueue() {
			wp_enqueue_script( 'kirki-repeater-customize-controls', plugin_dir_url( __FILE__ ) . 'kirki-hooks.js', array( 'jquery', 'customize-base' ), '123', true );
		}

		public function get_rows( $setting_key = 'default' ) {
			if ( ! isset( $this->settings[ $setting_key ] ) )
				return array();

			return $this->settings[ $setting_key ]->get_fields();
		}

		public function get_link( $setting_key = 'default' ) {
			$fields = $this->get_rows( $setting_key );
			$field_id = $fields[ $setting_key ]['id'];
			return 'data-customize-setting-link="' . esc_attr( $this->settings[ $setting_key ]->id . "[$subsetting_id]" ) . '"';
		}


		public function get_links( $setting_key = 'default' ) {
			$fields = $this->get_rows( $setting_key );
			$links = array();
			foreach ( $fields as $field ) {
				$links[ $field['id'] ] = 'data-customize-setting-link="' . esc_attr( $this->settings[ $setting_key ]->id ) . '[' .  $field['id'] . ']" data-repeater-row="' . $setting_key . '" data-repeater-field="' . $field['id'] . '"';
			}

			return $links;
		}


		public function to_json() {

			// Call parent to_json() method to get the core defaults like "label", "description", etc.
			parent::to_json();

			$settings_keys = wp_list_pluck( $this->settings, 'id' );
			$this->json['value'] = array();
			$this->json['controlID'] = $this->id;
			$this->json['fieldsProperties'] = $this->fields_properties;
			$this->json['currentIndex'] = count( $this->settings ) - 2;

			foreach ( $this->settings as $key => $setting ) {
				if ( $key === 'button' || $key === 'default' )
					continue;

				//$this->json['links'][ $key ] = $this->get_links( $key );
				$this->json['value'][ $key ] = $this->value( $key );
				$this->json['rows'][ $key ] = $this->get_rows( $key );
			}

			$this->json['fieldKeys'] = $this->field_ids;


		}

		public function render_content() {}


		public function content_template() {
			?>
			<#

				if ( ! data.rows ) {
					return;
				}

				var rowFields, fields;

				for ( var i = 0; i < data.rows.length; i++ ) {
					rowFields = data.rows[ i ];
					#>
					<div id="{{{ data.controlID }}}-{{{ i }}}" class="repeater-row">
					<#

					for ( var j = 0; j < rowFields.length; j++ ) {
						field = rowFields[j];
						value = data.value[i][field.id];

						#>

						<label>

							<# if ( data.label ) { #>
								<span class="customize-control-title">{{ data.label }}</span>
							<# } #>

							<# if ( data.description ) { #>
								<span class="description customize-control-description">{{{ data.description }}}</span>
							<# } #>

							<input type="text" data-customize-setting-link="{{{ data.controlID }}}[{{{ i }}}][{{{ field.id }}}]" data-repeater-row="{{{ i }}}" data-repeater-field="{{{ field.id }}}" value="{{{ value }}}" />

						</label>

						<#
					}

					#>
					<hr/>
					</div>
					<#
				}


			#>

			<button id="{{{ data.controlID }}}-add-row" class="button">Add new row</button>
			<?php

		}


	}


	// All this will be passed to a new Kirki class/function
	$repeater_id = 'my-repeater';



	// should be get as $theme_mod = $all_mods['repeater_setting_test']
	$theme_mod = array(
		array( 'subsetting_1' => 'SETTING 0 SUB 1', 'subsetting_2' =>'SETTING 0 SUB 2' ),
		array( 'subsetting_1' => 'SETTING 1 SUB 1', 'subsetting_2' =>'SETTING 1 SUB 2' )
	);


	$fields = array(
		array(
			'id' => 'subsetting_1',
			'default' => 'Yeah'
		),
		array(
			'id' => 'subsetting_2'
		)
	);

	// End of data

	// Register the control type.
	$wp_customize->register_control_type( 'Kirki_Customize_Control_Repeater_Control' );

	$wp_customize->add_section( 'themename_test', array(
		'title'          => __( 'Test', 'themename' ),
		'priority'       => 35,
	) );



	$settings = array();

	foreach ( $theme_mod as $key => $mod ) {
		$setting_id = $repeater_id . '[' . $key . ']';
		$wp_customize->add_setting( new Kirki_Customize_Control_Repeater_Setting( $wp_customize, $setting_id, array(
			'fields' => $fields
		) ) );

		$settings[] = $setting_id;
	}

	$button_setting_id = $repeater_id . '-button';
	$wp_customize->add_setting( new Kirki_Customize_Control_Repeater_Button_Setting( $wp_customize, $button_setting_id, array(
			'label' => 'Add new Row'
	) ) );



	$wp_customize->add_control( new Kirki_Customize_Control_Repeater_Control( $wp_customize, $repeater_id, array(
		'section' => 'themename_test', // Required, core or custom.
		'label' => __( 'Setting Test' ),
		'settings' => $settings,
		'button_setting' => $button_setting_id,
		'fields_properties' => $fields
	) ) );
}
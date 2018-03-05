<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Custom
 * @since 1.0
 */
class Forminator_Custom extends Forminator_Field {

	/**
	* @var string
	*/
	public $name = '';

	/**
	* @var string
	*/
	public $slug = 'custom';

	/**
	 * @var string
	 */
	public $type = 'custom';

	/**
	 * @var array
	 */
	public $options = array();

	/**
	* @var string
	*/
	//public $category = 'posts';
	//Disable for now until we know what to do with this

	/**
	 * Forminator_Custom constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'Custom field', Forminator::DOMAIN );
	}

	/**
	* @param array $settings
	*
	* @return array
	*/
	public function load_settings( $settings = array() ) {
		return array(
			array(
				'id' => 'required',
				'type' => 'Toggle',
				'name' => 'required',
				'className' => 'required-field',
				'hide_label' => true,
				'values' => array(
					array(
						'value' => "true",
						'label' => __( 'Required', Forminator::DOMAIN ),
						'labelSmall' => "true"
					)
				)
			),

			array(
				'id' => 'separator-1',
				'type' => 'Separator',
				'hide_label' => true,
			),

			array(
				'id' => 'field-label',
				'type' => 'Text',
				'name' => 'field_label',
				'hide_label' => false,
				'label'	=> __( 'Field label', Forminator::DOMAIN ),
				'className' => 'text-field',
			),

			array(
				'id' => 'field-type',
				'type' => 'Select',
				'name' => 'field_type',
				'className' => 'select-field',
				'label_hidden' => false,
				'label' => __( 'Field type', Forminator::DOMAIN ),
				'values' => array(
					array(
						'value' => "text",
						'label' => __( 'Single line text', Forminator::DOMAIN )
					),
					array(
						'value' => "textarea",
						'label' => __( 'Multi line text', Forminator::DOMAIN )
					),
					array(
						'value' => "dropdown",
						'label' => __( 'Dropdown', Forminator::DOMAIN )
					),
					array(
						'value' => "multiselect",
						'label' => __( 'Multi select', Forminator::DOMAIN )
					),
					array(
						'value' => "number",
						'label' => __( 'Number', Forminator::DOMAIN )
					),
					array(
						'value' => "checkbox",
						'label' => __( 'Checkboxes', Forminator::DOMAIN )
					),
					array(
						'value' => "radio",
						'label' => __( 'Radio buttons', Forminator::DOMAIN )
					),
					array(
						'value' => "hidden",
						'label' => __( 'Hidden', Forminator::DOMAIN )
					)
				)
			),

			array(
				'id' => 'custom-field-name',
				'type' => 'RadioContainer',
				'name' => 'custom_field_name',
				'className' => 'custom-field-name-field',
				'containerClass' => 'wpmudev-is_gray',
				'label' => __( "Custom field name", Forminator::DOMAIN ),
				'values' => array(
					array(
						'value' => "existing",
						'label' => __( 'Existing field', Forminator::DOMAIN )
					),
					array(
						'value' => "new",
						'label' => __( 'New field', Forminator::DOMAIN )
					)
				),
				'fields' => array(
					array(
						'id' => 'existing-field',
						'type' => 'Select',
						'name' => 'existing_field',
						'className' => 'existing-field',
						'label' => __( 'Pick existing field', Forminator::DOMAIN ),
						'tab' => 'existing',
						'values' => forminator_to_field_array( forminator_get_existing_cfields() )
					),
				),
			),
		);
	}

	/**
	 * Field defaults
	 *
	 * @since 1.0
	 * @return array
	 */
	public function defaults() {
		return array(
			'value_type'  => 'select',
			'field_label' => '',
		);
	}

	/**
	 * Field admin markup
	 *
	 * @since 1.0
	 * @return string
	 */
	public function admin_html() {
		return '<div class="wpmudev-form-field--group">
			{[ if( field.field_label ) { ]}
			<label class="wpmudev-group--label">{{ encodeHtmlEntity( field.field_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
			{[ } ]}
			{[ if( field.field_type === "text" ) { ]}
			<input type="text" class="wpmudev-form-field--text" {{ field.required ? "required" : "" }}>
			{[ } ]}
			{[ if( field.field_type === "textarea" ) { ]}
			<textarea class="wpmudev-form-field--textarea" placeholder="{{ encodeHtmlEntity( field.placeholder ) }}" {{ field.required ? "required" : "" }}></textarea>
			{[ } ]}
			{[ if( field.field_type === "dropdown" ) { ]}
			<select class="wpmudev-form-field--select" style="width: 100%;" {{ field.required ? "required" : "" }}></select>
			{[ } ]}
			</div>';
	}

	/**
	 * Field front-end markup
	 *
	 * @since 1.0
	 * @param $field
	 *
	 * @return mixed
	 */
	public function markup( $field ) {
		$required    	= self::get_property( 'required', $field, false );
		$id = $name  	= self::get_property( 'element_id', $field );
		$field_type  	= self::get_property( 'field_type', $field );
		$placeholder 	= self::get_property( 'placeholder', $field );
		$description 	= self::get_property( 'description', $field );
		$label 			= self::get_property( 'field_label', $field );
		$id          	= $id . '-field';
		$html        	= '';
		$default_value 	= self::get_property( 'default_value', $field );

		switch ( $field_type ) {
			case "text":
				$html .= sprintf( '<input class="forminator-name--field forminator-input" type="text" data-required="%s" name="%s" placeholder="%s" id="%s" />', $required, $name, $placeholder, $id );
			break;
			case "textarea":
				$field_markup 	= array(
					'type' 			=> 'textarea',
					'class' 		=> 'forminator-textarea',
					'name' 			=> $name,
					'id' 			=> $id,
					'placeholder' 	=> $placeholder,
					'required'		=> $required
				);
				$html .= self::create_textarea( $field_markup, $label, $description );
			break;
			case "dropdown":

			break;
			case "multiselect":

			break;
			case "number":
				$html .= sprintf( '<input class="forminator-number--field forminator-input" type="number" data-required="%s" name="%s" placeholder="%s" value="%s" id="%s" />', $required, $name, $placeholder, $default_value, $id );
			break;
			case "checkbox":

			break;
			case "radio":

			break;
			case "hidden":
				$html .= sprintf( '<input class="forminator-hidden--field" type="hidden" id="%s" name="%s" value="%s" />', $id, $name, $default_value );
			break;
		}
		return apply_filters( 'forminator_field_custom_markup', $html, $id, $required, $field_type, $placeholder );
	}

	/**
	 * Field back-end validation
	 *
	 * @since 1.0
	 * @param array $field
	 * @param array|string $data
	 */
	public function validate( $field, $data ) {
		if ( $this->is_required( $field ) ) {
			$id 	= self::get_property( 'element_id', $field );
			$name 	= self::get_property( 'custom_field_name', $field, __( 'field name', Forminator::DOMAIN ) );
			if ( empty( $data ) ) {
				$this->validation_message[ $id ] = sprintf( __( 'This field is required. Please enter the %s', Forminator::DOMAIN ), $name );
			}
		}
	}


	/**
	 * Sanitize data
	 *
	 * @since 1.0.2
	 *
	 * @param array $field
	 * @param array|string $data - the data to be sanitized
	 *
	 * @return array|string $data - the data after sanitization
	 */
	public function sanitize( $field, $data ) {
		return $data;
	}
}

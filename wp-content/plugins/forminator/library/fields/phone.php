<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Phone
 *
 * @since 1.0
 */
class Forminator_Phone extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'phone';

	/**
	 * @var int
	 */
	public $position = 3;

	/**
	 * @var string
	 */
	public $type = 'phone';

	/**
	 * @var array
	 */
	public $options = array();

	/**
	 * @var string
	 */
	public $category = 'standard';

	/**
	 * @var bool
	 */
	public $is_input = true;

	/**
	 * @var bool
	 */
	public $has_counter = true;

	/**
	 * Forminator_Phone constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'Phone', Forminator::DOMAIN );
	}

	/**
	 * @since 1.0
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
				'name' => 'separator',
				'hide_label' => true,
				'size' => 12,
				'className' => 'separator-field',
			),

			array(
				'id' => 'field-label',
				'type' => 'Text',
				'name' => 'field_label',
				'hide_label' => false,
				'label'	=> __( 'Field label', Forminator::DOMAIN ),
				'size' => 12,
				'className' => 'text-field',
			),

			array(
				'id' => 'placeholder',
				'type' => 'Text',
				'name' => 'placeholder',
				'hide_label' => false,
				'label'	=> __( 'Placeholder', Forminator::DOMAIN ),
				'size' => 12,
				'className' => 'text-field',
			),

			array(
				'id' => 'field-description',
				'type' => 'Text',
				'name' => 'description',
				'hide_label' => false,
				'label'	=> __( 'Description', Forminator::DOMAIN ),
				'className' => 'text-field',
			),

			array(
				'id' => 'phone-limit',
				'type' => 'ToggleContainer',
				'name' => 'phone_limit',
				'hide_label' => true,
				'has_content' => true,
				'containerClass' => 'wpmudev-has_cols',
				'values' => array(
					array(
						'value' => "true",
						'label' => __( 'Use character limit', Forminator::DOMAIN ),
						'labelSmall' => "true"
					)
				),
				'fields' => array(
					array(
						'id' => 'field-limit',
						'type' => 'Number',
						'name' => 'limit',
						'className' => 'select-field',
						'label' => __( 'Limit field to:', Forminator::DOMAIN )
					),
					array(
						'id' => 'field-limit-type',
						'type' => 'Select',
						'name' => 'limit_type',
						'size' => 12,
						'className' => 'number-field',
						'label_hidden' => true,
						'values' => array(
							array(
								'value' => "characters",
								'label' => __( 'Characters', Forminator::DOMAIN )
							)
						)
					)
				)
			),

			array(
				'id' 				=> 'phone-validation',
				'type' 				=> 'ToggleContainer',
				'name' 				=> 'phone_validation',
				'hide_label' 		=> true,
				'has_content' 		=> true,
				'containerClass' 	=> 'wpmudev-has_cols',
				'values' 			=> array(
					array(
						'value' 		=> "true",
						'label' 		=> __( 'Enable Validation', Forminator::DOMAIN ),
						'labelSmall' 	=> "true"
					)
				),
				'fields' => array(
					array(
						'id' 			=> 'field-phone_validation-type',
						'type' 			=> 'Select',
						'name' 			=> 'phone_validation_type',
						'className' 	=> 'number-field',
						'label_hidden' 	=> true,
						'values' 		=> apply_filters( 'forminator_phone_validation_type', array(
							array(
								'value' => "standard",
								'label' => sprintf( __( 'Standard %s', Forminator::DOMAIN ), '(###) ###-####' )
							),
							array(
								'value' => "international",
								'label' => __( 'International', Forminator::DOMAIN )
							)
						) )
					)
				)
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
			'required'              => false,
			'limit'                 => 10,
			'limit_type'            => 'characters',
			'phone_validation_type' => "standard",
			'field_label'           => __( 'Phone', Forminator::DOMAIN ),
			'placeholder'           => __( 'E.g. +1 300 400 5000', Forminator::DOMAIN ),
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
			{[ if( field.field_label !== "" ) { ]}
				<label class="wpmudev-group--label">{{ encodeHtmlEntity( field.field_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
			{[ } ]}
			<input type="{{ field.type }}" class="wpmudev-input" placeholder="{{ encodeHtmlEntity( field.placeholder ) }}" {{ field.required ? "required" : "" }}>
			{[ if( field.description ) { ]}
			<div class="wpmudev-group--info">
				<span class="wpmudev-info--text">{{ encodeHtmlEntity( field.description ) }}</span>
			</div>
			{[ } ]}
		</div>';
	}

	/**
	 * Phonoe formats
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_phone_formats() {
		$phone_formats = array(
			'standard'      => array(
				'label'       => '(###) ###-####',
				'mask'        => '(999) 999-9999',
				/**
				 * match jquery-validation phoneUS validation
				 * https://github.com/jquery-validation/jquery-validation/blob/1.17.0/src/additional/phoneUS.js#L20
				 */
				'regex'       => '/^(\+?1-?)?(\([2-9]([02-9]\d|1[02-9])\)|[2-9]([02-9]\d|1[02-9]))-?[2-9]([02-9]\d|1[02-9])-?\d{4}$/',
				'instruction' => '(###) ###-####',
			),
			'international' => array(
				'label'       => __( 'International', Forminator::DOMAIN ),
				'mask'        => false,
				'regex'       => false,
				'instruction' => false,
			),
		);

		return apply_filters( 'forminator_phone_formats', $phone_formats );
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
		$this->field    = $field;
		$id = $name  	= self::get_property( 'element_id', $field );
		$id          	= $id . '-field';
		$required 		= self::get_property( 'required', $field, false );
		$placeholder 	= $this->sanitize_value( self::get_property( 'placeholder', $field ) );
		$value 			= self::get_property( 'value', $field );
		$format_check 	= self::get_property( 'phone_validation', $field, false );
		$phone_format 	= self::get_property( 'phone_validation_type', $field );

		if ( $format_check && !empty( $placeholder ) ) {
			$formats = $this->get_phone_formats();
			if ( isset( $formats[$phone_format] ) ) {
				$validation_type = $formats[$phone_format];
				if ( $validation_type['instruction'] ) {
					$placeholder .= ' ' . $validation_type['instruction'];
				}
			}
		}

		$html = sprintf( '<input class="forminator-phone--field forminator-input" type="text" data-required="%s" name="%s" placeholder="%s" value="%s" id="%s"/>', $required, $name, $placeholder, $value, $id );

		return apply_filters( 'forminator_field_phone_markup', $html, $id, $required, $placeholder, $value );
	}

	/**
	 * Return field inline validation rules
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_validation_rules() {
		$field        = $this->field;
		$format_check = self::get_property( 'phone_validation', $field, false );
		$phone_format = self::get_property( 'phone_validation_type', $field );
		$rules        = '"' . $this->get_id( $field ) . '": {';

		if ( $this->is_required( $field ) ) {
			$rules .= '"required": true,';
		}

		//standard means phoneUS
		if ( $format_check && $phone_format === 'standard' ) {
			$rules .= '"phoneUS": true';
		}
		//TODO: International Phone

		$rules .= '},';

		return $rules;
	}

	/**
	 * Return field inline validation errors
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_validation_messages() {
		$field          = $this->field;
		$format_check 	= self::get_property( 'phone_validation', $field, false );
		$phone_format 	= self::get_property( 'phone_validation_type', $field );
		$messages = '"' . $this->get_id( $field)  .'": {' . "\n";

		if( $this->is_required( $field ) ) {
			$messages .= 'required: "' . __( 'This field is required. Please input a phone number', Forminator::DOMAIN ) . '",' . "\n";
			$messages .= 'phoneUS: "' . __( 'Please input a valid phone number', Forminator::DOMAIN ) . '"';
		}

		$messages .= '},' . "\n";

		return $messages;
	}

	/**
	 * Field back-end validation
	 *
	 * @since 1.0
	 * @param array $field
	 * @param array|string $data
	 *
	 * @return bool
	 */
	public function validate( $field, $data ) {
		$id = self::get_property( 'element_id', $field );

		if ( $this->is_required( $field ) ) {
			if ( empty( $data ) ) {
				$this->validation_message[ $id ] = __( 'This field is required. Please input a phone number', Forminator::DOMAIN );

				return false;
			}
		}

		//if data is empty, no need to `$format_check`
		if ( empty( $data ) ) {
			return true;
		}

		//enable phone validation if `phone_validation` property enabled and data not empty, even the field is not required
		$format_check = self::get_property( 'phone_validation', $field, false );
		$phone_format = self::get_property( 'phone_validation_type', $field );
		if ( $format_check ) {
			$formats = $this->get_phone_formats();
			if ( isset( $formats[ $phone_format ] ) ) {
				$validation_type = $formats[ $phone_format ];
				if ( $validation_type['regex'] && ! preg_match( $validation_type['regex'], $data ) ) {
					$this->validation_message[ $id ] = sprintf( __( 'Invalid phone number. Please make sure the format is %s', Forminator::DOMAIN ), $validation_type['instruction'] );

					return false;
				}
			}
		}

		return true;
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
		// Sanitize
		$data = forminator_sanitize_field( $data );

		return apply_filters( 'forminator_field_phone_sanitize', $data, $field );
	}
}
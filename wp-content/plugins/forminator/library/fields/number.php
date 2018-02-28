<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Number
 *
 * @since 1.0
 */
class Forminator_Number extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'number';

	/**
	 * @var string
	 */
	public $type = 'number';

	/**
	 * @var int
	 */
	public $position = 8;

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
	 * Forminator_Number constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'Number', Forminator::DOMAIN );
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
				'className' => 'text-field',
			),

			array(
				'id' => 'field-placeholder',
				'type' => 'Text',
				'name' => 'placeholder',
				'hide_label' => false,
				'label'	=> __( 'Placeholder', Forminator::DOMAIN ),
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
				'id' => 'number-limit',
				'type' => 'ToggleContainer',
				'name' => 'number_limit',
				'hide_label' => true,
				'has_content' => true,
				'containerClass' => 'wpmudev-has_cols',
				'values' => array(
					array(
						'value' => "true",
						'label' => __( 'Limit input', Forminator::DOMAIN ),
						'labelSmall' => "true"
					)
				),
				'fields' => array(
					array(
						'id' => 'field-limit-min',
						'type' => 'Number',
						'name' => 'limit_min',
						'className' => 'number-field',
						'max' => '#number-limit input#field-limit-max',
						'label' => __( 'Min', Forminator::DOMAIN )
					),

					array(
						'id' => 'field-limit-max',
						'type' => 'Number',
						'name' => 'limit_max',
						'className' => 'number-field',
						'min' => '#number-limit input#field-limit-min',
						'label' => __( 'Max', Forminator::DOMAIN )
					),

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
			'limit_min'   => 0,
			'limit_max'   => 150,
			'field_label' => __( 'Number', Forminator::DOMAIN ),
			'placeholder' => __( 'E.g. 10', Forminator::DOMAIN ),
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
			<input type="{{ field.type }}" class="wpmudev-input" {[ if( field.number_limit ) { ]} min="{{ field.limit_min }}" max="{{ field.limit_max }}" {[ } ]} placeholder="{{ encodeHtmlEntity( field.placeholder ) }}" {{ field.required ? "required" : "" }}>
			{[ if( field.description ) { ]}
			<div class="wpmudev-group--info">
				<span class="wpmudev-info--text">{{ encodeHtmlEntity( field.description ) }}</span>
			</div>
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
		$this->field    = $field;
		$min            = 0;
		$max            = '';
		$id = $name  	= self::get_property( 'element_id', $field );
		$id          	= $id . '-field';
		$required 		= self::get_property( 'required', $field, false );
		$placeholder 	= self::get_property( 'placeholder', $field );
		$value 			= self::get_property( 'value', $field );
		$limit 	        = self::get_property( 'number_limit', $field, false );

		if( $limit ) {
			$min = self::get_property( 'limit_min', $field, '' );
			$max = self::get_property( 'limit_max', $field, '' );
		}

		$html = !empty( $max ) || $max === '0' ? sprintf( '<input class="forminator-number--field forminator-input" type="number" data-required="%s" name="%s" placeholder="%s" value="%s" id="%s" min="%s" max="%s"/>', $required, $name, $placeholder, $value, $id, $min, $max ) : sprintf( '<input class="forminator-number--field forminator-input" type="number" data-required="%s" name="%s" placeholder="%s" value="%s" id="%s" min="%s" />', $required, $name, $placeholder, $value, $id, $min );

		return apply_filters( 'forminator_field_number_markup', $html, $id, $required, $placeholder, $value );
	}

	/**
	 * Return field inline validation rules
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_validation_rules() {
		$field = $this->field;

		$min 	= self::get_property( 'limit_min', $field, false );
		$max 	= self::get_property( 'limit_max', $field, false );
		$limit 	= self::get_property( 'number_limit', $field, false );

		$rules = '"' . $this->get_id( $field ) . '": {';

		if( $this->is_required( $field ) ) {
			$rules .= '"required": true,';
		}

		$rules .= '"digits": true,';

		if( $limit ) {
			if ( $min ) {
				$rules .= '"min": ' . $min . ',';
			}
			if ( $max ) {
				$rules .= '"max": ' . $max . ',';
			}
		}

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
		$field = $this->field;

		$messages = '"' . $this->get_id( $field)  .'": {' . "\n";

		if( $this->is_required( $field ) ) {
			$messages .= 'required: "' . __( 'This field is required. Please enter digits', Forminator::DOMAIN ) . '",' . "\n";
		}

		$messages .= 'digits: "' . __( 'This is not valid number', Forminator::DOMAIN ) . '",' . "\n";
		$messages .= '},' . "\n";

		return $messages;
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
			$max 	= self::get_property( 'number_limit', $field, $data );
			$min 	= self::get_property( 'limit_min', $field, $data );

			if ( empty( $data ) ) {
				$this->validation_message[ $id ] = __( 'This field is required. Please input a number', Forminator::DOMAIN );
			} else {
				if ( !is_numeric( $data ) ) {
					$this->validation_message[ $id ] = __( 'Only numbers allowed', Forminator::DOMAIN );
				} else {
					$data 	= intval( $data );
					$min 	= intval( $min );
					$max 	= intval( $max );
					if ( ( $data < $min ) || ( $data > $max ) ) {
						$this->validation_message[ $id ] = sprintf( __( 'The number shoud be less than %d and greater than %d', Forminator::DOMAIN ), $max, $min );
					}
				}
			}
		}
	}
}
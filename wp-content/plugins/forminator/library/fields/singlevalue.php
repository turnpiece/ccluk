<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_SingleValue
 *
 * @property  array field
 * @since 1.0
 */
class Forminator_SingleValue extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'select';

	/**
	 * @var string
	 */
	public $type = 'select';

	/**
	 * @var int
	 */
	public $position = 9;

	/**
	 * @var array
	 */
	public $options = array();

	/**
	 * @var string
	 */
	public $category = 'standard';

	/**
	 * Forminator_SingleValue constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'Single Choice', Forminator::DOMAIN );
	}

	/**
	 * @since 1.0
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function load_settings( $settings = array() ) {
		return array(
			array(
				'id'         => 'required',
				'type'       => 'Toggle',
				'name'       => 'required',
				'className'  => 'required-field',
				'hide_label' => true,
				'values'     => array(
					array(
						'value'      => "true",
						'label'      => __( 'Required', Forminator::DOMAIN ),
					),
				),
			),

			array(
				'id'         => 'field-label',
				'type'       => 'Text',
				'name'       => 'field_label',
				'hide_label' => false,
				'label'      => __( 'Field label', Forminator::DOMAIN ),
				'className'  => 'text-field',
			),

			array(
				'id'         => 'description',
				'type'       => 'Text',
				'name'       => 'description',
				'hide_label' => false,
				'label'      => __( 'Description', Forminator::DOMAIN ),
				'className'  => 'text-field',
			),

			array(
				'id'            => 'value-type',
				'type'          => 'Radio',
				'name'          => 'value_type',
				'label'         => __( "Choice input type", Forminator::DOMAIN ),
				'className'     => 'radio-field',
				'default_value' => "select",
				'values'        => array(
					array(
						'value' => "select",
						'label' => __( 'Select dropdown', Forminator::DOMAIN ),
					),
					array(
						'value' => "radio",
						'label' => __( 'Radio buttons', Forminator::DOMAIN ),
					),
				),
			),

			array(
				'id'        => 'options',
				'type'      => 'MultiValue',
				'name'      => 'options',
				'label'     => __( "Choices", Forminator::DOMAIN ),
				'className' => 'multivalue-field',
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
			'field_label' => __( 'Single Choice', Forminator::DOMAIN ),
			'options'     => array(
				array(
					'label' => __( 'Option 1', Forminator::DOMAIN ),
					'value' => '',
				),
				array(
					'label' => __( 'Option 2', Forminator::DOMAIN ),
					'value' => '',
				),
			),
		);
	}

	/**
	 * Autofill Setting
	 *
	 * @since 1.0.5
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function autofill_settings( $settings = array() ) {
		$providers = apply_filters( 'forminator_field_' . $this->slug . '_autofill', array(), $this->slug );

		$autofill_settings = array(
			'select' => array(
				'values' => forminator_build_autofill_providers( $providers ),
			),
		);

		return $autofill_settings;
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
				<label class="sui-label">{{ encodeHtmlEntity( field.field_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
			{[ } ]}
			{[ if( field.value_type === "select" ) { ]}
				<select {{ field.required ? "required" : "" }}>
					{[ _.each( field.options, function( value, key ){ ]}
					<option>{{ encodeHtmlEntity( value.label.replace(/&quot;/g, "\\"") ) }}</option>
					{[ }) ]}
				</select>
			{[ } ]}
			{[ if( field.value_type === "radio" ) { ]}
				{[ _.each( field.options, function( value, key ){ ]}
				<label for="sample-option-{{ encodeHtmlEntity( field.value ) }}" class="sui-radio">
					<input type="radio" id="sample-option-{{ encodeHtmlEntity( field.value ) }}" {{ field.required ? "required" : "" }}>
					<span aria-hidden="true"></span>
					<span class="sui-description">{{ encodeHtmlEntity( value.label.replace(/&quot;/g, "\\"") ) }}</span>
				</label>
				{[ }) ]}
			{[ } ]}
			{[ if( field.description ) { ]}
			<div class="wpmudev-group--info">
				<span class="sui-description">{{ encodeHtmlEntity( field.description ) }}</span>
			</div>
			{[ } ]}
		</div>';
	}

	/**
	 * Field front-end markup
	 *
	 * @since 1.0
	 *
	 * @param $field
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function markup( $field, $settings = array() ) {
		$this->field = $field;
		$i           = 1;
		$html        = '';
		$id          = self::get_property( 'element_id', $field );
		$name        = $id;
		$id          = $id . '-field';
		$required    = self::get_property( 'required', $field, false );
		$options     = self::get_property( 'options', $field, array() );
		$value_type  = trim( $field['value_type'] ? $field['value_type'] : "multiselect" );
		$post_value  = self::get_post_data( $name, false );

		if ( "select" === $value_type ) {

			$html = sprintf( '<select class="forminator-select--field forminator-select" id="%s" data-required="%s" name="%s">', $id, $required, $name );

			foreach ( $options as $option ) {
				$value    = $option['value'] ? $option['value'] : $option['label'];
				$selected = $value === $post_value ? 'selected="selected"' : '';
				$html     .= sprintf( '<option value="%s" %s>%s</option>', $value, $selected, $option['label'] );
			}

			$html .= sprintf( '</select>' );

		} else {

			$uniq_id = uniqid();

			foreach ( $options as $option ) {

				$input_id = $id . '-' . $i . '-' . $uniq_id;
				$value    = $option['value'] ? $option['value'] : $option['label'];
				$selected = $value === $post_value ? 'checked="checked"' : '';

				if ( trim( $this->get_form_style( $settings ) ) === 'clean' ) {

					$html .= sprintf( '<label class="forminator-radio"><input id="%s" name="%s" type="radio" value="%s" %s> %s</label>', $input_id, $name, $value, $selected, $option['label'] );

				} else {

					$html .= '<div class="forminator-radio">';
					$html .= sprintf( '<input id="%s" name="%s" type="radio" value="%s" class="forminator-radio--input" %s>', $input_id, $name, $value, $selected );
					$html .= sprintf( '<label for="%s" class="forminator-radio--design" aria-hidden="true"></label>', $input_id );
					$html .= sprintf( '<label for="%s" class="forminator-radio--label">%s</label>', $input_id, $option['label'] );
					$html .= '</div>';

				}

				$i ++;

			}

		}

		return apply_filters( 'forminator_field_single_markup', $html, $id, $required, $options, $value_type );
	}

	/**
	 * Return field inline validation rules
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_validation_rules() {
		$rules       = '';
		$field       = $this->field;
		$is_required = $this->is_required( $field );

		if ( $is_required ) {
			$rules .= '"' . $this->get_id( $field ) . '": "required",';
		}

		return $rules;
	}

	/**
	 * Return field inline validation errors
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_validation_messages() {
		$messages    = '';
		$field       = $this->field;
		$id          = self::get_property( 'element_id', $field );
		$is_required = $this->is_required( $field );

		if ( $is_required ) {
			if ( $is_required ) {
				$required_message = apply_filters(
					'forminator_single_field_required_validation_message',
					__( 'This field is required. Please select a value', Forminator::DOMAIN ),
					$id,
					$field
				);
				$messages .= '"' . $this->get_id( $field ) . '": "' . $required_message . '",' . "\n";
			}
		}

		return $messages;
	}

	/**
	 * Field back-end validation
	 *
	 * @since 1.0
	 *
	 * @param array        $field
	 * @param array|string $data
	 */
	public function validate( $field, $data ) {
		if ( $this->is_required( $field ) ) {
			$id    = self::get_property( 'element_id', $field );
			if ( empty( $data ) ) {
				$this->validation_message[ $id ] = apply_filters(
					'forminator_single_field_required_validation_message',
					__( 'This field is required. Please select a value', Forminator::DOMAIN ),
					$id,
					$field
				);
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
		// Sanitize
		$data = forminator_sanitize_field( $data );

		return apply_filters( 'forminator_field_single_sanitize', $data, $field );
	}
}
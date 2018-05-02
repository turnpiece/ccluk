<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_MultiValue
 *
 * @since 1.0
 */
class Forminator_MultiValue extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'checkbox';

	/**
	 * @var string
	 */
	public $type = 'checkbox';

	/**
	 * @var int
	 */
	public $position = 10;

	/**
	 * @var array
	 */
	public $options = array();

	/**
	 * @var string
	 */
	public $category = 'standard';

	/**
	 * Forminator_MultiValue constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'Multiple Choices', Forminator::DOMAIN );
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
				'size'       => 12,
				'className'  => 'required-field',
				'hide_label' => true,
				'values'     => array(
					array(
						'value'      => "true",
						'label'      => __( 'Required', Forminator::DOMAIN ),
						'labelSmall' => "true",
					),
				),
			),

			array(
				'id'         => 'separator-1',
				'type'       => 'Separator',
				'name'       => 'separator',
				'hide_label' => true,
				'size'       => 12,
				'className'  => 'separator-field',
			),

			array(
				'id'         => 'field-label',
				'type'       => 'Text',
				'name'       => 'field_label',
				'hide_label' => false,
				'label'      => __( 'Field label', Forminator::DOMAIN ),
				'size'       => 12,
				'className'  => 'text-field',
			),

			array(
				'id'         => 'description',
				'type'       => 'Text',
				'name'       => 'description',
				'hide_label' => false,
				'label'      => __( 'Description', Forminator::DOMAIN ),
				'size'       => 12,
				'className'  => 'text-field',
			),

			array(
				'id'         => 'separator-2',
				'type'       => 'Separator',
				'name'       => 'separator',
				'hide_label' => true,
				'size'       => 12,
				'className'  => 'separator-field',
			),

			array(
				'id'            => 'value-type',
				'type'          => 'Radio',
				'name'          => 'value_type',
				'label'         => __( "Choice input type", Forminator::DOMAIN ),
				'size'          => 12,
				'className'     => 'radio-field',
				'default_value' => "checkbox",
				'values'        => array(
					array(
						'value' => "multiselect",
						'label' => __( 'Multi Select Field', Forminator::DOMAIN ),
					),
					array(
						'value' => "checkbox",
						'label' => __( 'Checkboxes', Forminator::DOMAIN ),
					),
				),
			),

			array(
				'id'        => 'options',
				'type'      => 'MultiValue',
				'name'      => 'options',
				'label'     => __( "Choices", Forminator::DOMAIN ),
				'size'      => 12,
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
			'value_type'  => 'checkbox',
			'field_label' => __( 'Multiple Choices', Forminator::DOMAIN ),
			'options'     => array(
				array(
					'label' => __( 'Option 1', Forminator::DOMAIN ),
					'value' => 'one',
				),
				array(
					'label' => __( 'Option 2', Forminator::DOMAIN ),
					'value' => 'two',
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
			'checkbox' => array(
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
				<label class="wpmudev-group--label">{{ encodeHtmlEntity( field.field_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
			{[ } ]}
			{[ if( field.value_type === "multiselect" ) { ]}
				<div class="wpmudev-form-field--multiselect">
					{[ _.each( field.options, function( value, key ){ ]}
					<div class="wpmudev-item">
						<input type="checkbox" id="sample-option-{{ encodeHtmlEntity( field.value ) }}" {{ field.required ? "required" : "" }}>
						<label for="sample-option-{{ encodeHtmlEntity( field.value ) }}">{{ encodeHtmlEntity( value.label.replace(/&quot;/g, "\\"") ) }}</label>
					</div>
					{[ }) ]}
				</div>
			{[ } else { ]}
				{[ _.each( field.options, function( value, key ){ ]}
				<div class="wpmudev-form-field--checkbox">
					<div class="wpmudev-checkbox--design">
						<input type="checkbox" id="sample-option-{{ encodeHtmlEntity( field.value ) }}" {{ field.required ? "required" : "" }}>
						<label for="sample-option-{{ encodeHtmlEntity( field.value ) }}" class="wpdui-icon wpdui-icon-check"></label>
					</div>
					<label for="sample-option-{{ encodeHtmlEntity( field.value ) }}" class="wpmudev-checkbox--label">{{ encodeHtmlEntity( value.label.replace(/&quot;/g, "\\"") ) }}</label>
				</div>
				{[ }) ]}
			{[ } ]}
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
	 *
	 * @param $field
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function markup( $field, $settings = array() ) {
		$this->field	= $field;
		$i				= 1;
		$html			= '';
		$id				= $name = self::get_property( 'element_id', $field );
		$ariaid			= $id;
		$id				= $id . '-field';
		$uniq_id		= uniqid();
		$name			= $name . '[]';
		$required		= self::get_property( 'required', $field, false );
		$options		= self::get_property( 'options', $field, array() );
		$value_type		= isset( $field['value_type'] ) ? $field['value_type'] : "multiselect";

		if ( $value_type == "multiselect" ) {

			$html .= '<ul class="forminator-multiselect">';

				foreach ( $options as $option ) {

					$value    = $option['value'] ? $option['value'] : $option['label'];
					$input_id = $id . '-' . $i;

					if ( $this->get_form_style( $settings ) == 'clean' ) {

						$html .= '<li class="forminator-multiselect--item">';
						$html .= sprintf( '<input id="%s" type="checkbox" name="%s" value="%s"> %s', $input_id . '-' . $uniq_id, $name, $value, $option['label'] );
						$html .= '</li>';

					} else {

						$html     .= sprintf( '<li class="forminator-multiselect--item">' );
						$html     .= sprintf( '<input id="%s" name="%s" type="checkbox" value="%s">', $input_id . '-' . $uniq_id, $name, $value );
						$html     .= sprintf( '<label for="%s">%s</label>', $input_id . '-' . $uniq_id, $option['label'] );
						$html     .= sprintf( '</li>' );

					}

					$i ++;
				}

			$html .= '</ul>';

		} else {

			foreach ( $options as $option ) {

				$value    = $option['value'] ? $option['value'] : $option['label'];
				$input_id = $id . '-' . $i;

				if ( $this->get_form_style( $settings ) == 'clean' ) {

					$html .= '<label class="forminator-checkbox">';
					$html .= sprintf( '<input id="%s" type="checkbox" name="%s" value="%s"> %s', $input_id . '-' . $uniq_id, $name, $value, $option['label'] );
					$html .= '</label>';

				} else {

					$html     .= '<div class="forminator-checkbox">';
					$html     .= sprintf( '<input id="%s" type="checkbox" name="%s" value="%s" class="forminator-checkbox--input">', $input_id . '-' . $uniq_id, $name, $value );
					$html     .= sprintf( '<label for="%s" class="forminator-checkbox--design wpdui-icon wpdui-icon-check" aria-hidden="true"></label>', $input_id . '-' . $uniq_id );
					$html     .= sprintf( '<label for="%s" class="forminator-checkbox--label">%s</label>', $input_id . '-' . $uniq_id, $option['label'] );
					$html     .= '</div>';

				}

				$i ++;

			}

		}

		return apply_filters( 'forminator_field_multiple_markup', $html, $id, $required, $options, $value_type );
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
			$rules .= '"' . $this->get_id( $field ) . '[]": "required",';
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
					'forminator_multi_field_required_validation_message',
					__( 'This field is required. Please select a value', Forminator::DOMAIN ),
					$id,
					$field
				);
				$messages .= '"' . $this->get_id( $field ) . '[]": "' . $required_message . '",' . "\n";
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
					'forminator_multi_field_required_validation_message',
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

		return apply_filters( 'forminator_field_multi_sanitize', $data, $field );
	}
}
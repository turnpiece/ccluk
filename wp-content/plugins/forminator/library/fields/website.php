<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Website
 *
 * @since 1.0
 */
class Forminator_Website extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'url';

	/**
	 * @var int
	 */
	public $position = 6;

	/**
	 * @var string
	 */
	public $type = 'url';

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
	 * Forminator_Website constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'Website', Forminator::DOMAIN );
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
				'size' => 12,
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
				'hide_label' => true
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
				'id' => 'description',
				'type' => 'Text',
				'name' => 'description',
				'hide_label' => false,
				'label'	=> __( 'Description', Forminator::DOMAIN ),
				'size' => 12,
				'className' => 'text-field',
			),

			array(
				'id' => 'separator-2',
				'type' => 'Separator',
				'name' => 'separator',
				'hide_label' => true,
				'size' => 12,
				'className' => 'separator-field',
			),

			array(
				'id' => 'validation',
				'type' => 'ToggleContainer',
				'name' => 'validation',
				'hide_label' => true,
				'has_content' => true,
				'values' => array(
					array(
						'value' => "true",
						'label' => __( 'Enable validation', Forminator::DOMAIN ),
						'labelSmall' => "true"
					)
				),
				'fields' => array(
					array(
						'id' => 'validation-text',
						'type' => 'Text',
						'name' => 'validation_text',
						'size' => 12,
						'className' => 'select-field',
						'label' => __( 'Custom validation error message', Forminator::DOMAIN )
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
			'field_label' => __( 'Website', Forminator::DOMAIN ),
			'placeholder' => __( 'E.g. http://www.example.com', Forminator::DOMAIN ),
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
			{[ if( ( field.validation == "true" ) && field.validation_text ) { ]}
			<div class="wpmudev-group--validation">
				<p>{{ encodeHtmlEntity( field.validation_text ) }}</p>
			</div>
			{[ } ]}
		</div>';
	}

	/**
	 * Field front-end markup
	 *
	 * @since 1.0
	 * @param $field
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function markup( $field, $settings = array() ) {
		$this->field = $field;
		$id = $name  = self::get_property( 'element_id', $field );
		$id          = $id . '-field';
		$required    = $this->get_property( 'required', $field, false );
		$placeholder = $this->sanitize_value( $this->get_property( 'placeholder', $field ) );
		$value       = $this->get_property( 'value', $field );

		$html = sprintf( '<input class="forminator-website--field forminator-input" type="url" data-required="%s" name="%s" placeholder="%s" value="%s" id="%s"/>', $required, $name, $placeholder, $value, $id );

		return apply_filters( 'forminator_field_website_markup', $html, $id, $required, $placeholder, $value );
	}

	/**
	 * Return field inline validation rules
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_validation_rules() {
		$field = $this->field;
		$rules = '"' . $this->get_id( $field )  .'": {' . "\n";

		if( $this->is_required( $field ) ) {
			$rules .= '"required": true,';
		}

		$rules .= '"url": true,';
		$rules .= '},' . "\n";

		return $rules;
	}

	/**
	 * Return field inline validation errors
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_validation_messages() {
		$field        = $this->field;
		$id           = $this->get_id( $field );
		$custom_error = self::get_property( 'validation', $field );
		$error        = self::get_property( 'validation_text', $field );
		$error        = preg_quote( $error, '"' );
		$messages = '"' . $id  .'": {' . "\n";

		if( $this->is_required( $field ) ) {
			$required_message = __( 'This field is required. Please input a valid URL', Forminator::DOMAIN );

			if( isset( $custom_error ) && $custom_error ) {
				if ( $error ) {
					$required_message = $error;
				}
			}

			$required_message = apply_filters(
				'forminator_website_field_required_validation_message',
				$required_message,
				$field,
				$id
			);
			$messages .= 'required: "' . $required_message . '",' . "\n";
		}

		$error_message = __( 'Please enter a valid Website URL (e.g. https://premium.wpmudev.org/).', Forminator::DOMAIN );
		if( isset( $custom_error ) && $custom_error ) {
			if ( $error ) {
				$error_message = $error;
			}

			$error_message = apply_filters(
				'forminator_website_field_custom_validation_message',
				$error_message,
				$id,
				$field,
				$custom_error
			);
			$messages .= 'url: "' . $error_message . '",' . "\n";
		}
		$messages .= '},' . "\n";

		$messages = apply_filters(
			'forminator_website_field_validation_message',
			$messages,
			$id,
			$field,
			$custom_error
		);

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
			$id           = self::get_property( 'element_id', $field );
			$custom_error = self::get_property( 'validation', $field );
			$error        = self::get_property( 'validation_text', $field );
			$error        = htmlentities( $error );

			if ( empty( $data ) ) {
				if( isset( $custom_error ) && $custom_error && $error ) {
					$this->validation_message[ $id ] = $error;
				} else {
					$this->validation_message[ $id ] = apply_filters(
						'forminator_website_field_required_validation_message',
						__( 'This field is required. Please input a valid URL', Forminator::DOMAIN ),
						$id,
						$field,
						$custom_error
					);
				}
			} else {
				if ( !filter_var( $data, FILTER_VALIDATE_URL ) ) {
					if( isset( $custom_error ) && $custom_error && $error ) {
						$this->validation_message[ $id ] = $error;
					} else {
						$this->validation_message[ $id ] = apply_filters(
							'forminator_website_field_custom_validation_message',
							__( 'Please enter a valid Website URL (e.g. https://premium.wpmudev.org/).', Forminator::DOMAIN ),
							$id,
							$field,
							$custom_error
						);
					}
				}
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

		return apply_filters( 'forminator_field_website_sanitize', $data, $field );
	}
}
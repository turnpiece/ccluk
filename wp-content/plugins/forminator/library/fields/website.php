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
				'id'         => 'placeholder',
				'type'       => 'Text',
				'name'       => 'placeholder',
				'hide_label' => false,
				'label'      => __( 'Placeholder', Forminator::DOMAIN ),
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
				'id'          => 'validation',
				'type'        => 'ToggleContainer',
				'name'        => 'validation',
				'hide_label'  => true,
				'has_content' => true,
				'values'      => array(
					array(
						'value'      => "true",
						'label'      => __( 'Enable validation', Forminator::DOMAIN ),
						'labelSmall' => "true",
					),
				),
				'fields'      => array(
					array(
						'id'        => 'validation-text',
						'type'      => 'Text',
						'name'      => 'validation_text',
						'className' => 'select-field',
						'label'     => __( 'Custom validation error message', Forminator::DOMAIN ),
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
			'field_label' => __( 'Website', Forminator::DOMAIN ),
			'placeholder' => __( 'E.g. http://www.example.com', Forminator::DOMAIN ),
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
			'website' => array(
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
			<input type="{{ field.type }}" class="sui-form-control" placeholder="{{ encodeHtmlEntity( field.placeholder ) }}" {{ field.required ? "required" : "" }}>
			{[ if( field.validation_text ) { ]}
				<span class="sui-error-message">{{ encodeHtmlEntity( field.validation_text ) }}</span>
			{[ } ]}
			{[ if( field.description ) { ]}
				<span class="sui-description">{{ encodeHtmlEntity( field.description ) }}</span>
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
		$id          = self::get_property( 'element_id', $field );
		$name        = $id;
		$ariaid      = $id;
		$id          = $id . '-field';
		$required    = $this->get_property( 'required', $field, false );
		$placeholder = $this->sanitize_value( $this->get_property( 'placeholder', $field ) );
		$value       = self::get_post_data( $name, $this->get_property( 'value', $field ) );

		$html = sprintf(
			'<input class="forminator-website--field forminator-input" type="url" data-required="%s" name="%s" placeholder="%s" value="%s" id="%s" aria-labelledby="%s"/>',
			$required,
			$name,
			$placeholder,
			$value,
			$id,
			$ariaid
		);

		return apply_filters( 'forminator_field_website_markup', $html, $id, $required, $placeholder, $value );
	}

	/**
	 * Return string with scheme part if needed
	 *
	 * @since 1.1
	 * @return string
	 */
	public function add_scheme_url( $url ) {
		if ( empty( $url ) ) {
			return $url;
		}
		$parts = wp_parse_url( $url );
		if ( false !== $parts ) {
			if ( ! isset( $parts['scheme'] ) ) {
				$url = 'http://' . $url;
			}
		}

		return $url;
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

		$rules .= '"validurl": true,';
		$rules .= '"url": false,';
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
		}
		$messages .= 'validurl: "' . $error_message . '",' . "\n";
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
		$id           = self::get_property( 'element_id', $field );
		$custom_error = self::get_property( 'validation', $field );
		$error        = self::get_property( 'validation_text', $field );
		$error        = htmlentities( $error );

		if ( $this->is_required( $field ) ) {

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
			}
		}
		if ( !empty( $data ) && !filter_var( $data, FILTER_VALIDATE_URL ) ) {
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
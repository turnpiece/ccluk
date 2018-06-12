<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Text
 *
 * @since 1.0
 */
class Forminator_Text extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'text';

	/**
	 * @var string
	 */
	public $type = 'text';

	/**
	 * @var int
	 */
	public $position = 4;

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
	 * Forminator_Text constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'Text', Forminator::DOMAIN );
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
				'id'            => 'required',
				'type'          => 'Toggle',
				'name'          => 'required',
				'className'     => 'required-field',
				'hide_label'    => true,
				'default_value' => false,
				'values'        => array(
					array(
						'value'      => "true",
						'label'      => __( 'Required', Forminator::DOMAIN ),
						'labelSmall' => "true",
					),
				),
			),

			array(
				'id'            => 'input-type',
				'type'          => 'Radio',
				'name'          => 'input_type',
				'label'         => __( "Text input type", Forminator::DOMAIN ),
				'className'     => 'radio-field',
				'default_value' => 'line',
				'values'        => array(
					array(
						'value' => 'line',
						'label' => __( 'Single line text', Forminator::DOMAIN ),
					),
					array(
						'value' => 'paragraph',
						'label' => __( 'Paragraph text', Forminator::DOMAIN ),
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
				'id'         => 'field-placeholder',
				'type'       => 'Text',
				'name'       => 'placeholder',
				'hide_label' => false,
				'label'      => __( 'Placeholder', Forminator::DOMAIN ),
				'className'  => 'text-field',
			),

			array(
				'id'         => 'field-description',
				'type'       => 'Text',
				'name'       => 'description',
				'hide_label' => false,
				'label'      => __( 'Description', Forminator::DOMAIN ),
				'className'  => 'text-field',
			),

			array(
				'id'             => 'text-limit',
				'type'           => 'ToggleContainer',
				'name'           => 'text_limit',
				'hide_label'     => true,
				'has_content'    => true,
				'containerClass' => 'wpmudev-has_cols',
				'values'         => array(
					array(
						'value'      => "true",
						'label'      => __( 'Use input limit', Forminator::DOMAIN ),
						'labelSmall' => "true",
					),
				),
				'fields'         => array(

					array(
						'id'            => 'field-limit',
						'type'          => 'Number',
						'name'          => 'limit',
						'className'     => 'select-field',
						'default_value' => 180,
						'label'         => __( 'Limit field to:', Forminator::DOMAIN ),
					),

					array(
						'id'            => 'field-limit-type',
						'type'          => 'Select',
						'name'          => 'limit_type',
						'className'     => 'number-field',
						'hide_label'    => true,
						'default_value' => 'characters',
						'values'        => array(
							array(
								'value' => 'characters',
								'label' => __( 'Characters', Forminator::DOMAIN ),
							),
							array(
								'value' => 'words',
								'label' => __( 'Words', Forminator::DOMAIN ),
							),
						),
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
			'input_type'  => 'line',
			'limit'       => 180,
			'limit_type'  => 'characters',
			'field_label' => __( 'Text', Forminator::DOMAIN ),
			'placeholder' => __( 'E.g. text placeholder', Forminator::DOMAIN ),
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
			'text' => array(
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
		return '{[ if( field.field_label !== "" ) { ]}
			<label class="sui-label">{{ encodeHtmlEntity( field.field_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
		{[ } ]}
		{[ if( field.input_type === "paragraph" ) { ]}
			<textarea class="sui-form-control" placeholder="{{ encodeHtmlEntity( field.placeholder ) }}" {{ field.required ? "required" : "" }}></textarea>
		{[ } else { ]}
			<input type="text" class="sui-form-control" placeholder="{{ encodeHtmlEntity( field.placeholder ) }}" {{ field.required ? "required" : "" }}>
		{[ } ]}
		{[ if( field.description && ( field.text_limit && field.limit ) ) { ]}
			<div class="fui-extended-description">
		{[ } ]}
			{[ if( field.description ) { ]}
				<span class="sui-description">{{ encodeHtmlEntity( field.description ) }}</span>
			{[ } ]}
			{[ if( field.description && ( field.text_limit && field.limit ) ) { ]}
				<div class="sui-actions-right">
			{[ } ]}
			{[ if( field.text_limit && field.limit ) { ]}
				<span class="sui-description">0 / {{ field.limit }}</span>
			{[ } ]}
			{[ if( field.description && ( field.text_limit && field.limit ) ) { ]}
				</div>
			{[ } ]}
		{[ if( field.description && ( field.text_limit && field.limit ) ) { ]}
			</div>
		{[ } ]}';
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
		$this->field         = $field;
		$this->form_settings = $settings;

		$this->init_autofill( $settings );

		$id          = self::get_property( 'element_id', $field );
		$name        = $id;
		$ariaid      = $id;
		$id          = $id . '-field';
		$required    = self::get_property( 'required', $field, false );
		$placeholder = $this->sanitize_value( self::get_property( 'placeholder', $field ) );
		$field_type  = trim( self::get_property( 'input_type', $field ) );
		$design      = $this->get_form_style( $settings );

		$html = '';

		$autofill_markup = $this->get_element_autofill_markup_attr( self::get_property( 'element_id', $field ), $this->form_settings );

		if ( "paragraph" === $field_type ) {

			if ( 'material' === $design ) {
				$html .= '<div class="forminator-textarea--wrap">';
			}

			$textarea = array(
				'class'           => 'forminator-textarea',
				'name'            => $name,
				'placeholder'     => $placeholder,
				'id'              => $id,
				'aria-labelledby' => 'forminator-label-' . $ariaid,
			);

			if ( isset( $autofill_markup['value'] ) ) {
				$textarea['content'] = $autofill_markup['value'];
				unset( $autofill_markup['value'] );
			}
			$textarea = array_merge( $textarea, $autofill_markup );

			$html .= self::create_textarea( $textarea );

			if ( 'material' === $design ) {
				$html .= '</div>';
			}

		} else {

			if ( 'material' === $design ) {
				$html .= '<div class="forminator-input--wrap">';
			}

			$input_text = array(
				'class'           => 'forminator-input forminator-name--field',
				'name'            => $name,
				'placeholder'     => $placeholder,
				'id'              => $id,
				'data-required'   => $required,
				'aria-labelledby' => 'forminator-label-' . $ariaid,
			);

			$input_text = array_merge( $input_text, $autofill_markup );

			$html .= self::create_input( $input_text );

			if ( 'material' === $design ) {
				$html .= '</div>';
			}

		}

		return apply_filters( 'forminator_field_text_markup', $html, $field );
	}

	/**
	 * Return field inline validation rules
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_validation_rules() {
		$field       = $this->field;
		$is_required = $this->is_required( $field );
		$has_limit   = $this->has_limit( $field );
		$rules       = '';

		if( ! isset( $field['limit'] ) ) {
			$field['limit'] = 0;
		}

		if ( $is_required || $has_limit ) {
			$rules = '"' . $this->get_id( $field ) . '": {';
			if ( $is_required ) {
				$rules .= '"required": true,';
			}

			if( $has_limit ) {
				if ( isset( $field['limit_type'] ) && 'characters' === trim($field['limit_type']) ) {
					$rules .= '"maxlength": ' . $field['limit'] . ',';
				} else {
					$rules .= '"maxwords": ' . $field['limit'] . ',';
				}
			}
			$rules .= '},';
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
		$field       = $this->field;
		$id          = self::get_property( 'element_id', $field );
		$is_required = $this->is_required( $field );
		$has_limit   = $this->has_limit( $field );
		$messages    = '';

		if ( $is_required || $has_limit ) {
			$messages .= '"' . $this->get_id( $field ) . '": {';

			if ( $is_required ) {
				$required_error = apply_filters(
					'forminator_text_field_required_validation_message',
					__( 'This field is required. Please enter text', Forminator::DOMAIN ),
					$id,
					$field
				);
				$messages       .= '"required": "' . $required_error . '",' . "\n";
			}

			if( $has_limit ) {
				if ( isset( $field['limit_type'] ) && 'characters' === trim($field['limit_type']) ) {
					$max_length_error = apply_filters(
						'forminator_text_field_characters_validation_message',
						__( 'You exceeded the allowed amount of characters. Please check again', Forminator::DOMAIN ),
						$id,
						$field
					);
					$messages         .= '"maxlength": "' . $max_length_error . '",' . "\n";
				} else {
					$max_words_error = apply_filters(
						'forminator_text_field_words_validation_message',
						__( 'You exceeded the allowed amount of words. Please check again', Forminator::DOMAIN ),
						$id,
						$field
					);
					$messages        .= '"maxwords": "' . $max_words_error . '",' . "\n";
				}
			}

			$messages .= '},';
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
		$id = self::get_property( 'element_id', $field );

		if( ! isset( $field['limit'] ) ) {
			$field['limit'] = 0;
		}

		if ( $this->is_required( $field ) ) {
			if ( empty( $data ) ) {
				$this->validation_message[ $id ] = apply_filters(
					'forminator_text_field_required_validation_message',
					__( 'This field is required. Please enter text', Forminator::DOMAIN ),
					$id,
					$field
				);
			}
		}
		if ( $this->has_limit( $field ) ) {
			if ( ( isset( $field['limit_type'] ) && 'characters' === trim( $field['limit_type']) ) && ( strlen( $data ) > $field['limit'] ) ) {
				$this->validation_message[ $id ] = apply_filters(
					'forminator_text_field_characters_validation_message',
					__( 'You exceeded the allowed amount of characters. Please check again', Forminator::DOMAIN ),
					$id,
					$field
				);
			} elseif ( ( isset( $field['limit_type'] ) && 'words' === trim($field['limit_type']) ) && ( str_word_count( $data) > $field['limit'] ) ) {
				$this->validation_message[ $id ] = apply_filters(
					'forminator_text_field_words_validation_message',
					__( 'You exceeded the allowed amount of words. Please check again', Forminator::DOMAIN ),
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
	 * @param array        $field
	 * @param array|string $data - the data to be sanitized
	 *
	 * @return array|string $data - the data after sanitization
	 */
	public function sanitize( $field, $data ) {
		// Sanitize
		$data = forminator_sanitize_field( $data );

		return apply_filters( 'forminator_field_text_sanitize', $data, $field );
	}
}
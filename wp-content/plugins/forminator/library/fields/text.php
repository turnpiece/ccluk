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
				'default_value' => false,
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
				'className' => 'separator-field',
			),

			array(
				'id' => 'input-type',
				'type' => 'Radio',
				'name' => 'input_type',
				'label' => __( "Text input type", Forminator::DOMAIN ),
				'className' => 'radio-field',
				'default_value' => 'line',
				'values' => array(
					array(
						'value' => 'line',
						'label' => __( 'Single line text', Forminator::DOMAIN )
					),
					array(
						'value' => 'paragraph',
						'label' => __( 'Paragraph text', Forminator::DOMAIN )
					)
				)
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
				'id' => 'separator-2',
				'type' => 'Separator',
				'name' => 'separator',
				'hide_label' => true,
				'className' => 'separator-field',
			),

			array(
				'id' => 'text-limit',
				'type' => 'ToggleContainer',
				'name' => 'text_limit',
				'hide_label' => true,
				'has_content' => true,
				'containerClass' => 'wpmudev-has_cols',
				'values' => array(
					array(
						'value' => "true",
						'label' => __( 'Use input limit', Forminator::DOMAIN ),
						'labelSmall' => "true"
					)
				),
				'fields' => array(

					array(
						'id' => 'field-limit',
						'type' => 'Number',
						'name' => 'limit',
						'className' => 'select-field',
						'default_value' => 180,
						'label' => __( 'Limit field to:', Forminator::DOMAIN )
					),

					array(
						'id' => 'field-limit-type',
						'type' => 'Select',
						'name' => 'limit_type',
						'className' => 'number-field',
						'hide_label' => true,
						'default_value' => 'characters',
						'values' => array(
							array(
								'value' => 'characters',
								'label' => __( 'Characters', Forminator::DOMAIN )
							),
							array(
								'value' => 'words',
								'label' => __( 'Words', Forminator::DOMAIN )
							)
						)
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
			'input_type'  => 'line',
			'limit'       => 180,
			'limit_type'  => 'characters',
			'field_label' => __( 'Text', Forminator::DOMAIN ),
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
			{[ if( field.input_type === "paragraph" ) { ]}
				<textarea class="wpmudev-textarea" placeholder="{{ encodeHtmlEntity( field.placeholder ) }}" {{ field.required ? "required" : "" }}></textarea>
			{[ } else { ]}
				<input type="text" class="wpmudev-input" placeholder="{{ encodeHtmlEntity( field.placeholder ) }}" {{ field.required ? "required" : "" }}>
			{[ } ]}
			{[ if( field.description || field.text_limit ) { ]}
			<div class="wpmudev-group--info">
				{[ if( field.description ) { ]}
				<span class="wpmudev-info--text">{{ encodeHtmlEntity( field.description ) }}</span>
				{[ } ]}
				{[ if( field.text_limit && field.limit ) { ]}
				<span class="wpmudev-info--limit">0 / {{ field.limit }}</span>
				{[ } ]}
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
		$this->field = $field;
		$id = $name  = self::get_property( 'element_id', $field );
		$id          = $id . '-field';
		$required    = self::get_property( 'required', $field, false );
		$placeholder = $this->sanitize_value( self::get_property( 'placeholder', $field ));
		$field_type  = self::get_property( 'input_type', $field );

		if( $field_type == "paragraph" ) {
			$html = sprintf( '<textarea class="forminator-textarea" name="%s" placeholder="%s" id="%s" /></textarea>', $name, $placeholder, $id );
		} else {
			$html = sprintf( '<input class="forminator-input forminator-name--field" type="text" data-required="%s" name="%s" placeholder="%s" id="%s" />', $required, $name, $placeholder, $id );
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

		if ( $is_required || $has_limit ) {
			$rules = '"' . $this->get_id( $field ) . '": {';
			if( $is_required ) {
				$rules .= '"required": true,';
			}
			if( $has_limit ) {
				if ( $field['limit_type'] === 'characters' ) {
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
		$is_required = $this->is_required( $field );
		$has_limit   = $this->has_limit( $field );
		$messages    = '';

		if ( $is_required || $has_limit ) {
			$messages .= '"' . $this->get_id( $field ) . '": {';

			if( $is_required ) {
				$messages .= '"required": "' . __( 'This field is required. Please enter text', Forminator::DOMAIN ) . '",' . "\n";
			}
			if( $has_limit ) {
				if ( $field['limit_type'] === 'characters' ) {
					$messages .= '"maxlength": "' . __( 'You exceeded the allowed amount of characters. Please check again', Forminator::DOMAIN ) . '",' . "\n";
				} else {
					$messages .= '"maxwords": "' . __( 'You exceeded the allowed amount of words. Please check again', Forminator::DOMAIN ) . '",' . "\n";
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
	 * @param array $field
	 * @param array|string $data
	 */
	public function validate( $field, $data ) {
		$id = self::get_property( 'element_id', $field );

		if ( $this->is_required( $field ) ) {
			if ( empty( $data ) ) {
				$this->validation_message[ $id ] = __( 'This field is required. Please enter some text', Forminator::DOMAIN );
			}
		}
		if ( $this->has_limit( $field ) ) {
			if ( ($field['limit_type'] === 'characters') && (strlen( $data ) > $field['limit']) ) {
				$this->validation_message[ $id ] = __( 'You exceeded the allowed amount of characters. Please check again', Forminator::DOMAIN );

			} elseif ( ($field['limit_type'] === 'words') && (str_word_count($data) > $field['limit']) ) {
				$this->validation_message[ $id ] = __( 'You exceeded the allowed amount of words. Please check again', Forminator::DOMAIN );
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

		return apply_filters( 'forminator_field_text_sanitize', $data, $field );
	}
}
<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_GdprCheckbox
 *
 * @since 1.0.5
 */
class Forminator_GdprCheckbox extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'gdprcheckbox';

	/**
	 * @var string
	 */
	public $type = 'gdprcheckbox';

	/**
	 * @var int
	 */
	public $position = 18;

	/**
	 * @var array
	 */
	public $options = array();

	/**
	 * @var string
	 */
	public $category = 'standard';

	/**
	 * Forminator_GdprChecbox constructor.
	 *
	 * @since 1.0.5
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'GDPR Checkbox', Forminator::DOMAIN );
	}

	/**
	 * @param array $settings
	 *
	 * @since 1.0.5
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
				'hide_label' => false,
				'label'      => __( 'GDPR Checkbox is always marked as required.', Forminator::DOMAIN ),
			),
			array(
				'id'         => 'separator',
				'type'       => 'Separator',
				'name'       => 'separator',
				'hide_label' => true,
				'className'  => 'separator-field',
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
				'id'         => 'gdpr-description',
				'type'       => 'Editor',
				'name'       => 'gdpr_description',
				'hide_label' => false,
				'label'      => __( 'GDPR Description', Forminator::DOMAIN ),
			),

		);
	}

	/**
	 * Field defaults
	 *
	 * @since 1.0.5
	 * @return array
	 */
	public function defaults() {
		return array(
			'required'         => 'true',
			'field_label'      => 'GDPR',
			'gdpr_description' => __( 'Yes, I agree with <a href="#">privacy policy</a>, <a href="#">terms and condition</a>', Forminator::DOMAIN ),
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
		//Unsupported Autofill
		$autofill_settings = array();

		return $autofill_settings;
	}

	/**
	 * Markup displayed on admin builder
	 *
	 * @since 1.0.5
	 *
	 * @return string
	 */
	public function admin_html() {
		return '{[ if( field.field_label !== "" ) { ]}
			<label class="sui-label">{{ encodeHtmlEntity( field.field_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
		{[ } ]}
		<label for="sample-option-gdpr" class="sui-checkbox">
			<input type="checkbox" id="sample-option-gdpr" required="required" disabled="disabled">
			<span aria-hidden="true"></span>
			{[ if( field.gdpr_description ) { ]}
				<span class="sui-description">{{ field.gdpr_description }}</span>
			{[ } ]}
		</label>';
	}

	/**
	 * Field front-end markup
	 *
	 * @since 1.0.5
	 *
	 * @param $field
	 * @param $settings
	 *
	 * @return mixed
	 */
	public function markup( $field, $settings = array() ) {
		$this->field = $field;
		$html        = '';
		$id          = self::get_property( 'element_id', $field );
		$name        = $id;
		$description = self::get_property( 'gdpr_description', $field );
		$id          = $id . '-field-' . uniqid();

		$html .= '<div class="forminator-checkbox">';
		$html .= sprintf( '<input id="%s" type="checkbox" name="%s" value="true" class="forminator-checkbox--input" data-required="true">', $id, $name );
		$html .= sprintf( '<label for="%s" class="forminator-checkbox--design wpdui-icon wpdui-icon-check" aria-hidden="true"></label>', $id );
		$html .= sprintf( '<label for="%s" class="forminator-checkbox--label">%s</label>', $id, $description );
		$html .= '</div>';

		return apply_filters( 'forminator_field_gdprcheckbox_markup', $html, $id, $description );
	}

	/**
	 * Return field inline validation rules
	 *
	 * @since 1.0.5
	 * @return string
	 */
	public function get_validation_rules() {
		$field = $this->field;

		return '"' . $this->get_id( $field ) . '":{"required":true},';
	}

	/**
	 * Return field inline validation errors
	 *
	 * @since 1.0.5
	 * @return string
	 */
	public function get_validation_messages() {
		$messages = '';
		$field    = $this->field;
		$id       = $this->get_id( $field );

		$required_message = apply_filters(
			'forminator_gdprcheckbox_field_required_validation_message',
			__( 'This field is required. Please check it.', Forminator::DOMAIN ),
			$id,
			$field
		);
		$messages         .= '"' . $this->get_id( $field ) . '": {required:"' . $required_message . '"},' . "\n";

		return $messages;
	}

	/**
	 * Field back-end validation
	 *
	 * @since 1.0.5
	 *
	 * @param array        $field
	 * @param array|string $data
	 */
	public function validate( $field, $data ) {
		// value of gdpr checkbox is `string` *true*
		$id = $this->get_id( $field );
		if ( empty( $data ) || 'true' !== $data ) {
			$this->validation_message[ $id ] = apply_filters(
				'forminator_gdprcheckbox_field_required_validation_message',
				__( 'This field is required. Please check it.', Forminator::DOMAIN ),
				$id,
				$field
			);
		}
	}

	/**
	 * Sanitize data
	 *
	 * @since 1.0.5
	 *
	 * @param array        $field
	 * @param array|string $data - the data to be sanitized
	 *
	 * @return array|string $data - the data after sanitization
	 */
	public function sanitize( $field, $data ) {
		// Sanitize
		$data = forminator_sanitize_field( $data );

		return apply_filters( 'forminator_field_gdprcheckbox_sanitize', $data, $field );
	}
}
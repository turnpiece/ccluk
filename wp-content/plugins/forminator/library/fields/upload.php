<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Upload
 *
 * @since 1.0
 */
class Forminator_Upload extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'upload';

	/**
	 * @var string
	 */
	public $type = 'upload';

	/**
	 * @var int
	 */
	public $position = 7;

	/**
	 * @var array
	 */
	public $options = array();

	/**
	 * @var string
	 */
	public $category = 'standard';

	/**
	 * Forminator_Upload constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'File Upload', Forminator::DOMAIN );
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
						'label' => __( 'Required', Forminator::DOMAIN )
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
				'id' => 'separator-2',
				'type' => 'Separator',
				'name' => 'separator',
				'hide_label' => true,
				'size' => 12,
				'className' => 'separator-field',
			),

			array(
				'id' => 'show-description',
				'type' => 'ToggleContainer',
				'name' => 'show_description',
				'size' => 12,
				'className' => 'toggle-container',
				'hide_label' => true,
				'values' => array(
					array(
						'value' => "true",
						'label' => __( 'Show description', Forminator::DOMAIN )
					)
				),
				'fields' => array(

					array(
						'id' => 'description',
						'type' => 'Text',
						'name' => 'description',
						'size' => 12,
						'className' => 'description-field',
						'label' => __( 'Description text', Forminator::DOMAIN )
					),

				)
			),

			array(
				'id' => 'separator-3',
				'type' => 'Separator',
				'name' => 'separator',
				'hide_label' => true,
				'size' => 12,
				'className' => 'separator-field',
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
			'field_label' => __( 'Upload file', Forminator::DOMAIN ),
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
            <div class="wpmudev-form-field--upload">
                <button class="wpmudev-upload-button">Choose file</button>
				<label class="wpmudev-upload-file">No file chosen</label>
				{[ if( field.show_description && field.description !== "" ) { ]}
				<small class="wpmudev-upload-info">{{ encodeHtmlEntity( field.description ) }}</small>
				{[ } ]}
            </div>
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
		$id = $name = self::get_property( 'element_id', $field );
		$html = self::create_file_upload( $id, $name );
		return apply_filters( 'forminator_field_file_markup', $html, $field );
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
			if ( empty( $data ) ) {
				$this->validation_message[ $id ] = __( 'This field is required. Please upload a file', Forminator::DOMAIN );
			}
		}
	}
}
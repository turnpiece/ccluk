<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Html
 *
 * @since 1.0
 */
class Forminator_Html extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'html';

	/**
	 * @var string
	 */
	public $type = 'html';

	/**
	 * @var int
	 */
	public $position = 14;

	/**
	 * @var array
	 */
	public $options = array();

	/**
	 * @var string
	 */
	public $category = 'standard';

	/**
	 * Forminator_Html constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'HTML', Forminator::DOMAIN );
	}

	/**
	 * @since 1.0
	 * @param array $settings
	 *
	 * @return array
	 */
	public function load_settings( $settings = array() ){
		return array(

			array(
				'id' => 'field-label',
				'type' => 'Text',
				'name' => 'field_label',
				'label'	=> __( 'Field label', Forminator::DOMAIN ),
			),

			array(
				'id' => 'variations',
				'type' => 'Editor',
				'name' => 'variations',
				'label'	=> __( 'Default value', Forminator::DOMAIN ),
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
			'field_label'  => __( 'HTML', Forminator::DOMAIN )
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
			{[ if( field.variations !== "" ) { ]}
				<div class="wpmudev-option--html">{{ field.variations }}</div>
			{[ } else { ]}
				<div class="wpmudev-option--html">... add custom html here ...</div>
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

		return forminator_replace_variables( self::get_property( 'variations', $field ) );

	}
}
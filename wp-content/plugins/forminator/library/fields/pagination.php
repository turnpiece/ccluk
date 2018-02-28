<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Pagination
 *
 * @since 1.0
 */
class Forminator_Pagination extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'pagination';

	/**
	 * @var string
	 */
	public $type = 'pagination';

	/**
	 * @var int
	 */
	public $position = 15;

	/**
	 * @var array
	 */
	public $options = array();

	/**
	 * @var string
	 */
	public $category = 'standard';

	/**
	 * @var string
	 */
	public $hide_advanced = "true";

	/**
	 * Forminator_Pagination constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		parent::__construct();

		$this->name = __( 'Pagination', Forminator::DOMAIN );

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
				'id' => 'pagination-step-label',
				'type' => 'Text',
				'name' => 'pagination-label',
				'hide_label' => false,
				'label'	=> __( 'Step label', Forminator::DOMAIN )
			),

			/*
			array(
				'id' => 'pagination-left-button',
				'type' => 'Text',
				'name' => 'btn_left',
				'hide_label' => false,
				'label'	=> __( 'Left button text', Forminator::DOMAIN )
			),

			array(
				'id' => 'pagination-right-button',
				'type' => 'Text',
				'name' => 'btn_right',
				'hide_label' => false,
				'label'	=> __( 'Right button text', Forminator::DOMAIN )
            ),
			*/
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
			'btn_left'	=> __( '« Previous Step', Forminator::DOMAIN ),
			'btn_right'	=> __( 'Next Step »', Forminator::DOMAIN ),
		);
	}

	/**
	 * Field admin markup
	 *
	 * @since 1.0
	 * @return string
	 */
	public function admin_html() {
		return '<div class="wpmudev-form-field--pagination">
			<div class="wpmudev-pagination--footer">
				{[ if( field.btn_left !== "" ) { ]}
					<button class="wpmudev-button-prev" disabled>{{ encodeHtmlEntity( field.btn_left ) }}</button>
				{[ } else { ]}
					<button class="wpmudev-button-prev" disabled>« Previous Step</button>
				{[ } ]}
				{[ if( field.btn_right !== "" ) { ]}
					<button class="wpmudev-button-next">{{ encodeHtmlEntity( field.btn_right ) }}</button>
				{[ } else { ]}
					<button class="wpmudev-button-next">Next Step »</button>
				{[ } ]}
			</div>
		</div>';
	}
}
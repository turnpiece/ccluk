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
		return apply_filters( 'forminator_pagination_btn_default_settings', array(
			array(
				'id'         => 'pagination-step-label',
				'type'       => 'Text',
				'name'       => 'pagination-label',
				'hide_label' => false,
				'label'      => __( 'Step label', Forminator::DOMAIN ),
			),
		) );
	}

	/**
	 * Field defaults
	 *
	 * @since 1.0
	 * @return array
	 */
	public function defaults() {
		return apply_filters( 'forminator_pagination_btn_label', array(
			'btn_left'	=> __( '« Previous Step', Forminator::DOMAIN ),
			'btn_right'	=> __( 'Next Step »', Forminator::DOMAIN ),
		) );
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
	 * Field admin markup
	 *
	 * @since 1.0
	 * @return string
	 */
	public function admin_html() {
		return '<div class="fui-extended-description">
			{[ if( field.btn_left !== "" ) { ]}
				<button class="sui-button" disabled>{{ encodeHtmlEntity( field.btn_left ) }}</button>
			{[ } else { ]}
				<button class="sui-button" disabled>« Previous Step</button>
			{[ } ]}
			<div class="sui-actions-right">
				{[ if( field.btn_right !== "" ) { ]}
					<button class="sui-button">{{ encodeHtmlEntity( field.btn_right ) }}</button>
				{[ } else { ]}
					<button class="sui-button">Next Step »</button>
				{[ } ]}
			</div>
		</div>';
	}
}
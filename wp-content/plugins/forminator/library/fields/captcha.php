<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Captcha
 *
 * @since 1.0
 */
class Forminator_Captcha extends Forminator_Field {

	/**
	 * @var string
	 */
	public $name = '';

	/**
	 * @var string
	 */
	public $slug = 'captcha';

	/**
	 * @var string
	 */
	public $type = 'captcha';

	/**
	 * @var int
	 */
	public $position = 11;

	/**
	 * @var array
	 */
	public $options = array();

	/**
	 * @var string
	 */
	public $category = 'standard';

	/**
	 * Forminator_Captcha constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		parent::__construct();

		$this->name = __( 'Captcha', Forminator::DOMAIN );
	}

	/**
	 * @param array $settings
	 *
	 * @since 1.0
	 * @return array
	 */
	public function load_settings( $settings = array() ) {
		return array(
			array(
				'id' => 'field-label',
				'type' => 'Text',
				'name' => 'field_label',
				'hide_label' => false,
				'label'	=> __( 'Field label', Forminator::DOMAIN )
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
			//'field_label'  => __( 'Are you a human?', Forminator::DOMAIN )
		);
	}

	/**
	 * Field admin markup
	 *
	 * @since 1.0
	 * @return string
	 */
	public function admin_html() {
		$path = forminator_plugin_url() . 'assets/img/google-recaptcha.png';
		$retinа = forminator_plugin_url() . 'assets/img/google-recaptcha@2x.png';

		return '<div class="wpmudev-form-field--group">
			{[ if( field.field_label !== "" ) { ]}
				<label class="wpmudev-group--label">{{ encodeHtmlEntity( field.field_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
			{[ } ]}
			<figure class="wpmudev-captcha"><img src="' . $path . '" srcset="' . $path . ' 1x, ' . $retinа . ', 2x" /></figure>
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
		$key 	= get_option( "forminator_captcha_key", false );
		$theme  = get_option( "forminator_captcha_theme", false );

		return sprintf( '<div class="g-recaptcha forminator-g-recaptcha" data-theme="%s" data-sitekey="%s"></div>', $theme, $key );
	}
}
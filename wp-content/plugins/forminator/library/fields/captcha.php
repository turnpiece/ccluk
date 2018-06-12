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
	 * @var string
	 */
	public $hide_advanced = "true";

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
				'id'         => 'field-label',
				'type'       => 'Text',
				'name'       => 'field_label',
				'hide_label' => false,
				'label'      => __( 'Field label', Forminator::DOMAIN ),
			),
			array(
				'id'         => 'invisible-captcha',
				'type'       => 'Toggle',
				'name'       => 'invisible_captcha',
				'hide_label' => true,
				'values'     => array(
					array(
						'value'      => "true",
						'label'      => __( 'Invisible reCAPTCHA', Forminator::DOMAIN ),
						'labelSmall' => "true",
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
			//'field_label'  => __( 'Are you a human?', Forminator::DOMAIN )
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
	 * Field admin markup
	 *
	 * @since 1.0
	 * @return string
	 */
	public function admin_html() {
		$path = forminator_plugin_url() . 'assets/img/google-recaptcha.png';
		$retina = forminator_plugin_url() . 'assets/img/google-recaptcha@2x.png';

		return '{[ if( field.field_label !== "" ) { ]}
			<label class="sui-label">{{ encodeHtmlEntity( field.field_label ) }}{[ if( field.required == "true" ) { ]} *{[ } ]}</label>
		{[ } ]}
		<img src="' . $path . '" srcset="' . $path . ' 1x, ' . $retina . ', 2x" class="sui-image">';
	}

	public function is_invisible_recaptcha( $field ) {
		$is_invisible = self::get_property( 'invisible_captcha', $field );
		$is_invisible = filter_var( $is_invisible, FILTER_VALIDATE_BOOLEAN );

		return $is_invisible;
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
		$key           = get_option( "forminator_captcha_key", false );
		$theme         = get_option( "forminator_captcha_theme", false );
		$captcha_size  = 'normal';
		$captcha_class = 'forminator-g-recaptcha';

		if ( $this->is_invisible_recaptcha( $field ) ) {
			$captcha_size  = 'invisible';
			$captcha_class .= ' recaptcha-invisible';
		}

		// dont use .g-recaptcha class as it will rendered automatically when other plugin load recaptcha with default render
		return sprintf( '<div class="%s" data-theme="%s" data-sitekey="%s" data-size="%s"></div>', $captcha_class, $theme, $key, $captcha_size );
	}


	/**
	 * Mark Captcha unavailable when captcha key not available
	 *
	 * @since 1.0.3
	 *
	 * @param $field
	 *
	 * @return bool
	 */
	public function is_available( $field ) {
		$key = get_option( "forminator_captcha_key", false );

		if ( ! $key ) {
			return false;
		}

		return true;
	}
}
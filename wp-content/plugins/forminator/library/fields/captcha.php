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
				'id' => 'field-label',
				'type' => 'Text',
				'name' => 'field_label',
				'hide_label' => false,
				'label'	=> __( 'Field label', Forminator::DOMAIN )
			),
			array(
				'id' => 'invisible-captcha',
				'type' => 'Toggle',
				'name' => 'invisible_captcha',
				'hide_label' => true,
				'values' => array(
					array(
						'value' => "true",
						'label' => __( 'Invisible reCAPTCHA', Forminator::DOMAIN ),
						'labelSmall' => "true"
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
	 *
	 * @return mixed
	 */
	public function markup( $field ) {
		$key   = get_option( "forminator_captcha_key", false );
		$theme = get_option( "forminator_captcha_theme", false );

		$captcha_size = 'normal';
		$captcha_class = 'forminator-g-recaptcha';
		if ( $this->is_invisible_recaptcha( $field ) ) {
			$captcha_size = 'invisible';
			$captcha_class .= ' recaptcha-invisible';
		}

		// dont use .g-recaptcha class as it will rendered automatically when other plugin load recaptcha with default render
		return sprintf( '<div class="%s" data-theme="%s" data-sitekey="%s" data-size="%s"></div>', $captcha_class, $theme, $key, $captcha_size );
	}
}
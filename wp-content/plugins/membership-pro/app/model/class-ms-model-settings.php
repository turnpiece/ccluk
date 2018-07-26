<?php
/**
 * Settings model.
 *
 * Singleton. Persisted by parent class MS_Model_Option.
 *
 * @since  1.0.0
 *
 * @package Membership2
 * @subpackage Model
 */
class MS_Model_Settings extends MS_Model_Option {

	/**
	 * Singleton instance.
	 *
	 * @since  1.0.0
	 *
	 * @staticvar MS_Model_Settings
	 */
	public static $instance;

	/**
	 * Protection Message Type constants.
	 *
	 * User can set 3 different protection message defaults:
	 * - Whole page is protected
	 * - Shortcode content is protected
	 * - Read-more content is protected
	 *
	 * @since  1.0.0
	 */
	const PROTECTION_MSG_CONTENT 	= 'content';
	const PROTECTION_MSG_SHORTCODE 	= 'shortcode';
	const PROTECTION_MSG_MORE_TAG 	= 'more_tag';

	/**
	 * ID of the model object.
	 *
	 * @since  1.0.0
	 *
	 * @var int
	 */
	protected $id = 'ms_plugin_settings';

	/**
	 * Model name.
	 *
	 * @since  1.0.0
	 *
	 * @var string
	 */
	protected $name = 'Plugin settings';

	/**
	 * Current db version.
	 *
	 * @since  1.0.0
	 *
	 * @var string
	 */
	protected $version = '';

	/**
	 * Plugin enabled status indicator.
	 *
	 * @since  1.0.0
	 *
	 * @var boolean
	 */
	protected $plugin_enabled = false;

	/**
	 * Initial setup status indicator.
	 *
	 * Wizard mode.
	 *
	 * @since  1.0.0
	 *
	 * @var boolean
	 */
	protected $initial_setup = true;

	/**
	 * Is set to false when the first membership was created.
	 *
	 * @since  1.0.0
	 *
	 * @var boolean
	 */
	protected $is_first_membership = true;

	/**
	 * Is set to false when the first paid membership was created.
	 *
	 * @since  1.0.0
	 *
	 * @var boolean
	 */
	protected $is_first_paid_membership = true;

	/**
	 * Wizard step tracker.
	 *
	 * Indicate which step of the wizard.
	 *
	 * @since  1.0.0
	 *
	 * @var boolean
	 */
	protected $wizard_step = '';

	/**
	 * Hide Membership2 Menu pointer indicator.
	 *
	 * Wizard mode.
	 *
	 * @since  1.0.0
	 *
	 * @var boolean
	 */
	protected $hide_wizard_pointer = false;

	/**
	 * Hide Toolbar for non admin users indicator.
	 *
	 * Wizard mode.
	 *
	 * @since  1.0.0
	 *
	 * @var boolean
	 */
	protected $hide_admin_bar = true;


	/**
	 * Enable use of cron when performing backen actions
	 *
	 * Wizard mode.
	 *
	 * @since  1.0.0
	 *
	 * @var boolean
	 */
	protected $enable_cron_use = true;


	/**
	 * Enable use of query cache
	 *
	 * Settings
	 *
	 * @since  1.1.3
	 *
	 * @var boolean
	 */
	protected $enable_query_cache = false;


	/**
	 * Force a single payment gateway as the default gateway
	 *
	 * Settings
	 *
	 * @since  1.1.3
	 *
	 * @var boolean
	 */
	protected $force_single_gateway = false;


	/**
	 * Registration verification
	 *
	 * Settings
	 *
	 * @since  1.1.3
	 *
	 * @var boolean
	 */
	protected $force_registration_verification = false;

	/**
	 * The currency used in the plugin.
	 *
	 * @since  1.0.0
	 *
	 * @var string
	 */
	protected $currency = 'USD';

	/**
	 * The name used in the invoices.
	 *
	 * @since  1.0.0
	 *
	 * @var string
	 */
	protected $invoice_sender_name = '';

	/**
	 * Global payments already set indicator.
	 *
	 * @since  1.0.0
	 *
	 * @var boolean
	 */
	protected $is_global_payments_set = false;

	/**
	 * Protection Messages.
	 *
	 * @since  1.0.0
	 *
	 * @var array
	 */
	protected $protection_messages = array();

	/**
	 * How menu items are protected.
	 *
	 * @since  1.0.0
	 *
	 * @var string
	 */
	protected $menu_protection = 'item';

	/**
	 * Media / Downloads settings.
	 *
	 * @since  1.0.0
	 *
	 * @var array
	 */
	protected $downloads = array(
		'protection_type' 	=> MS_Rule_Media_Model::PROTECTION_TYPE_COMPLETE,
		'masked_url' 		=> 'downloads',
		'direct_access' 	=> array( 'jpg', 'jpeg', 'png', 'gif', 'mp3', 'ogg' ),
		'application_server'=> ''
	);

	/**
	 * Invoice Settings
	 *
	 * @since 1.1.3
	 *
	 * @var array
	 */
	protected $invoice = array(
		'sequence_type' 	=> MS_Addon_Invoice::DEFAULT_SEQUENCE,
		'invoice_prefix'	=> 'MS-',
	);

	/**
	 * Global payments already set indicator.
	 *
	 * @since  1.0.4
	 *
	 * @var boolean
	 */
	 protected $is_advanced_media_protection = false;

	/**
	 * Default WP Rest settings
	 *
	 * @since 1.0.4
	 *
	 * @var array
	 */
	protected $wprest = array(
		'api_namespace' => MS_Addon_WPRest::API_NAMESPACE,
		'api_passkey' 	=> '123456789'
	);

	/**
	 * Import flags
	 *
	 * When data was imported a flag can be set here to remember that some
	 * members come from there.
	 *
	 * @since  1.0.0
	 *
	 * @var array
	 */
	protected $import = array();

	/**
	 * Special view.
	 *
	 * This defines a special view that is displayed when the plugin is loaded
	 * instead of the default plugin page that would be displayed.
	 *
	 * @since  1.0.0
	 *
	 * @var string
	 */
	protected $special_view = false;

	/**
	 * Get protection message types.
	 *
	 * @since  1.0.0
	 *
	 * @return string[] The available protection message types.
	 */
	public static function get_protection_msg_types() {
		$types = array(
			self::PROTECTION_MSG_CONTENT,
			self::PROTECTION_MSG_SHORTCODE,
			self::PROTECTION_MSG_MORE_TAG,
		);

		return apply_filters( 'ms_model_settings_get_protection_msg_types', $types );
	}

	/**
	 * Validate protection message type.
	 *
	 * @since  1.0.0
	 *
	 * @param string $type The protection message type to validate.
	 * @return boolean True if valid.
	 */
	public static function is_valid_protection_msg_type( $type ) {
		$types = self::get_protection_msg_types();

		return apply_filters(
			'ms_model_settings_is_valid_protection_msg_type',
			in_array( $type, $types )
		);
	}

	/**
	 * Set protection message type.
	 *
	 * @since  1.0.0
	 *
	 * @param string $type The protection message type.
	 * @param string $msg The protection message.
	 * @param  MS_Model_Membership $membership Optional. If defined the
	 *         protection message specific for this membership will be set.
	 */
	public function set_protection_message( $type, $msg, $membership = null ) {
		if ( self::is_valid_protection_msg_type( $type ) ) {
			$key = $type;

			if ( $membership ) {
				if ( $membership instanceof MS_Model_Membership ) {
					$key .= '_' . $membership->id;
				} elseif ( is_scalar( $membership ) ) {
					$key .= '_' . $membership;
				}
			}

			if ( null === $msg ) {
				unset( $this->protection_messages[ $key ] );
			} else {
				$this->protection_messages[ $key ] = stripslashes( wp_kses_post( $msg ) );
			}
		}

		do_action(
			'ms_model_settings_set_protection_message',
			$type,
			$msg,
			$membership,
			$this
		);
	}

	/**
	 * Get protection message type.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $type The protection message type.
	 * @param  MS_Model_Membership $membership Optional. If defined the
	 *         protection message specific for this membership will be returned.
	 * @param  bool $found This is set to true if the specified membership did
	 *         override this message.
	 * @return string $msg The protection message.
	 */
	public function get_protection_message( $type, $membership = null, &$found = null ) {
		$msg 	= '';
		$found 	= false;
		if ( self::is_valid_protection_msg_type( $type ) ) {
			$key = $type;

			if ( $membership ) {
				if ( $membership instanceof MS_Model_Membership ) {
					$key_override 	= $key . '_' . $membership->id;
				} elseif ( is_scalar( $membership ) ) {
					$key_override 	= $key . '_' . $membership;
				} else {
					$key_override 	= $key;
				}
				if ( isset( $this->protection_messages[ $key_override ] ) ) {
					$key 			= $key_override;
					$found 			= true;
				}
			}

			if ( isset( $this->protection_messages[ $key ] ) ) {
				$msg = $this->protection_messages[ $key ];
			} else {
				$msg = __( 'The content you are trying to access is only available to members. Sorry.', 'membership2' );
			}
		}

		return apply_filters(
			'ms_model_settings_get_protection_message',
			$msg,
			$type,
			$this
		);
	}

	/**
	 * Activates a special view.
	 * Next time the plugin is loaded this special view is displayed.
	 *
	 * This should be set in MS_Model_Upgrade (or earlier) to ensure the special
	 * view is displayed on the current page request.
	 *
	 * @since  1.0.0
	 * @param  string $name Name of the view to display.
	 */
	static public function set_special_view( $name ) {
		$settings = MS_Factory::load( 'MS_Model_Settings' );
		$settings->special_view = $name;
		$settings->save();
	}

	/**
	 * Returns the currently set special view.
	 *
	 * @since  1.0.0
	 * @return string Name of the view to display.
	 */
	static public function get_special_view() {
		$settings 	= MS_Factory::load( 'MS_Model_Settings' );
		$view 		= $settings->special_view;
		return $view;
	}

	/**
	 * Deactivates the special view.
	 *
	 * @since  1.0.0
	 */
	static public function reset_special_view() {
		$settings = MS_Factory::load( 'MS_Model_Settings' );
		$settings->special_view = false;
		$settings->save();
	}

	/**
	 * Get available currencies.
	 *
	 * @since  1.0.0
	 *
	 * @return array {
	 *     @type string $currency The currency.
	 *     @type string $title The currency title.
	 * }
	 */
	public static function get_currencies() {
		static $Currencies = null;

		if ( null === $Currencies ) {
			$Currencies = apply_filters(
				'ms_model_settings_get_currencies',
				array(
					'AUD' => __( 'AUD - Australian Dollar', 'membership2' ),
					'BRL' => __( 'BRL - Brazilian Real', 'membership2' ),
					'CAD' => __( 'CAD - Canadian Dollar', 'membership2' ),
					'CHF' => __( 'CHF - Swiss Franc', 'membership2' ),
					'CZK' => __( 'CZK - Czech Koruna', 'membership2' ),
					'DKK' => __( 'DKK - Danish Krone', 'membership2' ),
					'EUR' => __( 'EUR - Euro', 'membership2' ),
					'GBP' => __( 'GBP - Pound Sterling', 'membership2' ),
					'HKD' => __( 'HKD - Hong Kong Dollar', 'membership2' ),
					'HUF' => __( 'HUF - Hungarian Forint', 'membership2' ),
					'ILS' => __( 'ILS - Israeli Shekel', 'membership2' ),
					'JPY' => __( 'JPY - Japanese Yen', 'membership2' ),
					'MYR' => __( 'MYR - Malaysian Ringgits', 'membership2' ),
					'MXN' => __( 'MXN - Mexican Peso', 'membership2' ),
					'NOK' => __( 'NOK - Norwegian Krone', 'membership2' ),
					'NZD' => __( 'NZD - New Zealand Dollar', 'membership2' ),
					'PHP' => __( 'PHP - Philippine Pesos', 'membership2' ),
					'PLN' => __( 'PLN - Polish Zloty', 'membership2' ),
					'RUB' => __( 'RUB - Russian Ruble', 'membership2' ),
					'SEK' => __( 'SEK - Swedish Krona', 'membership2' ),
					'SGD' => __( 'SGD - Singapore Dollar', 'membership2' ),
					'TWD' => __( 'TWD - Taiwan New Dollars', 'membership2' ),
					'THB' => __( 'THB - Thai Baht', 'membership2' ),
					'USD' => __( 'USD - U.S. Dollar', 'membership2' ),
					'ZAR' => __( 'ZAR - South African Rand', 'membership2' ),
				)
			);
		}

		return $Currencies;
	}

	/**
	*
	* Code removed, edited by Panos
	*
	* Checks whether a membership has enabled a "*TYPE* protection message"
	*
	* @Since 1.0.3.5
	*
	* @param string $type The type of protection message.
	* @param MS_Model_Membership $membership
	*
	* @return bool
	*/
	//public function membership_has_protection_type( $type, $membership ){
	//
	//	if( ! $type || ! $membership ) return false;
	//
	//	return isset( $this->protection_messages[ $type . '_' . $membership->id ] );
	//
	//}

	/**
	 * Set specific property.
	 *
	 * @since  1.0.0
	 *
	 * @param string $property The name of a property to associate.
	 * @param mixed $value The value of a property.
	 */
	public function __set( $property, $value ) {
		if ( property_exists( $this, $property ) ) {
			switch ( $property ) {
				case 'currency':
					if ( array_key_exists( $value, self::get_currencies() ) ) {
						$this->$property = $value;
					}
					break;

				case 'invoice_sender_name':
					$this->$property = sanitize_text_field( $value );
					break;

				case 'plugin_enabled':
				case 'initial_setup':
				case 'is_first_membership':
				case 'enable_cron_use':
				case 'enable_query_cache':
				case 'force_single_gateway':
				case 'hide_admin_bar':
					$this->$property = mslib3()->is_true( $value );
					break;

				case 'force_registration_verification' :
					$is_enabled 	= mslib3()->is_true( $value );
					$comm 			= MS_Model_Communication::get_communication( MS_Model_Communication::COMM_TYPE_REGISTRATION_VERIFY );
					$comm->enabled 	= $is_enabled;
					$comm->save();
					$this->$property = mslib3()->is_true( $is_enabled );
					break;

				default:
					$this->$property = $value;
					break;
			}
		} else {
			switch ( $property ) {
				case 'protection_type':
					if ( MS_Rule_Media_Model::is_valid_protection_type( $value ) ) {
						$this->downloads['protection_type'] = $value;
					}
					break;

				case 'masked_url':
					$this->downloads['masked_url'] = sanitize_text_field( $value );
					break;

				case 'advanced_media_protection':
					$create_htaccess = mslib3()->is_true( $value );
					if ( $create_htaccess ) {
						MS_Model_Addon::toggle_media_htaccess( $this );
					} else {
						MS_Helper_Media::clear_htaccess();
					}
					$this->is_advanced_media_protection = $create_htaccess;
					break;

				case 'direct_access':
					$this->downloads['direct_access'] = explode( ",", sanitize_text_field( $value ) );
					break;
				case 'application_server':
					$this->downloads['application_server'] = sanitize_text_field( $value );
					break;

				case 'sequence_type':
					$this->invoice['sequence_type'] = sanitize_text_field( $value );
					break;

				case 'invoice_prefix':
					$this->invoice['invoice_prefix'] = sanitize_text_field( $value );
					break;


				case 'api_namespace' :
					$this->wprest['api_namespace'] = sanitize_text_field( $value );
					break;

				case 'api_passkey' :
					$this->wprest['api_passkey'] = sanitize_text_field( $value );
					break;

			}
		}
	}

	/**
	 * Returns a specific property.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $property The name of a property.
	 * @return mixed $value The value of a property.
	 */
	public function __get( $property ) {
		$value = null;

		switch ( $property ) {
			case 'menu_protection':
				if ( ! MS_Model_Addon::is_enabled( MS_Model_Addon::ADDON_ADV_MENUS ) ) {
					$value = 'item';
				} else {
					$value = $this->menu_protection;
				}
				break;

			default:
				if ( property_exists( $this, $property ) ) {
					$value = $this->$property;
				} else {
					switch ( $property ) {
						case 'currency_symbol':
							// Same translation table in:
							// -> ms-view-membership-setup-payment.js
							$symbol = $this->currency;
							switch ( $symbol ) {
								case 'USD': $symbol = '$'; break;
								case 'EUR': $symbol = '€'; break;
								case 'JPY': $symbol = '¥'; break;
							}
							$value = $symbol;
					}
				}
		}

		return apply_filters( 'ms_model_settings__get', $value, $property, $this );
	}

	/**
	 * Check if property isset.
	 *
	 * @since  1.0.0
	 * @internal
	 *
	 * @param string $property The name of a property.
	 * @return mixed Returns true/false.
	 */
	public function __isset( $property ) {
		return isset($this->$property);
	}
}
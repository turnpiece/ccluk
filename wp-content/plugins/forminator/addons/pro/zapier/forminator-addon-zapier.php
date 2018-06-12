<?php

require_once dirname( __FILE__ ) . '/forminator-addon-zapier-exception.php';
require_once dirname( __FILE__ ) . '/lib/class-wp-zapier-api.php';

/**
 * Class Forminator_Addon_Zapier
 * Zapier Addon Main Class
 *
 * @since 1.0 Zapier Addon
 */
final class Forminator_Addon_Zapier extends Forminator_Addon_Abstract {

	/**
	 * @var self|null
	 */
	private static $_instance = null;

	protected $_slug                   = 'zapier';
	protected $_version                = FORMINATOR_ADDON_ZAPIER_VERSION;
	protected $_min_forminator_version = '1.1';
	protected $_short_title            = 'Zapier';
	protected $_title                  = 'Zapier';
	protected $_url                    = 'https://premium.wpmudev.org';
	protected $_full_path              = __FILE__;

	protected $_form_settings = 'Forminator_Addon_Zapier_Form_Settings';
	protected $_form_hooks    = 'Forminator_Addon_Zapier_Form_Hooks';

	/**
	 * Forminator_Addon_Zapier constructor.
	 *
	 * @since 1.0 Zapier Addon
	 */
	public function __construct() {
		// late init to allow translation
		$this->_description                = __( 'Make your form Zap-able', Forminator::DOMAIN );
		$this->_activation_error_message   = __( 'Sorry but we failed to activate Zapier Integration, don\'t hesitate to contact us', Forminator::DOMAIN );
		$this->_deactivation_error_message = __( 'Sorry but we failed to deactivate Zapier Integration, please try again', Forminator::DOMAIN );

		$this->_update_settings_error_message = __(
			'Sorry, we are failed to update settings, please check your form and try again',
			Forminator::DOMAIN
		);

		$this->_icon     = forminator_addon_zapier_assets_url() . 'icons/zapier.png';
		$this->_icon_x2  = forminator_addon_zapier_assets_url() . 'icons/zapier@2x.png';
		$this->_image    = forminator_addon_zapier_assets_url() . 'img/zapier.png';
		$this->_image_x2 = forminator_addon_zapier_assets_url() . 'img/zapier@2x.png';
	}

	/**
	 * Get Instance
	 *
	 * @since 1.0 Zapier Addon
	 * @return self|null
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Override on is_connected
	 *
	 * @since 1.0 Zapier Addon
	 *
	 * @return bool
	 */
	public function is_connected() {
		try {
			if ( ! $this->is_active() ) {
				// Force Activate when its not yet activate
				$activated = Forminator_Addon_Loader::get_instance()->activate_addon( $this->get_slug() );
				if ( ! $activated ) {
					throw new Forminator_Addon_Zapier_Exception( Forminator_Addon_Loader::get_instance()->get_last_error_message() );
				}
			}

			// Always globally active / connected
			$is_connected = true;

		} catch ( Forminator_Addon_Zapier_Exception $e ) {
			forminator_addon_maybe_log( __METHOD__, $e->getMessage() );
			$is_connected = false;
		}

		/**
		 * Filter connected status of zapier
		 *
		 * @since 1.1
		 *
		 * @param bool $is_connected
		 */
		$is_connected = apply_filters( 'forminator_addon_zapier_is_connected', $is_connected );

		return $is_connected;
	}

	/**
	 * Check if zapier is connected with current form
	 *
	 * @since 1.0 Zapier Addon
	 *
	 * @param $form_id
	 *
	 * @return bool
	 */
	public function is_form_connected( $form_id ) {
		try {
			$form_settings_instance = null;
			if ( ! $this->is_connected() ) {
				throw new Forminator_Addon_Zapier_Exception( __( 'Zapier is not connected', Forminator::DOMAIN ) );
			}

			$form_settings_instance = $this->get_addon_form_settings( $form_id );
			if ( ! $form_settings_instance instanceof Forminator_Addon_Zapier_Form_Settings ) {
				throw new Forminator_Addon_Zapier_Exception( __( 'Invalid Form Settings of Zapier', Forminator::DOMAIN ) );
			}

			// Mark as active when there is at least one active connection
			if ( false === $form_settings_instance->find_one_active_connection() ) {
				throw new Forminator_Addon_Zapier_Exception( __( 'No active Zapier connection found in this form', Forminator::DOMAIN ) );
			}

			$is_form_connected = true;

		} catch ( Forminator_Addon_Zapier_Exception $e ) {
			$is_form_connected = false;
			forminator_addon_maybe_log( __METHOD__, $e->getMessage() );
		}

		/**
		 * Filter connected status of zapier with the form
		 *
		 * @since 1.1
		 *
		 * @param bool                                       $is_form_connected
		 * @param int                                        $form_id                Current Form ID
		 * @param Forminator_Addon_Zapier_Form_Settings|null $form_settings_instance Instance of form settings, or null when unavailable
		 *
		 */
		$is_form_connected = apply_filters( 'forminator_addon_mailchimp_is_form_connected', $is_form_connected, $form_id, $form_settings_instance );

		return $is_form_connected;
	}

	/**
	 * Override settings available,
	 *
	 * @since 1.0 Zapier Addon
	 * @return bool
	 */
	public function is_settings_available() {
		// No Global Settings for Zapier
		return false;
	}


	/**
	 * Get Zapier API
	 *
	 * @since 1.0 Zapier Addon
	 *
	 * @param string $endpoint
	 *
	 * @return Forminator_Addon_Zapier_Wp_Api|null
	 * @throws Forminator_Addon_Zapier_Wp_Api_Exception
	 */
	public function get_api( $endpoint ) {
		return Forminator_Addon_Zapier_Wp_Api::get_instance( $endpoint );
	}

	/**
	 * Flag show full log on entries
	 *
	 * @since 1.0 Zapier Addon
	 * @return bool
	 */
	public static function is_show_full_log() {
		if ( defined( 'FORMINATOR_ADDON_ZAPIER_SHOW_FULL_LOG' ) && FORMINATOR_ADDON_ZAPIER_SHOW_FULL_LOG ) {
			return true;
		}

		return false;
	}

	/**
	 * Allow multiple connection on one form
	 *
	 * @since 1.0 Zapier Addon
	 * @return bool
	 */
	public function is_allow_multi_on_form() {
		return true;
	}

}
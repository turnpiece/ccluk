<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Advanced_Tools\Model;

use Hammer\Helper\WP_Helper;

class Mask_Settings extends \Hammer\WP\Settings {
	public $maskUrl = '';
	public $redirectTraffic = false;
	public $redirectTrafficUrl = '';
	public $enabled = false;
	private static $_instance;

	public function __construct( $id, $is_multi ) {
		parent::__construct( $id, $is_multi );
	}

	/**
	 * @return Mask_Settings
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			$class           = new Mask_Settings( 'wd_masking_login_settings', WP_Helper::is_network_activate( wp_defender()->plugin_slug ) );
			self::$_instance = $class;
		}

		return self::$_instance;
	}

	/**
	 * @return bool
	 */
	public function isEnabled() {
		return $this->enabled && ( strlen( trim( $this->maskUrl ) ) > 0 );
	}

	public function isRedirect() {
		return $this->redirectTraffic && ( strlen( trim( $this->redirectTrafficUrl ) ) > 0 );
	}
}
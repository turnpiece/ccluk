<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Setting\Model;

use Hammer\Helper\WP_Helper;

class Settings extends \Hammer\WP\Settings {
	private static $_instance;

	public $translate;
	public $usage_tracking = 0;
	public $uninstall_data = 'remove';
	public $uninstall_settings = 'reset';
	public $high_contrast_mode = 0;

	public function behaviors() {
		return array(
			'utils' => '\WP_Defender\Behavior\Utils'
		);
	}

	public function __construct( $id, $is_multi ) {
		$site_locale = get_locale();

		if ( 'en_US' === $site_locale ) {
			$site_language = 'English';
		} else {
			require_once ABSPATH . 'wp-admin/includes/translation-install.php';
			$translations  = wp_get_available_translations();
			$site_language = $translations[ $site_locale ]['native_name'];
		}
		$this->translate = $site_language;

		parent::__construct( $id, $is_multi );
	}

	/**
	 * @return Settings
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			$class           = new Settings( 'wd_main_settings', WP_Helper::is_network_activate( wp_defender()->plugin_slug ) );
			self::$_instance = $class;
		}

		return self::$_instance;
	}
}
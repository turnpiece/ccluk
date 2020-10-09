<?php
/**
 * The GTM admin core class.
 *
 * @link    http://premium.wpmudev.org
 * @since   3.3.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Modules\Google_Tag_Manager
 */

namespace Beehive\Core\Modules\Google_Tag_Manager;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Data\Locale;
use Beehive\Core\Controllers\Menu;
use Beehive\Core\Utils\Abstracts\Base;
use Beehive\Core\Controllers\Capability;

/**
 * Class Admin
 *
 * @package Beehive\Core\Modules\Google_Analytics
 */
class Admin extends Base {

	/**
	 * Register all the hooks related to module.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	public function init() {
		// Settings menu in dashboard.
		add_action( 'beehive_admin_menu', array( $this, 'settings_menu' ) );

		// Add i18n strings for the locale.
		add_filter( 'beehive_i18n_get_locale_scripts', array( $this, 'setup_i18n' ), 10, 2 );

		// Register assets.
		add_filter( 'beehive_assets_get_scripts', array( $this, 'get_scripts' ), 10, 2 );
		add_filter( 'beehive_assets_get_styles', array( $this, 'get_styles' ), 10, 2 );

		// Admin admin class to our page.
		add_filter( 'beehive_admin_body_classes_is_plugin_admin', array( $this, 'admin_body_class' ) );
	}

	/**
	 * Register admin submenu for the settings page.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	public function settings_menu() {
		// Add accounts page.
		add_submenu_page(
			Menu::SLUG,
			__( 'Google Tag Manager Settings', 'ga_trans' ),
			__( 'Google Tag Manager', 'ga_trans' ),
			Capability::SETTINGS_CAP,
			'beehive-gtm-settings',
			array( Views\Admin::instance(), 'settings' )
		);
	}

	/**
	 * Add localized strings that can be used in JavaScript.
	 *
	 * @param array  $strings Existing strings.
	 * @param string $script  Script name.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	public function setup_i18n( $strings, $script ) {
		switch ( $script ) {
			case 'beehive-tag-manager':
				// Add strings.
				$strings = array_merge_recursive(
					$strings,
					Locale::welcome(),
					Data\Locale::common(),
					Data\Locale::account(),
					Data\Locale::settings()
				);
				break;
		}

		return $strings;
	}

	/**
	 * Get the styles list to register.
	 *
	 * @param array $styles Styles list.
	 * @param bool  $admin  Is admin assets?.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	public function get_styles( $styles, $admin ) {
		if ( $admin ) {
			// Post statistics.
			$styles['beehive-tag-manager'] = array(
				'src' => 'gtm-settings.min.css',
			);
		}

		return $styles;
	}

	/**
	 * Get the scripts list to register.
	 *
	 * @param array $scripts Scripts list.
	 * @param bool  $admin   Is admin assets?.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	public function get_scripts( $scripts, $admin ) {
		if ( $admin ) {
			// Post statistics.
			$scripts['beehive-tag-manager'] = array(
				'src'  => 'gtm-settings.min.js',
				'deps' => array( 'beehive-sui-common', 'beehive-vendors', 'beehive-common' ),
			);
		}

		return $scripts;
	}

	/**
	 * Add Beehive admin body class to plugin statistics page.
	 *
	 * @param bool $include Should add class.
	 *
	 * @since 3.3.0
	 *
	 * @return bool
	 */
	public function admin_body_class( $include ) {
		// Enqueue stats widget assets.
		if ( Helper::is_gtm_settings() ) {
			$include = true;
		}

		return $include;
	}
}
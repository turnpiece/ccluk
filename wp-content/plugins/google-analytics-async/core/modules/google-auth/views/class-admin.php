<?php
/**
 * The admin view class of the plugin.
 *
 * @link    http://premium.wpmudev.org
 * @since   3.2.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Modules\Google_Auth\Views
 */

namespace Beehive\Core\Modules\Google_Auth\Views;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Helpers\General;
use Beehive\Core\Modules\Google_Auth;
use Beehive\Core\Utils\Abstracts\View;
use Beehive\Core\Modules\Google_Analytics;

/**
 * Class Admin
 *
 * @package Beehive\Core\Modules\Google_Auth\Views
 */
class Admin extends View {

	/**
	 * Initialize the class by registering hooks.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function init() {
		// Localization.
		add_filter( 'beehive_assets_scripts_common_localize_vars', array( $this, 'common_vars' ) );
		add_filter( 'beehive_assets_scripts_localize_vars_beehive-accounts', array( $this, 'google_vars' ) );
		add_filter( 'beehive_assets_scripts_localize_vars_beehive-dashboard', array( $this, 'google_vars' ) );
		add_filter( 'beehive_assets_scripts_localize_vars_beehive-settings', array( $this, 'google_vars' ) );
	}

	/**
	 * Get the vars which should be available in all scripts for the script.
	 *
	 * Include only the required data. You can use another filter to add custom
	 * data to different scripts.
	 *
	 * @param array $vars Vars.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	public function common_vars( $vars ) {
		// Only for admins.
		if ( ! is_admin() ) {
			return $vars;
		}

		// Google specific vars.
		if ( ! isset( $vars['google'] ) ) {
			$vars['google'] = array();
		}

		// Check if the site is logged in with Google.
		$vars['google']['logged_in'] = Google_Auth\Helper::instance()->is_logged_in( $this->is_network() );

		// Get Google profiles.
		if ( Google_Auth\Helper::instance()->is_logged_in( $this->is_network() ) ) {
			$vars['google']['profiles'] = Google_Analytics\Data::instance()->profiles_list( $this->is_network() );
		} else {
			$vars['google']['profiles'] = array();
		}

		return $vars;
	}

	/**
	 * Setup Google auth related vars for scripts.
	 *
	 * @param array $vars Existing vars.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	public function google_vars( $vars ) {
		$google_helper = Google_Auth\Helper::instance();

		// Google specific vars.
		if ( ! isset( $vars['google'] ) ) {
			$vars['google'] = array();
		}

		if ( is_multisite() ) {
			// Network admin.
			if ( $this->is_network() ) {
				// Get credentials used for login.
				$creds = Google_Auth\Auth::instance()->get_default_credential( true );
				// Render API settings template for network admin.
				$vars['google']['login_url'] = $google_helper->auth_url( true, true );
				$vars['google']['client_id'] = $creds['client_id'];
			} else {
				// Logged in network admin.
				$network_logged_in = $google_helper->is_logged_in( true );
				// Plugin active networkwide.
				$networkwide = General::is_networkwide();
				// API creds setup in network admin.
				$network_setup = $google_helper->is_setup( true );
				// Network login method.
				$login_method = $google_helper->login_method( true );

				// Get login url.
				if ( $network_logged_in && $networkwide && $network_setup && 'api' === $login_method ) {
					$login_url = $google_helper->auth_url( true, false, true );
				} else {
					$login_url                   = $google_helper->auth_url( false, true );
					$creds                       = Google_Auth\Auth::instance()->get_default_credential();
					$vars['google']['client_id'] = $creds['client_id'];
				}

				$vars['google']['login_url']            = $login_url;
				$vars['google']['network_setup']        = $network_setup;
				$vars['google']['network_logged_in']    = $network_logged_in;
				$vars['google']['network_login_method'] = beehive_analytics()->settings->get( 'method', 'google_login', true );
			}
		} else {
			// Render API settings template for single site admin.
			$vars['google']['login_url'] = $google_helper->auth_url( false, true );
			// Get credentials used for login.
			$creds = Google_Auth\Auth::instance()->get_default_credential();
			// Client ID.
			$vars['google']['client_id'] = $creds['client_id'];
		}

		return $vars;
	}
}
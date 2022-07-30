<?php
/**
 * The Google core class.
 *
 * @link    http://wpmudev.com
 * @since   3.2.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Modules\Google_Analytics
 */

namespace Beehive\Core\Modules\Google_Analytics;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Helpers\Permission;
use Beehive\Google\Service\Analytics as Analytics_Service;
use Beehive\Core\Utils\Abstracts\Base;
use Beehive\Core\Modules\Google_Analytics\Views\Stats;
use Beehive\Core\Modules\Google_Analytics\Views\Tracking;

/**
 * Class Analytics
 *
 * @package Beehive\Core\Modules\Google_Analytics
 */
class Analytics extends Base {

	/**
	 * Register all the hooks related to module.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function init() {
		// Add Google Analytics auth scopes.
		add_filter( 'beehive_google_auth_scopes', array( $this, 'auth_scopes' ) );

		// Setup profiles after authentication.
		add_action( 'beehive_google_auth_completed', array( $this, 'setup_profiles' ), 10, 3 );

		// Stats menu is required only when logged in.
		if ( Helper::instance()->can_get_stats( $this->is_network() ) ) {
			// Setup widgets.
			add_action( 'widgets_init', array( $this, 'widgets' ) );
		}

		// Initialize sub classes.
		Admin::instance()->init();
		Stats::instance()->init();
		Tracking::instance()->init();

		// Rest API for UA.
		Endpoints\V1\Data::instance();
		Endpoints\V1\Stats::instance();
		// Rest API for GA4.
		Endpoints\V2\Data::instance();
		Endpoints\V2\Stats::instance();
	}

	/**
	 * Add Google Analytics scope for authentication.
	 *
	 * @param array $scopes Auth scopes.
	 *
	 * @since 3.2.0
	 *
	 * @return array $scopes
	 */
	public function auth_scopes( $scopes = array() ) {
		// Add Google Analytics auth scope.
		$scopes[] = Analytics_Service::ANALYTICS_READONLY;

		return $scopes;
	}

	/**
	 * Setup widgets for Google Analytics.
	 *
	 * Register all widgets with WordPress.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function widgets() {
		// Make sure the user has capability.
		if ( Permission::user_can( 'analytics' ) || ! is_admin() ) {
			// Most popular contents.
			register_widget( Widgets\Popular::instance() );
		}
	}


	/**
	 * Update the available list of GA profiles after the authentication.
	 *
	 * We are prefetching this so that the users won't see empty list.
	 *
	 * @param bool $success Is success or fail?.
	 * @param bool $default Did we connect using default credentials?.
	 * @param bool $network Network flag.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function setup_profiles( $success, $default, $network ) {
		// Fetch the list of profiles.
		if ( $success ) {
			Data::instance()->streams( $network, true );
			Data::instance()->profiles_list( $network, true );
		}
	}
}
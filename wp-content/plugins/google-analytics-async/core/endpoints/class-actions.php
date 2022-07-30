<?php
/**
 * Common action functionality REST endpoint.
 *
 * @link       http://wpmudev.com
 * @since      3.2.0
 *
 * @author     Joel James <joel@incsub.com>
 * @package    Beehive\Core\Endpoints
 */

namespace Beehive\Core\Endpoints;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use Beehive\Core\Helpers\Cache;
use Beehive\Core\Controllers\Cleanup;
use Beehive\Core\Utils\Abstracts\Endpoint;

/**
 * Class Actions
 *
 * @package Beehive\Core\Endpoints
 */
class Actions extends Endpoint {

	/**
	 * API endpoint for the current endpoint.
	 *
	 * @since 3.2.4
	 *
	 * @var string $endpoint
	 */
	private $endpoint = '/actions';

	/**
	 * Register the routes for handling actions functionality.
	 *
	 * All custom routes for the stats functionality should be registered
	 * here using register_rest_route() function.
	 *
	 * @since 3.2.4
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->get_namespace(),
			$this->endpoint,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'action' ),
					'permission_callback' => array( $this, 'settings_permission' ),
					'args'                => array(
						'action'  => array(
							'required'    => true,
							'description' => __( 'The action type to perform.', 'ga_trans' ),
							'type'        => 'string',
							'enum'        => array(
								'refresh',
								'dismiss_onboarding',
								'reset_settings',
							),
						),
						'network' => array(
							'required'    => false,
							'description' => __( 'The network flag.', 'ga_trans' ),
							'type'        => 'boolean',
						),
					),
				),
			)
		);
	}

	/**
	 * Get the action param and perform each actions.
	 *
	 * If network flag is set to true, the action will be
	 * performed in network admin.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 3.2.4
	 *
	 * @return WP_REST_Response
	 */
	public function action( $request ) {
		// Get the params.
		$action  = $request->get_param( 'action' );
		$network = (bool) $this->get_param( $request, 'network' );

		switch ( $action ) {
			// Clear cache.
			case 'refresh':
				return $this->refresh( $network );
			// Dismiss onboarding.
			case 'dismiss_onboarding':
				return $this->dismiss_onboarding( $network );
			// Reset settings.
			case 'reset_settings':
				return $this->reset_settings( $network );
		}

		// Send error response.
		return $this->get_response(
			array(
				'message' => __( 'Unknown action.', 'ga_trans' ),
			),
			false
		);
	}

	/**
	 * Clear the cache created by Beehive and refresh data.
	 *
	 * Please note we will clear the whole cache instead of
	 * To clear network cache, set network param.
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.2.4
	 *
	 * @return WP_REST_Response
	 */
	public function refresh( $network ) {
		// Clear entire cache.
		Cache::refresh_transient( $network );

		// Send response.
		return $this->get_response(
			array(
				'message' => __( 'Data refreshed. New data should begin feeding shortly.', 'ga_trans' ),
			)
		);
	}

	/**
	 * Skip on boarding setup screen.
	 *
	 * Set a flag in db that the onboarding has been
	 * completed/skipped. So it won't be shown again.
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.2.4
	 *
	 * @return WP_REST_Response
	 */
	public function dismiss_onboarding( $network ) {
		// Set the flag.
		beehive_analytics()->settings->update( 'onboarding_done', 1, 'misc', $network );

		// Send response.
		return $this->get_response( array() );
	}

	/**
	 * Clear the cache created by Beehive and refresh data.
	 *
	 * Please note we will clear the whole cache instead of
	 * To clear network cache, set network param.
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.3.5
	 *
	 * @return WP_REST_Response
	 */
	public function reset_settings( $network ) {
		// Clean cache transients.
		Cleanup::clean_transients( $network );

		// Reset settings.
		$success = beehive_analytics()->settings->reset_settings( $network );

		// Send response.
		return $this->get_response(
			array(
				'message' => $success ? __( 'Plugin settings reset succesfully.', 'ga_trans' ) : __( 'Couldn\'t reset the settings.', 'ga_trans' ),
			),
			$success
		);
	}
}
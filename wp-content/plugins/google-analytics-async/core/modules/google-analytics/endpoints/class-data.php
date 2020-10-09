<?php
/**
 * Data functionality REST endpoint.
 *
 * @link       http://premium.wpmudev.org
 * @since      3.3.0
 *
 * @author     Joel James <joel@incsub.com>
 * @package    Beehive\Core\Modules\Google_Analytics\Endpoints
 */

namespace Beehive\Core\Modules\Google_Analytics\Endpoints;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use Beehive\Core\Utils\Abstracts\Endpoint;
use Beehive\Core\Modules\Google_Analytics;

/**
 * Class Data
 *
 * @package Beehive\Core\Modules\Google_Analytics\Endpoints
 */
class Data extends Endpoint {

	/**
	 * API endpoint for the current endpoint.
	 *
	 * @var string $endpoint
	 *
	 * @since 3.2.4
	 */
	private $endpoint = '/data';

	/**
	 * Register the routes for handling data functionality.
	 *
	 * @since 3.2.4
	 *
	 * @return void
	 */
	public function register_routes() {
		// Route to get analytics profiles list.
		register_rest_route(
			$this->get_namespace(),
			$this->endpoint . '/analytics-profiles/',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'profiles' ),
					'permission_callback' => array( $this, 'settings_permission' ),
					'args'                => array(
						'network' => array(
							'required'    => false,
							'type'        => 'boolean',
							'description' => __( 'The network flag.', 'ga_trans' ),
						),
					),
				),
			)
		);
	}

	/**
	 * Get the list of analytics profiles.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 3.2.4
	 *
	 * @return WP_REST_Response
	 */
	public function profiles( $request ) {
		// Network flag.
		$network = $request->get_param( 'network' );

		// Get available profiles.
		$profiles = Google_Analytics\Data::instance()->profiles_list( $network );

		// Send response.
		return $this->get_response( $profiles );
	}
}
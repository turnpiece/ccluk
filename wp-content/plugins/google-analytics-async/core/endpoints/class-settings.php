<?php
/**
 * Stats functionality REST endpoint.
 *
 * @link       http://wpmudev.com
 * @since      3.3.0
 *
 * @author     Joel James <joel@incsub.com>
 * @package    Beehive\Core\Endpoints
 */

namespace Beehive\Core\Endpoints;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use WP_Error;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use Beehive\Core\Helpers\Permission;
use Beehive\Core\Utils\Abstracts\Endpoint;

/**
 * Class Settings
 *
 * @package Beehive\Core\Endpoints
 */
class Settings extends Endpoint {

	/**
	 * API endpoint for the current endpoint.
	 *
	 * @var string $endpoint
	 *
	 * @since 3.2.4
	 */
	private $endpoint = '/settings';

	/**
	 * Register the routes for handling settings functionality.
	 *
	 * All custom routes for the stats functionality should be registered
	 * here using register_rest_route() function.
	 *
	 * @since 3.2.4
	 *
	 * @return void
	 */
	public function register_routes() {
		// Routes to manage the settings.
		register_rest_route(
			$this->get_namespace(),
			$this->endpoint,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => array( $this, 'settings_permission' ),
					'args'                => array(
						'group'   => array(
							'required'    => false,
							'description' => __( 'The settings group name.', 'ga_trans' ),
							'type'        => 'string',
						),
						'name'    => array(
							'required'    => false,
							'description' => __( 'The settings item key.', 'ga_trans' ),
							'type'        => 'string',
						),
						'network' => array(
							'required'    => false,
							'description' => __( 'The network flag.', 'ga_trans' ),
							'type'        => 'boolean',
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_settings' ),
					'permission_callback' => array( $this, 'settings_permission' ),
					'args'                => array(
						'group'   => array(
							'required'    => false,
							'description' => __( 'The settings group name.', 'ga_trans' ),
							'type'        => 'string',
						),
						'name'    => array(
							'required'    => false,
							'description' => __( 'The settings item key.', 'ga_trans' ),
							'type'        => 'string',
						),
						'value'   => array(
							'required'    => true,
							'description' => __( 'The settings value to update.', 'ga_trans' ),
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
	 * Get the settings value from DB.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 3.2.4
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_settings( $request ) {
		// Get the params.
		$group   = $this->get_param( $request, 'group' );
		$name    = $this->get_param( $request, 'name' );
		$network = $this->get_param( $request, 'network' );

		// Get the settings value.
		if ( $name && $group ) {
			$data = beehive_analytics()->settings->get( $name, $group, $network, array() );
		} elseif ( $group ) {
			$data = beehive_analytics()->settings->get_options( $group, $network );
		} else {
			// Get full settings.
			$data = beehive_analytics()->settings->get_settings_with_default( $network );
		}

		// Send response.
		return $this->get_response( $data );
	}

	/**
	 * Get the post stats data from cache or API.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 3.2.4
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_settings( $request ) {
		// Get the params.
		$group   = $this->get_param( $request, 'group' );
		$name    = $this->get_param( $request, 'name' );
		$network = $this->get_param( $request, 'network' );
		$value   = $this->get_param( $request, 'value' );

		// Get the settings value.
		if ( $name && $value && $group ) {
			$updated = beehive_analytics()->settings->update( $name, $value, $group, $network );
		} elseif ( $value && $group ) {
			$updated = beehive_analytics()->settings->update_group( $value, $group, $network );
		} elseif ( $value ) {
			$updated = beehive_analytics()->settings->update_options( $value, $network );
		} else {
			$updated = false;
		}

		// Get all settings and return it.
		$data = beehive_analytics()->settings->get_settings_with_default( $network );

		// Send response.
		return $this->get_response( $data, $updated );
	}

	/**
	 * Make sure we don't expose sensitive data in API.
	 *
	 * @param array $data Data.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	private function mask_data( $data ) {
		unset( $data['google_login']['access_token'] );

		/**
		 * Filter to perform additional masking for settings endpoint.
		 *
		 * @param array $data Data.
		 *
		 * @since 3.2.4
		 */
		return apply_filters( 'beehive_api_settings_mask_data', $data );
	}

	/**
	 * Check if a given request has access to stats data.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 3.2.4
	 *
	 * @return bool
	 */
	public function permissions_check( $request ) {
		$capable = Permission::can_manage_settings(
			$this->get_param( $request, 'network', false )
		);

		/**
		 * Filter to modify settings endpoint capability.
		 *
		 * @paran bool $capable Is user capable?.
		 *
		 * @since 3.2.4
		 */
		return apply_filters( 'beehive_rest_settings_capability', $capable );
	}
}
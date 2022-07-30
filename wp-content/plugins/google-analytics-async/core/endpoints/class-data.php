<?php
/**
 * Common data functionality REST endpoint.
 *
 * @link       http://wpmudev.com
 * @since      3.2.5
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
use Beehive\Core\Helpers\General;
use Beehive\Core\Utils\Abstracts\Endpoint;

/**
 * Class Actions
 *
 * @package Beehive\Core\Endpoints
 */
class Data extends Endpoint {

	/**
	 * API endpoint for the current endpoint.
	 *
	 * @var string $endpoint
	 *
	 * @since 3.2.5
	 */
	private $endpoint = '/data';

	/**
	 * Register the routes for handling actions functionality.
	 *
	 * All custom routes for the stats functionality should be registered
	 * here using register_rest_route() function.
	 *
	 * @since 3.2.5
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->get_namespace(),
			$this->endpoint . '/users',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_users' ),
					'permission_callback' => array( $this, 'settings_permission' ),
					'args'                => array(
						'search'        => array(
							'required'    => true,
							'type'        => 'string',
							'description' => __( 'Search term to search', 'ga_trans' ),
						),
						'exclude_ids'   => array(
							'required'    => false,
							'default'     => array(),
							'description' => __( 'User IDs to exclude.', 'ga_trans' ),
							'items'       => array(
								'type' => 'integer',
							),
						),
						'exclude_roles' => array(
							'required'    => false,
							'default'     => array(),
							'description' => __( 'User roles to exclude.', 'ga_trans' ),
							'items'       => array(
								'type' => 'string',
							),
						),
						'include_roles' => array(
							'required'    => false,
							'default'     => array(),
							'description' => __( 'User roles to include.', 'ga_trans' ),
							'items'       => array(
								'type' => 'string',
							),
						),
						'super_admins'  => array(
							'required'    => false,
							'type'        => 'boolean',
							'description' => __( 'Should include super admins if it is network site. Default is false.', 'ga_trans' ),
						),
					),
				),
			)
		);
	}

	/**
	 * Get the list of users based on the filters.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 3.2.5
	 *
	 * @return WP_REST_Response
	 */
	public function get_users( $request ) {
		// Get the params.
		$search = $request->get_param( 'search' );
		// Ids to exclude.
		$ids                  = (array) $this->get_param( $request, 'exclude_ids' );
		$exclude_roles        = (array) $this->get_param( $request, 'exclude_roles' );
		$include_roles        = (array) $this->get_param( $request, 'include_roles' );
		$network              = (bool) $this->get_param( $request, 'network' );
		$include_super_admins = (bool) $this->get_param( $request, 'super_admins' );

		// Default arguments.
		$args = array(
			'fields'         => array( 'ID', 'user_email', 'display_name' ),
			'search'         => '*' . $search . '*',
			'search_columns' => array(
				'user_login',
				'user_email',
				'user_nicename',
				'display_name',
			),
			'exclude'        => array(
				get_current_user_id(),
			),
		);

		// Exclude user roles.
		if ( ! empty( $exclude_roles ) ) {
			$args['role__not_in'] = $exclude_roles;
		}

		// Include super admins.
		if ( empty( $include_super_admins ) ) {
			$args['login__not_in'] = get_super_admins();
		}

		// Include user roles.
		if ( ! empty( $include_roles ) ) {
			$args['role__in'] = $include_roles;
		}

		// Exclude user ids.
		if ( ! empty( $ids ) ) {
			$args['exclude'] = array_merge( $ids, $args['exclude'] );
		}

		// Search all sites in network.
		if ( $network ) {
			$args['blog_id'] = 0;
		}

		// Get the original user who activated the plugin.
		$owner = General::is_networkwide() ? get_site_option( 'beehive_owner_user' ) : get_option( 'beehive_owner_user' );
		// Exclude the original user.
		if ( ! empty( $owner ) ) {
			$args['exclude'][] = $owner;
		}

		/**
		 * Filter to modify arguments of the users list query.
		 *
		 * @param array           $args    Arguments
		 * @param WP_REST_Request $request Request object.
		 *
		 * @since 3.2.5
		 */
		$args = apply_filters( 'beehive_rest_data_get_users_args', $args, $request );

		// Get the result.
		$users = get_users( $args );

		// Send response.
		return $this->get_response( $users );
	}
}
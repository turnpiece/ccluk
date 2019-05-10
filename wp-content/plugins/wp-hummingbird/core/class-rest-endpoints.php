<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Hummingbird_REST_Endpoints
 *
 * Manage Hummingbird REST API endpoints
 */
class WP_Hummingbird_REST_Endpoints {

	/**
	 * REST API version.
	 *
	 * @var string
	 */
	public $version = '1';

	/**
	 * REST API namespace.
	 *
	 * @var string
	 */
	public $namespace = 'hummingbird';

	/**
	 * Hub Endpoints Initialize.
	 */
	public function init() {
		add_action( 'rest_api_init', array( &$this, 'register_routes' ) );
	}

	/**
	 * Get namespace with version.
	 *
	 * @return string
	 */
	protected function get_namespace() {
		return $this->namespace . '/v' . $this->version;
	}


	/**
	 * Register the REST routes.
	 */
	public function register_routes() {

		// Route to return a modules status.
		register_rest_route(
			$this->get_namespace(),
			'/status/(?P<module>[\\w-]+)',
			array(
				array(
					'methods'  => WP_REST_Server::READABLE,
					'callback' => array( $this, 'get_module_status' ),
					'args'     => array(
						'module' => array(
							'required'          => true,
							'sanitize_callback' => 'sanitize_key',
						),
					),
				),
			)
		);

		// Route to clear a modules cache.
		register_rest_route(
			$this->get_namespace(),
			'/clear_cache/(?P<module>[\\w-]+)',
			array(
				array(
					'methods'  => WP_REST_Server::READABLE,
					'callback' => array( $this, 'clear_module_cache' ),
					'module'   => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_key',
					),
				),
			)
		);

		// Test route used to check if API is working.
		register_rest_route(
			$this->get_namespace(),
			'/test',
			array(
				array(
					'methods'  => 'POST,GET,PUT,PATCH,DELETE,COPY,HEAD',
					'callback' => array( $this, 'test' ),
				),
			)
		);
	}

	/**
	 * Returns the status of a module.
	 *
	 * @param WP_REST_Request $request  Request.
	 * @return mixed
	 */
	public function get_module_status( $request ) {
		$module            = $request->get_param( 'module' );
		$available_modules = array(
			'gzip',
			'caching',
		);
		if ( in_array( $module, $available_modules, true ) ) {
			$response = array(
				'module_active' => WP_Hummingbird_Utils::get_module( $module )->is_active(),
				'data'          => WP_Hummingbird_Utils::get_module( $module )->analize_data(),
			);
			return rest_ensure_response( $response );
		} else {
			return new WP_Error(
				'invalid_module',
				__( 'The requested module status was invalid.', 'wphb' ),
				array(
					'status' => 400,
				)
			);
		}
	}

	/**
	 * Clears the cache of a module.
	 *
	 * @param WP_REST_Request $request  Request.
	 * @return mixed
	 */
	public function clear_module_cache( $request ) {
		$module            = $request->get_param( 'module' );
		$available_modules = array(
			'page_cache',
			'performance',
			'gravatar',
			'minify',
			'cloudflare',
		);

		// Make sure modules cache can be cleared.
		if ( ! in_array( $module, $available_modules, true ) ) {
			return new WP_Error(
				'invalid_module',
				__( 'The requested module was invalid.', 'wphb' ),
				array(
					'status' => 400,
				)
			);
		}

		// Make sure module is active.
		if ( ! WP_Hummingbird_Utils::get_module( $module )->is_active() ) {
			return new WP_Error(
				'inactive_module',
				__( 'The requested module is inactive.', 'wphb' ),
				array(
					'status' => 400,
				)
			);
		}

		// Clear the cache of module.
		switch ( $module ) {
			case 'minify':
				$response = array(
					'cache_cleared' => WP_Hummingbird_Utils::get_module( $module )->clear_cache( false ),
				);
				break;
			default:
				$response = array(
					'cache_cleared' => WP_Hummingbird_Utils::get_module( $module )->clear_cache(),
				);
				break;
		}
		return rest_ensure_response( $response );
	}

	/**
	 * Used for testing if API calls work.
	 */
	public function test() {
		return true;
	}
}

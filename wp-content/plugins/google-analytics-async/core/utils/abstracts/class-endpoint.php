<?php
/**
 * Base class for all endpoint classes.
 *
 * @link       http://premium.wpmudev.org
 * @since      3.3.0
 *
 * @author     Joel James <joel@incsub.com>
 * @package    Beehive\Core\Utils\Abstracts
 */

namespace Beehive\Core\Utils\Abstracts;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use WP_REST_Request;
use WP_REST_Response;
use Beehive\Core\Helpers\General;
use Beehive\Core\Helpers\Permission;

/**
 * Class Endpoint
 *
 * @package Beehive\Core\Utils\Abstracts
 */
abstract class Endpoint extends Base {

	/**
	 * API endpoint version.
	 *
	 * @var int $version
	 *
	 * @since 3.2.4
	 */
	protected $version = 1;

	/**
	 * API endpoint namespace.
	 *
	 * @var string $namespace
	 *
	 * @since 3.2.4
	 */
	private $namespace;

	/**
	 * Endpoint constructor.
	 *
	 * We need to register the routes here.
	 *
	 * @since 3.2.4
	 */
	protected function __construct() {
		parent::__construct();

		// Setup namespace of the endpoint.
		$this->namespace = 'beehive/v' . $this->version;

		// If the single instance hasn't been set, set it now.
		$this->register_hooks();
	}

	/**
	 * Set up WordPress hooks and filters
	 *
	 * @since 3.2.4
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Get namespace of the endpoint.
	 *
	 * @since 3.2.4
	 *
	 * @return string
	 */
	public function get_namespace() {
		return $this->namespace;
	}

	/**
	 * Get current version of the endpoint.
	 *
	 * @since 3.2.4
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Get formatted response for the current request.
	 *
	 * @param array $data    Response data.
	 * @param bool  $success Is request success.
	 *
	 * @since 3.2.4
	 *
	 * @return WP_REST_Response
	 */
	public function get_response( $data = array(), $success = true ) {
		// Response status.
		$status = $success ? 200 : 400;

		return new WP_REST_Response(
			array(
				'success' => $success,
				'data'    => $data,
			),
			$status
		);
	}

	/**
	 * Send error message response from exception class.
	 *
	 * @param \Exception|\Google_Service_Exception|bool $exception Exception object.
	 * @param array                                     $data      Response data.
	 * @param bool                                      $status    Response status.
	 *
	 * @since 3.2.4
	 *
	 * @return WP_REST_Response
	 */
	public function get_error_response( $exception, $data = array(), $status = false ) {
		// If error instance found, get error message.
		if ( $exception instanceof \Google_Service_Exception ) {
			// Format error message.
			$error = $exception->getErrors();
			$error = isset( $error[0]['message'] ) ? $error[0]['message'] : $error;
			$error = empty( $error ) ? __( 'Unknown error occurred.', 'ga_trans' ) : $error;

			// Try to get the error message.
			// translators: %s is error message from Google API.
			$data['error'] = sprintf( __( 'Google API Error: %s', 'ga_trans' ), $error );
		} elseif ( $exception instanceof \Exception ) {
			// translators: %s is error message from Google API.
			$data['error'] = sprintf( __( 'Google API Error: %s', 'ga_trans' ), $exception->getMessage() );
		} elseif ( ! empty( $exception ) && method_exists( $exception, 'getMessage' ) ) {
			// translators: %s is error message from Google API.
			$data['error'] = sprintf( __( 'Google API Error: %s', 'ga_trans' ), $exception->getMessage() );
		}

		// Make sure we have error text.
		if ( empty( $data['error'] ) ) {
			$data['error'] = __( 'Unknown error occurred. Please try clearing cache.', 'ga_trans' );
		}

		// Send error response.
		return $this->get_response( $data, $status );
	}

	/**
	 * Retrieves a parameter from the request.
	 *
	 * This is a wrapper function to get default value if the param
	 * is not found. Also with optional sanitization.
	 *
	 * @param WP_REST_Request $request           Request object.
	 * @param string          $key               Parameter name.
	 * @param mixed           $default           Default value.
	 * @param string|bool     $sanitize_callback Sanitization callback.
	 *
	 * @since 3.2.4
	 *
	 * @return mixed
	 */
	public function get_param( WP_REST_Request $request, $key, $default = '', $sanitize_callback = false ) {
		// Get param.
		$value = $request->get_param( $key );

		// Default value if null.
		$value = ( null === $value ? $default : $value );

		// If sanitization is requested.
		if ( $sanitize_callback && is_callable( $sanitize_callback ) ) {
			return call_user_func( $sanitize_callback, $value );
		} else {
			return $value;
		}
	}

	/**
	 * Check if a given request has access to manage settings.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 3.2.4
	 *
	 * @return bool
	 */
	public function settings_permission( $request ) {
		$capable = Permission::can_manage_settings(
			$this->get_param( $request, 'network', false )
		);

		/**
		 * Filter to modify settings rest capability.
		 *
		 * @paran bool $capable Is user capable?.
		 *
		 * @param WP_REST_Request $request Request object.
		 *
		 * @since 3.2.4
		 */
		return apply_filters( 'beehive_rest_settings_permission', $capable, $request );
	}

	/**
	 * Check if a given request has access to the analytics data.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 3.2.4
	 *
	 * @return bool
	 */
	public function analytics_permission( $request ) {
		$capable = Permission::can_view_analytics(
			$this->get_param( $request, 'network', false )
		);

		/**
		 * Filter to modify stats rest capability.
		 *
		 * @paran bool $capable Is user capable?.
		 *
		 * @param WP_REST_Request $request Request object.
		 *
		 * @since 3.2.4
		 */
		return apply_filters( 'beehive_rest_analytics_permission', $capable, $request );
	}

	/**
	 * Check if a given request has access to public data.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 3.2.4
	 *
	 * @return bool
	 */
	public function public_permission( $request ) {
		$capable = true;

		/**
		 * Filter to modify stats rest capability.
		 *
		 * @paran bool $capable Is user capable?.
		 *
		 * @param WP_REST_Request $request Request object.
		 *
		 * @since 3.2.4
		 */
		return apply_filters( 'beehive_rest_public_permission', $capable, $request );
	}

	/**
	 * Custom parameter validation for routes.
	 *
	 * This function works based on the parameter key name.
	 * Please note, if the parameter key is not specifically
	 * checked, the validation will return true.
	 *
	 * @param mixed           $param   Paramter value.
	 * @param WP_REST_Request $request Request object.
	 * @param string          $key     Paramter key.
	 *
	 * @since 3.2.4
	 *
	 * @return bool
	 */
	public function validate_param( $param, $request, $key ) {
		switch ( $key ) {
			case 'to':
			case 'from':
				return General::check_date_format( $param );
			default:
				return true;
		}
	}

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * This should be defined in extending class.
	 *
	 * @since 3.2.4
	 */
	abstract public function register_routes();
}
<?php

/**
 * Manage the errors during minification processing
 */
class WP_Hummingbird_Minification_Errors_Controller {

	/**
	 * Minification errors list
	 *
	 * @var array|bool
	 */
	private $errors;

	/**
	 * Saves the errors coming from our servers
	 *
	 * @var array
	 */
	private $server_errors = array();

	public function __construct() {
		$this->errors = $this->get_errors();
		$this->server_errors = $this->get_server_errors();
	}

	/**
	 * Retrieve the list of errors coming from the server
	 *
	 * @return array|mixed
	 */
	public function get_server_errors() {
		$errors =  get_transient( 'wphb-minify-server-errors' );
		if ( ! $errors || ! is_array( $errors ) ) {
			return array();
		}
		return $errors;
	}

	/**
	 * Add an error coming from server
	 *
	 * @param $error
	 */
	public function add_server_error( $error ) {
		$this->server_errors[] = $error;
		set_transient( 'wphb-minify-server-errors', $this->server_errors, 7200 ); // save for 2 hours
	}


	/**
	 * Check if there's an error in the server
	 * This function should be used to stop minification
	 * if there have been too many errors
	 */
	public function is_server_error() {
		// More than 2 errors is too many
		return ( count( $this->server_errors ) > 2 );
	}

	/**
	 * Return the server errors time left (the time to delete the transient)
	 * in minutes
	 */
	public function server_error_time_left() {
		if ( ! $this->is_server_error() ) {
			return 0;
		}
		$timeout = get_option( '_transient_timeout_wphb-minify-server-errors', false );
		if ( ! $timeout ) {
			return 0;
		}
		return ceil( ( $timeout - time() ) / 60 );
	}

	/**
	 * Get all minification errors
	 *
	 * @return array|bool False if there are no errors
	 */
	private function get_errors() {
		$default = array( 'scripts' => array(), 'styles' => array() );

		/**
		 * Filter the minification errors
		 */
		return apply_filters( 'wphb_minification_errors', get_option( 'wphb-minification-errors', $default ) );
	}

	/**
	 * Return a single handle error
	 *
	 * @param string $handle
	 * @param string $type styles|scripts
	 *
	 * @return bool|array
	 */
	public function get_handle_error( $handle, $type ) {
		$error = false;
		if ( isset( $this->errors[ $type ][ $handle ] ) ) {
			$defaults = array(
				'handle' => '',
				'error' => '',
				'disable' => array()
			);
			$error = wp_parse_args( $this->errors[ $type ][ $handle ], $defaults );
		}

		return apply_filters( "wphb_handle_error_{$handle}_{$type}", $error, $handle, $type );;
	}

	/**
	 * Clear all errors
	 */
	public static function clear_errors() {
		delete_option( 'wphb-minification-errors' );
		delete_option( 'wphb-minify-server-errors' );
	}

	/**
	 * Delete a single handle error
	 *
	 * @param string|array $handles
	 * @param string $type
	 */
	public function clear_handle_error( $handles, $type ) {
		if ( ! is_array( $handles ) ) {
			$handles = array( $handles );
		}

		foreach ( $handles as $handle ) {
			$error = $this->get_handle_error( $handle, $type );
			if ( ! $error ) {
				continue;
			}

			unset( $this->errors[ $type ][ $handle ] );
		}


		update_option( 'wphb-minification-errors', $this->errors );
	}

	/**
	 * Add a minification error for a list of handles
	 *
	 * @param array|string $handles Handles list or single handle
	 * @param string $type scripts|styles
	 * @param string $code Error code
	 * @param string $message Error message
	 * @param array $actions List of actions to take (don't minify, don't combine)
	 * @param array $disable List of switchers to disable in Minification screen (minify, combine)
	 */
	public function add_error( $handles, $type, $code, $message, $actions = array(), $disable = array() ) {
		if ( ! is_array( $handles ) ) {
			$handles = array( $handles );
		}

		$options = wphb_get_settings();

		foreach ( $handles as $handle ) {
			$this->errors[ $type ][ $handle ] = array(
				'code' => $code,
				'error' => $message,
				'disable' => $disable
			);

			if ( ! empty( $actions ) && is_array( $actions ) ) {

				if ( in_array( 'minify', $actions ) ) {
					$options['dont_minify'][ $type ][] = $handle;
				}

				$key = in_array( $handle, $options['combine'][ $type ] );
				if ( in_array( 'combine', $actions ) && false !== $key ) {
					unset( $options['combine'][ $type ][ $key ] );
					$options['combine'][ $type ] = array_values( $options['combine'][ $type ] );
				}

			}
		}

		wphb_update_settings( $options );
		update_option( 'wphb-minification-errors', $this->errors );
	}
}
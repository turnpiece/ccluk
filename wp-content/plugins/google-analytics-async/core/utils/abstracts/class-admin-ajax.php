<?php

namespace Beehive\Core\Utils\Abstracts;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Google_Service_Exception;
use Beehive\Core\Helpers\Permission;

/**
 * Base class for all ajax requests.
 *
 * @note
 *
 * @link   http://premium.wpmudev.org
 * @since  3.2.0
 *
 * @author Joel James <joel@incsub.com>
 */
abstract class Admin_Ajax extends Singleton {

	/**
	 * Security check for the ajax request.
	 *
	 * All ajax requests must contain nonce value and the nonce field name
	 * should be `beehive_nonce` with action `beehive_admin_nonce`.
	 * Otherwise the security check will fail.
	 * If the security check failed, we will send Json response.
	 *
	 * @param bool   $capability_check Should check capability also?.
	 * @param string $type             Capability type.
	 *
	 * @since 3.2.0
	 *
	 * @uses  wp_send_json_error
	 *
	 * @return void
	 */
	protected function security_check( $capability_check = true, $type = 'base' ) {
		// Security check.
		if ( ! check_ajax_referer( 'beehive_admin_nonce', 'beehive_nonce', false ) ) {
			// Send error response.
			wp_send_json_error( [
				'error' => __( 'Security check failed.', 'ga_trans' ),
			] );
		}

		// Capability check.
		if ( $capability_check ) {
			$this->capability_check( $type );
		}
	}

	/**
	 * User capability check for the ajax request.
	 *
	 * @param string $type Capability type.
	 *
	 * @since 3.2.0
	 *
	 * @uses  wp_send_json_error
	 *
	 * @return void
	 */
	protected function capability_check( $type = 'base' ) {
		// Check capability.
		$capable = Permission::user_can( $type, $this->is_network() );

		// Do a capability check.
		if ( ! $capable ) {
			// Send error response.
			wp_send_json_error( [
				'error' => __( 'Sorry. You are not allowed to do this.', 'ga_trans' ),
			] );
		}
	}

	/**
	 * Check if all required fields are found in request.
	 *
	 * @param array  $fields  Required field names.
	 * @param string $message Message to display if check fail.
	 *
	 * @since 3.2.0
	 *
	 * @uses  wp_send_json_error
	 *
	 * @return void
	 */
	protected function required_check( $fields = [], $message = '' ) {
		// Get missing items.
		$missing = array_diff( (array) $fields, array_keys( $_REQUEST ) );

		// If missing items found.
		if ( ! empty( $missing ) ) {
			// Get error message.
			$message = empty( $message ) ? __( 'Required values are not set. ', 'ga_trans' ) : $message;

			// Send error response.
			wp_send_json_error( [
				'error' => $message,
			] );
		}
	}

	/**
	 * Check if the current ajax request is within network admin.
	 *
	 * @note  This will work only if `network` value is found in current request.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	protected function is_network() {
		return ( ! empty( $_REQUEST['network'] ) );
	}

	/**
	 * Send error message as json response to ajax.
	 *
	 * @param \Exception|Google_Service_Exception|bool $exception Exception object.
	 *
	 * @since 3.2.0
	 */
	protected function send_json_error( $exception ) {
		// If error instance found, get error message.
		if ( $exception instanceof Google_Service_Exception ) {
			wp_send_json_error( [
				'error' => $exception->getErrors(),
			] );
		} elseif ( $exception instanceof \Exception ) {
			wp_send_json_error( [
				'error' => $exception->getMessage(),
			] );
		} else {
			wp_send_json_error();
		}
	}
}
<?php
/**
 * Shipper controllers: AJAX controller abstraction
 *
 * All concrete AJAX controllers inherit from this.
 *
 * @package shipper
 */

/**
 * AJAX controller abstraction
 */
abstract class Shipper_Controller_Ajax extends Shipper_Controller {

	const TYPE_GET = 'GET';
	const TYPE_POST = 'POST';

	/**
	 * Checks whether the current user can perform any of the AJAX actions
	 *
	 * Dies with JSON error if they can't as a side-effect.
	 *
	 * @param string $action Optional nonce action to check.
	 * @param string $type Optional request type (used with action param).
	 *
	 * @return bool
	 */
	public function do_request_sanity_check( $action = '', $type = false ) {
		if ( ! empty( $action ) ) {
			$type = ! empty( $type ) ? $type : self::TYPE_POST;
			// @codingStandardsIgnoreLine This is where we actually process the nonce.
			$request = self::TYPE_POST === $type ? $_POST : $_GET;
			if (
				! isset( $request['_wpnonce'] ) ||
				! wp_verify_nonce( $request['_wpnonce'], $action )
			) {
				Shipper_Helper_Log::write( sprintf(
					__( 'Direct %s access attempt', 'shipper' ), $action
				) );
				return wp_send_json_error(
					__( 'You are not wanted here, leave.', 'shipper' )
				);
			}
		}

		if ( shipper_user_can_ship() ) {
			// All good.
			return true;
		}
		return wp_send_json_error(
			__( 'How did you get here?', 'shipper' )
		);
	}

}

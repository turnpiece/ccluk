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

	const TYPE_GET  = 'GET';
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
				! wp_verify_nonce( $request['_wpnonce'], $action ) ||
				! shipper_user_can_ship()
			) {
				Shipper_Helper_Log::write(
					sprintf(
						/* translators: %s: action name. */
						__( 'Direct or unauthorized %s access attempt', 'shipper' ),
						$action
					)
				);
				return wp_send_json_error(
					__( 'You are not authorized to perform this action.', 'shipper' )
				);
			}
		}

		if ( shipper_user_can_ship() ) {
			// All good.
			return true;
		}
		return wp_send_json_error(
			__( 'You are not authorized to perform this action.', 'shipper' )
		);
	}

	/**
	 * Adds an AJAX action handler to an action
	 *
	 * @since v1.1
	 *
	 * @param string   $action Action suffix to bind to.
	 * @param callable $handler Handler method to bind.
	 * @param bool     $is_nopriv Is the action wide-open (defaults to false).
	 */
	public function add_handler( $action, $handler, $is_nopriv = false ) {

		return add_action(
			$this->get_handler_action( $action, $is_nopriv ),
			$handler
		);
	}

	/**
	 * Gets an AJAX handler action from action sufix
	 *
	 * @param string $action Action suffix.
	 * @param bool   $is_nopriv Is the action wide-open (defaults to false).
	 *
	 * @return string
	 */
	public function get_handler_action( $action, $is_nopriv = false ) {
		$obj   = strtolower( get_class( $this ) );
		$class = strtolower( __CLASS__ );
		$infix = preg_replace( '/^' . preg_quote( $class, '/' ) . '/', '', $obj );
		$str   = (bool) $is_nopriv
			? 'wp_ajax_nopriv_shipper%1$s_%2$s'
			: 'wp_ajax_shipper%1$s_%2$s';
		return sprintf( $str, $infix, $action );
	}

}
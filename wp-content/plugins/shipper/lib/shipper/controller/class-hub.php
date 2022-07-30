<?php
/**
 * Shipper controllers: Hub action receiver abstraction
 *
 * Handles actions received remotely, from the Hub.
 * All Hub controllers extend from this.
 *
 * @package shipper
 */

/**
 * Hub actions controller class
 */
abstract class Shipper_Controller_Hub extends Shipper_Controller {

	const ACTION_MIGRATION_START     = 'migration_start';
	const ACTION_MIGRATION_CANCEL    = 'migration_cancel';
	const ACTION_MIGRATION_KICKSTART = 'migration_kickstart';
	const ACTION_PING                = 'ping';
	const ACTION_PREFLIGHT           = 'preflight';
	const ACTION_RESET_DESTINATIONS  = 'reset_destination_cache';
	const ACTION_RESET_CREDS         = 'reset_creds_cache';
	const ACTION_ADD                 = 'destination_add';

	/**
	 * Gets the list of known Hub actions
	 *
	 * @return array Known actions
	 */
	abstract public function get_known_actions();

	/**
	 * Boots the controller and sets up event listeners.
	 */
	public function boot() {
		add_filter( 'wdp_register_hub_action', array( $this, 'register_endpoints' ) );
	}

	/**
	 * Registers handlers for actions pushed from the Hub
	 *
	 * @param array $actions Known actions.
	 *
	 * @return array Augmented actions
	 */
	public function register_endpoints( $actions ) {
		if ( ! is_array( $actions ) ) {
			return $actions; }

		$known = $this->get_known_actions();
		if ( ! is_array( $known ) ) {
			return $actions; }

		foreach ( $known as $action_raw_name ) {
			$method = "json_{$action_raw_name}";
			if ( ! is_callable( array( $this, $method ) ) ) {
				continue; // We don't know how to handle this action.
			}

			$action_name             = "shipper_{$action_raw_name}";
			$actions[ $action_name ] = array( $this, $method );
		}

		return $actions;
	}

	/**
	 * Wraps error sending response
	 *
	 * If we have enough info to build appropriate response
	 * and we have access to request object, use that to send back more
	 * meaningful, verbose response.
	 * Otherwise default to WP JSON response handling.
	 *
	 * @param WP_Error|mixed $info Info on what went wrong.
	 * @param object         $request Optional WPMUDEV_Dashboard_Remote object.
	 *
	 * @return bool
	 */
	public function send_response_error( $info, $request = false ) {
		$status = $info;
		if ( is_wp_error( $info ) ) {
			$code   = $info->get_error_code();
			$status = array(
				'code'    => $code,
				'message' => $info->get_error_message( $code ),
				'data'    => $info->get_error_data( $code ),
			);
		}
		if (
			! empty( $status ) &&
			is_object( $request ) &&
			is_callable( array( $request, 'send_json_error' ) )
		) {
			return $request->send_json_error( $status );
		}
		return wp_send_json_error( $status );
	}

	/**
	 * Wraps success sending response
	 *
	 * If we have enough info to build appropriate response
	 * and we have access to request object, use that to send back more
	 * meaningful, verbose response.
	 * Otherwise default to WP JSON response handling.
	 *
	 * @param mixed  $info Info status.
	 * @param object $request Optional WPMUDEV_Dashboard_Remote object.
	 *
	 * @return bool
	 */
	public function send_response_success( $info, $request = false ) {
		$status = $info;
		if (
			! empty( $status ) &&
			is_object( $request ) &&
			is_callable( array( $request, 'send_json_success' ) )
		) {
			return $request->send_json_success( $status );
		}
		return wp_send_json_success( $status );
	}
}

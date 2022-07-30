<?php
/**
 * Shipper AJAX controllers: access permissions controller class
 *
 * @since v1.0.3
 *
 * @package shipper
 */

/**
 * Permissions AJAX controller class
 */
class Shipper_Controller_Ajax_Permissions extends Shipper_Controller_Ajax {

	/**
	 * Boots the controller and sets up event listeners.
	 */
	public function boot() {
		if ( ! is_admin() ) {
			return false; }

		add_action(
			'wp_ajax_shipper_permissions_verify',
			array( $this, 'handle_permissions_verify' )
		);
	}

	/**
	 * Verifies user by supplied user name and responds with data.
	 */
	public function handle_permissions_verify() {
		$this->do_request_sanity_check( 'shipper_user_access_add' );
		// @codingStandardsIgnoreLine Nonce already checked in `do_request_sanity_check`
		$data = stripslashes_deep( $_POST );
		$user_id = ! empty( $data['user_id'] )
			? (int) $data['user_id']
			: false;

		if ( empty( $user_id ) ) {
			return wp_send_json_error(
				__( 'User is mandatory', 'shipper' )
			);
		}

		$user = get_user_by( 'id', $user_id );
		if ( empty( $user ) ) {
			return wp_send_json_error(
				/* translators: %s: user id. */
				sprintf( __( 'User does not exist: %s', 'shipper' ), $user_id )
			);
		}

		$ctrl = Shipper_Controller_Admin::get();
		if ( ! user_can( $user, $ctrl->get_capability() ) ) {
			return wp_send_json_error(
				/* translators: %s: user id. */
				sprintf( __( 'User is not an admin: %s', 'shipper' ), $user_id )
			);
		}

		$tpl  = new Shipper_Helper_Template();
		$data = $tpl->get(
			'pages/settings/permissions-user',
			array(
				'user_id' => $user->ID,
				'email'   => $user->user_email,
				'name'    => shipper_get_user_name( $user->ID ),
			)
		);

		$response = array(
			'data'    => $data,
			'message' => sprintf(
				/* translators: %s: username.*/
				__( '%s is added as a user but you still need to save changes', 'shipper' ),
				shipper_get_user_name( $user->ID )
			),
		);

		wp_send_json_success( $response );
	}

}
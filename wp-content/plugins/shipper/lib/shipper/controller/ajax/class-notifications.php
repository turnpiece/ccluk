<?php
/**
 * Shipper AJAX controllers: notifications controller class
 *
 * @package shipper
 */

/**
 * Notifications AJAX controller class
 */
class Shipper_Controller_Ajax_Notifications extends Shipper_Controller_Ajax {

	/**
	 * Boots the controller and sets up event listeners.
	 */
	public function boot() {
		if ( ! is_admin() ) {
			return false; }

		add_action(
			'wp_ajax_shipper_notifications_enable',
			array( $this, 'handle_notifications_enable' )
		);
		add_action(
			'wp_ajax_shipper_notifications_disable',
			array( $this, 'handle_notifications_disable' )
		);
		add_action(
			'wp_ajax_shipper_notifications_add',
			array( $this, 'handle_add_email_notification' )
		);
		add_action(
			'wp_ajax_shipper_notifications_rmv',
			array( $this, 'handle_rmv_email_notification' )
		);
		add_action(
			'wp_ajax_shipper_notifications_fail_only_enable',
			array( $this, 'handle_notifications_failure_enable' )
		);
		add_action(
			'wp_ajax_shipper_notifications_fail_only_disable',
			array( $this, 'handle_notifications_failure_disable' )
		);
	}

	/**
	 * Adds an email to the notification queue
	 */
	public function handle_add_email_notification() {
		$this->do_request_sanity_check( 'shipper_email_notifications_add' );
		// @codingStandardsIgnoreLine Nonce already checked in `do_request_sanity_check`
		$data = stripslashes_deep( $_POST );
		$email = ! empty( $data['email'] ) ? $data['email'] : false;
		$name  = ! empty( $data['name'] ) ? $data['name'] : false;

		if ( empty( $name ) ) {
			return wp_send_json_error(
				__( 'Name is mandatory field', 'shipper' )
			);
		}

		if ( ! is_email( $email ) ) {
			return wp_send_json_error(
				/* translators: %s: email address. */
				sprintf( __( 'This is not a valid email: %s', 'shipper' ), esc_html( $email ) )
			);
		}

		$model = new Shipper_Model_Stored_Options();

		$status = $model->add_email( $email, $name );
		if ( $status ) {
			$tpl  = new Shipper_Helper_Template();
			$data = array(
				'data'    => $tpl->get( 'pages/settings/notifications' ),
				'message' => __( 'Recipient has been added', 'shipper' ),
			);

			return wp_send_json_success( $data );
		}

		return wp_send_json_error(
			/* translators: %s: email address. */
			sprintf( __( 'Unable to add email %s', 'shipper' ), $email )
		);
	}

	/**
	 * Removes an email from the notifications queue
	 */
	public function handle_rmv_email_notification() {
		$this->do_request_sanity_check( 'shipper_email_notifications_rmv' );
		// @codingStandardsIgnoreLine Nonce already checked in `do_request_sanity_check`
		$data = stripslashes_deep( $_POST );
		$email = ! empty( $data['email'] ) ? $data['email'] : false;

		if ( ! is_email( $email ) ) {
			return wp_send_json_error(
				__( 'This is not a valid email', 'shipper' )
			);
		}

		$model = new Shipper_Model_Stored_Options();

		$status = $model->drop_email( $email );
		if ( $status ) {
			$tpl  = new Shipper_Helper_Template();
			$data = array(
				'data'    => $tpl->get( 'pages/settings/notifications' ),
				'message' => __( 'Recipient has been removed', 'shipper' ),
			);

			return wp_send_json_success( $data );
		}

		return wp_send_json_error(
			/* translators: %s: email address. */
			sprintf( __( 'Unable to delete email %s', 'shipper' ), $email )
		);
	}

	/**
	 * Enables email notifications
	 */
	public function handle_notifications_enable() {
		$this->do_request_sanity_check( 'shipper_email_notifications_toggle' );
		$model = new Shipper_Model_Stored_Options();
		$model->set( Shipper_Model_Stored_Options::KEY_SEND, true );
		$model->save();
		return wp_send_json_success();
	}

	/**
	 * Disables email notifications
	 */
	public function handle_notifications_disable() {
		$this->do_request_sanity_check( 'shipper_email_notifications_toggle' );
		$model = new Shipper_Model_Stored_Options();
		$model->set( Shipper_Model_Stored_Options::KEY_SEND, false );
		$model->save();
		return wp_send_json_success();
	}

	/**
	 * Enables email failure notifications
	 */
	public function handle_notifications_failure_enable() {
		$this->do_request_sanity_check( 'shipper_email_fail_only' );
		$model = new Shipper_Model_Stored_Options();
		$model->set( Shipper_Model_Stored_Options::KEY_SEND_FAIL, true );
		$model->save();
		return wp_send_json_success();
	}

	/**
	 * Disables email failure notifications
	 */
	public function handle_notifications_failure_disable() {
		$this->do_request_sanity_check( 'shipper_email_fail_only' );
		$model = new Shipper_Model_Stored_Options();
		$model->set( Shipper_Model_Stored_Options::KEY_SEND_FAIL, false );
		$model->save();
		return wp_send_json_success();
	}
}
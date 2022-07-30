<?php
/**
 * Shipper controllers: activation setup controller
 *
 * Handles plugin activation.
 *
 * @package shipper
 */

/**
 * Setup activation class
 */
class Shipper_Controller_Setup_Activate extends Shipper_Controller_Setup {

	/**
	 * Runs on plugin activation
	 */
	public static function activate() {
		// Clear storage on activation!
		self::get()->clear_fs_storage();

		self::get()
			->add_admin_notification()
			->activate_email_notifications()
			->add_to_api();
	}

	/**
	 * Adds admin user for notifications on plugin activation
	 *
	 * Only adds admin if the notifications were OFF on activation
	 * - i.e. on first activation.
	 *
	 * @return object Shipper_Controller_Setup instance
	 */
	public function add_admin_notification() {
		$model = new Shipper_Model_Stored_Options();
		if ( $model->get( Shipper_Model_Stored_Options::KEY_SEND ) ) {
			// Already has been activated, let's not waste time here.
			return $this;
		}
		$email = get_option( 'admin_email' );
		$name  = shipper_get_user_name();
		if ( shipper_user_can_ship() ) {
			$user  = wp_get_current_user();
			$email = $user->user_email;
		}

		if ( empty( $name ) || ! is_email( $email ) ) {
			return $this;
		}
		$model->add_email( $email, $name );
		return $this;
	}

	/**
	 * Activates email notifications on plugin activation
	 *
	 * @return object Shipper_Controller_Setup instance
	 */
	public function activate_email_notifications() {
		$model = new Shipper_Model_Stored_Options();
		$model->set( Shipper_Model_Stored_Options::KEY_SEND, true );
		$model->save();
		return $this;
	}

	/**
	 * Adds the site to the API on activation
	 *
	 * @since v1.0.2
	 *
	 * @return bool
	 */
	public function add_to_api() {
		$task   = new Shipper_Task_Api_Destinations_Add();
		$result = $task->apply();

		if ( ! empty( $result ) ) {
			// Let's also refresh our systems info.
			$info_task = new Shipper_Task_Api_Info_Set();
			$system    = new Shipper_Model_System();
			$result    = $info_task->apply( $system->get_data() );
		}

		return $result;
	}
}
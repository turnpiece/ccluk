<?php
/**
 * Shipper controllers: notifications
 *
 * Handles user notifications according to options.
 *
 * @package shipper
 */

/**
 * Notifications controller class
 */
class Shipper_Controller_Notifications extends Shipper_Controller {

	/**
	 * Options model instance
	 *
	 * @var object Shipper_Model_Stored_Options instance
	 */
	protected $_model;

	/**
	 * Boot event listeners
	 */
	public function boot() {
		$model = $this->get_model();
		if ( $model->get( Shipper_Model_Stored_Options::KEY_SEND ) ) {
			add_action(
				'shipper_migration_complete',
				array( $this, 'send_notifications_complete' )
			);
			add_action(
				'shipper_migration_cancel',
				array( $this, 'send_notifications_failure' )
			);
		}
	}

	/**
	 * Sends out notifications on migration complete
	 *
	 * Will check migration errors for status, to dispatch to proper
	 * notifications handler.
	 *
	 * @uses Shipper_Controller_Notifications::send_notifications
	 *
	 * @param object $migration Shipper_Model_Stored_Migration instance.
	 */
	public function send_notifications_complete( $migration ) {
		$data = $migration->get_data();
		$success = empty( $data['errors'] );

		if ( ! empty( $success ) ) {
			$model = $this->get_model();
			if ( ! $model->get( Shipper_Model_Stored_Options::KEY_SEND_FAIL ) ) {
				return $this->send_notifications( $migration, true );
			}
			return false;
		}
		return $this->send_notifications_failure( $migration );
	}

	/**
	 * Sends out failure notifications
	 *
	 * @uses Shipper_Controller_Notifications::send_notifications
	 *
	 * @param object $migration Shipper_Model_Stored_Migration instance.
	 */
	public function send_notifications_failure( $migration ) {
		return $this->send_notifications( $migration, false );
	}

	/**
	 * Sends out notifications to registered email receivers
	 *
	 * @param object $migration Shipper_Model_Stored_Migration instance.
	 * @param bool   $status Migration status.
	 */
	public function send_notifications( $migration, $status ) {
		$model = $this->get_model();
		$tpl = new Shipper_Helper_Template;
		$type = $migration->get_type();

		foreach ( $model->get_emails() as $email => $name ) {
			$args = array(
				'name' => $name,
				'migration' => $migration,
				'status' => $status,
			);

			$subject = $tpl->get( sprintf( 'emails/%s/subject', $type ), $args );
			$body = $tpl->get( sprintf( 'emails/%s/body', $type ), $args );

			if ( ! empty( $subject ) && ! empty( $body ) && is_email( $email ) ) {
				wp_mail(
					$email,
					$subject,
					$body
				);
			}
		}
	}

	/**
	 * Returns model instance
	 *
	 * @return object Shipper_Model_Stored_Options instance
	 */
	public function get_model() {
		if ( ! empty( $this->_model ) ) { return $this->_model; }
		$this->set_model( new Shipper_Model_Stored_Options );
		return $this->_model;
	}

	/**
	 * Sets internal model reference
	 *
	 * Used in tests
	 *
	 * @param object $model Shipper_Model_Stored instance.
	 *
	 * @return object Shipper_Controller_Notifications instance
	 */
	public function set_model( Shipper_Model_Stored $model ) {
		$this->_model = $model;
		return $this;
	}
}
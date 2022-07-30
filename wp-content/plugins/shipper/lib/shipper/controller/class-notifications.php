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
	protected $model;

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
		$data    = $migration->get_data();
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
		if ( $migration->is_from_hub() ) {
			// Do not send out notifications if we're not local.
			// Hub-originated migrations have a corresponding local one.
			// The local one should notify.
			return false;
		}

		$model   = $this->get_model();
		$tpl     = new Shipper_Helper_Template();
		$type    = $migration->get_type();
		$subject = $this->get_subject( $migration, $status );
		$headers = array(
			'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ),
		);

		foreach ( $model->get_emails() as $email => $name ) {
			$args = array(
				'name'      => $name,
				'migration' => $migration,
				'status'    => $status,
				'subject'   => $subject,
			);

			$body = $tpl->get( sprintf( 'emails/%s/body', $type ), $args );

			if ( ! empty( $subject ) && ! empty( $body ) && is_email( $email ) ) {
				wp_mail(
					$email,
					$subject,
					$body,
					$headers
				);
			}
		}
	}

	/**
	 * Get notification subject
	 *
	 * @since 1.2.6
	 *
	 * @param object $migration Shipper_Model_Stored_Migration instance.
	 * @param bool   $status Migration status.
	 *
	 * @return string
	 */
	public function get_subject( $migration, $status ) {
		$type = 'export' === $migration->get_type()
			? __( 'Exported', 'shipper' )
			: __( 'Imported', 'shipper' );

		$to_or_from = 'export' === $migration->get_type()
			? __( 'from', 'shipper' )
			: __( 'to', 'shipper' );

		$subject = $status
			? sprintf(
				/* translators: %1$s %2$s %3$s: migration type and source site url. */
				__( 'Shipper successfully %1$s your site %2$s %3$s', 'shipper' ),
				$type,
				$to_or_from,
				$migration->get_source()
			)
			: __( 'Shipper Encountered An Error', 'shipper' );

		return apply_filters( 'shipper_get_notifications_subject', $subject, $migration, $status );
	}

	/**
	 * Returns model instance
	 *
	 * @return object Shipper_Model_Stored_Options instance
	 */
	public function get_model() {
		if ( ! empty( $this->model ) ) {
			return $this->model;
		}

		$this->set_model( new Shipper_Model_Stored_Options() );

		return $this->model;
	}

	/**
	 * Sets internal model reference
	 *
	 * Used in tests
	 *
	 * @param Shipper_Model_Stored $model Shipper_Model_Stored instance.
	 *
	 * @return object Shipper_Controller_Notifications instance
	 */
	public function set_model( Shipper_Model_Stored $model ) {
		$this->model = $model;

		return $this;
	}
}
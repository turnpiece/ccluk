<?php
/**
 * Tasks reusable, are atomic units of work in Shipper.
 *
 * A task performs actions on data in response to controller actions.
 *
 * @package shipper
 */

/**
 * Task abstraction class
 */
abstract class Shipper_Task {

	/**
	 * Task main entry point - actually runs the task
	 *
	 * @param array $args Optional task arguments (if any).
	 *
	 * @return mixed Task-dependent return value
	 */
	abstract public function apply( $args = array() );

	/**
	 * Holds a list of errors encoutered during task execution
	 *
	 * @var array
	 */
	private $errors = array();

	/**
	 * Gets current errors list
	 *
	 * @return array
	 */
	public function get_errors() {
		return (array) $this->errors;
	}

	/**
	 * Checks whether we had any errors
	 *
	 * @return bool
	 */
	public function has_errors() {
		return ! empty( $this->errors );
	}

	/**
	 * Clears errors storage
	 *
	 * @return object Shipper_Task instance
	 */
	public function clear_errors() {
		$this->errors = array();

		return $this;
	}

	/**
	 * Adds a new execution error
	 *
	 * @param string $err Error suffix to be added to error type.
	 * @param string $msg Optional error message.
	 * @param array  $data Optional error data.
	 *
	 * @return object Shipper_Task instance
	 */
	public function add_error( $err, $msg = '', $data = array() ) {
		// @codingStandardsIgnoreLine Plugin-backported.
		$cls = get_called_class();

		$error          = new WP_Error(
			$cls . Shipper_Model::SCOPE_DELIMITER . $err,
			$msg,
			$data
		);
		$this->errors[] = $error;

		return $this;
	}

	/**
	 * We have to check if this is from newer to older migration while upgrading
	 *
	 * @return bool
	 */
	public function is_signal_come_from_compatibility_version() {
		$migration = new Shipper_Model_Stored_Migration();
		$is_valid  = $migration->get( 'is_compatibility', false );
		if ( -1 === $is_valid ) {
			// this should not here as it will cancel right away, but just a fail safe.
			return false;
		}
		if ( $is_valid ) {
			return true;
		}
		Shipper_Helper_Log::write( 'Request to get info from API for checking compatibility, should only see this message once time.' );
		$target  = $migration->get_destination();
		$request = new Shipper_Task_Api_Info_Get();
		$info    = $request->apply( array( 'domain' => $target ) );
		if ( ! isset( $info['wordpress'][ Shipper_Model_System_Wp::SHIPPER_VERSION ] ) ) {
			$migration->set( 'is_compatibility', -1 );
			$migration->save();

			return false;
		}
		$migration->set( 'is_compatibility', true );
		$migration->save();

		return true;
	}
}
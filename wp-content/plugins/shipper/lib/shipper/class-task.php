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
	private $_errors = array();

	/**
	 * Gets current errors list
	 *
	 * @return array
	 */
	public function get_errors() {
		return (array) $this->_errors;
	}

	/**
	 * Checks whether we had any errors
	 *
	 * @return bool
	 */
	public function has_errors() {
		return ! empty( $this->_errors );
	}

	/**
	 * Clears errors storage
	 *
	 * @return object Shipper_Task instance
	 */
	public function clear_errors() {
		$this->_errors = array();
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
		// @codingStandardsIgnoreLine Plugin-backported
		$cls = get_called_class();

		$error = new WP_Error(
			$cls . Shipper_Model::SCOPE_DELIMITER . $err,
			$msg,
			$data
		);
		$this->_errors[] = $error;

		return $this;
	}
}

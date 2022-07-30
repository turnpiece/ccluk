<?php
/**
 * Shipper tasks: check abstraction class
 *
 * All checks will inherit from this abstraction.
 *
 * @package shipper
 */

/**
 * Checks task class
 */
abstract class Shipper_Task_Check extends Shipper_Task {

	const ERR_WARNING  = 'issue_warning';
	const ERR_BLOCKING = 'issue_blocking';

	/**
	 * Holds performed checks
	 *
	 * @var array
	 */
	private $checks = array();

	/**
	 * Adds a check result
	 *
	 * @param Shipper_Model_Check $check Shipper_Model_Check instance.
	 *
	 * @return object Shipper_Task_Check instance
	 */
	public function add_check( Shipper_Model_Check $check ) {
		$this->checks[] = $check;

		return $this;
	}

	/**
	 * Gets a list of checks
	 *
	 * @return array
	 */
	public function get_checks() {
		return (array) $this->checks;
	}

	/**
	 * Gets a list of checks with errors
	 *
	 * @return array
	 */
	public function get_checks_with_errors() {
		$with_errors = array();
		foreach ( $this->get_checks() as $check ) {
			if ( $check->is_fatal() ) {
				$with_errors[] = $check; }
		}
		return $with_errors;
	}

	/**
	 * Checks whether we have any checks with breaking errors
	 *
	 * @return bool
	 */
	public function has_checks_with_errors() {
		return 0 !== count( $this->get_checks_with_errors() );
	}

	/**
	 * Checks whether the check has been fully completed
	 *
	 * @return bool
	 */
	public function is_done() {
		return true;
	}

	/**
	 * Reset any stored progress indicators
	 *
	 * @return bool
	 */
	public function restart() {
		return true;
	}

	/**
	 * Sets check message.
	 *
	 * Also escapes the message.
	 *
	 * @param object $check Shipper_Model_Check instance.
	 * @param string $msg Message to set.
	 *
	 * @return object Check with message set.
	 */
	public function set_check_message( $check, $msg ) {
		$check->set(
			'message',
			'<p>' . esc_html( $msg ) . '</p>'
		);
		return $check;
	}
}
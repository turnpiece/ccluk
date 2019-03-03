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

	const ERR_WARNING = 'issue_warning';
	const ERR_BLOCKING = 'issue_blocking';

	/**
	 * Holds performed checks
	 *
	 * @var array
	 */
	private $_checks = array();

	/**
	 * Adds a check result
	 *
	 * @param object $check Shipper_Model_Check instance.
	 *
	 * @return object Shipper_Task_Check instance
	 */
	public function add_check( Shipper_Model_Check $check ) {
		$this->_checks[] = $check;

		return $this;
	}

	/**
	 * Gets a list of checks
	 *
	 * @return array
	 */
	public function get_checks() {
		return (array) $this->_checks;
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
	 * Reset any stored progress indicatos
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
<?php
/**
 * Basic timer implementation
 *
 * This implementation is a simple, in-memory timer counter.
 *
 * @package shipper
 */

/**
 * Basic timer implementation class
 */
class Shipper_Helper_Timer_Basic extends Shipper_Helper_Timer {

	/**
	 * Holds registered timers
	 *
	 * @var array
	 */
	private $times = array();

	/**
	 * Gets individual timer hash
	 *
	 * A timer is a hash described with start key and an optional end key.
	 *
	 * @param string $timer Timer ID.
	 *
	 * @return array
	 */
	public function get_timer( $timer ) {
		return isset( $this->times[ $timer ] ) && is_array( $this->times[ $timer ] )
			? $this->times[ $timer ]
			: array();
	}

	/**
	 * Sets individual timer data
	 *
	 * @param string $timer Timer ID.
	 * @param array  $data Timer data - an array with start and optional end keys.
	 */
	public function set_timer( $timer, $data ) {
		$this->times[ $timer ] = $data;
	}

	/**
	 * Reset individual timer
	 *
	 * @param string $timer Timer ID.
	 */
	public function reset( $timer ) {
		$this->times[ $timer ] = array();
	}

	/**
	 * Reset all timers
	 */
	public function reset_all() {
		$this->times = array();
	}

	/**
	 * Get all currently known timers
	 *
	 * @return array A list of timer hashes.
	 */
	public function get_all() {
		return $this->times;
	}
}
<?php
/**
 * Persistent timer implementation
 *
 * This implementation uses stored timer counters for multi-request support.
 *
 * @package shipper
 */

/**
 * Persistent timer implementation class
 */
class Shipper_Helper_Timer_Persistent extends Shipper_Helper_Timer {

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
		$model = new Shipper_Model_Stored_Timer();
		return $model->get( $timer, array() );
	}

	/**
	 * Sets individual timer data
	 *
	 * @param string $timer Timer ID.
	 * @param array  $data Timer data - an array with start and optional end keys.
	 */
	public function set_timer( $timer, $data ) {
		$model = new Shipper_Model_Stored_Timer();
		$model->set( $timer, $data )->save();
	}

	/**
	 * Reset individual timer
	 *
	 * @param string $timer Timer ID.
	 */
	public function reset( $timer ) {
		$model = new Shipper_Model_Stored_Timer();
		$model->set( $timer, array() )->save();
	}

	/**
	 * Reset all timers
	 */
	public function reset_all() {
		$model = new Shipper_Model_Stored_Timer();
		$model->clear()->save();
	}

	/**
	 * Get all currently known timers
	 *
	 * @return array A list of timer hashes.
	 */
	public function get_all() {
		$model = new Shipper_Model_Stored_Timer();
		return $model->get_data();
	}

}
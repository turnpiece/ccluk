<?php
/**
 * Performance timer abstraction
 *
 * All timer implementations will share this API.
 *
 * @package shipper
 */

/**
 * Timer abstraction
 */
abstract class Shipper_Helper_Timer extends Shipper_Helper_Singleton {

	const TIMER_START = 'start';
	const TIMER_END   = 'end';

	/**
	 * Gets individual timer hash
	 *
	 * A timer is a hash described with start key and an optional end key.
	 *
	 * @param string $timer Timer ID.
	 *
	 * @return array
	 */
	abstract public function get_timer( $timer );

	/**
	 * Sets individual timer data
	 *
	 * @param string $timer Timer ID.
	 * @param array  $data Timer data - an array with start and optional end keys.
	 */
	abstract public function set_timer( $timer, $data );

	/**
	 * Reset individual timer
	 *
	 * @param string $timer Timer ID.
	 */
	abstract public function reset( $timer );

	/**
	 * Reset all timers
	 */
	abstract public function reset_all();

	/**
	 * Get all currently known timers
	 *
	 * @return array A list of timer hashes.
	 */
	abstract public function get_all();

	/**
	 * Gets precise timestamp for this very moment
	 *
	 * @return float
	 */
	public function get_timestamp() {
		return microtime( true );
	}

	/**
	 * Starts a timer
	 *
	 * @param string $timer Timer ID.
	 */
	public function start( $timer ) {
		$this->reset( $timer );
		$this->set_time( $timer, self::TIMER_START );
	}

	/**
	 * Whether a timer is started
	 *
	 * @param string $timer Timer ID.
	 *
	 * @return bool
	 */
	public function is_started( $timer ) {
		return false !== $this->get_time( $timer, self::TIMER_START );
	}

	/**
	 * Whether a timer is stopped
	 *
	 * @param string $timer Timer ID.
	 *
	 * @return bool
	 */
	public function is_stopped( $timer ) {
		return false !== $this->get_time( $timer, self::TIMER_END );
	}

	/**
	 * Whether a timer is running
	 *
	 * @param string $timer Timer ID.
	 *
	 * @return bool
	 */
	public function is_running( $timer ) {
		return $this->is_started( $timer ) && ! $this->is_stopped( $timer );
	}

	/**
	 * Gets a timestamp recorded for the time part of a timer.
	 *
	 * @param string $timer Timer ID.
	 * @param string $part Known timer part (see constants) - start or end.
	 *
	 * @return float|bool
	 */
	public function get_time( $timer, $part = false ) {
		$part = self::TIMER_START === $part
			? self::TIMER_START
			: self::TIMER_END;
		$data = $this->get_timer( $timer );
		return ! empty( $data[ $part ] ) && is_numeric( $data[ $part ] )
			? $data[ $part ]
			: false;
	}

	/**
	 * Sets timestamp for the time part of a timer.
	 *
	 * @param string $timer Timer ID.
	 * @param string $part Known timer part (see constants) - start or end.
	 * @param float  $time Optional timestamp - will default to current.
	 */
	public function set_time( $timer, $part, $time = false ) {
		$part = self::TIMER_START === $part
			? self::TIMER_START
			: self::TIMER_END;
		$time = ! empty( $time ) && is_numeric( $time )
			? $time
			: $this->get_timestamp();

		$data          = $this->get_timer( $timer );
		$data[ $part ] = $time;
		$this->set_timer( $timer, $data );
	}

	/**
	 * Stops a timer
	 *
	 * @param string $timer Timer ID.
	 */
	public function stop( $timer ) {
		$this->set_time( $timer, self::TIMER_END );
	}

	/**
	 * Gets time elapsed for a timer.
	 *
	 * If a timer hasn't been stopped, the diff will be based on now.
	 *
	 * @param string $timer Timer ID.
	 *
	 * @return float|bool
	 */
	public function diff( $timer ) {
		$start = $this->get_time( $timer, self::TIMER_START );
		if ( empty( $start ) ) {
			return false; }

		$end = $this->get_time( $timer, self::TIMER_END );
		if ( empty( $end ) ) {
			$end = $this->get_timestamp(); }

		return $end - $start;
	}
}
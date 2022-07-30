<?php
/**
 * Shipper models: Progress model
 *
 * Abstracts process progress information handling.
 *
 * @package shipper
 */

/**
 * Progress model class
 */
class Shipper_Model_Progress extends Shipper_Model {

	const KEY_TOTAL   = 'total';
	const KEY_CURRENT = 'current';
	const KEY_STATUS  = 'status';
	const KEY_ERROR   = 'error';

	const STATUS_IDLE    = 'idle';
	const STATUS_WORKING = 'working';
	const STATUS_DONE    = 'done';
	const STATUS_ERROR   = 'error';

	/**
	 * Constructor
	 *
	 * Sets up data.
	 */
	public function __construct() {
		$this->populate();
	}

	/**
	 * Initializes the data
	 */
	public function populate() {
		$this->set_data(
			array(
				self::KEY_TOTAL   => 1,
				self::KEY_CURRENT => 0,
			)
		);
	}

	/**
	 * Sets total progress steps
	 *
	 * @param int $total Total number of progress steps.
	 *
	 * @return object
	 */
	public function set_total( $total ) {
		$total = (int) $total;
		if ( empty( $total ) ) {
			$total = 1; }

		return $this->set( self::KEY_TOTAL, $total );
	}

	/**
	 * Sets current step
	 *
	 * @param int $current Current step in progression.
	 *
	 * @return object
	 */
	public function set_current( $current ) {
		$current = (int) $current;
		return $this->set( self::KEY_CURRENT, $current );
	}

	/**
	 * Sets error status and optional message
	 *
	 * @param string $msg Optional error message.
	 *
	 * @return object
	 */
	public function set_error( $msg = '' ) {
		if ( ! empty( $msg ) ) {
			$this->set( self::KEY_ERROR, $msg );
		}
		return $this->set( self::KEY_STATUS, self::STATUS_ERROR );
	}

	/**
	 * Gets error message
	 *
	 * @return string|bool Message string, or (bool)false if we're not in error state.
	 * */
	public function get_error() {
		if ( ! $this->has_error() ) {
			// Error status auto-means we're done.
			// If we're not done, nothing to return.
			return false;
		}

		return (string) $this->get( self::KEY_ERROR, '' );
	}

	/**
	 * Checks if we're in error state
	 *
	 * @return bool
	 */
	public function has_error() {
		return self::STATUS_ERROR === $this->get_status();
	}

	/**
	 * Gets current progress percentage
	 *
	 * @return float
	 */
	public function get_percentage() {
		$current = (int) $this->get( self::KEY_CURRENT );
		if ( empty( $current ) ) {
			return 0; }

		$total = (int) $this->get( self::KEY_TOTAL );
		if ( empty( $total ) ) {
			return 0; }

		return ( $current * 100 ) / $total;
	}

	/**
	 * Gets progress status
	 *
	 * @return string Status constant value
	 */
	public function get_status() {
		return $this->get( self::KEY_STATUS, self::STATUS_IDLE );
	}

	/**
	 * Checks whether the progress is done
	 *
	 * @return bool
	 */
	public function is_done() {
		return in_array(
			$this->get_status(),
			array( self::STATUS_DONE, self::STATUS_ERROR ),
			true
		);
	}

	/**
	 * Update progress progression
	 *
	 * Sets status as a side-effect.
	 *
	 * @param int $step_size Update current by amount.
	 *
	 * @return bool
	 */
	public function update( $step_size = 1 ) {
		if ( $this->is_done() ) {
			return false; // We're done - nothing to update.
		}

		$step_size = (int) $step_size;

		if ( empty( $step_size ) ) {
			$step_size = 1;
		}

		$current = (int) $this->get( self::KEY_CURRENT );

		if ( empty( $current ) ) {
			$current = 0;
		}

		$total = (int) $this->get( self::KEY_TOTAL );

		if ( empty( $total ) ) {
			$total = 1;
		}

		$current += $step_size;

		if ( $current > $total ) {
			// We're either at 100, or lower.
			$total = $current;
		}

		$this->set( self::KEY_CURRENT, $current );
		$status = self::STATUS_WORKING;

		if ( $total === $current ) {
			// We're done!
			$status = self::STATUS_DONE;
		}

		$this->set( self::KEY_STATUS, $status );

		return true;
	}
}
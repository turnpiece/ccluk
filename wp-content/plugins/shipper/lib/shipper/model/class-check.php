<?php
/**
 * Shipper models: check model class
 *
 * Used by Shipper_Task_Checking implementations.
 *
 * @package shipper
 */

/**
 * Shipper check model class
 */
class Shipper_Model_Check extends Shipper_Model {

	const STATUS_PENDING = 'pending';
	const STATUS_OK      = 'ok';
	const STATUS_WARNING = 'warning';
	const STATUS_ERROR   = 'error';

	/**
	 * Constructor
	 *
	 * @param string $title Check title.
	 */
	public function __construct( $title ) {
		$this->set_data(
			array(
				'title'   => $title,
				'message' => '',
				'status'  => self::STATUS_PENDING,
			)
		);
	}

	/**
	 * Completes the check, with status
	 *
	 * @param string $status Task completion status.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function complete( $status ) {
		if ( self::STATUS_PENDING === $this->get( 'status' ) ) {
			$this->set( 'status', $status );
		}

		return $this;
	}

	/**
	 * Checks whether the check failed
	 *
	 * @return bool
	 */
	public function is_failed() {
		$status = $this->get( 'status' );

		if ( self::STATUS_PENDING === $status ) {
			return false;
		}

		return self::STATUS_OK !== $status;
	}

	/**
	 * Checks whether the check failed in a catastrophic way
	 *
	 * @return bool
	 */
	public function is_fatal() {
		if ( ! $this->is_failed() ) {
			return false;
		}

		$status = $this->get( 'status' );

		return self::STATUS_ERROR === $status;
	}
}
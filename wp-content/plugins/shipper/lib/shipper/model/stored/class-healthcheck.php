<?php
/**
 * Shipper models: health status model
 *
 * Holds the migration health status info
 *
 * @package shipper
 */

/**
 * Stored migration health status info
 */
class Shipper_Model_Stored_Healthcheck extends Shipper_Model_Stored {

	const KICKSTARTED = 'kickstarted';
	const STALLED     = 'stalled';
	const PINGED      = 'pinged';

	const THRESHOLD_KICKSTARTS = 3;

	/**
	 * Constructor
	 *
	 * Sets up appropriate storage namespace
	 */
	public function __construct() {
		// Store the model in the database.
		parent::__construct( 'healthcheck', true );
	}

	/**
	 * Check whether we're dealing with a slow migration
	 *
	 * @return bool
	 */
	public function is_slow_migration() {
		if ( ! empty( $this->get( self::PINGED ) ) ) {
			// At some point, we were remotely reactivated.
			// It's slow.
			return true;
		}

		if ( ! empty( $this->get( self::STALLED ) ) ) {
			// At some point we had to force-clear process locks.
			// It's slow.
			return true;
		}

		$kickstarts = $this->get( self::KICKSTARTED, array() );

		return count( $kickstarts ) > $this->get_slow_kickstarts_threshold();
	}

	/**
	 * Gets the max number of kickstarts
	 *
	 * This is the maximum number of acceptable kickstarts.
	 * After this, we consider the migration to be slow.
	 *
	 * @return int
	 */
	public function get_slow_kickstarts_threshold() {
		return (int) apply_filters(
			'shipper_healthcheck_kickstarts_threshold',
			self::THRESHOLD_KICKSTARTS
		);
	}
}
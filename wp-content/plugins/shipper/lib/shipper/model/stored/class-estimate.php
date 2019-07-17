<?php
/**
 * Shipper models: cached destinations class
 *
 * Holds migration estimates related info.
 *
 * Rates:
 * 0.000031705173
 * 0.0000474953824992674
 * 0.0000280331080042246
 * 0.0000334056712034759
 * 0.0000822984448926682
 * 0.0000659464953038672
 * 0.0000659464953038672
 *
 * @package shipper
 */

/**
 * Stored destinations model class
 */
class Shipper_Model_Stored_Estimate extends Shipper_Model_Stored {

	/**
	 * Static message helper
	 *
	 * Used in templates.
	 *
	 * @param int $size Optional package size in bytes.
	 *
	 * @return string
	 */
	static public function get_estimated_migration_time_msg( $size = 0 ) {
		$me = new self;
		return $me->get_migration_time_msg( $size );
	}

	/**
	 * Static message helper
	 *
	 * Used in templates.
	 *
	 * @since v1.0.3
	 *
	 * @param int $size Optional package size in bytes.
	 *
	 * @return string
	 */
	static public function get_estimated_migration_time_span( $size = 0 ) {
		$me = new self;
		return $me->get_migration_time_span( $size );
	}

	/**
	 * Constructor
	 *
	 * Sets up appropriate storage namespace
	 */
	public function __construct() {
		parent::__construct( 'estimate' );
	}

	/**
	 * Gets migration estimated time span, in hours
	 *
	 * @param int $size Optional package size in bytes.
	 *
	 * @return array
	 */
	public function get_migration_time_span( $size = 0 ) {
		$time_per_b = 0.000050690110;
		$package_size = ! empty( $size ) && is_numeric( $size )
			? (float) $size
			: $this->get( 'package_size', 0 );

		$estimate_secs = $time_per_b * $package_size;
		$padding = $estimate_secs * 0.2;

		$time_low_estimate = ! empty( $package_size )
			? max( 1, floor( ($estimate_secs - $padding) / HOUR_IN_SECONDS ) )
			: 0;
		$time_high_estimate = ! empty( $package_size )
			? ceil( ($estimate_secs + $padding) / HOUR_IN_SECONDS )
			: 0;

		return array(
			'high' => $time_high_estimate,
			'low' => $time_low_estimate,
		);
	}

	/**
	 * Returns formatted estimated migration time message
	 *
	 * @return string
	 */
	public function get_migration_time_msg() {
		$estimated_span = $this->get_migration_time_span();
		if ( empty( $estimated_span['high'] ) || empty( $estimated_span['low'] ) ) {
			return '';
		}

		$package_size = $this->get( 'package_size', 0 );
		$msg =  __( 'Your website is %1$s in size which <b>could take %2$d to %3$d hours to migrate</b> as we are using our advanced API to make sure the process is as stable as possible.', 'shipper' );

		return sprintf(
			$msg,
			size_format( $package_size ),
			$estimated_span['low'],
			$estimated_span['high']
		);
	}
}

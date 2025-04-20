<?php
/**
 * Shipper models: cached destinations class
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
	public static function get_estimated_migration_time_msg( $size = 0 ) {
		$me = new self();
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
	 * @return array
	 */
	public static function get_estimated_migration_time_span( $size = 0 ) {
		$me = new self();
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
	 * Gets migration estimated time span, in hours or minutes
	 *
	 * @param int $size Optional package size in bytes.
	 *
	 * @return array
	 */
	public function get_migration_time_span( $size = 0 ) {
		/**
		 * 331MB for 11 minutes = 347078656 bytes in 660s
		 */
		$number_of_rows    = $this->get_estimated_number_of_table_rows();
		$time_for_database = $this->get_estimated_time_for_database( $number_of_rows );
		$package_size      = ! empty( $size ) && is_numeric( $size )
			? (float) $size
			: $this->get( 'package_size', 0 );
		$time_per_b        = 0.000002593 * 2;
		$estimate_secs     = ( $time_per_b * $package_size ) + ( ( $package_size / 2097152 ) * 2 ) + $time_for_database;
		$padding           = $estimate_secs * 0.2;

		// The minimum time for package import on the remote site is 2 minutes, so add that anyway.
		if ( $estimate_secs <= MINUTE_IN_SECONDS * 2 ) {
			$estimate_secs += MINUTE_IN_SECONDS * 2;
		}

		$low_estimated_time_in_minutes  = ! empty( $package_size )
			? max( 1, floor( $estimate_secs - $padding ) / MINUTE_IN_SECONDS )
			: 0;
		$high_estimated_time_in_minutes = ! empty( $package_size )
			? max( 1, ceil( $estimate_secs + $padding ) / MINUTE_IN_SECONDS )
			: 0;

		return array(
			'unit' => $high_estimated_time_in_minutes > 60 ? __( 'hours', 'shipper' ) : __( 'minutes', 'shipper' ),
			'high' => $this->get_human_readable_time( $high_estimated_time_in_minutes ),
			'low'  => $this->get_human_readable_time( $low_estimated_time_in_minutes ),
		);
	}

	/**
	 * Get human readable time
	 *
	 * @since 1.1.4
	 *
	 * @param int $number_of_minutes number of minutes.
	 *
	 * @return string
	 */
	public function get_human_readable_time( $number_of_minutes ) {
		if ( $number_of_minutes < 60 ) {
			/* translators: %d: %d: number of minute. */
			$estimated_time = sprintf( _n( '%d minute', '%d minutes', $number_of_minutes, 'shipper' ), $number_of_minutes );
		} else {
			$time    = $this->get_hours_and_minutes( $number_of_minutes );
			$hours   = $time['hours'];
			$minutes = $time['minutes'];

			/* translators: %d: %d: number of hours. */
			$estimated_time = sprintf( _n( '%d hour', '%d hours', $hours, 'shipper' ), $hours );
			$estimated_time = $minutes
				/* translators: %d: %d: number of minute. */
				? $estimated_time . sprintf( _n( ' %d minute', ' %d minutes', $minutes, 'shipper' ), $minutes )
				: $estimated_time;
		}

		return apply_filters( 'shipper_get_human_readable_time', $estimated_time );
	}


	/**
	 * Get hours and minutes from number of minutes in an array
	 *
	 * @since 1.1.4
	 *
	 * @param int $number_of_minutes number of minutes.
	 *
	 * @return array
	 */
	public function get_hours_and_minutes( $number_of_minutes ) {
		if ( $number_of_minutes < 60 ) {
			return array(
				'hours'   => 0,
				'minutes' => $number_of_minutes,
			);
		}

		$result  = $number_of_minutes / 60;
		$hours   = is_float( $result ) ? explode( '.', $result )[0] : $result;
		$minutes = ceil($number_of_minutes % 60);

		return array(
			'hours'   => $hours,
			'minutes' => $minutes,
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

		/* translators: %1$s %2$s: package size and eta time. */
		$msg = __( 'Your website is %1$s in size which <b>could take up to %2$s to migrate</b> as we are using our advanced API to make sure the process is as stable as possible.', 'shipper' );

		return sprintf(
			$msg,
			size_format( $package_size ),
			$estimated_span['high']
		);
	}

	/**
	 * Get estimated number of table rows
	 *
	 * @since 1.1.4
	 *
	 * @return int
	 */
	public function get_estimated_number_of_table_rows() {
		$db                = new Shipper_Model_Database();
		$exclusion         = new Shipper_Model_Stored_MigrationMeta();
		$tables            = $db->get_tables_list();
		$excluded_tables   = $exclusion->get( Shipper_Model_Stored_MigrationMeta::KEY_EXCLUSIONS_DB, array() );
		$tables_to_include = array_diff( $tables, $excluded_tables );

		return array_sum( $db->get_tables_rows_count( $tables_to_include ) );
	}

	/**
	 * Get estimated time for database rows in seconds
	 *
	 * @since 1.1.4
	 *
	 * @param int $number_of_rows number of rows.
	 *
	 * @return int
	 */
	public function get_estimated_time_for_database( $number_of_rows ) {
		if ( empty( $number_of_rows ) || intval( $number_of_rows ) < 1 ) {
			$number_of_rows = 0;
		}

		$time_per_row_in_seconds = 0.0015; // Assuming 2 seconds is required for every 1k rows.
		$seconds                 = round( $number_of_rows * $time_per_row_in_seconds );

		return apply_filters( 'shipper_get_estimated_time_for_database', $seconds );
	}
}
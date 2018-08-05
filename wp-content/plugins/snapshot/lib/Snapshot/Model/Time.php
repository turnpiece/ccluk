<?php // phpcs:ignore

class Snapshot_Model_Time {

	private static $_instance;

	public static function get () {
		if (empty(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Returns the very next Monday in future, relative to timestamp
	 *
	 * @param int $timestamp Pivot date for finding next Monday as UNIX timestamp.
	 *
	 * @return int
	 */
	public function get_next_monday ($timestamp= false) {
		$timestamp = !empty($timestamp)
			? $timestamp
			: $this->get_utc_time()
		;
		$monday = strtotime('this Monday', $timestamp);
		if ($monday < $timestamp) $monday += 7 * DAY_IN_SECONDS;

		return $monday;
	}

	/**
	 * Centralized local timestamp fetching
	 *
	 * @return int UNIX timestamp
	 */
	public function get_local_time () {
		return (int)apply_filters(
			$this->get_filter('local_timestamp'),
			current_time('timestamp', 0)
		);
	}

	/**
	 * Centralized UTC timestamp fetching
	 *
	 * @return int UNIX timestamp
	 */
	public function get_utc_time () {
		return (int)apply_filters(
			$this->get_filter('utc_timestamp'),
			current_time('timestamp', 1)
		);
	}

	/**
	 * Gets time diff from UTC
	 *
	 * @return float Hours
	 */
	public function get_utc_offset () {
		$tz = get_option( 'timezone_string' );
		if ( $tz ) {
			// This actually returns seconds. Convert to hours.
			return timezone_offset_get( timezone_open( $tz ), new DateTime() ) / HOUR_IN_SECONDS;
		} else {
			return floatval( get_option( 'gmt_offset' ) );
		}
	}

	/**
	 * Gets time diff from UTC
	 *
	 * @return int Seconds
	 */
	public function get_utc_diff () {
		return $this->get_utc_offset() * HOUR_IN_SECONDS;
	}

	/**
	 * Convert a local timestamp to the UTC timestamp
	 *
	 * @param int $local_time Local timestamp to convert
	 *
	 * @return int
	 */
	public function to_utc_time ($local_time) {
		if (!is_numeric($local_time)) return $local_time;
		$local_time = (int)$local_time;

		return $local_time - $this->get_utc_diff();
	}

	/**
	 * Convert an UTC timestamp to the local timestamp
	 *
	 * @param int $utc_time UTC timestamp to convert
	 *
	 * @return int
	 */
	public function to_local_time ($utc_time) {
		if (!is_numeric($utc_time)) return $utc_time;
		$utc_time = (int)$utc_time;

		return $utc_time + $this->get_utc_diff();
	}

	/**
	 * Filter/action name getter
	 *
	 * @param string $filter Filter name to convert
	 *
	 * @return string Full filter name
	 */
	public function get_filter ($filter= false) {
		if (empty($filter)) return false;
		if (!is_string($filter)) return false;
		return 'snapshot-model-time-' . $filter;
	}

	/**
	 * Get local hours for equivalent UTC hours
	 *
	 * @param $utc_time
	 *
	 * @return float|int Local hours in second
	 */
	public function convert_to_local_timestamp( $utc_time ) {
		if ( ! is_numeric( $utc_time ) ) {
			return $utc_time;
		}
		$utc_time = (int) $utc_time;
		$offset   = $this->get_utc_diff();

		$seconds = $utc_time - $offset; // Deal with seconds, not hours
		if ( $seconds < 0 ) {
			$seconds += DAY_IN_SECONDS;
		}
		if ( $seconds >= DAY_IN_SECONDS ) {
			$seconds -= DAY_IN_SECONDS;
		}
		if ( 0 === intval( $seconds ) ) {
			$seconds = 1;
		}

		return $seconds;

	}
}
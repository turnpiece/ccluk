<?php // phpcs:ignore

class Snapshot_Model_Transient {

	const TTL_CLEAR = 'clear';
	const TTL_TICK = 'tick';
	const TTL_SHORT = 'short';
	const TTL_CACHE = 'cache';
	const TTL_LONG = 'long';
	const TTL_PERMA = 'perma';

	const EXPIRY_SUFFIX = '__expired__';

	private static $_expired_cache = array();

	/**
	 * Gets *current* transient value
	 *
	 * If the transient is expired, drops the regular transient and updates expired one
	 *
	 * @param string $transient Transient key
	 * @param mixed $fallback Optional fallback value, defaults to (bool)false
	 *
	 * @return mixed
	 */
	public static function get ($transient, $fallback= false) {
		$raw = get_site_option($transient, false);
		if (false === $raw) return $fallback;

		if (!is_array($raw) || !isset($raw['value'])) return $fallback;
		$value = $raw['value'];
		$now = time();

		$expiry = !empty($raw['expiry']) && is_numeric($raw['expiry'])
			? (int)$raw['expiry']
			: $now + self::ttl(self::get_default_ttl())
		;

		if ($now > $expiry) {
			delete_site_option($transient);
			update_site_option($transient . self::EXPIRY_SUFFIX, $value, false);
		}

		self::$_expired_cache[$transient] = $now > $expiry;

		return $expiry >= $now
			? $value
			: $fallback
		;
	}

	/**
	 * Gets expired transient value
	 *
	 * @param string $transient Transient key
 	 * @param mixed $fallback Optional fallback value, defaults to (bool)false
 	 *
 	 * @return mixed
	 */
	public static function get_expired ($transient, $fallback= false) {
		$value = get_site_option($transient . self::EXPIRY_SUFFIX, false);
		if (false !== $value)
			self::$_expired_cache[$transient] = true;
		return false !== $value
			? $value
			: $fallback
		;
	}

	/**
	 * Gets current, or expired transient value
	 *
	 * @param string $transient Transient key
 	 * @param mixed $fallback Optional fallback value, defaults to (bool)false
 	 *
 	 * @return mixed
	 */
	public static function get_any ($transient, $fallback= false) {
		$value = self::get($transient, false);

		return false === $value
			? self::get_expired($transient, $fallback)
			: $value
		;
	}

	/**
	 * Check if the value comes from an expired transient
	 *
	 * Useful in combination with `get_any`
	 *
	 * @param string $transient Transient key
	 *
	 * @return bool
	 */
	public static function is_expired ($transient) {
		if (!isset(self::$_expired_cache[$transient])) {
			self::get_any($transient);
		}
		return !empty(self::$_expired_cache[$transient]);
	}

	/**
	 * Force transient to expire
	 *
	 * @param string $transient Transient key
	 *
	 * @return bool
	 */
	public static function expire ($transient) {
		if (empty($transient)) return false;

		$value = self::get($transient, false);
		return self::set(
			$transient,
			$value,
			self::ttl(self::TTL_CLEAR)
		);
	}

	/**
	 * Set current transient value with timeout in the future
	 *
	 * Also clears up any previously stored expired transients
	 *
	 * @param string $transient Transient key
	 * @param mixed $value Value to store
	 * @param int $timeout Optional timeout, defaults to our own TTL
	 */
	public static function set ($transient, $value, $timeout= 0) {
		if (empty($transient)) return false;
		$timeout = !empty($timeout) && is_numeric($timeout)
			? (int)$timeout
			: self::ttl(self::get_default_ttl())
		;

		$now = time();
		$expiry = $now + (int)$timeout;

		// Clear previous old value
		delete_site_option($transient . self::EXPIRY_SUFFIX);

		// Clear internal queue
		if (isset(self::$_expired_cache[$transient])) unset(self::$_expired_cache[$transient]);

		// Store the new value with expiry
		return update_site_option(
            $transient, array(
				'value' => $value,
				'expiry' => $expiry,
			), false
		);
	}

	/**
	 * Deletes the transient
	 *
	 * @param string $transient Transient key
	 *
	 * @return bool
	 */
	public static function delete ($transient) {
		delete_site_option($transient . self::EXPIRY_SUFFIX);
		delete_site_option($transient);
		return true;
	}

	/**
	 * Gets predefined time period duration
	 *
	 * @param string $period_name Predefined period name (one of the class TTL_* constants)
	 *
	 * @return int
	 */
	public static function ttl ($period_name) {
		static $ttls;
		if (empty($ttls)) {
			$ttls = self::get_known_ttls();
		}
		$ttl = empty($period_name) || !in_array($period_name, array_keys($ttls), true)
			? self::get_default_ttl()
			: $period_name
		;
		return (int)$ttls[$ttl];
	}

	/**
	 * Gets a map of known named periods and their durations
	 *
	 * @return array
	 */
	public static function get_known_ttls () {
		return array(
			self::TTL_CLEAR => DAY_IN_SECONDS * -1,
			self::TTL_TICK => 60,
			self::TTL_SHORT => 600,
			self::TTL_CACHE => HOUR_IN_SECONDS,
			self::TTL_LONG => HOUR_IN_SECONDS * 5,
			self::TTL_PERMA => DAY_IN_SECONDS,
		);
	}

	/**
	 * Gets default named period name
	 *
	 * @return string
	 */
	public static function get_default_ttl () {
		return self::TTL_CACHE;
	}
}
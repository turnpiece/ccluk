<?php
/**
 * Shipper helpers: hashing utilities
 *
 * @package shipper
 */

/**
 * Shipper hash class
 */
class Shipper_Helper_Hash {

	const ALGO_DELIMITER = '//';

	const INTERVAL_SHORT  = 120;
	const INTERVAL_MEDIUM = 600;
	const INTERVAL_LONG   = 3600;

	/**
	 * Constructor
	 *
	 * Also sets validity interval.
	 *
	 * @param int $interval Default validity interval.
	 */
	public function __construct( $interval = false ) {
		if ( ! empty( $interval ) && is_numeric( $interval ) ) {
			$this->interval = (int) $interval;
		}
	}

	/**
	 * Gets simply concealed (obfuscated) string value
	 *
	 * @param string $what Value to conceal.
	 *
	 * @return string
	 */
	public function get_concealed( $what = '' ) {
		$tahw   = strrev( $what );
		$secret = $this->get_obfuscation_key();

		$algo = $this->get_default_algo();
		$hash = hash_hmac( $algo, "{$what}{$tahw}", $secret );
		return $hash;
	}

	/**
	 * Gets key string used for obfuscation/concealement
	 *
	 * @param string $fallback Optional fallback value to use.
	 *
	 * @return string
	 */
	public function get_obfuscation_key( $fallback = false ) {
		$key = shipper_get_site_uniqid( shipper_network_home_url() );

		if ( empty( $key ) && empty( $fallback ) ) {
			$fallback = md5( uniqid( 'shipper' ) );
		}

		$algo = $this->get_default_algo();
		$raw  = strrev( str_rot13( $key ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_str_rot13

		$key = ! empty( $key )
			? hash_hmac( $algo, $raw, md5( $raw ) )
			: $fallback;

		/**
		 * Gets the site-specific obfuscation key
		 *
		 * @param string $key Site-specific obfuscation key.
		 * @param string $fallback Optional provided fallback.
		 *
		 * @return string
		 */
		return apply_filters(
			'shipper_hash_obfuscation_key',
			$key,
			$fallback
		);
	}

	/**
	 * Gets validity interval
	 *
	 * @return int
	 */
	public function get_interval() {
		if ( empty( $this->interval ) ) {
			$this->interval = self::INTERVAL_MEDIUM;
		}
		return (int) $this->interval;
	}

	/**
	 * Gets actual hash
	 *
	 * @param string $what String to hash.
	 * @param string $secret Optional secret to use in hashing.
	 *
	 * @return string Formatted hash
	 */
	public function get_hash( $what, $secret = '' ) {
		if ( empty( $secret ) ) {
			$secret = $this->get_default_secret();
		}

		$validity = $this->get_interval();
		$tick     = $this->get_tick( $validity );

		$algo = $this->get_default_algo();
		$hash = hash_hmac( $algo, "{$what}{$tick}", $secret );

		$final = join(
			self::ALGO_DELIMITER,
			array(
				$algo,
				$hash,
			)
		);

		return $final;
	}

	/**
	 * Gets time validity base window (tick)
	 *
	 * @param int $validity Number of seconds.
	 * @param int $time Optional timestamp.
	 *
	 * @return float
	 */
	public function get_tick( $validity, $time = false ) {
		if ( empty( $time ) ) {
			$time = time(); }
		$tick = ceil( $time / $validity ) * $validity;

		return $tick;
	}

	/**
	 * Checks if we know the requested hash algorithm
	 *
	 * @param string $algo Hash algorithm.
	 *
	 * @return bool
	 */
	public function is_known_algo( $algo = false ) {
		if ( empty( $algo ) ) {
			return false; }
		return in_array( $algo, hash_algos(), true );
	}

	/**
	 * Checks whether the hash algorithm is known locally, and allowed for usage
	 *
	 * @param string $algo Hash algorithm.
	 *
	 * @return bool
	 */
	public function is_preferred_algo( $algo = false ) {
		if ( ! $this->is_known_algo( $algo ) ) {
			return false; }
		return in_array( $algo, $this->get_preferred_algos(), true );
	}

	/**
	 * Gets a finite list of preferred hash algorithms
	 *
	 * Hash algorithm are listed in order of preference
	 *
	 * @return array
	 */
	public function get_preferred_algos() {
		return array(
			'sha512',
			'sha256',
			'sha1',
			'md5',
		);
	}

	/**
	 * Gets the preferred hash algorithm
	 *
	 * @param string $preferred Optional preferred hash algorithm.
	 *
	 * @return string
	 */
	public function get_preferred_algo( $preferred = false ) {
		$preferred = ! empty( $preferred ) ? $preferred : $this->get_default_algo();

		if ( ! $this->is_preferred_algo( $preferred ) ) {
			$preferred = $this->get_default_algo();
		}

		return $preferred;
	}

	/**
	 * Gets the default hash algorithm for usage
	 *
	 * @return string
	 */
	public function get_default_algo() {
		static $preferred;

		if ( ! $preferred ) {
			foreach ( $this->get_preferred_algos() as $algo ) {
				if ( ! $this->is_known_algo( $algo ) ) {
					continue; }

				$preferred = $algo;
				break;
			}
		}

		return $preferred;
	}

	/**
	 * Gets default secret string
	 *
	 * Used for local-to-local hashes.
	 *
	 * @return string
	 */
	public function get_default_secret() {
		$raw    = $this->get_obfuscation_key();
		$secret = md5( strrev( str_rot13( $raw ) ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_str_rot13
		return $secret;
	}
}
<?php
/**
 * Shipper models: cached destinations class
 *
 * Holds list of available destinations for Shipper migrations.
 *
 * This is a timed destinations abstraction.
 * It will get updated from the Hub periodically, in an appropriate task.
 *
 * @package shipper
 */

/**
 * Stored destinations model class
 */
class Shipper_Model_Stored_Destinations extends Shipper_Model_Stored {

	/**
	 * Constructor
	 *
	 * Sets up appropriate storage namespace
	 */
	public function __construct() {
		parent::__construct( 'destinations' );
	}

	/**
	 * Gets time to live for this storage bucket
	 *
	 * This data will be re-synced from the Hub periodically.
	 * It can be also refreshed on demand, so it can fairly long lifetime.
	 *
	 * @return int Time to live, in seconds
	 */
	public function get_ttl() {
		return Shipper_Model_Stored::TTL_LONG;
	}

	/**
	 * Check if a destination is valid
	 *
	 * @param array $dest Raw destination hash.
	 *
	 * @return bool
	 */
	public function is_valid_destination( $dest = array() ) {
		if ( empty( $dest['domain'] ) ) {
			return false;
		}
		if ( empty( $dest['site_id'] ) || ! is_numeric( $dest['site_id'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Gets a destination hash by its site ID
	 *
	 * @param int $site_id Site ID of the destination to get.
	 *
	 * @return array Destination hash
	 */
	public function get_by_site_id( $site_id ) {
		if ( ! is_numeric( $site_id ) ) {
			return array();
		}

		$domains = $this->get_data();
		foreach ( $domains as $domain ) {
			if ( empty( $domain['site_id'] ) ) {
				continue;
			}
			if ( (int) $domain['site_id'] === (int) $site_id ) {
				return $domain;
			}
		}

		return array();
	}

	/**
	 * Gets a destination hash by its site domain
	 *
	 * @param string $source Site domain of the destination to get.
	 *
	 * @return array Destination hash
	 */
	public function get_by_domain( $source ) {
		if ( ! is_string( $source ) ) {
			return array();
		}

		$source = self::get_normalized_domain( $source );

		$domains = $this->get_data();
		foreach ( $domains as $domain ) {
			if ( empty( $domain['domain'] ) ) {
				continue;
			}
			if ( $domain['domain'] === $source ) {
				return $domain;
			}
		}

		return array();
	}

	/**
	 * Check if the supplied destination is actually current site
	 *
	 * @param mixed $destination Either a destination hash, a domain or numeric site ID.
	 *
	 * @return bool
	 */
	public function is_current( $destination ) {
		if ( is_string( $destination ) && ! is_numeric( $destination ) ) {
			return self::get_normalized_domain( $destination ) === self::get_current_domain();
		}
		$current = $this->get_current();

		if ( ! is_array( $destination ) && is_numeric( $destination ) ) {
			return (int) $current['site_id'] === (int) $destination;
		}

		if ( is_array( $destination ) && ! empty( $destination['domain'] ) && ! empty( $destination['site_id'] ) ) {
			return $current['domain'] === $destination['domain'] && $current['site_id'] === $destination['site_id'];
		}

		return false;
	}

	/**
	 * Gets current site destination hash
	 *
	 * @return array
	 */
	public function get_current() {
		$domains = $this->get_data();
		$current = self::get_current_domain();
		foreach ( $domains as $domain ) {
			if ( empty( $domain['domain'] ) ) {
				continue;
			}
			if ( $current !== $domain['domain'] ) {
				continue;
			}

			return $domain;
		}

		return array(
			'domain'    => $current,
			'home_url'  => self::get_normalized_domain( home_url() ),
			'admin_url' => network_admin_url(),
			'site_id'   => null,
		);
	}

	/**
	 * Gets current site domain
	 *
	 * @return string
	 */
	public static function get_current_domain() {
		return self::get_normalized_domain( network_site_url() );
	}

	/**
	 * Normalizes an URL into a domain
	 *
	 * @param string $url URL to normalize.
	 *
	 * @return string
	 */
	public static function get_normalized_domain( $url ) {
		return untrailingslashit( shipper_get_protocol_agnostic( $url, true ) );
	}
}
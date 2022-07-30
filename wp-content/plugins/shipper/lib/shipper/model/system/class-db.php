<?php
/**
 * Shipper models: DB system info
 *
 * @package shipper
 */

/**
 * MySQL info model class
 */
class Shipper_Model_System_Db extends Shipper_Model {

	const VERSION    = 'version';
	const QUERY_SIZE = 'max_allowed_packet';
	const CHARSET    = 'charset';
	const COLLATE    = 'collate';

	/**
	 * Constructor
	 *
	 * Populates internal data structure
	 */
	public function __construct() {
		$this->populate();
	}

	/**
	 * Populates internal data structure
	 */
	public function populate() {
		global $wpdb;

		$results = $wpdb->get_row(
			'SELECT @@max_allowed_packet AS pkt, @@version AS ver',
			ARRAY_A
		);

		$this->set( self::QUERY_SIZE, (int) $results['pkt'] );
		$this->set( self::VERSION, $results['ver'] );

		$this->set( self::CHARSET, $wpdb->charset );
		$this->set( self::COLLATE, $wpdb->collate );
	}

	/**
	 * Get value formatted nicely for output
	 *
	 * @param string $key Value key.
	 * @param mixed  $fallback What to use as fallback.
	 *
	 * @return string
	 */
	public function get_output_value( $key, $fallback = false ) {
		switch ( $key ) {
			case self::QUERY_SIZE:
				return size_format( $this->get( $key, $fallback ) );
		}

		return $this->get( $key, $fallback );
	}
}
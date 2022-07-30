<?php
/**
 * Shipper storage helper
 *
 * Centralizes access to shipper stored data.
 *
 * @package shipper
 */

/**
 * Storage helper class
 */
abstract class Shipper_Helper_Storage {

	/**
	 * Loads current state from storage medium
	 *
	 * @return bool
	 */
	abstract public function load();

	/**
	 * Saves current state to implementation-specific storage medium
	 *
	 * @return bool
	 */
	abstract public function save();

	const DEFAULT_NAMESPACE = 'shipper-storage';

	/**
	 * Holds namespaced storage instances
	 *
	 * @var array()
	 */
	private static $buckets = array();

	/**
	 * Current namespaced storage instance data
	 *
	 * @var array
	 */
	public $data = array();

	/**
	 * Set-specific data namespace
	 *
	 * @var string
	 */
	private $namespace = '';

	/**
	 * Constructor (internal)
	 *
	 * @param string $namespace Storage namespace.
	 */
	private function __construct( $namespace ) {
		$this->set_namespace( $namespace );
	}

	/**
	 * No clones, please
	 */
	private function __clone() {
	}

	/**
	 * Namespaced instance getter
	 *
	 * @param string $namespace Storage namespace.
	 * @param bool   $alt Use alternative storage method.
	 *
	 * @return object Shipper_Helper_Storage namespaced storage instance
	 */
	public static function get( $namespace, $alt = false ) {
		$cls = ! empty( $alt )
			? 'Shipper_Helper_Storage_Db'
			: 'Shipper_Helper_Storage_File';
		if ( ! isset( self::$buckets[ $namespace ] ) ) {
			self::$buckets[ $namespace ] = new $cls( $namespace );
		}
		return self::$buckets[ $namespace ];
	}

	/**
	 * Sets set-specific storage part ID
	 *
	 * @param string $namespace Optional set-specific namespace.
	 *
	 * @return string New value
	 */
	public function set_namespace( $namespace = '' ) {
		$namespace = is_string( $namespace ) && ! empty( $namespace )
			? strtolower( preg_replace( '/[^_a-z0-9]/', '', $namespace ) )
			: self::DEFAULT_NAMESPACE;

		if ( false === stristr( $namespace, self::DEFAULT_NAMESPACE ) ) {
			$namespace = self::DEFAULT_NAMESPACE . "-{$namespace}";
		}

		return $this->namespace = $namespace; // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found
	}

	/**
	 * Gets current set-specific storage part ID
	 *
	 * @return string
	 */
	public function get_namespace() {
		return (string) $this->namespace;
	}

	/**
	 * Collapses data into string representation
	 *
	 * @param mixed $data Data to encode.
	 *
	 * @return string
	 */
	public function encode( $data ) {
		return wp_json_encode( $data );
	}

	/**
	 * Expands data from string to internal representation
	 *
	 * @param string $str Data string to expand.
	 *
	 * @return mixed Data, or empty array on failure
	 */
	public function decode( $str ) {
		$data = json_decode( $str, true );

		return JSON_ERROR_NONE === json_last_error()
			? $data
			: array();
	}
}
<?php // phpcs:ignore
/**
 * Storage abstraction root.
 * All storage implementations will be sharing the common API, with concrete implementations.
 *
 * @package snapshot
 */

/**
 * Common storage API and internal logic
 */
abstract class Snapshot_Model_Storage {

	const DEFAULT_NAMESPACE = 'snapshot-storage';

	/**
	 * Loads current state from implementation-specific storage medium
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

	/**
	 * Set-specific data namespace
	 *
	 * @var string
	 */
	protected $_namespace = '';

	/**
	 * Internal dataset storage
	 *
	 * @var array
	 */
	protected $_data = array();

	/**
	 * Constructor
	 *
	 * @param string $namespace Optional set-specific namespace.
	 */
	public function __construct( $namespace = '' ) {
		$this->set_namespace( $namespace );
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
			: self::DEFAULT_NAMESPACE
		;

		if ( false === stristr( $namespace, self::DEFAULT_NAMESPACE ) ) {
			$namespace = self::DEFAULT_NAMESPACE . "-{$namespace}";
		}

		$this->_namespace = $namespace;
		return $this->_namespace;
	}

	/**
	 * Gets current set-specific storage part ID
	 *
	 * @return string
	 */
	public function get_namespace() {
		return (string) $this->_namespace;
	}

	/**
	 * Gets specific value from storage
	 *
	 * @param string $key ID of the value to get.
	 * @param mixed  $fallback Optional fallback value.
	 *
	 * @return mixed Value, or fallback if not set
	 */
	public function get_value( $key, $fallback = false ) {
		return isset( $this->_data[ $key ] )
			? $this->_data[ $key ]
			: $fallback
		;
	}

	/**
	 * Sets specific value
	 *
	 * Does *not* sync storage automatically.
	 *
	 * @param string $key ID of the value to set.
	 * @param mixed  $value Value to set.
	 *
	 * @return bool
	 */
	public function set_value( $key, $value ) {
		$this->_data[ $key ] = $value;
		return true;
	}

	/**
	 * Resets internal data representation
	 *
	 * @return bool
	 */
	public function clear() {
		$this->_data = array();
		return true;
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
	 * @param string $str Data string to expand
	 *
	 * @return mixed Data, or empty array on failure
	 */
	public function decode( $str ) {
		$data = json_decode( $str, true );
		return JSON_ERROR_NONE === json_last_error()
			? $data
			: array()
		;
	}
}
<?php
/**
 * Shipper models: Storage abstraction root.
 *
 * All storage implementations will be sharing the common API, with concrete implementations.
 *
 * @package shipper
 */

/**
 * Common storage API and internal logic
 */
abstract class Shipper_Model_Stored extends Shipper_Model {

	const KEY_TIMESTAMP = '::timestamp::';
	const KEY_DATA      = '::data::';

	const TTL_PERMANENT = -1;
	const TTL_LONG      = 86400;
	const TTL_SHORT     = 600;

	/**
	 * Holds centralizes storage data helper
	 *
	 * @var object Shipper_Helper_Storage instance
	 */
	private $storage;

	/**
	 * Constructor
	 *
	 * @param string $namespace Optional set-specific namespace.
	 * @param bool   $alt Use alternative storage method.
	 */
	public function __construct( $namespace, $alt = false ) {
		$this->storage = Shipper_Helper_Storage::get( $namespace, $alt );
		$this->load();
	}

	/**
	 * Gets when the data was last updated
	 *
	 * @return int
	 */
	public function get_timestamp() {
		return ! empty( $this->storage->data[ self::KEY_TIMESTAMP ] )
			? (int) $this->storage->data[ self::KEY_TIMESTAMP ]
			: 0;
	}

	/**
	 * Sets data last updated time
	 *
	 * @param int $timestamp UNIX timestamp.
	 *
	 * @return object Shipper_Model_Storage instance
	 */
	public function set_timestamp( $timestamp ) {
		$this->storage->data[ self::KEY_TIMESTAMP ] = (int) $timestamp;
		return $this;
	}

	/**
	 * Check if this storage has time to live set
	 *
	 * @return bool
	 */
	public function has_ttl() {
		return $this->get_ttl() !== self::TTL_PERMANENT;
	}

	/**
	 * Gets time to live for this storage bucket
	 *
	 * By default, storage buckets are permanent.
	 * This method should be overridden in concrete implementations that
	 * require time limit on data.
	 *
	 * @return int Time to live, in seconds
	 */
	public function get_ttl() {
		return self::TTL_PERMANENT;
	}

	/**
	 * Check if time-sensitive storage bucket has expired
	 *
	 * @return bool
	 */
	public function is_expired() {
		if ( ! $this->has_ttl() ) {
			return false;
		}

		return $this->get_timestamp() + $this->get_ttl() < time();
	}

	/**
	 * Loads current state from implementation-specific storage medium
	 *
	 * @return bool
	 */
	public function load() {
		return $this->storage->load();
	}

	/**
	 * Saves current state to implementation-specific storage medium
	 *
	 * @return bool
	 */
	public function save() {
		return $this->storage->save();
	}

	/**
	 * Gets the whole internal data store
	 *
	 * @return array
	 */
	public function get_data() {
		return ! empty( $this->storage->data[ self::KEY_DATA ] )
			? $this->storage->data[ self::KEY_DATA ]
			: array();
	}

	/**
	 * Sets all of the internal storage in one go
	 *
	 * @param array $values Values to replace storage with.
	 *
	 * @return object Shipper_Model instance
	 */
	public function set_data( $values ) {
		if ( is_array( $values ) ) {
			$this->storage->data[ self::KEY_DATA ] = $values;
		}
		return $this;
	}

	/**
	 * Clears all internal data
	 *
	 * @return object Shipper_Model instance
	 */
	public function clear() {
		return $this->set_data( array() );
	}

	/**
	 * Removes a key from internal storage
	 *
	 * @param string $what Key to remove.
	 *
	 * @return object Shipper_Model instance
	 */
	public function remove( $what ) {
		if ( isset( $this->storage->data[ self::KEY_DATA ][ $what ] ) ) {
			unset( $this->storage->data[ self::KEY_DATA ][ $what ] );
		}
		return $this;
	}

	/**
	 * Sets value to an internal storage key
	 *
	 * @param string $what Value key.
	 * @param mixed  $value Value to set.
	 *
	 * @return object Shipper_Model instance
	 */
	public function set( $what, $value ) {
		$this->storage->data[ self::KEY_DATA ][ $what ] = $value;
		return $this;
	}

	/**
	 * Gets value from internal storage
	 *
	 * @param string $what Value key.
	 * @param mixed  $fallback Optional fallback.
	 *
	 * @return mixed Corresponding value, or fallback
	 */
	public function get( $what, $fallback = false ) {
		return isset( $this->storage->data[ self::KEY_DATA ][ $what ] )
			? $this->storage->data[ self::KEY_DATA ][ $what ]
			: $fallback;
	}

	/**
	 * Returns the storage instance
	 *
	 * Used in tests suite.
	 *
	 * @return Shipper_Helper_Storage
	 */
	public function get_storage() {
		return $this->storage;
	}
}
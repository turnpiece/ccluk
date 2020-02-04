<?php
/**
 * Shipper model abstraction class
 *
 * Shipper models are units of data, with corresponding manipulation methods.
 *
 * @package shipper
 */

/**
 * Model abstraction class
 */
abstract class Shipper_Model {

	const SCOPE_DELIMITER = '::';

	/**
	 * Internal data storage reference
	 *
	 * @var array
	 */
	private $_data = array();

	/**
	 * Gets value from internal storage
	 *
	 * @param string $what Value key.
	 * @param mixed  $fallback Optional fallback.
	 *
	 * @return mixed Corresponding value, or fallback
	 */
	public function get( $what, $fallback = false ) {
		return isset( $this->_data[ $what ] )
			? $this->_data[ $what ]
			: $fallback
		;
	}

	/**
	 * Gets the whole internal data store
	 *
	 * @return array
	 */
	public function get_data() {
		return (array) $this->_data;
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
		$this->_data[ $what ] = $value;
		return $this;
	}

	/**
	 * Removes a key from internal storage
	 *
	 * @param string $what Key to remove.
	 *
	 * @return object Shipper_Model instance
	 */
	public function remove( $what ) {
		if ( isset( $this->_data[ $what ] ) ) {
			unset( $this->_data[ $what ] );
		}
		return $this;
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
			$this->_data = $values;
		}
		return $this;
	}

	/**
	 * Clears all internal data
	 *
	 * @return object Shipper_Model instance
	 */
	public function clear() {
		$this->_data = array();
		return $this;
	}
}
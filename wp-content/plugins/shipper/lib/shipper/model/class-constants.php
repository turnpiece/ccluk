<?php
/**
 * Shipper model: constants wrapper
 *
 * Used in testing
 *
 * @package shipper
 */

/**
 * Prefixed constants abstraction
 */
abstract class Shipper_Model_Constants {

	/**
	 * Constant prefix
	 *
	 * @var string
	 */
	private $prefix = '';

	/**
	 * Constant overrides
	 *
	 * @var array An overrides map
	 */
	private $overrides = array();

	/**
	 * Constructor
	 *
	 * @param string $prefix Prefix to use, for prefixed constants.
	 *
	 * @var string
	 */
	public function __construct( $prefix ) {
		$this->prefix = $prefix;
	}

	/**
	 * Gets proper constant name
	 *
	 * @param string $raw Raw name to process.
	 *
	 * @return string Constant name
	 */
	public function get_constant_name( $raw ) {
		if ( empty( $this->prefix ) ) {
			return strtoupper( $raw );
		}

		$prefix = strtolower( $this->prefix );
		return preg_match( '/^' . $prefix . '/i', $raw )
			? strtoupper( $raw )
			: strtoupper( $prefix ) . strtoupper( $raw );
	}

	/**
	 * Checks whether a shipper constant has been defined
	 *
	 * @param string $what Constant to check.
	 *
	 * @return bool
	 */
	public function is_defined( $what ) {
		return defined( $this->get_constant_name( $what ) );
	}

	/**
	 * Returns constant value
	 *
	 * Returns fallback if constant is not set.
	 *
	 * @param string $what Constant to check.
	 * @param mixed  $fallback Value to return if constant is not set.
	 *
	 * @return mixed Constant value or fallback
	 */
	public function get( $what, $fallback = false ) {
		if ( $this->is_overridden( $what ) ) {
			return $this->overrides[ $this->get_constant_name( $what ) ];
		}
		if ( ! $this->is_defined( $what ) ) {
			return $fallback;
		}
		return constant( $this->get_constant_name( $what ) );
	}

	/**
	 * Adds an override
	 *
	 * @param string $what Constant name to override.
	 * @param mixed  $value Constant value to set in overrides.
	 */
	public function add_override( $what, $value ) {
		$this->overrides[ $this->get_constant_name( $what ) ] = $value;
	}

	/**
	 * Checks if we have an active override for constant name
	 *
	 * @param string $what Constant name to check.
	 *
	 * @return bool
	 */
	public function is_overridden( $what ) {
		return isset( $this->overrides[ $this->get_constant_name( $what ) ] );
	}
}
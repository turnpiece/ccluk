<?php
/**
 * Base class for all classes.
 *
 * @link    http://wpmudev.com
 * @since   3.2.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Utils\Abstracts
 */

namespace Beehive\Core\Utils\Abstracts;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

/**
 * Class Base
 *
 * @package Beehive\Core\Utils\Abstracts
 */
abstract class Base extends Singleton {

	/**
	 * Setter method.
	 *
	 * Set property and values to class.
	 *
	 * @param string $key   Property to set.
	 * @param mixed  $value Value to assign to the property.
	 *
	 * @since 3.2.0
	 */
	public function __set( $key, $value ) {
		$this->{$key} = $value;
	}

	/**
	 * Getter method.
	 *
	 * Allows access to extended site properties.
	 *
	 * @param string $key Property to get.
	 *
	 * @since 3.2.0
	 *
	 * @return mixed Value of the property. Null if not available.
	 */
	public function __get( $key ) {
		// If set, get it.
		if ( isset( $this->{$key} ) ) {
			return $this->{$key};
		}

		return null;
	}

	/**
	 * Get network admin flag.
	 *
	 * When called from an ajax request using admin-ajax, we will
	 * check for network flag in $_REQUEST.
	 *
	 * @since 3.2.0
	 *
	 * @return bool
	 */
	public function is_network() {
		// If called from Ajax, check request.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			// phpcs:ignore
			$network = ! empty( $_REQUEST['network'] );
		} else {
			$network = is_network_admin();
		}

		/**
		 * Filter to change network admin flag.
		 *
		 * @param bool $network Is network.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'beehive_is_network', $network );
	}
}
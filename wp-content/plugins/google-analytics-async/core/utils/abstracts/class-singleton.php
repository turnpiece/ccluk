<?php
/**
 * Singleton class for all classes.
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
 * Class Singleton
 *
 * @package Beehive\Core\Utils\Abstracts
 */
abstract class Singleton {

	/**
	 * Singleton constructor.
	 *
	 * Protect the class from being initiated multiple times.
	 *
	 * @since 3.2.0
	 */
	protected function __construct() {
		// Protect class from initiated multiple times.
	}

	/**
	 * Instance obtaining method.
	 *
	 * @since 3.2.0
	 *
	 * @return static Called class instance.
	 */
	public static function instance() {
		static $instances = array();

		// @codingStandardsIgnoreLine Plugin-backported
		$called_class_name = get_called_class();

		if ( ! isset( $instances[ $called_class_name ] ) ) {
			$instances[ $called_class_name ] = new $called_class_name();
		}

		return $instances[ $called_class_name ];
	}
}
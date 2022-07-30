<?php
/**
 * The base class for Widgets.
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

use WP_Widget;

/**
 * Class Widget
 *
 * @package Beehive\Core\Utils\Abstracts
 */
abstract class Widget extends WP_Widget {

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

	/**
	 * Update the widget admin form.
	 *
	 * Updates the values of the widget. Uses the serialization class
	 * to sanitize the information before saving it.
	 *
	 * @param array $new Values to be sanitized and saved.
	 * @param array $old Values that were originally saved.
	 *
	 * @since 3.2.0
	 *
	 * @return array|void
	 */
	public function update( $new, $old ) {
		// Update each values.
		foreach ( $new as $key => $value ) {
			$old[ $key ] = wp_strip_all_tags( stripslashes( $value ) );
		}

		return $old;
	}
}
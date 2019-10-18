<?php
/**
 * Author: Hoang Ngo
 */

namespace Hammer\Helper;

class HTTP_Helper {
	/**
	 * @param $key
	 * @param null $default
	 *
	 * @return null
	 */
	public static function retrieveGet( $key, $default = null ) {
		$value = Array_Helper::getValue( $_GET, $key, $default );

		return $value;
	}

	/**
	 * @param $key
	 * @param null $default
	 *
	 * @return null
	 */
	public static function retrievePost( $key, $default = null ) {
		return Array_Helper::getValue( $_POST, $key, $default );
	}
}
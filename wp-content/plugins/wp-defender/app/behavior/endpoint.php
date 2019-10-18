<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Behavior;

use Hammer\Helper\WP_Helper;

class Endpoint extends \Hammer\Base\Behavior {

	/**
	 * This will contains the endpoints of current session
	 * @return array
	 */
	public function getAllAvailableEndpoints( $module ) {
		$endpoints = (array) WP_Helper::getArrayCache()->get( 'endpoints' );

		return isset( $endpoints[ $module ] ) ? $endpoints[ $module ] : array();
	}

	/**
	 * a quick helper for static class
	 * @return Endpoint
	 */
	public static function instance() {
		return new Endpoint();
	}
}
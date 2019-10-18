<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use Hammer\Helper\HTTP_Helper;
use WP_Defender\Module\Hardener\IRule_Service;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Hardener\Rule_Service;

class WP_Rest_API_Service extends Rule_Service implements IRule_Service {
	const KEY = 'wp_rest_api';

	/**
	 * @return bool
	 */
	public function process() {
		//$mode     = HTTP_Helper::retrievePost( 'mode' );
		//always this
		$mode     = 'allow-auth';
		$settings = Settings::instance();
		$data     = $settings->getDValues( self::KEY );
		if ( in_array( $mode, [ 'allow-auth', 'allow-all' ] ) ) {
			$data['mode'] = $mode;
			$settings->setDValues( self::KEY, $data );
		}
	}

	/**
	 * @return bool
	 */
	public function revert() {
		$settings = Settings::instance();
		$settings->setDValues( self::KEY, null );
	}

	/**
	 * @return mixed
	 */
	public function check() {
		$settings = Settings::instance();
		$data     = $settings->getDValues( self::KEY );
		if ( is_array( $data ) && isset( $data['mode'] ) && in_array( $data['mode'], [ 'allow-auth', 'allow-all' ] ) ) {
			return true;
		}

		return false;
	}
}
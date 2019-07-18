<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use Hammer\Base\Container;
use Hammer\Helper\WP_Helper;
use WP_Defender\Module\Hardener\IRule_Service;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Hardener\Rule_Service;

class Disable_Xml_Rpc_Service extends Rule_Service implements IRule_Service {
	const CACHE_KEY = 'disable_xml_rpc';

	/**
	 * @return bool
	 */
	public function process() {
		//first need to cache the status
		Settings::instance()->setDValues( self::CACHE_KEY, 1 );
		return true;
	}

	/**
	 * @return bool
	 */
	public function revert() {
		Settings::instance()->setDValues( self::CACHE_KEY, 0 );
		return true;
	}

	/**
	 * @return mixed
	 */
	public function check() {
		$key = Settings::instance()->getDValues( self::CACHE_KEY );

		return $key == 1;
	}
}

<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use WP_Defender\Module\Hardener\IRule_Service;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Hardener\Rule_Service;

class Prevent_Enum_Users_Service extends Rule_Service implements IRule_Service {
	const CACHE_KEY = 'prevent_enum_users';

	/**
	 * Check if current rule fixed or not
	 * @return bool
	 */
	public function check() {
		$flag = Settings::instance()->getDValues( Prevent_Enum_Users_Service::CACHE_KEY );
		if ( $flag == 0 ) {
			return false;
		}

		return true;
	}

	/**
	 * Process the rule
	 * @return bool|\WP_Error
	 */
	public function process() {
		Settings::instance()->setDValues( self::CACHE_KEY, 1 );

		return true;
	}

	/**
	 * Revert if able
	 * @return bool|\WP_Error
	 */
	public function revert() {
		Settings::instance()->setDValues( self::CACHE_KEY, 0 );

		return true;
	}
}
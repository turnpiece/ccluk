<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module;

use Hammer\Base\Module;
use Hammer\Helper\HTTP_Helper;

use WP_Defender\Module\Hardener\Controller\Main;
use WP_Defender\Module\Hardener\Controller\Rest;
use WP_Defender\Module\Hardener\Model\Settings;

class Hardener extends Module {
	const Settings = 'hardener_settings';

	public function __construct() {
		//init dependency
		$this->initRulesStats();
		//call the controller
		new Main();
		new Rest();
	}

	/**
	 * Init rules status
	 */
	public function initRulesStats() {
		$settings = Settings::instance( true );
		/**
		 * now we have a list of rules, and lists of their status
		 */
		if ( ! defined( 'DOING_AJAX' ) ) {
			//only init when page load
			$interval = '+0 seconds';
			//only refresh if on admin, if not we just do the listening

			if ( ( ( is_admin() || is_network_admin() )
			     ) && ( HTTP_Helper::retrieveGet( 'page' ) == 'wdf-hardener'
			            || HTTP_Helper::retrieveGet( 'page' ) == 'wp-defender'
			            || HTTP_Helper::retrieveGet( 'page' ) == 'wdf-setting' )
			) {
				//this mean we dont have any data, or data is overdue need to refresh
				//refetch those list
				$settings->refreshStatus();
			} elseif ( defined( 'DOING_CRON' ) ) {
				//if this is in cronjob, we refresh it too
				$settings->refreshStatus();
			}
			$settings->save();
		}

		//we will need to add every hooks needed
		foreach ( $settings->getDefinedRules( true ) as $rule ) {
			$rule->addHooks();
		}
	}

}
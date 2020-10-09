<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Advanced_Tools\Component;

use Hammer\Helper\HTTP_Helper;
use Hammer\WP\Component;
use WP_Defender\Module\Advanced_Tools\Model\Security_Headers_Settings;

class Security_Headers_Listener extends Component {
	public function __construct() {
		//init dependency
		$this->initHeaders();
	}

	/**
	 * Init headers
	 */
	public function initHeaders() {
		$settings = Security_Headers_Settings::instance();
		if ( ! defined( 'DOING_AJAX' ) ) {

			//refresh if on admin, on page with headers
			if ( ( is_admin() || is_network_admin() )
			     &&
			     (
				     ( 'wdf-advanced-tools' === HTTP_Helper::retrieveGet( 'page' ) )
				     || ( 'wp-defender' === HTTP_Helper::retrieveGet( 'page' ) )
			     )
			) {
				//this mean we dont have any data or data is overdue need to refresh list of headers
				$settings->refreshHeaders();
			} elseif ( defined( 'DOING_CRON' ) ) {
				//if this is in cronjob, we refresh it too
				$settings->refreshHeaders();
			}
		}

		foreach ( $settings->getHeaders() as $rule ) {
			$rule->addHooks();
		}
	}
}
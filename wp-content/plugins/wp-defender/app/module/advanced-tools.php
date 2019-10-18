<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module;

use Hammer\Base\Module;
use WP_Defender\Module\Advanced_Tools\Controller\Main;
use WP_Defender\Module\Advanced_Tools\Controller\Mask_Login;
use WP_Defender\Module\Advanced_Tools\Controller\Rest;
use WP_Defender\Module\Advanced_Tools\Controller\Rest_Auth;

class Advanced_Tools extends Module {
	public function __construct() {
		$main = new Main();
		$rest = new Rest();
	}
}
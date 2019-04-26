<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module;

use Hammer\Base\Module;
use WP_Defender\Module\Setting\Controller\Main;

class Setting extends Module {
	public function __construct() {
		new Main();
	}
}
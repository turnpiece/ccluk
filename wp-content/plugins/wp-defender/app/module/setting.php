<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module;

use Hammer\Base\Module;
use WP_Defender\Module\Setting\Controller\Main;
use WP_Defender\Module\Setting\Controller\Rest;

class Setting extends Module {
	public function __construct() {
		$this->addController( 'main', new Main() );
		new Rest();
	}
}
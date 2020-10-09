<?php

namespace WP_Defender\Module;

use Hammer\Base\Module;
use WP_Defender\Module\Two_Factor\Controller\Main;
use WP_Defender\Module\Two_Factor\Controller\Rest;

class Two_Factor extends Module {
	public function __construct() {
		new Main();
		new Rest();
	}
}
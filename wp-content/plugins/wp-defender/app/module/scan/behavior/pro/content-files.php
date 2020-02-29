<?php

namespace WP_Defender\Module\Scan\Behavior\Pro;

use Hammer\Base\Behavior;
use Hammer\Base\Component;
use WP_Defender\Module\Scan\Component\Scan_Api;

class Content_Files extends Behavior {

	public function processItemInternal( $args, $current ) {
		Scan_Api::getContentFiles();

		return true;
	}
}
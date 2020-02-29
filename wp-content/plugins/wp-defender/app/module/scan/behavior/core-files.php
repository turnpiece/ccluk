<?php

namespace WP_Defender\Module\Scan\Behavior;

use Hammer\Base\Behavior;
use WP_Defender\Module\Scan\Component\Scan_Api;

class Core_Files extends Behavior {
	public function processItemInternal( $args, $current ) {
		Scan_Api::getCoreFiles();
		Scan_Api::getCoreChecksums();

		return true;
	}
}
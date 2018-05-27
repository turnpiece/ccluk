<?php
/**
 * Author: Hoang Ngo
 */
ini_set( 'memory_limit', 0 );
require_once dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) . '/wp-load.php';
$activeScan = \WP_Defender\Module\Scan\Component\Scan_Api::getActiveScan();
if ( ! is_object( $activeScan ) ) {
	echo 'create new scan' . PHP_EOL;
	\WP_Defender\Module\Scan\Component\Scan_Api::createScan();
}

$result = \WP_Defender\Module\Scan\Component\Scan_Api::processActiveScan();
while ( $result !== true ) {
	echo \WP_Defender\Module\Scan\Component\Scan_Api::getScanProgress() . PHP_EOL;
	$result = \WP_Defender\Module\Scan\Component\Scan_Api::processActiveScan();
}
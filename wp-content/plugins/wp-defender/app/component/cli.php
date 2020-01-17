<?php

namespace WP_Defender\Component;

use WP_Defender\Module\Scan\Component\Scan_Api;

class Cli {

	public function scan() {
		echo 'Check if there is a scan ongoing...' . PHP_EOL;
		$model = Scan_Api::getActiveScan();
		if ( ! is_object( $model ) ) {
			echo 'No active scan, create one now...' . PHP_EOL;
			Scan_Api::createScan();
		} else {
			echo 'Found active scan, process...' . PHP_EOL;
		}
		echo sprintf( 'Total core files: %d' . PHP_EOL, count( Scan_Api::getCoreFiles() ) );
		echo sprintf( 'Total content files: %d' . PHP_EOL, count( Scan_Api::getContentFiles() ) );
		echo '=============================================' . PHP_EOL;
		$is_done = false;
		while ( $is_done == false ) {
			Scan_Api::releaseLock();
			$memory = ( memory_get_peak_usage( true ) / 1024 / 1024 ) . PHP_EOL;
			echo 'Memory: ' . $memory . ' MB';
			if ( $memory > 256 ) {
				break;
			}
			$is_done  = Scan_Api::processActiveScan();
			$progress = Scan_Api::getScanProgress();
			echo 'Scanning at ' . $progress . PHP_EOL;
			gc_collect_cycles();
		}
		if ( $is_done ) {
			$model = Scan_Api::getLastScan();
			\WP_CLI::log( sprintf( 'Found %s issues. Please go to %s for more info.' . PHP_EOL, count( $model->getItems() ), network_admin_url( 'admin.php?page=wdf-scan&view=issues' ) ) );
			\WP_CLI::success( 'Scan done.' );
		} else {
			\WP_CLI::log( 'Run the command wp defender scan again to continue process the scan.' );
		}
	}

	/**
	 * @param $args
	 */
	public function scan_a_file( $args ) {
		$file = ABSPATH . $args[0];

		$this->scan();
	}
}
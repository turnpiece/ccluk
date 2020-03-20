<?php

namespace WP_Defender\Module\Scan\Component;

use Hammer\Base\Component;
use Hammer\Helper\File_Helper;
use Hammer\Helper\WP_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Component\Error_Code;
use WP_Defender\Module\Scan\Model\Scan;
use WP_Defender\Module\Scan\Model\Settings;

class Scanning extends Component {
	const CACHE_CURRENT_STEP = 'wdf_scan_current', CACHE_PERCENTAGE = 'wdf_scan_percent';

	/**
	 * @return bool|\WP_Error
	 */
	public function run() {
		$model = Scan_Api::getActiveScan();
		$start = microtime( true );
		if ( ! is_object( $model ) ) {
			return new \WP_Error( Error_Code::INVALID, __( "No scan record exists", wp_defender()->domain ) );
		}

		if ( $model->status == Scan::STATUS_ERROR ) {
			//stop scan
			$this->releaseLock();

			return new \WP_Error( Error_Code::SCAN_ERROR, $model->statusText );
		}

		if ( $this->isLock() ) {
			return false;
		}
		$this->createLock();
		$settings = Settings::instance();
		$cache    = WP_Helper::getCache();
		$steps    = $settings->getScansAvailable();
		$current  = $cache->get( self::CACHE_CURRENT_STEP, $steps[0] );
		$index    = array_search( $current, $steps );
		list( $queue, $status_text ) = Queue_Factory::queueFactory( $current, [
			'model'      => $model,
			'ignoreList' => Scan_Api::getIgnoreList()
		] );
		$model->statusText = $status_text;
		$model->status     = Scan::STATUS_PROCESS;
		$model->save();
		while ( ! $queue->isEnd() ) {
			$result = $queue->processItem();
			if ( $result === false ) {
				//current item fail, return
				//moving on
				$queue->next();
				$queue->saveProcess();
				$this->releaseLock();

				return false;
			}
			if ( microtime( true ) - $start > 5 ) {
				//the process take too long, break out
				$queue->saveProcess();
				break;
			}
		}
		Utils::instance()->log( sprintf( 'current %s took %s', $current, ( microtime( true ) - $start ) ), 'scan' );
		$base_progress     = round( ( ( $index ) / count( $steps ) ) * 100, 2 );
		$internal_progress = ( ( $queue->key() / $queue->count() ) * 100 ) * ( 1 / count( $steps ) * 100 ) * 0.01;
		$cache->set( self::CACHE_PERCENTAGE, $base_progress + $internal_progress );
		if ( $queue->isEnd() ) {
			$remaining = array_slice( $steps, $index + 1 );
			if ( count( $remaining ) == 0 ) {
				//scan done
				$this->markScanFinish( $model );

				return true;
			} else {
				//we store the next
				$cache->set( self::CACHE_CURRENT_STEP, $remaining[0] );
			}
		}
		$this->releaseLock();

		return false;
	}

	/**
	 * @return false|mixed|null
	 */
	public function getScanProgress() {
		$cache = WP_Helper::getCache();

		return $cache->get( self::CACHE_PERCENTAGE, 0 );
	}

	/**
	 * @param Scan $model
	 */
	private function markScanFinish( $model ) {
		$lastScan = Scan_Api::getLastScan();
		if ( is_object( $lastScan ) ) {
			$lastScan->delete();
		}
		//mark the current as complted
		$model->status = Scan::STATUS_FINISH;
		$model->save();
		$this->flushCache();
		$this->releaseLock();
	}

	public function flushCache( $flushQueue = true ) {
		$cache = WP_Helper::getCache();
		if ( $flushQueue == true ) {
			$settings = Settings::instance();
			$steps    = $settings->getScansAvailable();
			foreach ( $steps as $step ) {
				$queue = Settings::queueFactory( $step, array() );
				if ( is_object( $queue ) ) {
					$queue->clearStatusData();
				}
			}
		}
		//todo still update
		$cache->delete( Scan_Api::CACHE_CORE );
		$cache->delete( Scan_Api::CACHE_CONTENT );
		$cache->delete( Scan_Api::SCAN_PATTERN );
		$cache->delete( self::CACHE_CURRENT_STEP );
		delete_site_option( Scan_Api::SCAN_PATTERN );
		$cache->delete( 'filestried' );
		$cache->delete( Scan_Api::CACHE_CHECKSUMS );
		$cache->delete( 'defenderScanPercent' );
		$altCache = WP_Helper::getArrayCache();
		$altCache->delete( 'lastScan' );
		$altCache->delete( 'activeScan' );
	}

	/**
	 * Create a lock
	 * @return int
	 */
	public function createLock() {
		$lockPath = Utils::instance()->getDefUploadDir();

		$lockFile = $lockPath . DIRECTORY_SEPARATOR . 'scan-lock';

		return file_put_contents( $lockFile, time(), LOCK_EX );
	}

	/**
	 * @return bool
	 */
	public function isLock() {
		$lockPath = Utils::instance()->getDefUploadDir();
		$lockFile = $lockPath . DIRECTORY_SEPARATOR . 'scan-lock';
		if ( ! is_file( $lockFile ) ) {
			return false;
		}

		$time = file_get_contents( $lockFile );
		if ( strtotime( '+1 minutes', $time ) < time() ) {
			//this lock locked for too long, unlock it
			@unlink( $lockFile );

			return false;
		}

		return true;
	}

	/**
	 * Release scan lock
	 */
	public function releaseLock() {
		$lockPath = WP_Helper::getUploadDir() . '/wp-defender/';
		$lockFile = $lockPath . 'scan-lock';
		@unlink( $lockFile );
	}

}
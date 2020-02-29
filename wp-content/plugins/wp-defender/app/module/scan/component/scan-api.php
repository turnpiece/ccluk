<?php

namespace WP_Defender\Module\Scan\Component;

use Hammer\Base\Component;
use Hammer\Base\Container;
use Hammer\Helper\File_Helper;
use Hammer\Helper\Log_Helper;
use Hammer\Helper\WP_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Component\Error_Code;
use WP_Defender\Module\Scan\Behavior\Pro\Content_Scan;
use WP_Defender\Module\Scan\Model\Result_Item;
use WP_Defender\Module\Scan\Model\Scan;
use WP_Defender\Module\Scan\Model\Settings;

/**
 * Here contains every function need for scanning module
 * Class Scan_Api
 * @package WP_Defender\Module\Scan\Component
 */
class Scan_Api extends Component {
	const CACHE_CORE = 'wdfcore', CACHE_CONTENT = 'wdfcontent', CACHE_CHECKSUMS = 'wdfchecksum';
	const IGNORE_LIST = 'wdfscanignore', SCAN_PATTERN = 'wdfscanparttern';

	private static $ignoreList = false;

	public static $scanResults = array();

	/**
	 * @return Scan|\WP_Error
	 */
	public static function createScan() {
		if ( is_null( self::getActiveScan() ) ) {
			( new Scanning() )->flushCache();

			$model             = new Scan();
			$model->status     = Scan::STATUS_INIT;
			$model->statusText = __( "Initializing...", wp_defender()->domain );
			$model->save();
			Utils::instance()->clear_log( 'scan' );

			return $model;
		} else {
			return new \WP_Error( Error_Code::INVALID, __( "A scan is already in progress", wp_defender()->domain ) );
		}
	}

	/**
	 * Check if this module is active
	 */
	public static function isActive() {
		return Settings::instance()->notification;
	}

	/**
	 * Get the current scan on going
	 *
	 * @param $fresh
	 *
	 * @return false|mixed|Scan|null
	 */
	public static function getActiveScan( $fresh = false ) {
		$cache = WP_Helper::getArrayCache();
		if ( $cache->exists( 'activeScan' ) && $fresh == false ) {
			return $cache->get( 'activeScan' );
		}
		$model = Scan::findOne( array(
			'status' => array(
				Scan::STATUS_INIT,
				Scan::STATUS_ERROR,
				Scan::STATUS_PROCESS
			)
		) );

		$cache->set( 'activeScan', $model );

		return $model;
	}

	/**
	 * @return null|Scan
	 */
	public static function getLastScan() {
		$cache = WP_Helper::getArrayCache();
		if ( $cache->exists( 'lastScan' ) ) {
			return $cache->get( 'lastScan' );
		}
		$model = Scan::findOne( array(
			'status' => array(
				Scan::STATUS_FINISH
			)
		), 'ID', 'DESC' );

		$cache->set( 'lastScan', $model );

		return $model;
	}

	/**
	 * @return array
	 */
	public static function getCoreFiles() {
		/**
		 * We we will get one level files & folder inside root, all files inside
		 */
		$cache  = Container::instance()->get( 'cache' );
		$cached = $cache->get( self::CACHE_CORE, false );
		if ( is_array( $cached ) && ! empty( $cached ) ) {
			return $cached;
		}
		$time            = microtime( true );
		$settings        = Settings::instance();
		$firstLevelFiles = File_Helper::findFiles( ABSPATH, true, true, array(
			'dir'      => array(
				ABSPATH . 'wp-content',
				ABSPATH . 'wp-admin',
				ABSPATH . 'wp-includes'
			),
			'filename' => array(
				'wp-config.php'
			)
		), array(), false );

		$coreFiles = File_Helper::findFiles( ABSPATH, true, false, array(), array(
			'dir' => array(
				ABSPATH . 'wp-admin',
				ABSPATH . 'wp-includes',
			)
		), true );
		$files     = array_merge( $firstLevelFiles, $coreFiles );
		$files     = apply_filters( 'wd_core_files', $files );
		$cache->set( self::CACHE_CORE, $files, 0 );
		Utils::instance()->log( sprintf( 'Core files: %d time finished: %s', count( $files ), microtime( true ) - $time ), 'scan' );

		return $files;
	}

	/**
	 * @return array
	 */
	public static function getContentFiles() {
		$cache  = Container::instance()->get( 'cache' );
		$cached = $cache->get( self::CACHE_CONTENT, false );

		if ( is_array( $cached ) && ! empty( $cached ) ) {
			return $cached;
		}
		$settings = Settings::instance();
		$files    = File_Helper::findFiles( WP_CONTENT_DIR, true, false, array(), array(
			'ext' => array( 'php' )
		), true, $settings->max_filesize, true );
//		$files = File_Helper::findFiles( WP_CONTENT_DIR . '/removidosnomaa', true, false, array(), array(
//			'ext' => array( 'php' )
//		), true, $settings->max_filesize, true );
		//include wp-config.php here
		$files[] = ABSPATH . 'wp-config.php';
		$files   = apply_filters( 'wd_content_files', $files );
		$cache->set( self::CACHE_CONTENT, $files );
		Utils::instance()->log( sprintf( 'Content files: %d', count( $files ) ), 'scan' );

		return $files;
	}

	/**
	 * Get checksums
	 * @return array|bool
	 */
	public static function getCoreChecksums() {
		$cache  = Container::instance()->get( 'cache' );
		$cached = $cache->get( self::CACHE_CHECKSUMS, false );
		if ( is_array( $cached ) && ! empty( $cached ) ) {
			return $cached;
		}

		global $wp_version, $wp_local_package;
		$locale = 'en_US';
		if ( ! is_null( $wp_local_package ) && count( explode( '_', $wp_local_package ) ) == 2 ) {
			$locale = $wp_local_package;
		}
		if ( ! function_exists( 'get_core_checksums' ) ) {
			include_once ABSPATH . 'wp-admin/includes/update.php';
		}
		$checksum = get_core_checksums( $wp_version, $locale );
		if ( $checksum == false ) {
			return $checksum;
		}

		if ( isset( $checksum[ $wp_version ] ) ) {
			return $checksum = $checksum[ $wp_version ];
		}

		$cache->set( self::CACHE_CHECKSUMS, $checksum, 86400 );

		return $checksum;
	}

	/**
	 * remove all scan models
	 */
	public static function removeAllScanRecords() {
		$models = Scan::findAll();
		foreach ( $models as $model ) {
			$model->delete();
		}
	}

	/**
	 * Ignoe list will be a global array, so it can share from each scan
	 * @return Result_Item[]
	 */
	public static function getIgnoreList() {
		$cache  = WP_Helper::getArrayCache();
		$cached = $cache->get( self::IGNORE_LIST, false );
		if ( is_array( $cached ) ) {
			return $cached;
		}
		$ids = get_site_option( self::IGNORE_LIST, [] );

		$ignoreList = Result_Item::findAll( array(
			'id' => $ids
		) );

		$cached = $ignoreList;
		$cache->set( self::IGNORE_LIST, $cached );

		return $ignoreList;
	}

	/**
	 * Check if a file get ignored
	 *
	 * @param $id int the ID of resultScan get ignored
	 *
	 * @return bool
	 */
	public static function isIgnored( $slug ) {
		$ignoreList = Scan_Api::getIgnoreList();
		foreach ( $ignoreList as $model ) {
			if ( $model->hasMethod( 'getSlug' ) && $model->getSlug() == $slug ) {
				return $model->id;
			}
		}

		return false;
	}

	/**
	 * Add an item to ignore list
	 *
	 * @param $id
	 */
	public static function indexIgnore( $id ) {
		$ids   = get_site_option( self::IGNORE_LIST, [] );
		$ids[] = $id;
		$ids   = array_unique( $ids );
		update_site_option( self::IGNORE_LIST, $ids );
	}

	/**
	 * Remove an item from ignore list
	 *
	 * @param $id
	 */
	public static function unIndexIgnore( $id ) {
		$ids = get_site_option( self::IGNORE_LIST, [] );
		if ( empty( $ids ) ) {
			return;
		}
		unset( $ids[ array_search( $id, $ids ) ] );
		update_site_option( self::IGNORE_LIST, $ids );
	}

	/**
	 * flush all cache generated during scan process
	 * @deprecated
	 */
	public static function flushCache() {
		( new Scanning() )->flushCache();
	}

	/**
	 * A function for dealing with windows host, as wordpress checksums path all in UNIX format
	 *
	 * @param $file
	 *
	 * @return mixed
	 */
	public static function convertToUnixPath( $file ) {
		//check if this is windows OS, if so convert the ABSPATH
		//Removed : Adds unecessay slashes in windows
		/*if ( DIRECTORY_SEPARATOR == '\\' ) {
			$abs_path = rtrim( ABSPATH, '/' );
			$abs_path = $abs_path . '\\';
		} else {
			$abs_path = ABSPATH;
		}*/
		//now getting the relative path
		$relative_path = str_replace( ABSPATH, '', $file );
		if ( DIRECTORY_SEPARATOR == '\\' ) {
			$relative_path = str_replace( '\\', '', $relative_path ); //Make sure the files do not have a /filename.etension or checksum fails
		}

		return $relative_path;
	}


	/**
	 * A function for dealing with windows host, Fixes the URL path on Windows
	 *
	 * @param $file
	 *
	 * @return mixed
	 */
	public static function convertToWindowsAbsPath( $file ) {
		//check if this is windows OS, if so convert the ABSPATH
		if ( DIRECTORY_SEPARATOR == '\\' ) {
			$abs_path = rtrim( ABSPATH, '/' );
			$abs_path = $abs_path . '\\';

			//now getting the relative path
			$abs_path = str_replace( $abs_path, '', $file );
			$abs_path = str_replace( '\\', '/', $abs_path );
			$abs_path = str_replace( '//', '/', $abs_path );

			return $abs_path;
		}

		return $file;
	}

	/**
	 * Get the schedule time for a scan
	 *
	 * @param $clearCron bool - force to clear scanning cron
	 *
	 * @return false|int
	 * @deprecated 1.4.2
	 */
	public static function getScheduledScanTime( $clearCron = true ) {
		if ( $clearCron ) {
			wp_clear_scheduled_hook( 'processScanCron' );
		}
		$settings = Settings::instance();
		switch ( $settings->frequency ) {
			case '1':
				//check if the time is over or not, then send the date
				$timeString     = date( 'Y-m-d' ) . ' ' . $settings->time . ':00';
				$nextTimeString = date( 'Y-m-d', strtotime( 'tomorrow' ) ) . ' ' . $settings->time . ':00';
				break;
			case '7':
			default:
				$timeString     = date( 'Y-m-d', strtotime( $settings->day . ' this week' ) ) . ' ' . $settings->time . ':00';
				$nextTimeString = date( 'Y-m-d', strtotime( $settings->day . ' next week' ) ) . ' ' . $settings->time . ':00';
				break;
			case '30':
				$timeString     = date( 'Y-m-d', strtotime( $settings->day . ' this month' ) ) . ' ' . $settings->time . ':00';
				$nextTimeString = date( 'Y-m-d', strtotime( $settings->day . ' next month' ) ) . ' ' . $settings->time . ':00';
				break;
		}
		$toUTC = Utils::instance()->localToUtc( $timeString );
		if ( $toUTC < time() ) {
			return Utils::instance()->localToUtc( $nextTimeString );
		} else {
			return $toUTC;
		}
	}

	/**
	 * @return array|mixed|object|\WP_Error
	 */
	public static function getPatterns() {
		$activeScan = self::getActiveScan();
		if ( ! is_object( $activeScan ) ) {
			return array();
		}

		$patterns = get_site_option( Scan_Api::SCAN_PATTERN, null );

		if ( is_array( $patterns ) ) {
			//return pattern if that exists, no matter the content
			return $patterns;
		}
		$base         = defined( 'WPMUDEV_CUSTOM_API_SERVER' ) && WPMUDEV_CUSTOM_API_SERVER
			? WPMUDEV_CUSTOM_API_SERVER
			: 'https://premium.wpmudev.org/';
		$api_endpoint = "{$base}api/defender/v1/yara-signatures";

		$patterns = Utils::instance()->devCall( $api_endpoint, array(), array(
			'method' => 'GET'
		) );
		if ( is_wp_error( $patterns ) ) {
			Utils::instance()->log( $patterns->get_error_message(), 'scan' );
		}
		if ( is_wp_error( $patterns ) || $patterns == false ) {
			$patterns = array();
		}
		Utils::instance()->log( sprintf( 'Fetch rules from %s. Found %d', $api_endpoint, count( $patterns ) ), 'scan' );
		update_site_option( Scan_Api::SCAN_PATTERN, $patterns );

		return $patterns;
	}
}
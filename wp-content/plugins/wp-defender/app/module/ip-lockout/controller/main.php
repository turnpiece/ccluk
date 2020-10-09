<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\IP_Lockout\Controller;

use Hammer\Helper\HTTP_Helper;
use Hammer\Helper\WP_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Controller;
use WP_Defender\Module\IP_Lockout\Component\IP_API;
use WP_Defender\Module\IP_Lockout\Component\Login_Listener;
use WP_Defender\Module\IP_Lockout\Component\Login_Protection_Api;
use WP_Defender\Module\IP_Lockout\Component\Notfound_Listener;
use WP_Defender\Module\IP_Lockout\Model\IP_Model;
use WP_Defender\Module\IP_Lockout\Model\Log_Model;
use WP_Defender\Module\IP_Lockout\Model\Settings;

class Main extends Controller {
	protected $slug = 'wdf-ip-lockout';

	/**
	 * @return array
	 */
	public function behaviors() {
		$behaviors = array(
			'utils'     => '\WP_Defender\Behavior\Utils',
			'endpoints' => '\WP_Defender\Behavior\Endpoint',
			'wpmudev'   => '\WP_Defender\Behavior\WPMUDEV'
		);
		if ( wp_defender()->isFree == false ) {
			$behaviors['pro'] = '\WP_Defender\Module\IP_Lockout\Behavior\Pro\Reporting';
		}

		return $behaviors;
	}

	public function __construct() {
		$this->maybeLockouts();

		if ( $this->isNetworkActivate( wp_defender()->plugin_slug ) ) {
			$this->addAction( 'network_admin_menu', 'adminMenu' );
		} else {
			$this->addAction( 'admin_menu', 'adminMenu' );
		}

		if ( $this->isInPage() || $this->isDashboard() ) {
			$this->addAction( 'defender_enqueue_assets', 'scripts', 11 );
		}

		$this->maybeExport();

		if ( ! Login_Protection_Api::checkIfTableExists() ) {
			//no table logs, omething happen
			return;
		}

		$ip       = $this->getUserIp();
		$settings = Settings::instance();
		if ( $settings->report && $this->hasMethod( 'lockoutReportCron' ) ) {
			//report
			$this->addAction( 'lockoutReportCron', 'lockoutReportCron' );
		}

		//cron for cleanup
		$nextCleanup = wp_next_scheduled( 'cleanUpOldLog' );
		if ( $nextCleanup === false || $nextCleanup > strtotime( '+90 minutes' ) ) {
			wp_clear_scheduled_hook( 'cleanUpOldLog' );
			wp_schedule_event( time(), 'hourly', 'cleanUpOldLog' );
		}

		$this->addAction( 'cleanUpOldLog', 'cleanUpOldLog' );

		if ( $settings->isWhitelist( $ip ) ) {
			return;
		}

		$arr = $this->defaultWhiteListIps();

		if ( in_array( $ip, $arr ) ) {
			return;
		}

		$loginListener    = new Login_Listener();
		$notfoundListener = new Notfound_Listener();
	}

	private function defaultWhiteListIps() {
		return apply_filters( 'ip_lockout_default_whitelist_ip', array(
			'192.241.148.185',
			'104.236.132.222',
			'192.241.140.159',
			'192.241.228.89',
			'198.199.88.192',
			'54.197.28.242',
			'54.221.174.186',
			'54.236.233.244',
			'18.204.159.253',
			'66.135.60.59',
			'34.196.51.17',
			'52.57.5.20',
			'127.0.0.1',
			array_key_exists( 'SERVER_ADDR', $_SERVER ) ? $_SERVER['SERVER_ADDR'] : ( isset( $_SERVER['LOCAL_ADDR'] ) ? $_SERVER['LOCAL_ADDR'] : null )
		) );
	}

	/**
	 * Determine if an ip get lockout or not
	 */
	public function maybeLockouts() {
		do_action( 'wd_before_lockout' );
		$settings = Settings::instance();
		$isTest   = HTTP_Helper::retrieveGet( 'def-lockout-demo', false ) == 1;
		if ( $isTest ) {
			$message = null;
			$type    = HTTP_Helper::retrieveGet( 'type' );
			switch ( $type ) {
				case 'login':
					$message = $settings->login_protection_lockout_message;
					break;
				case '404':
					$message = $settings->detect_404_lockout_message;
					break;
				case 'blocklist':
					$message = $settings->ip_lockout_message;
					break;
				default:
					$message = __( "Demo", wp_defender()->domain );
			}
			$this->renderPartial( 'locked', array(
				'message' => $message
			) );
			die;
		}

		$ip             = $this->getUserIp();
		$arr            = $this->defaultWhiteListIps();
		$cache          = WP_Helper::getCache();
		$temp_whitelist = $cache->get( 'staff_ips', [] );
		if ( $this->listenToStaffAccess() ) {
			//tmp whitelist this ip till the access end
			$temp_whitelist[] = $ip;
			$temp_whitelist   = array_unique( $temp_whitelist );
			$temp_whitelist   = array_filter( $temp_whitelist );
			$cache->set( 'staff_ips', $temp_whitelist, DAY_IN_SECONDS );
			Utils::instance()->log( sprintf( 'Temporary allowlist ip %s', $ip ), 'lockout' );
		}
		$arr = array_merge( $arr, $temp_whitelist );

		if ( in_array( $ip, $arr ) ) {
			return;
		}

		if ( $settings->isWhitelist( $ip ) ) {
			return;
		} elseif ( $settings->isBlacklist( $ip ) ) {
			if ( ! defined( 'DONOTCACHEPAGE' ) ) {
				define( 'DONOTCACHEPAGE', true );
			}
			header( 'HTTP/1.0 403 Forbidden' );
			header( 'Cache-Control: private' );
			$this->renderPartial( 'locked', array(
				'message' => $settings->ip_lockout_message
			) );
			die;
		} elseif ( $settings->isCountryBlacklist() ) {
			if ( ! defined( 'DONOTCACHEPAGE' ) ) {
				define( 'DONOTCACHEPAGE', true );
			}
			header( 'HTTP/1.0 403 Forbidden' );
			header( 'Cache-Control: private' );
			$this->renderPartial( 'locked', array(
				'message' => $settings->ip_lockout_message
			) );
			die;
		} else {
			if ( $settings->detect_404_logged == false && is_user_logged_in() ) {
				/**
				 * We don't need to check the IP if:
				 * the current user can logged in and no blacklisted,
				 * the option detect_404_logged is disabled
				 */
				return;
			}

			$model = IP_Model::findOne( array(
				'ip' => $ip
			) );
			if ( is_object( $model ) && $model->is_locked() ) {
				if ( ! defined( 'DONOTCACHEPAGE' ) ) {
					define( 'DONOTCACHEPAGE', true );
				}
				header( 'HTTP/1.0 403 Forbidden' );
				header( 'Cache-Control: private' );
				$this->renderPartial( 'locked', array(
					'message' => $model->lockout_message
				) );
				die;
			}
		}
	}

	public function listenToStaffAccess() {
		if ( defined( 'WPMUDEV_DISABLE_REMOTE_ACCESS' ) && constant( 'WPMUDEV_DISABLE_REMOTE_ACCESS' ) == true ) {
			return false;
		}
		if ( class_exists( 'WPMUDEV_Dashboard' ) && Utils::instance()->getAPIKey() && isset( $_REQUEST['wdpunkey'] ) ) {
			$access = \WPMUDEV_Dashboard::$site->get_option( 'remote_access' );
			Utils::instance()->log( var_export( $access, true ), 'settings' );

			return hash_equals( $_REQUEST['wdpunkey'], $access['key'] );
		}

		return false;
	}

	/**
	 * cron for delete old log
	 */
	public function cleanUpOldLog() {
		$timestamp = Utils::instance()->localToUtc( apply_filters( 'ip_lockout_logs_store_backward', '-' . Settings::instance()->storage_days . ' days' ) );
		Log_Model::deleteAll( array(
			'date' => array(
				'compare' => '<=',
				'value'   => $timestamp
			),
		), '0,1000' );
	}

	/**
	 * After each log recorded, we will check if the threshold is met for a lockout
	 *
	 * @param Log_Model $log
	 */
	public function updateIpStats( Log_Model $log ) {

		if ( $log->type == Log_Model::AUTH_FAIL ) {
			Login_Protection_Api::maybeLock( $log );
		} elseif ( $log->type == Log_Model::ERROR_404 ) {
			Login_Protection_Api::maybe404Lock( $log );
		}
	}

	/**
	 * listener to process export Ips request
	 */
	public function maybeExport() {
		if ( HTTP_Helper::retrieveGet( 'page' ) == 'wdf-ip-lockout' && HTTP_Helper::retrieveGet( 'view' ) == 'export' ) {
			if ( ! $this->checkPermission() ) {
				return;
			}

			if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'exportIPs' ) ) {
				return;
			}
			$setting = Settings::instance();
			$data    = array();
			foreach ( $setting->getIpBlacklist() as $ip ) {
				$data[] = array(
					'ip'   => $ip,
					'type' => 'blocklist'
				);
			}
			foreach ( $setting->getIpWhitelist() as $ip ) {
				$data[] = array(
					'ip'   => $ip,
					'type' => 'allowlist'
				);
			}
			$fp = fopen( 'php://memory', 'w' );
			foreach ( $data as $fields ) {
				fputcsv( $fp, $fields );
			}
			$filename = 'wdf-ips-export-' . date( 'ymdHis' ) . '.csv';
			fseek( $fp, 0 );
			header( 'Content-Type: text/csv' );
			header( 'Content-Disposition: attachment; filename="' . $filename . '";' );
			// make php send the generated csv lines to the browser
			fpassthru( $fp );
			exit();
		}
	}

	/**
	 * Add submit admin page
	 */
	public function adminMenu() {
		$cap    = is_multisite() ? 'manage_network_options' : 'manage_options';
		$action = "actionIndex";
		add_submenu_page( 'wp-defender', esc_html__( "Firewall", wp_defender()->domain ), esc_html__( "Firewall", wp_defender()->domain ), $cap, $this->slug, array(
			&$this,
			$action
		) );
	}

	/**
	 * queue scripts
	 */
	public function scripts() {
		if ( $this->isInPage() ) {
			wp_enqueue_style( 'wpmudev-sui' );
			wp_enqueue_style( 'defender' );
			wp_register_script( 'defender-iplockout', wp_defender()->getPluginUrl() . 'assets/app/ip-lockout.js', array(
				'def-vue',
				'defender',
				'wp-i18n'
			), wp_defender()->version, true );
			//wp_register_script( 'iplockout', wp_defender()->getPluginUrl() . 'front/js/src/lockout.js', array(), wp_defender()->version, true );
			wp_set_script_translations( 'defender-iplockout', 'wpdef' );
			wp_localize_script( 'defender-iplockout', 'iplockout', $this->_scriptsData() );
			Utils::instance()->createTranslationJson( 'defender-iplockout' );
			wp_set_script_translations( 'defender-iplockout', 'wpdef', wp_defender()->getPluginPath() . 'languages' );

			wp_enqueue_media();
			wp_enqueue_script( 'def-momentjs', wp_defender()->getPluginUrl() . 'assets/js/vendor/moment/moment.min.js' );
//			wp_enqueue_style( 'def-daterangepicker', wp_defender()->getPluginUrl() . 'assets/js/vendor/daterangepicker/daterangepicker.css' );
			wp_enqueue_script( 'def-daterangepicker', wp_defender()->getPluginUrl() . 'assets/js/vendor/daterangepicker/daterangepicker.js' );
			wp_enqueue_script( 'defender-iplockout' );
			wp_enqueue_script( 'wpmudev-sui' );
		}
	}

	/**
	 * Define all the data passing to view layer
	 * @return array
	 * @since 2.2
	 */
	private function _scriptsData() {
		if ( ! $this->checkPermission() ) {
			return [];
		}
		$summaryData = Log_Model::getSummary();
		$model       = Settings::instance()->export( array( 'cache', 'geoIP_db' ) );
		//base on view we will filter out the model data
		$model['country_blacklist'] = Settings::instance()->getCountryBlacklist();
		$model['country_whitelist'] = Settings::instance()->getCountryWhitelist();
		$host                       = parse_url( get_site_url(), PHP_URL_HOST );
		$host                       = str_replace( 'www.', '', $host );
		$host                       = explode( '.', $host );
		if ( is_array( $host ) ) {
			$host = array_shift( $host );
		} else {
			$host = null;
		}
		$settings = Settings::instance();
		$tz       = get_option( 'gmt_offset' );
		if ( substr( $tz, 0, 1 ) == '-' ) {
			$tz = ' - ' . str_replace( '-', '', $tz );
		} else {
			$tz = ' + ' . $tz;
		}
		$current_country = IP_API::getCurrentCountry();
		$data            = [
			'nonces'       => [
				'fetchSummary'   => wp_create_nonce( 'fetchSummary' ),
				'updateSettings' => wp_create_nonce( 'updateSettings' ),
				'downloadGeoDB'  => wp_create_nonce( 'downloadGeoDB' ),
				'importIPs'      => wp_create_nonce( 'importIPs' ),
				'exportIPs'      => wp_create_nonce( 'exportIPs' ),
				'queryLogs'      => wp_create_nonce( 'queryLogs' ),
				'bulkAction'     => wp_create_nonce( 'bulkAction' ),
				'toggleIpAction' => wp_create_nonce( 'toggleIpAction' ),
				'emptyLogs'      => wp_create_nonce( 'emptyLogs' ),
				'queryLockedIps' => wp_create_nonce( 'queryLockedIps' ),
				'ipAction'       => wp_create_nonce( 'ipAction' ),
				'exportAsCsv'    => wp_create_nonce( 'exportAsCsv' )
			],
			'endpoints'    => $this->getAllAvailableEndpoints( \WP_Defender\Module\IP_Lockout::getClassName() ),
			'whitelabel'   => $this->whiteLabelStatus(),
			'highContrast' => $this->maybeHighContrast(),
			//'model'        => $model,
			'model'        => [
				'ip_lockout'   => $settings->exportByKeys(
					[
						'login_protection',
						'login_protection_login_attempt',
						'login_protection_lockout_timeframe',
						'login_protection_lockout_ban',
						'login_protection_lockout_duration',
						'login_protection_lockout_duration_unit',
						'login_protection_lockout_message',
						'username_blacklist',
					] ),
				'nf_lockout'   => $settings->exportByKeys( [
					'detect_404',
					'detect_404_threshold',
					'detect_404_timeframe',
					'detect_404_lockout_ban',
					'detect_404_lockout_duration',
					'detect_404_lockout_duration_unit',
					'detect_404_lockout_message',
					'detect_404_blacklist',
					'detect_404_whitelist',
					'detect_404_filetypes_blacklist',
					'detect_404_ignored_filetypes',
					'detect_404_logged',
				] ),
				'blacklist'    => $settings->exportByKeys( [
					'ip_blacklist',
					'ip_whitelist',
					'country_blacklist',
					'country_whitelist',
					'ip_lockout_message',
				] ),
				'notification' => $settings->exportByKeys( [
					'login_lockout_notification',
					'ip_lockout_notification',
					'receipts',
					'cooldown_enabled',
					'cooldown_number_lockout',
					'cooldown_period'
				] ),
				'settings'     => $settings->exportByKeys( [
					'storage_days'
				] ),
				'report'       => $settings->exportByKeys( [
					'report',
					'report_receipts',
					'report_frequency',
					'report_day',
					'report_time'
				] )
			],
			'summaryData'  => [
				'ip'          => [
					'day'  => $summaryData['loginLockoutToday'],
					'week' => $summaryData['loginLockoutThisWeek'],
				],
				'nf'          => [
					'week' => $summaryData['lockout404ThisWeek'],
					'day'  => $summaryData['lockout404Today'],
				],
				'month'       => $summaryData['lockoutThisMonth'],
				'day'         => $summaryData['lockoutToday'],
				'lastLockout' => $summaryData['lastLockout']
			],
			'misc'         => [
				'geo_db_downloaded'   => Settings::instance()->isGeoDBDownloaded(),
				'current_country'     => isset( $current_country['iso'] ) ? $current_country['iso'] : null,
				'blacklist_countries' => array_merge( [ 'all' => __( "Block all", wp_defender()->domain ) ], Utils::instance()->countriesList() ),
				'whitelist_countries' => array_merge( [ 'all' => __( "Allow all", wp_defender()->domain ) ], Utils::instance()->countriesList() ),
				'days_of_weeks'       => Utils::instance()->getDaysOfWeek(),
				'times_of_days'       => Utils::instance()->getTimes(),
				'host'                => $host,
				'user_ip'             => Utils::instance()->getUserIp(),
				'geo_requirement'     => version_compare( phpversion(), '5.4', '>=' ),
				'tz'                  => $tz,
				'current_time'        => \WP_Defender\Behavior\Utils::instance()->formatDateTime( current_time( 'timestamp' ), false )
			],
			'table'        => [
				'date_from' => Http_Helper::retrieveGet( 'date_from', date( 'm/d/Y', strtotime( 'today midnight', strtotime( '-14 days', current_time( 'timestamp' ) ) ) ) ),
				'date_to'   => Http_Helper::retrieveGet( 'date_to', date( 'm/d/Y', current_time( 'timestamp' ) ) )
			]
		];

		return $data;
	}

	/**
	 * Internal route
	 */
	public function actionIndex() {

		return $this->render( 'main' );
	}
}
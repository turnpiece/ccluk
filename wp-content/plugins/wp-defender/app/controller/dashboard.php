<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Controller;

use Hammer\Base\Container;
use Hammer\Helper\HTTP_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Behavior\WPMUDEV;
use WP_Defender\Component\Data_Factory;
use WP_Defender\Controller;
use WP_Defender\Module\Advanced_Tools\Model\Mask_Settings;
use WP_Defender\Module\Audit\Component\Audit_API;
use WP_Defender\Module\Audit\Model\Events;
use WP_Defender\Module\IP_Lockout\Component\Login_Protection_Api;
use WP_Defender\Module\IP_Lockout\Model\Log_Model;
use WP_Defender\Module\Scan\Component\Scan_Api;
use WP_Defender\Module\Scan\Component\Scanning;
use WP_Defender\Module\Scan\Model\Result_Item;
use WP_Defender\Module\Scan\Model\Settings;
use WP_Defender\Module\Setting\Component\Backup_Settings;
use WP_Defender\Module\Two_Factor\Model\Auth_Settings;

class Dashboard extends Controller {
	protected $slug = 'wp-defender';

	public function __construct() {
		if ( $this->isNetworkActivate( wp_defender()->plugin_slug ) ) {
			$this->addAction( 'network_admin_menu', 'admin_menu' );
		} else {
			$this->addAction( 'admin_menu', 'admin_menu' );
		}

		if ( $this->isInPage() ) {
			$this->addAction( 'defender_enqueue_assets', 'scripts', 11 );
		}

		$this->addAjaxAction( 'wp-defender/v1/blacklistWidgetStatus', 'blacklistWidgetStatus' );
		$this->addAjaxAction( 'wp-defender/v1/toggleBlacklistWidget', 'toggleBlacklistWidget' );

		$module_activation = wp_defender()->isFree ? 'activateModuleFree' : 'activateModule';
		$this->addAjaxAction( 'wp-defender/v1/activateModule', $module_activation );
		$this->addAjaxAction( 'wp-defender/v1/skipActivator', 'skipQuickSetup' );
		$this->addAjaxAction( 'wp-defender/v1/hideFeature', 'hideFeature' );
		$this->addAjaxAction( 'wp-defender/v1/hideTutorials', 'hideTutorials' );
		$this->addAction( 'defenderSubmitStats', 'defenderSubmitStats' );
		$this->addFilter( 'wdp_register_hub_action', 'addMyEndpoint' );
		add_filter( 'custom_menu_order', '__return_true' );
		$this->addFilter( 'menu_order', 'menuOrder' );
	}

	/**
	 * Activate modules
	 */
	public function activateModule() {
		if ( ! Utils::instance()->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'activateModule' ) ) {
			return;
		}

		$activator = $_POST;
		$activated = array();
		if ( count( $activator ) ) {
			foreach ( $activator as $item => $status ) {
				if ( 'false' === (string) $status ) {
					continue;
				}
				switch ( $item ) {
					case 'activate_scan':
						$settings               = \WP_Defender\Module\Scan\Model\Settings::instance();
						$settings->notification = true;
						$settings->time         = '4:00';
						$settings->day          = 'monday';
						$settings->frequency    = 7;
						$cron_time              = Utils::instance()->reportCronTimestamp( $settings->time,
							'scanReportCron' );
						wp_schedule_event( $cron_time, 'daily', 'scanReportCron' );
						$settings->save();
						//start a new scan
						Scan_Api::createScan();
						$activated[] = $item;
						break;
					case 'activate_audit':
						$settings               = \WP_Defender\Module\Audit\Model\Settings::instance();
						$settings->enabled      = true;
						$settings->notification = true;
						$settings->time         = '4:00';
						$settings->day          = 'monday';
						$settings->frequency    = 7;
						$cron_time              = Utils::instance()->reportCronTimestamp( $settings->time,
							'auditReportCron' );
						wp_schedule_event( $cron_time, 'daily', 'auditReportCron' );
						$activated[] = $item;
						$settings->save();
						break;
					case 'activate_blacklist':
						if ( $this->hasMethod( 'toggleStatus' ) ) {
							$this->toggleStatus( - 1, false );
						}
						$activated[] = $item;
						break;
					case 'activate_lockout':
						$settings                   = \WP_Defender\Module\IP_Lockout\Model\Settings::instance();
						$settings->detect_404       = true;
						$settings->login_protection = true;
						$settings->report           = true;
						$settings->report_frequency = 7;
						$settings->report_day       = 'monday';
						$settings->report_time      = '4:00';
						$cron_time                  = Utils::instance()->reportCronTimestamp( $settings->report_time,
							'lockoutReportCron' );
						wp_schedule_event( $cron_time, 'daily', 'lockoutReportCron' );
						$activated[] = $item;
						$settings->save();
						break;
					default:
						//param not from the button on frontend, log it
						Utils::instance()->log( sprintf( 'Unexpected value %s from IP %s', $item,
							Utils::instance()->getUserIp() ) );
						break;
				}
			}
		}

		update_site_option( 'wp_defender_is_activated', 1 );
		wp_send_json_success(
			array(
				'activated' => $activated,
			)
		);
	}

	/**
	 * Activate modules
	 */
	public function activateModuleFree() {
		if ( ! Utils::instance()->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'activateModule' ) ) {
			return;
		}

		$activator = $_POST;
		$activated = array();
		if ( count( $activator ) ) {
			foreach ( $activator as $item => $status ) {
				if ( 'false' === (string) $status ) {
					continue;
				}
				switch ( $item ) {
					case 'activate_scan':
						//start a new scan
						Scan_Api::createScan();
						$activated[] = $item;
						break;
					case 'activate_lockout':
						$settings                   = \WP_Defender\Module\IP_Lockout\Model\Settings::instance();
						$settings->detect_404       = true;
						$settings->login_protection = true;
						$activated[]                = $item;
						$settings->save();
						break;
					default:
						//param not from the button on frontend, log it
						Utils::instance()->log( sprintf( 'Unexpected value %s from IP %s', $item,
							Utils::instance()->getUserIp() ) );
						break;
				}
			}
		}

		update_site_option( 'wp_defender_free_is_activated', 1 );
		wp_send_json_success(
			array(
				'activated' => $activated,
			)
		);
	}

	/**
	 * Skip quick setup
	 */
	public function skipQuickSetup() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'skipActivator' ) ) {
			return;
		}
		$is_free = wp_defender()->isFree ? '_free' : null;
		update_site_option( 'wp_defender' . $is_free . '_is_activated', 1 );
		wp_send_json_success();
	}

	public function hideFeature() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'hideFeature' ) ) {
			return;
		}

		delete_site_option( 'waf_show_new_feature' );
		wp_send_json_success();
	}

	public function hideTutorials() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'hideTutorials' ) ) {
			return;
		}
		delete_site_option( 'wp_defender_show_tutorials' );
		wp_send_json_success( array(
			'message' => sprintf( __( "The widget has been removed. You can check all defender tutorials at the <a href=\"%s\">tutorials' tab</a> at any time.",
				wp_defender()->domain ),
				network_admin_url( 'admin.php?page=wdf-tutorial' ) ),
		) );
	}

	public function menuOrder( $menu_order ) {
		global $submenu;
		if ( isset( $submenu['wp-defender'] ) ) {
			$defender_menu          = $submenu['wp-defender'];
			$defender_menu[0][0]    = esc_html__( 'Dashboard', wp_defender()->domain );
			$defender_menu          = array_values( $defender_menu );
			$submenu['wp-defender'] = $defender_menu;
		}

		global $menu;
		$count     = $this->countTotalIssues();
		$indicator = $count > 0 ? ' <span class="update-plugins wd-issue-indicator-sidebar"><span class="plugin-count">' . $count . '</span></span>' : null;
		foreach ( $menu as $k => $item ) {
			if ( 'wp-defender' === $item[2] ) {
				$menu[ $k ][0] .= $indicator;
			}
		}

		return $menu_order;
	}

	public function defenderSubmitStats() {
		if ( $this->hasMethod( '_submitStatsToDev' ) ) {
			$this->_submitStatsToDev();
		}
	}

	/**
	 * @param $actions
	 *
	 * @return mixed
	 */
	public function addMyEndpoint( $actions ) {
		$actions['defender_new_scan']          = array( &$this, 'newScan' );
		$actions['defender_schedule_scan']     = array( &$this, 'scheduleScan' );
		$actions['defender_manage_audit_log']  = array( &$this, 'manageAuditLog' );
		$actions['defender_manage_lockout']    = array( &$this, 'manageLockout' );
		$actions['defender_whitelist_ip']      = array( &$this, 'whitelistIP' );
		$actions['defender_blacklist_ip']      = array( &$this, 'blacklistIP' );
		$actions['defender_get_stats']         = array( &$this, 'getStats' );
		$actions['defender_get_scan_progress'] = array( &$this, 'getScanProgress' );

		//backup/restore settings
		$actions['defender_export_settings'] = array( &$this, 'exportSettings' );
		$actions['defender_import_settings'] = array( &$this, 'importSettings' );

		$actions['defender_get_stats_v2'] = [ &$this, 'defender_get_stats_v2' ];

		return $actions;
	}

	public function defender_get_stats_v2( $params, $action ) {
		if ( ! class_exists( WPMUDEV::class ) ) {
			return wp_send_json_error();
		}
		$date_format = 'm/d/Y';
		$wpmudev     = WPMUDEV::instance();
		$summary     = $wpmudev->stats_summary();
		$report      = $wpmudev->stats_report();
		$tweaks      = $wpmudev->stats_security_tweaks();
		global $wp_version;
		$for_hub  = true;
		$scan     = $wpmudev->stats_malware_scan();
		$firewall = Log_Model::getSummary( $for_hub );

		$audit            = Audit_API::summary( $for_hub );
		$security_headers = $wpmudev->stats_security_headers();

		$ret = [
			'summary'         => [
				'count'     => $summary['count'],
				'next_scan' => $summary['next_scan']
			],
			'report'          => [
				'malware_scan'  => $report['scan'],
				'firewall'      => $report['firewall'],
				'audit_logging' => $report['audit']
			],
			'security_tweaks' => [
				'issues'       => $tweaks['issues'],
				'fixed'        => $tweaks['fixed'],
				'notification' => $tweaks['notification'],
				'wp_version'   => $wp_version,
				'php_version'  => phpversion()
			],
			'malware_scan'    => [
				'count'        => $scan['count'],
				'notification' => $scan['notification']
			],
			'firewall'        => [
				'last_lockout'        => $firewall['lastLockout'],
				'24_hours'            => [
					'login_lockout' => $firewall['loginLockoutToday'],
					'404_lockout'   => $firewall['lockout404Today']
				],
				'7_days'              => [
					'login_lockout' => $firewall['loginLockoutThisWeek'],
					'404_lockout'   => $firewall['lockout404ThisWeek']
				],
				'30_days'             => [
					'login_lockout' => $firewall['lockoutLoginThisMonth'],
					'404_lockout'   => $firewall['lockout404ThisMonth']
				],
				'notification_status' => [
					'login_lockout' => \WP_Defender\Module\IP_Lockout\Model\Settings::instance()->login_lockout_notification,
					'404_lockout'   => \WP_Defender\Module\IP_Lockout\Model\Settings::instance()->ip_lockout_notification
				]
			],
			'audit'           => [
				'last_event' => $audit['lastEvent'],
				'24_hours'   => $audit['day_count'],
				'7_days'     => $audit['last_7_days'],
				'30_days'    => $audit['last_30_days']
			],
			'advanced_tools'  => [
				'security_headers' => $security_headers,
				'mask_login'       => Mask_Settings::instance()->isEnabled()
			],
			'two_fa'          => [
				'status'     => Auth_Settings::instance()->enabled,
				'lost_phone' => Auth_Settings::instance()->lost_phone
			]
		];
		Utils::instance()->log( json_encode( $ret ) );
		wp_send_json_success( [
			'stats' => $ret
		] );
	}

	public function importSettings( $params ) {
		//dirty but quick
		$configs = json_decode( json_encode( $params->configs ), true );
		foreach ( $configs as $module => $mdata ) {
			foreach ( $mdata as $key => $value ) {
				if ( 'geoIP_db' === $key ) {
					if ( ! empty( $value ) ) {
						//download it
						Login_Protection_Api::downloadGeoIP();
					} else {
						//reset it
						$mdata[ $key ] = '';
					}
				} elseif ( is_string( $value ) ) {
					$value         = str_replace( '{nl}', PHP_EOL, $value );
					$mdata[ $key ] = $value;
				}
			}
			$configs[ $module ] = $mdata;
		}
		Backup_Settings::restoreData( $configs );

		wp_send_json_success();
	}

	public function exportSettings() {
		$data = Backup_Settings::parseDataForHub();
		//we have to replace all the new line in configs
		$configs = $data['configs'];
		foreach ( $configs as $module => $mdata ) {
			foreach ( $mdata as $key => $value ) {
				if ( is_string( $value ) ) {
					$value         = str_replace( array( "\r", "\n" ), '{nl}', $value );
					$mdata[ $key ] = $value;
				}
			}
			$configs[ $module ] = $mdata;
		}
		$data['configs'] = $configs;
		wp_send_json_success( $data );
	}

	public function getScanProgress() {
		$scanning = new Scanning();
		$ret      = $scanning->run();
		if ( is_wp_error( $ret ) ) {
			wp_send_json_error(
				array(
					'message' => $ret->get_error_message(),
				)
			);
		} else {
			$percent = $scanning->getScanProgress();
			if ( true === $ret ) {
				$percent = 100;
			}
			wp_send_json_success(
				array(
					'progress' => $percent,
				)
			);
		}
	}

	/**
	 * @param $params
	 * @param $action
	 */
	public function newScan( $params, $action ) {
		$ret = Scan_Api::createScan();
		if ( is_wp_error( $ret ) ) {
			wp_send_json_error(
				array(
					'message' => $ret->get_error_message(),
				)
			);
		}

		wp_send_json_success();
	}

	/**
	 * @param $params
	 * @param $action
	 */
	public function scheduleScan( $params, $action ) {
		$frequency    = $params['frequency'];
		$day          = $params['day'];
		$time         = $params['time'];
		$allowed_freq = array( 1, 7, 30 );
		if ( ! in_array( $frequency, $allowed_freq ) || ! in_array( $day,
				Utils::instance()->getDaysOfWeek() ) || ! in_array( $time, Utils::instance()->getTimes() ) ) {
			wp_send_json_error();
		}
		$settings            = Settings::instance();
		$settings->frequency = $frequency;
		$settings->day       = $day;
		$settings->time      = $time;

		wp_send_json_success();
	}

	/**
	 * Hub Audit log endpoint
	 *
	 * @param $params
	 * @param $action
	 */
	public function manageAuditLog( $params, $action ) {
		$response = null;
		if ( class_exists( '\WP_Defender\Module\Audit\Model\Settings' ) ) {
			$response = array();
			$settings = \WP_Defender\Module\Audit\Model\Settings::instance();
			if ( true === $settings->enabled ) {
				$settings->enabled   = false;
				$response['enabled'] = false;
			} else {
				$settings->enabled   = true;
				$response['enabled'] = true;
			}
			$settings->save();
		}
		wp_send_json_success( $response );
	}

	/**
	 * Hub Lockouts endpoint
	 *
	 * @param $params
	 * @param $action
	 */
	public function manageLockout( $params, $action ) {
		$type     = $params['type'];
		$settings = \WP_Defender\Module\IP_Lockout\Model\Settings::instance();
		$response = array();
		if ( 'login' === $type ) {
			if ( $settings->login_protection ) {
				$settings->login_protection = 0;
				$response[ $type ]          = 'disabled';
			} else {
				$settings->login_protection = 1;
				$response[ $type ]          = 'enabled';
			}
			$settings->save();
		} elseif ( '404' === $type ) {
			if ( $settings->detect_404 ) {
				$settings->detect_404 = 0;
				$response[ $type ]    = 'disabled';
			} else {
				$settings->detect_404 = 1;
				$response[ $type ]    = 'enabled';
			}
			$settings->save();
		} else {
			$response[ $type ] = 'invalid';
		}
		wp_send_json_success();
	}

	/**
	 * Hub Whitelist IP endpoint
	 *
	 * @param $params
	 * @param $action
	 */
	public function whitelistIP( $params, $action ) {
		$settings = \WP_Defender\Module\IP_Lockout\Model\Settings::instance();
		$ip       = $params['ip'];
		if ( $ip && filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			$settings->removeIpFromList( $ip, 'blocklist' );
			$settings->addIpToList( $ip, 'allowlist' );
		} else {
			wp_send_json_error();
		}
		wp_send_json_success();
	}

	/**
	 * Hub Blacklist IP endpoint
	 *
	 * @param $params
	 * @param $action
	 */
	public function blacklistIP( $params, $action ) {
		$settings = \WP_Defender\Module\IP_Lockout\Model\Settings::instance();
		$ip       = $params['ip'];
		if ( $ip && filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			$settings->removeIpFromList( $ip, 'allowlist' );
			$settings->addIpToList( $ip, 'blocklist' );
		} else {
			wp_send_json_error();
		}
		wp_send_json_success();
	}

	/**
	 * Hub Stats endpoint
	 *
	 * @param $params
	 * @param $action
	 */
	public function getStats( $params, $action ) {
		$stats = Utils::instance()->generateStats();
		wp_send_json_success(
			array(
				'stats' => $stats,
			)
		);
	}

	public function actionIndex() {
		$this->render( 'main' );
	}

	public function blacklistWidgetStatus() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'blacklistWidgetStatus' ) ) {
			return;
		}

		if ( $this->hasMethod( 'pullBlacklistStatus' ) ) {
			$this->pullBlacklistStatus();
		}

		exit;
	}

	public function toggleBlacklistWidget() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'toggleBlacklistWidget' ) ) {
			return;
		}

		if ( $this->hasMethod( 'toggleStatus' ) ) {
			$this->toggleStatus();
		}

		exit;
	}

	/**
	 * @param  bool  $detail
	 *
	 * @return array|int|null|string
	 */
	public function countTotalIssues( $detail = false ) {
		$hardener_count = count( \WP_Defender\Module\Hardener\Model\Settings::instance()->issues );
		$scan           = Scan_Api::getLastScan();
		$total          = $hardener_count;
		$scan_count     = 0;
		if ( is_object( $scan ) ) {
			$scan_count = $scan->countAll( Result_Item::STATUS_ISSUE );

			$total += $scan_count;
		}
		if ( false === $detail ) {
			return $total;
		}

		return array( $hardener_count, $scan_count );
	}

	/**
	 *
	 */
	public function admin_menu() {
		$cap        = is_multisite() ? 'manage_network_options' : 'manage_options';
		$menu_title = wp_defender()->isFree ? esc_html__( 'Defender',
			wp_defender()->domain ) : esc_html__( 'Defender Pro',
			wp_defender()->domain );
		add_menu_page(
			$menu_title,
			$menu_title,
			$cap,
			'wp-defender',
			array(
				&$this,
				'actionIndex',
			),
			$this->get_menu_icon()
		);
	}

	/**
	 * Return svg image
	 * @return string
	 */
	private function get_menu_icon() {
		ob_start();
		?>
     <svg width="17px" height="18px" viewBox="10 397 17 18" version="1.1" xmlns="http://www.w3.org/2000/svg"
     >
      <!-- Generator: Sketch 3.8.3 (29802) - http://www.bohemiancoding.com/sketch -->
      <desc>Created with Sketch.</desc>
      <defs></defs>
      <path
        d="M24.8009393,403.7962 L23.7971393,410.1724 C23.7395393,410.5372 23.5313393,410.8528 23.2229393,411.0532 L18.4001393,413.6428 L13.5767393,411.0532 C13.2683393,410.8528 13.0601393,410.5372 13.0019393,410.1724 L11.9993393,403.7962 L11.6153393,401.3566 C12.5321393,402.9514 14.4893393,405.5518 18.4001393,408.082 C22.3115393,405.5518 24.2675393,402.9514 25.1855393,401.3566 L24.8009393,403.7962 Z M26.5985393,398.0644 C25.7435393,397.87 22.6919393,397.2106 19.9571393,397 L19.9571393,403.4374 L18.4037393,404.5558 L16.8431393,403.4374 L16.8431393,397 C14.1077393,397.2106 11.0561393,397.87 10.2011393,398.0644 C10.0685393,398.0938 9.98213933,398.221 10.0031393,398.3536 L10.8875393,403.969 L11.8913393,410.3446 C12.0071393,411.0796 12.4559393,411.7192 13.1105393,412.0798 L16.8431393,414.1402 L18.4001393,415 L19.9571393,414.1402 L23.6891393,412.0798 C24.3431393,411.7192 24.7925393,411.0796 24.9083393,410.3446 L25.9121393,403.969 L26.7965393,398.3536 C26.8175393,398.221 26.7311393,398.0938 26.5985393,398.0644 L26.5985393,398.0644 Z"
        id="Defender-Icon" stroke="none" fill="#FFFFFF" fill-rule="evenodd"></path>
     </svg>
		<?php
		$svg = ob_get_clean();

		return 'data:image/svg+xml;base64,' . base64_encode( $svg );
	}

	public function isShowTutorials() {
		return get_site_option( 'wp_defender_show_tutorials' );
	}

	public function scripts() {
		wp_enqueue_style( 'defender' );
		wp_register_script(
			'defender-dashboard',
			wp_defender()->getPluginUrl() . 'assets/app/dashboard.js',
			array(
				'def-vue',
				'defender',
				'wp-i18n',
			),
			wp_defender()->version,
			true
		);
		Utils::instance()->createTranslationJson( 'defender-dashboard' );
		wp_set_script_translations( 'defender-dashboard', 'wpdef', wp_defender()->getPluginPath() . 'languages' );
		$waf = Container::instance()->get( 'waf' );
		wp_localize_script(
			'defender-dashboard',
			'dashboard',
			array_merge(
				Data_Factory::buildData(),
				array(
					'quick_setup'  => array(
						'show'      => $this->isShowActivator(),
						'nonces'    => array(
							'skip'     => wp_create_nonce( 'skipActivator' ),
							'activate' => wp_create_nonce( 'activateModule' ),
						),
						'endpoints' => array(
							'skip'     => 'wp-defender/v1/skipActivator',
							'activate' => 'wp-defender/v1/activateModule',
						),
					),
					'new_features' => array(
						'show'      => $waf->maybe_show_modal(),
						'nonces'    => array(
							'hide' => wp_create_nonce( 'hideFeature' ),
						),
						'endpoints' => array(
							'hide' => 'wp-defender/v1/hideFeature',
						),
					),
					'tutorials'    => array(
						'show'      => $this->isShowTutorials(),
						'nonces'    => array(
							'hide' => wp_create_nonce( 'hideTutorials' ),
						),
						'endpoints' => array(
							'hide' => 'wp-defender/v1/hideTutorials',
						),
					)
				)
			)
		);
		wp_enqueue_script( 'defender-dashboard' );
		wp_enqueue_script( 'wpmudev-sui' );
	}

	/**
	 * @return array
	 */
	public function behaviors() {
		return array(
			'utils'     => '\WP_Defender\Behavior\Utils',
			'activator' => wp_defender()->isFree ? '\WP_Defender\Behavior\Activator_Free' : '\WP_Defender\Behavior\Activator',
			'blacklist' => wp_defender()->isFree ? '\WP_Defender\Behavior\Blacklist_Free' : '\WP_Defender\Behavior\Blacklist',
		);
	}
}
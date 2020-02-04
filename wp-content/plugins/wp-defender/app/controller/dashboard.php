<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Controller;

use Hammer\Helper\HTTP_Helper;
use Hammer\Helper\WP_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Component\Data_Factory;
use WP_Defender\Controller;
use WP_Defender\Module\Audit\Component\Audit_API;
use WP_Defender\Module\IP_Lockout;
use WP_Defender\Module\IP_Lockout\Component\Login_Protection_Api;
use WP_Defender\Module\Scan\Component\Scan_Api;
use WP_Defender\Module\Scan\Model\Result_Item;
use WP_Defender\Module\Scan\Model\Settings;
use WP_Defender\Module\Setting\Component\Backup_Settings;

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
		$this->addAjaxAction( 'wp-defender/v1/activateModule', 'activateModule' );
		$this->addAjaxAction( 'wp-defender/v1/skipActivator', 'skipQuickSetup' );
		$this->addAction( 'defenderSubmitStats', 'defenderSubmitStats' );
		$this->addFilter( 'wdp_register_hub_action', 'addMyEndpoint' );
		add_filter( 'custom_menu_order', '__return_true' );
		$this->addFilter( 'menu_order', 'menuOrder' );
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

	public function menuOrder( $menu_order ) {
		global $submenu;
		if ( isset( $submenu['wp-defender'] ) ) {
			$defender_menu = $submenu['wp-defender'];
			//$defender_menu[6][4] = 'wd-menu-hide';
			$defender_menu[0][0]    = esc_html__( "Dashboard", wp_defender()->domain );
			$defender_menu          = array_values( $defender_menu );
			$submenu['wp-defender'] = $defender_menu;
		}

		global $menu;
		$count     = $this->countTotalIssues();
		$indicator = $count > 0 ? ' <span class="update-plugins wd-issue-indicator-sidebar"><span class="plugin-count">' . $count . '</span></span>' : null;
		foreach ( $menu as $k => $item ) {
			if ( $item[2] == 'wp-defender' ) {
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

		return $actions;
	}

	public function importSettings( $params ) {
		//dirty but quick
		$configs = json_decode( json_encode( $params->configs ), true );
		foreach ( $configs as $module => $mdata ) {
			foreach ( $mdata as $key => $value ) {
				if ( $key == 'geoIP_db' ) {
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
		$ret = Scan_Api::processActiveScan();
		if ( is_wp_error( $ret ) ) {
			wp_send_json_error( array(
				'message' => $ret->get_error_message()
			) );
		} else {
			$percent = Scan_Api::getScanProgress();
			if ( $ret == true ) {
				$percent = 100;
			}
			wp_send_json_success( array(
				'progress' => $percent
			) );
		}
	}

	/**
	 * @param $params
	 * @param $action
	 */
	public function newScan( $params, $action ) {
		$ret = Scan_Api::createScan();
		if ( is_wp_error( $ret ) ) {
			wp_send_json_error( array(
				'message' => $ret->get_error_message()
			) );
		}

		wp_send_json_success();
	}

	/**
	 * @param $params
	 * @param $action
	 */
	public function scheduleScan( $params, $action ) {
		$frequency   = $params['frequency'];
		$day         = $params['day'];
		$time        = $params['time'];
		$allowedFreq = array( 1, 7, 30 );
		if ( ! in_array( $frequency, $allowedFreq ) || ! in_array( $day, Utils::instance()->getDaysOfWeek() ) || ! in_array( $time, Utils::instance()->getTimes() ) ) {
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
			if ( $settings->enabled == true ) {
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
		if ( $type == 'login' ) {
			if ( $settings->login_protection ) {
				$settings->login_protection = 0;
				$response[ $type ]          = 'disabled';
			} else {
				$settings->login_protection = 1;
				$response[ $type ]          = 'enabled';
			}
			$settings->save();
		} else if ( $type == '404' ) {
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
			$settings->removeIpFromList( $ip, 'blacklist' );
			$settings->addIpToList( $ip, 'whitelist' );
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
			$settings->removeIpFromList( $ip, 'whitelist' );
			$settings->addIpToList( $ip, 'blacklist' );
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
				'stats' => $stats
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
	 * @param bool $detail
	 *
	 * @return array|int|null|string
	 */
	public function countTotalIssues( $detail = false ) {
		$hardenerCount = count( \WP_Defender\Module\Hardener\Model\Settings::instance()->issues );
		$scan          = Scan_Api::getLastScan();
		$total         = $hardenerCount;
		$scanCount     = 0;
		if ( is_object( $scan ) ) {
			$scanCount = $scan->countAll( Result_Item::STATUS_ISSUE );

			$total += $scanCount;
		}
		if ( $detail == false ) {
			return $total;
		}

		return array( $hardenerCount, $scanCount );
	}

	/**
	 *
	 */
	public function admin_menu() {
		$cap        = is_multisite() ? 'manage_network_options' : 'manage_options';
		$menu_title = wp_defender()->isFree ? esc_html__( "Defender", wp_defender()->domain ) : esc_html__( "Defender Pro", wp_defender()->domain );
		add_menu_page( $menu_title, $menu_title, $cap, 'wp-defender', array(
			&$this,
			'actionIndex'
		), $this->get_menu_icon() );
	}

	/**
	 * Return svg image
	 * @return string
	 */
	private function get_menu_icon() {
		ob_start();
		?>
		<svg width="17px" height="18px" viewBox="10 397 17 18" version="1.1" xmlns="http://www.w3.org/2000/svg"
		     xmlns:xlink="http://www.w3.org/1999/xlink">
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

	public function scripts() {
		wp_enqueue_style( 'defender' );
		wp_register_script( 'defender-dashboard', wp_defender()->getPluginUrl() . 'assets/app/dashboard.js', array(
			'vue',
			'defender',
			'wp-i18n'
		), wp_defender()->version, true );
		\WP_Defender\Behavior\Utils::instance()->createTranslationJson( 'defender-dashboard' );
		wp_set_script_translations( 'defender-dashboard', 'wpdef', wp_defender()->getPluginPath() . 'languages' );
		wp_localize_script( 'defender-dashboard', 'dashboard', array_merge( Data_Factory::buildData(), [
			'quick_setup' => [
				'show'      => $this->isShowActivator(),
				'nonces'    => [
					'skip'     => wp_create_nonce( 'skipActivator' ),
					'activate' => wp_create_nonce( 'activateModule' )
				],
				'endpoints' => [
					'skip'     => 'wp-defender/v1/skipActivator',
					'activate' => 'wp-defender/v1/activateModule'
				]
			]
		] ) );
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
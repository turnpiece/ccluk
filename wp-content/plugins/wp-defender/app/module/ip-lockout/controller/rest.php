<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\IP_Lockout\Controller;

use Hammer\Helper\Array_Helper;
use Hammer\Helper\HTTP_Helper;
use Hammer\Helper\WP_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Controller;
use WP_Defender\Module\IP_Lockout;
use WP_Defender\Module\IP_Lockout\Component\Login_Protection_Api;
use WP_Defender\Module\IP_Lockout\Model\Log_Model;
use WP_Defender\Module\IP_Lockout\Model\Settings;

class Rest extends Controller {
	public function __construct() {
		$namespace = 'wp-defender/v1';
		$namespace .= '/lockout';
		$routes    = [
			$namespace . '/updateSettings' => 'updateSettings',
			$namespace . '/downloadGeoDB'  => 'downloadGeoDB',
			$namespace . '/importIPs'      => 'importIPs',
			$namespace . '/exportIPs'      => 'exportIPs',
			$namespace . '/queryLogs'      => 'queryLogs',
			$namespace . '/bulkAction'     => 'bulkAction',
			$namespace . '/toggleIpAction' => 'toggleIpAction',
			$namespace . '/emptyLogs'      => 'emptyLogs',
			$namespace . '/queryLockedIps' => 'queryLockedIps',
			$namespace . '/ipAction'       => 'ipAction',
			$namespace . '/exportAsCsv'    => 'exportAsCsv'
		];

		$this->registerEndpoints( $routes, IP_Lockout::getClassName() );
	}

	/**
	 * Endpoint for toggle IP status, use in IP Banning->ban IPs
	 */
	public function ipAction() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'ipAction' ) ) {
			return;
		}

		$ip     = HTTP_Helper::retrievePost( 'ip' );
		$action = HTTP_Helper::retrievePost( 'behavior' );
		$model  = IP_Lockout\Model\IP_Model::findOne( [
			'ip' => $ip
		] );

		if ( is_object( $model ) ) {
			if ( $action === 'unban' ) {
				$model->status = IP_Lockout\Model\IP_Model::STATUS_NORMAL;
				$model->save();
			} elseif ( $action === 'ban' ) {
				$model->status = IP_Lockout\Model\IP_Model::STATUS_BLOCKED;
				$model->save();
			}
			wp_send_json_success( [ 'data' => '' ] );
		}
	}

	/**
	 * Endpoint to query locked IPs, use in IP Banning->ban IPs
	 */
	public function queryLockedIps() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'queryLockedIps' ) ) {
			return;
		}

		$results = IP_Lockout\Model\IP_Model::queryLockedIp();

		wp_send_json_success( [
			'ips_locked' => $results,
		] );
	}

	/**
	 * Endpoint for toggle IP blacklist or whitelist, use on logs item content
	 */
	public function toggleIpAction() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'toggleIpAction' ) ) {
			return;
		}

		$ip   = HTTP_Helper::retrievePost( 'ip', false );
		$type = HTTP_Helper::retrievePost( 'type' );
		$type = sanitize_key( $type );

		if ( $ip && filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			if ( 'unwhitelist' === $type || 'unblacklist' === $type ) {
				$type = substr( $type, 2 );
				$type = 'whitelist' === $type ? 'allowlist' : 'blocklist';
				Settings::instance()->removeIpFromList( $ip, $type );
				wp_send_json_success( array(
					'message' => sprintf( __( "IP %s has been removed from your %s. You can control your %s in <a href=\"%s\">IP Lockouts.</a>",
						wp_defender()->domain ), $ip, $type, $type,
						network_admin_url( 'admin.php?page=wdf-ip-lockout&view=blocklist' ) ),
				) );
			} else {
				$type = 'whitelist' === $type ? 'allowlist' : 'blocklist';
				Settings::instance()->addIpToList( $ip, $type );
				wp_send_json_success( array(
					'message' => sprintf( __( "IP %s has been added to your %s You can control your %s in <a href=\"%s\">IP Lockouts.</a>",
						wp_defender()->domain ), $ip, $type, $type,
						network_admin_url( 'admin.php?page=wdf-ip-lockout&view=blocklist' ) ),
				) );
			}

		} else {
			wp_send_json_error( array(
				'message' => __( "No record found", wp_defender()->domain )
			) );
		}
	}

	public function emptyLogs() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'emptyLogs' ) ) {
			return;
		}

		$perPage = 500;
		$count   = Log_Model::deleteAll( array(), '0,' . $perPage );
		if ( $count == 0 ) {
			wp_send_json_success( array(
				'message' => __( "Your logs have been successfully deleted.", wp_defender()->domain )
			) );
		}

		wp_send_json_error( array() );
	}

	/**
	 *
	 */
	public function bulkAction() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'bulkAction' ) ) {
			return;
		}

		$ids      = HTTP_Helper::retrievePost( 'ids', [] );
		$type     = HTTP_Helper::retrievePost( 'type' );
		$messages = '';
		$ips      = [];
		if ( count( $ids ) && $type ) {
			$settings = Settings::instance();
			switch ( $type ) {
				case 'whitelist':
					foreach ( $ids as $id ) {
						$model = Log_Model::findByID( $id );
						$ips[] = $model->ip;
						$settings->addIpToList( $model->ip, 'allowlist' );
					}
					$messages = sprintf( __( "IP %s has been added to your allowlist. You can control your allowlist in <a href=\"%s\">IP Lockouts.</a>",
						wp_defender()->domain ), implode( ',', $ips ),
						network_admin_url( 'admin.php?page=wdf-ip-lockout&view=blocklist' ) );
					break;
				case 'ban':
					foreach ( $ids as $id ) {
						$model = Log_Model::findByID( $id );
						$ips[] = $model->ip;
						$settings->addIpToList( $model->ip, 'blocklist' );
					}
					$messages = sprintf( __( "IP %s has been added to your blocklist You can control your blocklist in <a href=\"%s\">IP Lockouts.</a>",
						wp_defender()->domain ), implode( ',', $ips ),
						network_admin_url( 'admin.php?page=wdf-ip-lockout&view=blocklist' ) );
					break;
				case 'delete':
					foreach ( $ids as $id ) {
						$model = Log_Model::findByID( $id );
						$ips[] = $model->ip;
						$model->delete();
					}
					$messages = sprintf( __( "IP %s has been deleted", wp_defender()->domain ), implode( ',', $ips ) );
					break;
				default:
					//param not from the button on frontend, log it
					//error_log( sprintf( 'Unexpected value %s from IP %s', $type, Utils::instance()->getUserIp() ) );
					break;
			}

			wp_send_json_success( array(
				'reload'  => 1,
				'message' => $messages
			) );
		}
	}

	/**
	 * Query the data
	 */
	public function queryLogs() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'queryLogs' ) ) {
			return;
		}
		$data    = $_POST;
		$filters = [
			'dateFrom' => strtotime( 'midnight', strtotime( Array_Helper::getValue( $data, 'date_from' ) ) ),
			'dateTo'   => strtotime( 'tomorrow', strtotime( Array_Helper::getValue( $data, 'date_to' ) ) ),
			'type'     => Array_Helper::getValue( $data, 'type', false ),
			'ip'       => Array_Helper::getValue( $data, 'ip', false )
		];

		$paged   = Array_Helper::getValue( $data, 'paged', 1 );
		$orderBy = Array_Helper::getValue( $data, 'orderBy', 'id' );
		$order   = Array_Helper::getValue( $data, 'order', 'DESC' );

		$pageSize = 20;

		list( $logs, $countAllLogs ) = Log_Model::queryLogs( $filters, $paged, $orderBy, $order, $pageSize );
		$ids = [];
		foreach ( $logs as $log ) {
			$log->date       = $log->get_date();
			$log->statusText = Login_Protection_Api::getIPStatusText( $log->ip );
			$log->ip_status  = $log->blackOrWhite();
			$log->is_mine    = Utils::instance()->getUserIp() == $log->ip;
			$ids[]           = $log->id;
		}
		$totalPages = ceil( $countAllLogs / $pageSize );
		wp_send_json_success( array(
			'logs'       => $logs,
			'countAll'   => $countAllLogs,
			'totalPages' => $totalPages
		) );
	}

	/**
	 * Importing IPs from our exporter
	 */
	public function importIPs() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		$id = HTTP_Helper::retrievePost( 'id' );
		if ( ! is_object( get_post( $id ) ) ) {
			wp_send_json_error( array(
				'message' => __( "Your file is invalid!", wp_defender()->domain )
			) );
		}
		$file = get_attached_file( $id );

		if ( ! is_file( $file ) ) {
			wp_send_json_error( array(
				'message' => __( "Your file is invalid!", wp_defender()->domain )
			) );
		}

		if ( ! ( $data = Login_Protection_Api::verifyImportFile( $file ) ) ) {
			wp_send_json_error( array(
				'message' => __( "Your file content is invalid!", wp_defender()->domain )
			) );
		}

		$settings = Settings::instance();
		//all good, start to import

		foreach ( $data as $line ) {
			$settings->addIpToList( $line[0], $line[1] );
		}
		wp_send_json_success( array(
			'message'   => __( "Your allowlist/blocklist has been successfully imported.", wp_defender()->domain ),
			'reload'    => 1,
			'blacklist' => $settings->getIpBlacklist(),
			'whitelist' => $settings->getIpWhitelist()
		) );
	}

	/**
	 * Downloading GeoDB from maxmind
	 */
	public function downloadGeoDB() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'downloadGeoDB' ) ) {
			return;
		}

		$license_key = HTTP_Helper::retrievePost( 'api_key' );
		$license_key = sanitize_text_field( $license_key );
		$url         = "https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-Country&license_key=$license_key&suffix=tar.gz";
		$tmp         = download_url( $url );
		if ( ! is_wp_error( $tmp ) ) {
			$phar    = new \PharData( $tmp );
			$defPath = Utils::instance()->getDefUploadDir();
			$path    = $defPath . DIRECTORY_SEPARATOR . 'maxmind';
			if ( ! is_dir( $path ) ) {
				mkdir( $path );
			}
			$phar->extractTo( $path, null, true );
			$settings           = Settings::instance();
			$settings->geoIP_db = $path . DIRECTORY_SEPARATOR . $phar->current()->getFileName() . DIRECTORY_SEPARATOR . 'GeoLite2-Country.mmdb';
			$settings->save();
			wp_send_json_success( array(
				'message' => __( "Database downloaded", wp_defender()->domain )
			) );
		} else {
			wp_send_json_error( [
				'message' => $tmp->get_error_message()
			] );
		}
	}

	/**
	 * Update IP lockout settings, parameters sent via an ajax _POST request
	 */
	public function updateSettings() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'updateSettings' ) ) {
			return;
		}
		$settings     = Settings::instance();
		$lastSettings = clone $settings;
		$data         = stripslashes( $_POST['data'] );
		$data         = json_decode( $data, true );

		$settings->import( $data );

		if ( $settings->validate() ) {
			$settings->save();
			$faultIps = WP_Helper::getArrayCache()->get( 'faultIps', array() );
			$isBLSelf = WP_Helper::getArrayCache()->get( 'isBlacklistSelf', false );
			if ( $faultIps || $isBLSelf ) {
				$res = array(
					'message' => sprintf( __( "Your settings have been updated, however some IPs were removed because invalid format, or you blocklist yourself",
						wp_defender()->domain ), implode( ',', $faultIps ) ),
					'reload'  => 1
				);
			} else {
				$res = array( 'message' => __( "Your settings have been updated.", wp_defender()->domain ), );
			}
			if ( ( $lastSettings->login_protection != $settings->login_protection )
			     || ( $lastSettings->detect_404 != $settings->detect_404 )
			) {
				if ( isset( $data['login_protection'] ) ) {
					if ( $data['login_protection'] == 1 ) {
						$status = __( "Login Protection has been activated.", wp_defender()->domain );
					} else {
						$status = __( "Login Protection has been deactivated.", wp_defender()->domain );
					}
				}
				if ( isset( $data['detect_404'] ) ) {
					if ( $data['detect_404'] == 1 ) {
						$status = __( "404 Detection has been activated.", wp_defender()->domain );
					} else {
						$status = __( "404 Detection has been deactivated.", wp_defender()->domain );
					}
				}
				//mean enabled or disabled, reload
				$res['reload'] = 1;
				if ( isset( $status ) && strlen( $status ) ) {
					$res['message'] = $status;
				}
			}
			if ( $this->hasMethod( 'scheduleReport' ) ) {
				$this->scheduleReport();
			}
			Utils::instance()->submitStatsToDev();
			$res['reload'] = 1;
			wp_send_json_success( $res );
		} else {
			wp_send_json_error( array(
				'message' => implode( '<br/>', $settings->getErrors() )
			) );
		}
	}

	/**
	 * Csv exporter
	 */
	public function exportAsCsv() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'exportAsCsv' ) ) {
			return;
		}
		$logs    = Log_Model::findAll();
		$fp      = fopen( 'php://memory', 'w' );
		$headers = array(
			__( 'Log', wp_defender()->domain ),
			__( 'Date / Time', wp_defender()->domain ),
			__( 'Type', wp_defender()->domain ),
			__( 'IP address', wp_defender()->domain ),
			__( 'Status', wp_defender()->domain )
		);
		fputcsv( $fp, $headers );
		foreach ( $logs as $log ) {
			$item = array(
				$log->log,
				$log->get_date(),
				$log->get_type(),
				$log->ip,
				Login_Protection_Api::getIPStatusText( $log->ip )
			);
			fputcsv( $fp, $item );
		}

		$filename = 'wdf-lockout-logs-export-' . date( 'ymdHis' ) . '.csv';
		fseek( $fp, 0 );
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '";' );
		// make php send the generated csv lines to the browser
		fpassthru( $fp );
		exit();
	}

	public function behaviors() {
		$behaviors = array(
			'utils' => '\WP_Defender\Behavior\Utils',
		);
		if ( class_exists( 'WP_Defender\Module\IP_Lockout\Behavior\Pro\Reporting' ) ) {
			$behaviors['report'] = 'WP_Defender\Module\IP_Lockout\Behavior\Pro\Reporting';
		}

		return $behaviors;
	}
}
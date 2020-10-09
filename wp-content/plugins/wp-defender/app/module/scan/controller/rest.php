<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Scan\Controller;


use Hammer\Helper\Array_Helper;
use Hammer\Helper\HTTP_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Controller;
use WP_Defender\Module\Scan;
use WP_Defender\Module\Scan\Component\Scan_Api;
use WP_Defender\Module\Scan\Model\Result_Item;

class Rest extends Controller {
	public function __construct() {
		$namespace = 'wp-defender/v1';
		$namespace .= '/scan';
		$routes    = [
			$namespace . '/newScan'        => 'newScan',
			$namespace . '/cancelScan'     => 'cancelScan',
			$namespace . '/processScan'    => 'processScan',
			$namespace . '/ignoreIssue'    => 'ignoreIssue',
			$namespace . '/unignoreIssue'  => 'unignoreIssue',
			$namespace . '/deleteIssue'    => 'deleteIssue',
			$namespace . '/solveIssue'     => 'solveIssue',
			$namespace . '/updateSettings' => 'updateSettings',
			$namespace . '/getFileSrcCode' => 'getFileSrcCode',
			$namespace . '/bulkAction'     => 'bulkAction',
		];
		$this->registerEndpoints( $routes, Scan::getClassName() );
	}

	public function bulkAction() {
		if ( ! $this->checkPermission() ) {
			return;
		}
		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'bulkAction' ) ) {
			return;
		}

		$items = HTTP_Helper::retrievePost( 'items' );
		if ( ! is_array( $items ) ) {
			$items = array();
		}
		$bulk = HTTP_Helper::retrievePost( 'bulk' );
		switch ( $bulk ) {
			case 'ignore':
				foreach ( $items as $id ) {
					$item = Result_Item::findByID( $id );
					if ( is_object( $item ) ) {
						$item->ignore();
					}
				}
				$this->submitStatsToDev();
				wp_send_json_success( array_merge( Scan\Component\Data_Factory::buildData(), array(
					'message' => _n( "The suspicious file has been successfully ignored.",
						"The suspicious files have been successfully ignored.",
						count( $items ),
						wp_defender()->domain )
				) ) );
				break;
			case 'unignore':
				foreach ( $items as $id ) {
					$item = Result_Item::findByID( $id );
					if ( is_object( $item ) ) {
						$item->unignore();
					}
				}
				$this->submitStatsToDev();
				wp_send_json_success( array_merge( Scan\Component\Data_Factory::buildData(), array(
					'message' => _n( "The suspicious file has been successfully restored.",
						"The suspicious files have been successfully restored.",
						count( $items ),
						wp_defender()->domain )
				) ) );
				break;
			case 'delete':
				foreach ( $items as $id ) {
					$item = Result_Item::findByID( $id );
					if ( is_object( $item ) ) {
						$ret = $item->purge();
						if ( is_wp_error( $ret ) ) {
							break;
							wp_send_json_error( array(
								'message' => $ret->get_error_message()
							) );
						}
					}
				}
				$this->submitStatsToDev();
				wp_send_json_success( array_merge( Scan\Component\Data_Factory::buildData(), array(
					'message' => _n( "The suspicious file has been successfully deleted.",
						"The suspicious files have been successfully deleted.",
						count( $items ),
						wp_defender()->domain )
				) ) );
				break;
			default:
				//param not from the button on frontend, log it
				error_log( sprintf( 'Unexpected value %s from IP %s', $bulk, Utils::instance()->getUserIp() ) );
				break;
		}
	}

	public function solveIssue() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'solveIssue' ) ) {
			return;
		}
		$id = HTTP_Helper::retrievePost( 'id', false );

		$model = Result_Item::findByID( $id );
		if ( is_object( $model ) ) {
			$ret = $model->resolve();
			if ( is_wp_error( $ret ) ) {
				wp_send_json_error( array(
					'message' => $ret->get_error_message()
				) );
			} else {
				if ( $ret === true ) {
					$this->submitStatsToDev();
					wp_send_json_success( array_merge( Scan\Component\Data_Factory::buildData(),
						[ 'message' => __( "This item has been resolved.", wp_defender()->domain ), ] ) );
				} elseif ( $ret === false ) {
					wp_send_json_error( array(
						'message' => __( "Please try again!", wp_defender()->domain )
					) );
				} elseif ( is_string( $ret ) ) {
					$this->submitStatsToDev();
					wp_send_json_success( array(
						'url' => $ret
					) );
				}
			}
		} else {
			wp_send_json_error( array(
				'message' => __( "The item doesn't exist!", wp_defender()->domain )
			) );
		}
	}

	/**
	 * Delete an issue
	 */
	public function deleteIssue() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'deleteIssue' ) ) {
			return;
		}

		$id    = HTTP_Helper::retrievePost( 'id', false );
		$model = Result_Item::findByID( $id );
		if ( is_object( $model ) ) {
			$ret = $model->purge();
			$this->submitStatsToDev();
			if ( is_wp_error( $ret ) ) {
				wp_send_json_error( array(
					'message' => $ret->get_error_message()
				) );

			} else {
				wp_send_json_success( array_merge( Scan\Component\Data_Factory::buildData(),
					[ 'message' => __( "This item has been permanently removed", wp_defender()->domain ), ] ) );
			}
		} else {
			wp_send_json_error( array(
				'message' => __( "The item doesn't exist!", wp_defender()->domain )
			) );
		}
	}

	/**
	 * Get source code of an issue
	 */
	public function getFileSrcCode() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'getFileSrcCode' ) ) {
			return;
		}
		$id    = HTTP_Helper::retrievePost( 'id' );
		$model = Result_Item::findByID( $id );
		if ( ! is_object( $model ) ) {
			wp_send_json_error();
		}

		wp_send_json_success( array(
			'code' => $model->getSrcCode()
		) );

	}

	/**
	 * Ignore an issue
	 */
	public function ignoreIssue() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'ignoreIssue' ) ) {
			return;
		}

		$id    = HTTP_Helper::retrievePost( 'id', false );
		$model = Result_Item::findByID( $id );
		if ( is_object( $model ) ) {
			$model->ignore();
			$this->submitStatsToDev();
			wp_send_json_success( array_merge( Scan\Component\Data_Factory::buildData(),
				[ 'message' => __( "The suspicious file has been successfully ignored.", wp_defender()->domain ), ] ) );
		} else {
			wp_send_json_error( array(
				'message' => __( "The item doesn't exist!", wp_defender()->domain )
			) );
		}
	}

	/**
	 * Unignore an issue
	 */
	public function unignoreIssue() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'unignoreIssue' ) ) {
			return;
		}

		$id    = HTTP_Helper::retrievePost( 'id', false );
		$model = Result_Item::findByID( $id );
		if ( is_object( $model ) ) {
			$model->unignore();
			$this->submitStatsToDev();
			wp_send_json_success( array_merge( Scan\Component\Data_Factory::buildData(),
				[
					'message' => __( "The suspicious file has been successfully restored.", wp_defender()->domain ),
				] ) );
		} else {
			wp_send_json_error( array(
				'message' => __( "The item doesn't exist!", wp_defender()->domain )
			) );
		}
	}

	/**
	 * Create a new scan
	 */
	public function newScan() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'newScan' ) ) {
			return;
		}
		$ret = Scan\Component\Scan_Api::createScan();
		if ( ! is_wp_error( $ret ) ) {
			wp_send_json_success( [
				'status'      => $ret->status,
				'status_text' => $ret->statusText,
				'percent'     => 0
			] );
		}

		wp_send_json_error( array(
			'message' => $ret->get_error_message(),
		) );
	}

	/**
	 * Request to cancel current scan
	 */
	public function cancelScan() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'cancelScan' ) ) {
			return;
		}

		$activeScan = Scan\Component\Scan_Api::getActiveScan();
		if ( is_object( $activeScan ) ) {
			$activeScan->delete();
			( new Scan\Component\Scanning() )->flushCache();
		}
		$data = Scan\Component\Data_Factory::buildData();

		wp_send_json_success( $data );
	}

	/**
	 * Processing the scan
	 */
	public function processScan() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'processScan' ) ) {
			return;
		}

		/**
		 * When we processing the scan by ajax, clear all the which does the same job
		 */
		wp_clear_scheduled_hook( 'processScanCron' );

		//$ret = Scan\Component\Scan_Api::processActiveScan();
		$scanning = new Scan\Component\Scanning();
		$ret      = $scanning->run();
		if ( $ret == true ) {
			do_action( 'sendScanEmail' );

			$this->submitStatsToDev();
			$data = Scan\Component\Data_Factory::buildData();

			wp_send_json_success( $data );
		} else {
			$model = Scan\Component\Scan_Api::getActiveScan();
			$data  = array(
				'status'      => $model->status,
				'percent'     => round( $scanning->getScanProgress(), 2 ),
				'status_text' => is_object( $model ) ? $model->statusText : null,
			);
			//not completed
			//we will schedule a cron here in case user close tthe page, the scan still continue
			wp_schedule_single_event( strtotime( '+1 minutes' ), 'processScanCron' );
			wp_send_json_error( $data );
		}
	}

	/**
	 * Update settings
	 */
	public function updateSettings() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'updateSettings' ) ) {
			return;
		}

		$data     = stripslashes( $_POST['data'] );
		$data     = json_decode( $data, true );
		$settings = Scan\Model\Settings::instance();
		foreach ( $data as $key => $val ) {
			if ( in_array( $key, array( 'email_all_ok', 'email_has_issue' ) ) ) {
				$data[ $key ] = wp_kses_post( $val );
			} elseif ( is_string( $val ) ) {
				$data[ $key ] = sanitize_text_field( $val );
			}
		}

		$settings->import( $data );
		$settings->email_all_ok    = stripslashes( $settings->email_all_ok );
		$settings->email_has_issue = stripslashes( $settings->email_has_issue );
		$settings->save();
		if ( $this->hasMethod( 'scheduleReportTime' ) ) {
			if ( $settings->last_report_sent == null ) {
				$settings->last_report_sent = current_time( 'timestamp' );
				$settings->save();
			}
			$this->scheduleReportTime( $settings );
			$this->submitStatsToDev();
		}
		wp_send_json_success( array(
			'message' => __( "Your settings have been updated.", wp_defender()->domain )
		) );
	}

	/**
	 * Declaring behaviors
	 * @return array
	 */
	/**
	 * @return array
	 */
	public function behaviors() {
		$behaviors = [
			'utils'     => '\WP_Defender\Behavior\Utils',
			'endpoints' => '\WP_Defender\Behavior\Endpoint',
			'wpmudev'   => '\WP_Defender\Behavior\WPMUDEV'
		];

		if ( wp_defender()->isFree == false ) {
			$behaviors['pro'] = Scan\Behavior\Pro\Reporting::class;
		}

		return $behaviors;
	}
}
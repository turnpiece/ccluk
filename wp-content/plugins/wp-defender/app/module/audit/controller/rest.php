<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Audit\Controller;


use Hammer\Helper\HTTP_Helper;
use Hammer\Helper\WP_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Controller;
use WP_Defender\Module\Audit;
use WP_Defender\Module\Audit\Component\Audit_API;
use WP_Defender\Module\Audit\Model\Events;

class Rest extends Controller {
	public function __construct() {
		$namespace = 'wp-defender/v1';
		$namespace .= '/audit';
		$routes    = [
			$namespace . '/loadData'       => 'loadData',
			$namespace . '/updateSettings' => 'updateSettings',
			$namespace . '/summary'        => 'summary',
			$namespace . '/exportAsCvs'    => 'exportAsCvs'
		];
		$this->registerEndpoints( $routes, Audit::getClassName() );
	}

	/**
	 * Csv exporter
	 */
	public function exportAsCvs() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'exportAsCvs' ) ) {
			return;
		}

		$params = $this->prepareAuditParams();
		if ( Audit\Model\Events::instance()->hasData() ) {
			Utils::instance()->log( 'Pull log from local' );
			$data = Audit\Model\Events::instance()->getData( $params );
		} else {
			Utils::instance()->log( 'Pull log from API' );
			$data = Audit_API::pullLogs( $params, 'timestamp', 'desc', true );
		}

		$logs    = $data['data'];
		$fp      = fopen( 'php://memory', 'w' );
		$headers = array(
			__( "Summary", wp_defender()->domain ),
			__( "Date / Time", wp_defender()->domain ),
			__( "Context", wp_defender()->domain ),
			__( "Type", wp_defender()->domain ),
			__( "IP address", wp_defender()->domain ),
			__( "User", wp_defender()->domain )
		);
		fputcsv( $fp, $headers );
		foreach ( $logs as $fields ) {
			$vars = array(
				$fields['msg'],
				is_array( $fields['timestamp'] )
					? $this->formatDateTime( date( 'Y-m-d H:i:s', $fields['timestamp'][0] ) )
					: $this->formatDateTime( date( 'Y-m-d H:i:s', $fields['timestamp'] ) ),
				ucwords( Audit_API::get_action_text( $fields['context'] ) ),
				ucwords( Audit_API::get_action_text( $fields['action_type'] ) ),
				$fields['ip'],
				$this->getDisplayName( $fields['user_id'] )
			);
			fputcsv( $fp, $vars );
		}
		$filename = 'wdf-audit-logs-export-' . date( 'ymdHis' ) . '.csv';
		fseek( $fp, 0 );
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '";' );
		// make php send the generated csv lines to the browser
		fpassthru( $fp );
		exit();
	}

	/**
	 * Get summary data, for dashboard widget
	 */
	public function summary() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'summary' ) ) {
			return;
		}

		wp_send_json_success( Audit_API::summary() );
	}

	/**
	 * Save the settings
	 * If report info changed, we will re-queue the cronjob for reporting
	 */
	public function updateSettings() {
		if ( ! $this->checkPermission() ) {
			wp_send_json_error( [
				'message' => 'You are not allow here'
			] );

			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'updateSettings' ) ) {
			wp_send_json_error( [
				'message' => 'You are not allow here'
			] );

			return;
		}
		$data       = stripslashes( $_POST['data'] );
		$data       = json_decode( $data, true );
		$settings   = Audit\Model\Settings::instance();
		$last_state = $settings->enabled;
		$settings->import( $data );
		$settings->save();
		if ( $last_state == false && Audit\Model\Events::instance()->hasData() == false ) {
			//this mean the previous state is disable, now is enable and no data fetched from api
			Events::instance()->fetch();
		}
		$cronTime = $this->reportCronTimestamp( $settings->time, 'auditReportCron' );
		if ( $settings->notification == true ) {
			wp_schedule_event( $cronTime, 'daily', 'auditReportCron' );
		}
		$res = array(
			'message' => __( "Your settings have been updated.", wp_defender()->domain ),
			'summary' => [
				'report_time' => $settings->get_report_times_as_string()
			]
		);
		$this->submitStatsToDev();
		wp_send_json_success( $res );
	}

	/**
	 * Load all the necessary data
	 */
	public function loadData() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( '_wpnonce' ), 'loadData' ) ) {
			return;
		}
		$params = $this->prepareAuditParams();

		if ( Audit\Model\Events::instance()->hasData( $params ) ) {
			$logs = Audit\Model\Events::instance()->getData( $params, 'timestamp', 'desc', true );
		} else {
			//fallback to directly API if the local cache has issues
			Utils::instance()->log( 'Pull audit logs from API' );
			//we will push and refresh here so we always get latest source
			$logs = Audit_API::pullLogs( $params, 'timestamp', 'desc', true );
		}
		Utils::instance()->log( sprintf( 'audit min date %s', $params['date_from'] ), 'audit' );
		if ( is_wp_error( $logs ) ) {
			wp_send_json_error( [
				'message' => $logs->get_error_message()
			] );
		}
		$time         = microtime( true );
		$logs['data'] = array_map( function ( $item ) {
			$item['user'] = Utils::instance()->getDisplayName( $item['user_id'] );
			//$item['user']=1;
			unset( $item['user_id'] );
			$item['msg'] = htmlspecialchars_decode( $item['msg'] );

			return $item;
		}, $logs['data'] );
		$time         = microtime( true ) - $time;
		$cache        = WP_Helper::getArrayCache();
		if ( ! is_wp_error( $logs ) ) {
			$data = [
				'logs'        => $logs['data'],
				'total_items' => $logs['total_items'],
				'total_pages' => ceil( $logs['total_items'] / $logs['per_page'] ),
				'per_page'    => $logs['per_page'],
				'debug'       => [
					'hit' => $cache->get( 'hit' ),
					'est' => $time
				]
			];
			wp_send_json_success( $data );
		} else {
			$data = [
				'logs'        => [],
				'total_items' => 0,
				'total_pages' => 0,
				'per_page'    => 0
			];
			wp_send_json_error( $data );
		}
	}

	/**
	 * Prepare parameters from _REQUEST before
	 * @return array
	 */
	private function prepareAuditParams() {
		$date_format = 'm/d/Y';
		$attributes  = array(
			'date_from'   => date( $date_format, strtotime( '-7 days', current_time( 'timestamp' ) ) ),
			'date_to'     => date( $date_format, current_time( 'timestamp' ) ),
			'user_id'     => '',
			'event_type'  => '',
			'ip'          => '',
			'context'     => '',
			'action_type' => '',
			'blog_id'     => 1,
			'date_range'  => HTTP_Helper::retrieveGet( 'date_range', null ),
			'paged'       => HTTP_Helper::retrieveGet( 'paged', 1 )
		);
		$params      = array();
		$_GET        = array_filter( $_GET );
		foreach ( $attributes as $att => $value ) {
			$params[ $att ] = HTTP_Helper::retrieveGet( $att, $value );
			if ( $att == 'date_from' || $att == 'date_to' ) {
				$df_object = new \DateTime( $params[ $att ] );
				if ( $att == 'date_from' ) {
					$params[ $att ] = $df_object->setTime( 0, 0 )->format( 'Y-m-d H:i:s' );
				} elseif ( $att == 'date_to' ) {
					$params[ $att ] = $df_object->setTime( 23, 59, 59 )->format( 'Y-m-d H:i:s' );
				}
			} elseif ( $att == 'user_id' ) {
				$term = HTTP_Helper::retrieveGet( 'term' );
				if ( filter_var( $term, FILTER_VALIDATE_INT ) ) {
					$params['user_id'] = $term;
				} elseif ( strlen( $term ) > 0 ) {
					$u = get_user_by( 'user_login', $term );
					if ( is_object( $u ) ) {
						$params['user_id'] = $u->ID;
					} else {
						$params['user_id'] = 0;
					}
				}
			} elseif ( $att == 'date_range' && in_array( $value, array( 1, 7, 30 ) ) ) {
				$params['date_from'] = date( 'Y-m-d',
					strtotime( '-' . $value . ' days', current_time( 'timestamp' ) ) );
			}
		}
		if ( HTTP_Helper::retrieveGet( 'all_type' ) == 1 ) {
			$params['event_type'] = Audit_API::getEventType();
		}

		return $params;
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
			$behaviors['pro'] = '\WP_Defender\Module\Scan\Behavior\Pro\Reporting';
		}

		return $behaviors;
	}
}
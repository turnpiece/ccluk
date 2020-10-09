<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Scan\Component;


use WP_Defender\Behavior\Utils;
use WP_Defender\Module\Scan;
use WP_Defender\Module\Scan\Model\Result_Item;
use WP_Defender\Module\Scan\Model\Settings;

class Data_Factory {
	/**
	 * Build the data we use to output when page load
	 * @return array
	 */
	public static function buildData() {
		$lastScan = Scan_Api::getLastScan();
		$model    = Scan_Api::getActiveScan();
		if ( $lastScan === null && $model === null ) {
			/**
			 * this case no scan
			 */
			$scan = null;
		} elseif ( is_object( $model ) ) {
			/**
			 * This case there is a scan on progress, show status, status text and percent
			 */
			$scanning = new Scan\Component\Scanning();
			$scan = [
				'status'      => $model->status,
				'status_text' => $model->statusText,
				'percent'     => round( $scanning->getScanProgress(), 2 )
			];
		} else {
			$issuesItems = $lastScan->getItemsAsJson( 0, Result_Item::STATUS_ISSUE, null );
			$scan        = [
				'status'        => $lastScan->status,
				'issues_items'  => $issuesItems,
				'ignored_items' => $lastScan->getItemsAsJson( 0, Result_Item::STATUS_IGNORED, null ),
				'last_scan'     => Utils::instance()->formatDateTime( $lastScan->dateFinished ),
				'count'         => [
					'total'   => count( $issuesItems ),
					'core'    => (int) $lastScan->getCount( 'core' ),
					'content' => (int) $lastScan->getCount( 'content' ),
					'vuln'    => (int) $lastScan->getCount( 'vuln' )
				]
			];
		}
		$settings = Settings::instance();

		return [
			'scan'  => $scan,
			'model' => [
				'settings'     => $settings->exportByKeys( [
					'scan_core',
					'scan_vuln',
					'scan_content',
					'max_filesize'
				] ),
				'reporting'    => $settings->exportByKeys( [
					'report',
					'always_send',
					'recipients',
					'day',
					'time',
					'frequency'
				] ),
				'notification' => $settings->exportByKeys( [
					'notification',
					'always_send_notification',
					'recipients_notification',
					'email_subject',
					'email_subject_issue',
					'email_all_ok',
					'email_has_issue'
				] )
			],
			'misc'  => [
				"times_of_day" => Utils::instance()->getTimes(),
				"days_of_week" => Utils::instance()->getDaysOfWeek()
			],
		];
	}

	/**
	 * Build data for dashboard page
	 * @return array
	 */
	public static function buildLiteData() {
		$data = self::buildData();
		unset( $data['model'] );
		unset( $data['misc'] );

		return $data;
	}
}
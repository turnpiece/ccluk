<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Behavior;

use Hammer\Base\Behavior;
use WP_Defender\Module\Advanced_Tools\Model\Security_Headers_Settings;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\IP_Lockout\Model\Log_Model;
use WP_Defender\Module\Scan\Component\Scan_Api;
use WP_Defender\Module\Scan\Component\Scanning;
use WP_Defender\Module\Scan\Model\Result_Item;
use WP_Defender\Module\Scan\Model\Scan;

/**
 * This class contains everything relate to WPMUDEV
 * Class WPMUDEV
 * @package WP_Defender\Behavior
 * @since 2.2
 */
class WPMUDEV extends Behavior {
	/**
	 * @param $campaign
	 *
	 * @return string
	 */
	public function campaignURL( $campaign ) {
		$url = "https://premium.wpmudev.org/project/wp-defender/?utm_source=defender&utm_medium=plugin&utm_campaign=" . $campaign;

		return $url;
	}

	/**
	 * Get whitelabel status from Dev Dashboard
	 * Properties
	 *  - hide_branding
	 *  - hero_image
	 *  - footer_text
	 *  - change_footer
	 *  - hide_doc_link
	 *
	 * @return mixed
	 */
	public function whiteLabelStatus() {
		if ( \WP_Defender\Behavior\Utils::instance()->getAPIKey() ) {
			$site = \WPMUDEV_Dashboard::$site;
			if ( is_object( $site ) ) {
				$info            = $site->get_wpmudev_branding( array() );
				$info['enabled'] = $this->is_whitelabel_enabled();

				return $info;
			}
		} else {
			return [
				'enabled'       => false,
				'hide_branding' => false,
				'hero_image'    => '',
				'footer_text'   => '',
				'change_footer' => false,
				'hide_doc_link' => false
			];
		}
	}

	public function is_whitelabel_enabled() {
		if ( \WP_Defender\Behavior\Utils::instance()->getAPIKey() ) {
			$site     = \WPMUDEV_Dashboard::$site;
			$settings = $site->get_whitelabel_settings();

			return $settings['enabled'];
		}

		return false;
	}

	public function is_dev_dashboard_installed() {
		var_dump( get_plugins() );
		die;
	}

	/**
	 * a quick helper for static class
	 * @return WPMUDEV
	 */
	public static function instance() {
		return new WPMUDEV();
	}

	/**
	 * Return the highcontrast css class if it is
	 * @return string
	 */
	public function maybeHighContrast() {
		return \WP_Defender\Module\Setting\Model\Settings::instance()->high_contrast_mode;
	}

	/**
	 * @return array
	 */
	public function stats_summary() {
		$count = 0;
		$scan  = Scan_Api::getLastScan();
		if ( is_object( $scan ) ) {
			$count += $scan->countAll( Result_Item::STATUS_ISSUE );
		}
		$count += count( Settings::instance()->getIssues() );

		$scan_setting = \WP_Defender\Module\Scan\Model\Settings::instance();

		$next_scan = $scan_setting->report === true ?
			Utils::instance()->getNextRun( $scan_setting->frequency, $scan_setting->day, $scan_setting->time,
				$scan_setting->last_report_sent ) : 'N/A';

		return [
			'count'     => $count,
			'next_scan' => $next_scan
		];
	}

	public function stats_report() {
		$scan     = \WP_Defender\Module\Scan\Model\Settings::instance();
		$audit    = \WP_Defender\Module\Audit\Model\Settings::instance();
		$firewall = \WP_Defender\Module\IP_Lockout\Model\Settings::instance();

		return [
			'scan'     => $scan->report === true ? sprintf( '%s, %s', $scan->day, $scan->time ) : false,
			'audit'    => $audit->notification === true ? sprintf( '%s, %s', $audit->day, $audit->time ) : false,
			'firewall' => $firewall->report === true ? sprintf( '%s, %s', $firewall->report_day,
				$firewall->report_time ) : false
		];
	}

	public function stats_security_tweaks() {
		$settings = Settings::instance();

		return [
			'issues'       => count( $settings->issues ),
			'fixed'        => count( $settings->fixed ),
			'notification' => $settings->notification
		];
	}

	public function stats_malware_scan() {
		$scan  = Scan_Api::getLastScan();
		$count = 0;
		if ( is_object( $scan ) ) {
			$count = $scan->countAll( Result_Item::STATUS_ISSUE );
		}

		return [
			'count'        => $count,
			'notification' => \WP_Defender\Module\Scan\Model\Settings::instance()->notification
		];
	}

	public function stats_security_headers() {
		$settings = Security_Headers_Settings::instance();
		$headers  = [];
		foreach ( $settings->getHeaders() as $header ) {
			$headers[ $header::$rule_slug ] = $header->check();
		}

		return $headers;
	}
}
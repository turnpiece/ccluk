<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Component;

use Hammer\Base\Container;
use WP_Defender\Behavior\Utils;
use WP_Defender\Module\Advanced_Tools\Component\Mask_Api;
use WP_Defender\Module\Advanced_Tools\Model\Mask_Settings;
use WP_Defender\Module\Advanced_Tools\Model\Security_Headers_Settings;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\IP_Lockout\Model\Log_Model;
use WP_Defender\Module\Two_Factor\Model\Auth_Settings;

class Data_Factory {
	public static function buildData() {
		if ( ! Utils::instance()->checkPermission() ) {
			return [];
		}

		return [
			'security_tweaks' => self::buildTweaksData(),
			'scan'            => self::buildScanData(),
			'blacklist'       => self::buildBlacklistData(),
			'ip_lockout'      => self::buildIpLockoutData(),
			'audit'           => self::buildAuditData(),
			'report'          => self::buildReportData(),
			'advanced_tools'  => self::buildAToolsData(),
			'two_fa'          => self::buildTwoFaData(),
			'waf'             => self::buildWafData(),
			'settings'        => self::buildSettingsData()
		];
	}

	public static function buildSettingsData() {
		$module     = Container::instance()->get( 'setting' );
		$controller = $module->getController( 'main' );

		return $controller->scriptsData();
	}

	public static function buildWafData() {
		return Container::instance()->get( 'waf' )->_scriptsData();
	}

	public static function buildTwoFaData() {
		$settings = Auth_Settings::instance();

		return [
			'enabled'   => $settings->enabled,
			'useable'   => $settings->enabled && count( $settings->user_roles ),
			'nonces'    => [
				'updateSettings' => wp_create_nonce( 'updateSettings' ),
			],
			'endpoints' => [
				'updateSettings' => 'wp-defender/v1/twoFa/updateSettings',
			]
		];
	}

	/**
	 * @return array
	 */
	public static function buildAToolsData() {
		$headers = [];
		if ( isset( wp_defender()->global['security_headers_enabled'] ) ) {
			$headers = wp_defender()->global['security_headers_enabled'];
		}
		$data = [];
		foreach ( $headers as $header ) {
			$data[] = [
				'slug'  => $header::$rule_slug,
				'title' => $header->getTitle()
			];
		}

		return [
			'security_headers' => $data,
			'mask_login'       => [
				'enabled'   => Mask_Settings::instance()->enabled,
				'useable'   => strlen( Mask_Settings::instance()->mask_url ) > 0,
				'login_url' => Mask_Api::getNewLoginUrl()
			],
			'nonces'           => [
				'updateSettings' => wp_create_nonce( 'updateSettings' )
			],
			'endpoints'        => [
				'updateSettings' => 'wp-defender/v1/advanced-tools/updateSettings'
			]
		];
	}

	/**
	 * @return array
	 */
	public static function buildReportData() {
		if ( ! class_exists( '\WP_Defender\Module\Audit\Model\Settings' ) ) {
			return [
				'scan'       => \WP_Defender\Module\Scan\Model\Settings::instance()->report ? \WP_Defender\Module\Scan\Model\Settings::instance()->frequency : - 1,
				'ip_lockout' => \WP_Defender\Module\IP_Lockout\Model\Settings::instance()->report ? \WP_Defender\Module\IP_Lockout\Model\Settings::instance()->report_frequency : - 1,
				'audit'      => - 1
			];
		} else {
			return [
				'scan'       => \WP_Defender\Module\Scan\Model\Settings::instance()->report ? \WP_Defender\Module\Scan\Model\Settings::instance()->frequency : - 1,
				'ip_lockout' => \WP_Defender\Module\IP_Lockout\Model\Settings::instance()->report ? \WP_Defender\Module\IP_Lockout\Model\Settings::instance()->report_frequency : - 1,
				'audit'      => \WP_Defender\Module\Audit\Model\Settings::instance()->notification ? \WP_Defender\Module\Audit\Model\Settings::instance()->frequency : - 1
			];
		}
	}

	/**
	 * @return array
	 */
	public static function buildAuditData() {
		if ( ! class_exists( '\WP_Defender\Module\Audit\Model\Settings' ) ) {
			//free version
			return [];
		}
		$setting = \WP_Defender\Module\Audit\Model\Settings::instance();

		return [
			'enabled'   => $setting->enabled,
			'report'    => $setting->notification,
			'nonces'    => [
				'summary'        => wp_create_nonce( 'summary' ),
				'updateSettings' => wp_create_nonce( 'updateSettings' )
			],
			'endpoints' => [
				'summary'        => 'wp-defender/v1/audit/summary',
				'updateSettings' => 'wp-defender/v1/audit/updateSettings'
			]
		];
	}

	/**
	 * @return array
	 */
	private static function buildIpLockoutData() {
		$summaryData = Log_Model::getSummary();
		$settings    = \WP_Defender\Module\IP_Lockout\Model\Settings::instance();

		return [
			'nonces'       => [
				'updateSettings' => wp_create_nonce( 'updateSettings' ),
			],
			'endpoints'    => [
				'updateSettings' => 'wp-defender/v1/lockout/updateSettings'
			],
			'summary'      => [
				'ip'          => [
					'week' => $summaryData['loginLockoutThisWeek'],
				],
				'nf'          => [
					'week' => $summaryData['lockout404ThisWeek'],
				],
				'lastLockout' => $summaryData['lastLockout']
			],
			'notification' => $settings->login_lockout_notification && $settings->ip_lockout_notification,
			'enabled'      => $settings->login_protection || $settings->detect_404
		];
	}

	/**
	 * @return array
	 */
	private static function buildBlacklistData() {
		return [
			'nonces'    => [
				'toggleBlacklistWidget' => wp_create_nonce( 'toggleBlacklistWidget' ),
				'blacklistWidgetStatus' => wp_create_nonce( 'blacklistWidgetStatus' )
			],
			'endpoints' => [
				'toggleBlacklistWidget' => 'wp-defender/v1/toggleBlacklistWidget',
				'blacklistWidgetStatus' => 'wp-defender/v1/blacklistWidgetStatus'
			]
		];
	}

	/**
	 * @return array
	 */
	private static function buildTweaksData() {
		$rules    = Settings::instance()->getTweaksAsArray( 'issues' );
		$resolved = Settings::instance()->getTweaksAsArray( 'fixed' );
		$ignored  = Settings::instance()->getTweaksAsArray( 'ignore' );
		$total    = count( Settings::instance()->getDefinedRules() );

		return [
			'rules' => array_slice( $rules, 0, 5 ),
			'count' => [
				'issues'   => count( $rules ),
				'resolved' => count( $resolved ) + count( $ignored ),
				'total'    => $total
			],
		];
	}

	/**
	 * @return array
	 */
	private static function buildScanData() {
		$settings = \WP_Defender\Module\Scan\Model\Settings::instance();

		return array_merge( \WP_Defender\Module\Scan\Component\Data_Factory::buildLiteData(), [
			'nonces'    => [
				'newScan'     => wp_create_nonce( 'newScan' ),
				'processScan' => wp_create_nonce( 'processScan' ),
				'cancelScan'  => wp_create_nonce( 'cancelScan' )
			],
			'endpoints' => [
				'newScan'     => 'wp-defender/v1/scan/newScan',
				'processScan' => 'wp-defender/v1/scan/processScan',
				'cancelScan'  => 'wp-defender/v1/scan/cancelScan',
			],
			'report'    => [
				'enabled'   => $settings->report,
				'frequency' => $settings->frequency
			]
		] );
	}
}
<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Behavior;

use Hammer\Base\Behavior;
use Hammer\Helper\HTTP_Helper;
use Hammer\Helper\WP_Helper;
use WP_Defender\Module\Scan\Component\Scan_Api;
use WP_Defender\Module\Scan\Model\Settings;

class Activator_Free extends Behavior {
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
				if ( $status != true ) {
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
						error_log( sprintf( 'Unexpected value %s from IP %s', $item, Utils::instance()->getUserIp() ) );
						break;
				}
			}
		}

		update_site_option( 'wp_defender_free_is_activated', 1 );

		wp_send_json_success( array(
			'activated' => $activated,
			//'message'   => __( "" )
		) );
	}

	/**
	 * Check if we should show activator screen
	 * @return bool
	 */
	public function isShowActivator() {
		$cache = WP_Helper::getCache();
		if ( $cache->get( 'wdf_isActivated', false ) == 1 ) {
			return 0;
		}
		if ( get_site_transient( 'wp_defender_free_is_activated' ) == 1 ) {
			return 0;
		}

		if ( get_site_option( 'wp_defender_free_is_activated' ) == 1 ) {
			return 0;
		}

		$keys = [
			'wp_defender',
			'wd_scan_settings',
			'wd_hardener_settings',
			'wd_audit_settings',
			'wd_2auth_settings',
			'wd_masking_login_settings'
		];
		foreach ( $keys as $key ) {
			$option = get_site_option( $key );
			if ( is_array( $option ) ) {
				return 0;
			}
		}

		return 1;
	}
}
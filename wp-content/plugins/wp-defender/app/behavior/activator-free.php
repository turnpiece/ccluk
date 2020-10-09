<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Behavior;

use Hammer\Base\Behavior;
use Hammer\Helper\WP_Helper;

class Activator_Free extends Behavior {
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

		$keys = array(
			'wp_defender',
			'wd_scan_settings',
			'wd_hardener_settings',
			'wd_audit_settings',
			'wd_2auth_settings',
			'wd_masking_login_settings',
		);
		foreach ( $keys as $key ) {
			$option = get_site_option( $key );
			if ( is_array( $option ) ) {
				return 0;
			}
		}

		return 1;
	}
}
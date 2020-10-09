<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Behavior;

use Hammer\Base\Behavior;
use Hammer\Helper\WP_Helper;

class Activator extends Behavior {
	/**
	 * Check if we should show activator screen
	 * @return bool
	 */
	public function isShowActivator() {
		$cache = WP_Helper::getCache();

		if ( $cache->get( 'isActivated', false ) == 1 ) {
			return 0;
		}

		if ( get_site_option( 'wp_defender_is_free_activated' ) == 1 ) {
			return 1;
		}

		if ( get_site_option( 'wp_defender_is_activated' ) == 1 ) {
			return 0;
		}

		if ( get_site_transient( 'wp_defender_is_activated' ) == 1 ) {
			return 0;
		}

		if ( $cache->get( 'wdf_isActivated', false ) == 1 ) {
			//this mean user just upgraded from the free
			return 1;
		}

		if ( get_site_transient( 'wp_defender_is_free_activated' ) == 1 ) {
			return 1;
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
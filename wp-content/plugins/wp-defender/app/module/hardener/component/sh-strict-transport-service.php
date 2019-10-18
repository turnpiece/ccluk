<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use Hammer\Helper\HTTP_Helper;
use WP_Defender\Component\Error_Code;
use WP_Defender\Module\Hardener\IRule_Service;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Hardener\Rule_Service;

class Sh_Strict_Transport_Service extends Rule_Service implements IRule_Service {
	const KEY = 'sh_strict_transport';

	/**
	 * @return bool
	 */
	public function check() {
		$response = wp_remote_head( network_site_url() );
		if ( is_wp_error( $response ) ) {
			return false;
		}
		$headers = $response['headers'];
		if ( isset( $headers['strict-transport-security'] ) ) {
			$settings = Settings::instance();
			$data     = $settings->getDValues( self::KEY );
			if ( $data === null ) {
				$data = [ 'somewhere' => true ];
				//someone applied first, we need to get the current settings
				$content = explode( ';', $headers['strict-transport-security'] );
				foreach ( $content as $line ) {
					if ( stristr( $line, 'max-age' ) ) {
						$value   = explode( '=', $line );
						$arr     = [
							'1 hour'    => 1 * 3600,
							'24 hours'  => 86400,
							'7 days'    => 7 * 86400,
							'3 months'  => ( 3 * 30 + 1 ) * 86400,
							'6 months'  => ( 6 * 30 + 3 ) * 86400,
							'12 months' => 365 * 86400
						];
						$seconds = $value[1];
						$closest = null;
						$key     = null;
						//get the closest
						foreach ( $arr as $k => $item ) {
							if ( $closest === null || abs( $seconds - $closest ) > abs( $item - $seconds ) ) {
								$closest = $item;
								$key     = $k;
							}
						}
						$data['hsts_cache_duration'] = $key;
					} elseif ( stristr( $line, 'preload' ) ) {
						$data['hsts_preload'] = 1;
					} elseif ( stristr( $line, 'includeSubDomains' ) ) {
						$data['include_subdomain'] = 1;
					}
				}
				$settings->setDValues( self::KEY, $data );
			}

			return true;
		}

		return false;
	}

	/**
	 * @return bool|\WP_Error
	 */
	public function process() {
		$hsts                = HTTP_Helper::retrievePost( 'hsts_preload' );
		$include_subdomain   = HTTP_Helper::retrievePost( 'include_subdomain' );
		$hsts_cache_duration = HTTP_Helper::retrievePost( 'hsts_cache_duration' );
		$scenario            = HTTP_Helper::retrievePost( 'scenario' );
		$settings            = Settings::instance();
		$data                = $settings->getDValues( self::KEY );
		if ( ! is_array( $data ) ) {
			$data = [];
		}
		$data['hsts_preload']        = $hsts;
		$data['include_subdomain']   = $include_subdomain;
		$data['hsts_cache_duration'] = $hsts_cache_duration;
		if ( $scenario == 'enforce' ) {
			unset( $data['somewhere'] );
		}
		$settings->setDValues( self::KEY, $data );
	}

	public function revert() {
		$settings = Settings::instance();
		$settings->setDValues( self::KEY, null );
	}

	public function listen() {

	}
}
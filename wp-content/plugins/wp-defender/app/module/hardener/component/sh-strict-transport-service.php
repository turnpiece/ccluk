<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use Hammer\Helper\HTTP_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Component\Error_Code;
use WP_Defender\Module\Hardener\IRule_Service;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Hardener\Rule_Service;

class Sh_Strict_Transport_Service extends Rule_Service implements IRule_Service {
	const KEY = 'sh_strict_transport';
	public $hsts;
	public $include_subdomain;
	public $hsts_cache_duration;
	public $scenario;

	/**
	 * @return bool
	 */
	public function check() {
		$settings = Settings::instance();

		$headers = $this->headRequest( network_site_url(), self::KEY );
		if ( is_wp_error( $headers ) ) {
			Utils::instance()->log( sprintf( 'Self ping error: %s', $headers->get_error_message() ),'tweaks' );

			return false;
		}
		if ( isset( $headers['strict-transport-security'] ) ) {
			$data = $settings->getDValues( self::KEY );
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

		$data = $settings->getDValues( self::KEY );
		if ( is_array( $data ) && isset( $data['hsts_cache_duration'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @return bool|\WP_Error
	 */
	public function process() {
		$hsts                = $this->hsts;
		$include_subdomain   = $this->include_subdomain;
		$hsts_cache_duration = $this->hsts_cache_duration;
		$scenario            = $this->scenario;
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
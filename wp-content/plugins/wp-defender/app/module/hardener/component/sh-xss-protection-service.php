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

class Sh_XSS_Protection_Service extends Rule_Service implements IRule_Service {
	const KEY = 'sh_xss_protection';

	/**
	 * @return bool
	 */
	public function check() {
		$response = wp_remote_head( network_site_url() );
		if ( is_wp_error( $response ) ) {
			return false;
		}
		$headers = $response['headers'];
		if ( isset( $headers['x-xss-protection'] ) ) {
			$settings = Settings::instance();
			$data     = $settings->getDValues( self::KEY );
			if ( $data === null ) {
				$data['somewhere'] = true;
				$content           = strtolower( trim( $headers['x-xss-protection'] ) );
				$content           = explode( ';', $content );
				if ( count( $content ) == 1 ) {
					$data['mode'] = 'sanitize';
				} else {
					$content      = explode( '=', $content[1] );
					$data['mode'] = $content[1];
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
		$mode     = HTTP_Helper::retrievePost( 'mode' );
		$scenario = HTTP_Helper::retrievePost( 'scenario' );
		if ( empty( $mode ) || ! in_array( $mode, [ 'sanitize', 'block', 'none' ] ) ) {
			return new \WP_Error( Error_Code::INVALID, __( "Mode empty or invalid", wp_defender()->domain ) );
		}
		$settings     = Settings::instance();
		$data         = $settings->getDValues( self::KEY );
		$data['mode'] = $mode;
		if ( $scenario == 'enforce' ) {
			unset( $data['somewhere'] );
		}
		$settings->setDValues( self::KEY, $data );
	}

	public function revert() {
		$settings = Settings::instance();
		$settings->setDValues( self::KEY, null );
	}
}
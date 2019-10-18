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

class Sh_Content_Type_Options_Service extends Rule_Service implements IRule_Service {
	const KEY = 'sh_content_type_options';

	/**
	 * @return bool
	 */
	public function check() {
		$response = wp_remote_head( network_site_url() );
		if ( is_wp_error( $response ) ) {
			return false;
		}
		$headers = $response['headers'];
		if ( isset( $headers['x-content-type-options'] ) ) {
			$settings = Settings::instance();
			$data     = $settings->getDValues( self::KEY );
			if ( $data === null ) {
				$data['mode']      = 'nosniff';
				$data['somewhere'] = 1;
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
		if ( empty( $mode ) || ! in_array( $mode, [ 'nosniff' ] ) ) {
			wp_send_json_error( [
				'message' => __( "Mode empty or invalid", wp_defender()->domain )
			] );
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

	public function listen() {

	}
}
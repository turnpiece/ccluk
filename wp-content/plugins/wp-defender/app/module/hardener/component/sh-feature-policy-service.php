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

class Sh_Feature_Policy_Service extends Rule_Service implements IRule_Service {
	const KEY = 'sh_feature_policy';

	/**
	 * @return bool
	 */
	public function check() {
		$response = wp_remote_head( network_site_url() );
		if ( is_wp_error( $response ) ) {
			return false;
		}
		$headers = $response['headers'];
		if ( isset( $headers['feature-policy'] ) ) {
			$settings = Settings::instance();
			$data     = $settings->getDValues( self::KEY );
			if ( $data === null ) {
				$data['somewhere'] = true;
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
		$values   = HTTP_Helper::retrievePost( 'values' );
		$scenario = HTTP_Helper::retrievePost( 'scenario' );
		if ( empty( $mode ) || ! in_array( $mode, [ 'self', 'allow', 'origins', 'none' ] ) ) {
			wp_send_json_error( [
				'message' => __( "Mode empty or invalid", wp_defender()->domain )
			] );
		}
		$values = trim( $values );
		$values = sanitize_textarea_field( $values );
		$values = explode( PHP_EOL, $values );
		foreach ( $values as $key => $url ) {
			if ( filter_var( $url, FILTER_VALIDATE_URL ) == false ) {
				unset( $values[ $key ] );
			}
		}
		$settings       = Settings::instance();
		$data           = $settings->getDValues( self::KEY );
		$data['mode']   = $mode;
		$data['values'] = $values;
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
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

class Sh_Feature_Policy_Service extends Rule_Service implements IRule_Service {
	const KEY = 'sh_feature_policy';
	public $mode;
	public $scenario;
	public $values;

	/**
	 * @return bool
	 */
	public function check() {
		$settings = Settings::instance();
		$headers  = $this->headRequest( network_site_url(), self::KEY );
		if ( is_wp_error( $headers ) ) {
			Utils::instance()->log( sprintf( 'Self ping error: %s', $headers->get_error_message() ), 'tweaks' );

			return false;
		}

		if ( isset( $headers['feature-policy'] ) ) {
			$data = $settings->getDValues( self::KEY );
			if ( $data === null ) {
				$data['somewhere'] = true;
				$settings->setDValues( self::KEY, $data );
			}

			return true;
		}

		$data = $settings->getDValues( self::KEY );
		if ( is_array( $data ) && isset( $data['mode'] ) && in_array( $data['mode'], [
				'self',
				'allow',
				'origins',
				'none'
			] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param $mode
	 * @param $values
	 * @param $scenario
	 *
	 * @return bool|\WP_Error
	 */
	public function process() {
		$mode     = $this->mode;
		$scenario = $this->scenario;
		$values   = $this->values;
		if ( empty( $mode ) || ! in_array( $mode, [ 'self', 'allow', 'origins', 'none' ] ) ) {
			return new \WP_Error( Error_Code::INVALID, __( "Mode empty or invalid", wp_defender()->domain ) );
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
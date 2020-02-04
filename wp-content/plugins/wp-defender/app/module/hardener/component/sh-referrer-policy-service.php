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

class Sh_Referrer_Policy_Service extends Rule_Service implements IRule_Service {
	const KEY = 'sh_referrer_policy';
	public $mode;
	public $scenario;

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

		if ( isset( $headers['referrer-policy'] ) ) {
			$data = $settings->getDValues( self::KEY );
			if ( $data === null ) {
				$data['somewhere'] = true;
				$settings->setDValues( self::KEY, $data );
			}

			return true;
		}

		$data = $settings->getDValues( self::KEY );
		if ( is_array( $data ) && isset( $data['mode'] ) && in_array( $data['mode'], [
				'no-referrer',
				'no-referrer-when-downgrade',
				'origin',
				'origin-when-cross-origin',
				'same-origin',
				'strict-origin',
				'strict-origin-when-cross-origin',
				'unsafe-url'
			] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @return bool|\WP_Error
	 */
	public function process() {
		$mode     = $this->mode;
		$scenario = $this->scenario;
		if ( empty( $mode ) || ! in_array( $mode, [
				'no-referrer',
				'no-referrer-when-downgrade',
				'origin',
				'origin-when-cross-origin',
				'same-origin',
				'strict-origin',
				'strict-origin-when-cross-origin',
				'unsafe-url'
			] ) ) {
			return new \WP_Error( Error_Code::INVALID, __( "Mode empty or invalid", wp_defender()->domain ) );
		}
		$settings     = Settings::instance();
		$data         = $settings->getDValues( self::KEY );
		$data['mode'] = $mode;
		$data         = [
			'mode' => $mode
		];
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
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

class Sh_X_Frame_Service extends Rule_Service implements IRule_Service {
	const KEY = 'sh_xframe';
	public $mode;
	public $values;
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
		if ( isset( $headers['x-frame-options'] ) ) {
			$settings = Settings::instance();
			$data     = $settings->getDValues( self::KEY );
			if ( $data === null ) {
				$data    = [ 'somewhere' => true ];
				$content = strtolower( trim( $headers['x-frame-options'] ) );
				if ( stristr( $content, 'allow-from' ) ) {
					$data['mode'] = 'allow-from';
					$urls         = explode( ' ', $content );
					unset( $urls[0] );
					$data['values'] = implode( PHP_EOL, $urls );
				} elseif ( in_array( strtolower( $content ), [ 'sameorigin', 'deny' ] ) ) {
					$data['mode'] = strtolower( $content );
				}
				$settings->setDValues( self::KEY, $data );
			}

			return true;
		}
		$data = $settings->getDValues( self::KEY );
		if ( is_array( $data ) && in_array( $data['mode'], [ 'sameorigin', 'allow-from', 'deny' ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @return bool|\WP_Error
	 */
	public function process() {
		$mode     = $this->mode;
		$values   = $this->values;
		$scenario = $this->scenario;
		if ( empty( $mode ) || ! in_array( $mode, [ 'sameorigin', 'allow-from', 'deny' ] ) ) {
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
		$settings = Settings::instance();
		$data     = $settings->getDValues( self::KEY );
		if ( ! is_array( $data ) ) {
			$data = [];
		}
		$data['mode']   = $mode;
		$data['values'] = implode( ' ', $values );
		if ( $scenario == 'enforce' ) {
			unset( $data['somewhere'] );
		}
		$settings->setDValues( self::KEY, $data );

		return true;
	}

	public function revert() {
		$settings = Settings::instance();
		$settings->setDValues( self::KEY, null );
	}

	public function listen() {

	}
}
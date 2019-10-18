<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use Hammer\Helper\HTTP_Helper;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Hardener\Rule;

class Sh_Feature_Policy extends Rule {
	static $slug = 'sh-feature-policy';
	static $service;

	public function getDescription() {

	}

	public function check() {
		return $this->getService()->check();
	}

	/**
	 * @return array
	 */
	public function getMiscData() {
		$settings = Settings::instance();
		$data     = $settings->getDValues( Sh_Feature_Policy_Service::KEY );

		return [
			'mode'   => is_array( $data ) && isset( $data['mode'] ) ? $data['mode'] : 'self',
			'values' => is_array( $data ) && isset( $data['values'] ) ? $data['values'] : array(),
		];
	}

	/**
	 * This will return the short summary why this rule show up as issue
	 *
	 * @return string
	 */
	function getErrorReason() {
		return __( "The Feature-Policy header isn't enforced. All browser features are accessible when embedded through an iframe.", wp_defender()->domain );
	}

	/**
	 * This will return a short summary to show why this rule works
	 * @return mixed
	 */
	function getSuccessReason() {
		return __( "You've enforced Feature-Policy, good job!", wp_defender()->domain );
	}

	public function addHooks() {
		$this->addAction( 'processingHardener' . self::$slug, 'process' );
		$this->addAction( 'wp', 'appendHeader', PHP_INT_MAX );
		$this->addAction( 'processRevert' . self::$slug, 'revert' );
	}

	public function revert() {
		$this->getService()->revert();
		Settings::instance()->addToIssues( self::$slug );
	}

	public function appendHeader() {
		if ( headers_sent() ) {
			//header already sent, do nothing
			return;
		}
		$settings = Settings::instance();
		$data     = $settings->getDValues( Sh_Feature_Policy_Service::KEY );
		if ( ! $this->maybeSubmitHeader( 'Feature-Policy', isset( $data['somewhere'] ) ? $data['somewhere'] : false ) ) {
			//this mean Defender can't override the already output, marked to show notification
			$data['overrideable'] = false;
			$settings->setDValues( Sh_Feature_Policy_Service::KEY, $data );

			return;
		}
		if ( is_array( $data ) && in_array( $data['mode'], [ 'self', 'allow', 'origins', 'none' ] ) ) {
			$headers  = '';
			$features = [
				'accelerometer',
				'ambient-light-sensor',
				'autoplay',
				'camera',
				'encrypted-media',
				'fullscreen',
				'geolocation',
				'gyroscope',
				'magnetometer',
				'microphone',
				'midi',
				'payment',
				'picture-in-picture',
				'speaker',
				'usb',
				//'vibrate',
				'vr'
			];
			switch ( $data['mode'] ) {
				case 'self':
					array_walk( $features, function ( &$value, $key ) {
						$value .= " 'self'";
					} );
					$headers = 'Feature-Policy: ' . implode( '; ', $features );
					break;
				case 'allow':
					array_walk( $features, function ( &$value, $key ) {
						$value .= " *";
					} );
					$headers = 'Feature-Policy: ' . implode( '; ', $features );
					break;
				case 'origins':
					$urls = implode( ' ', $data['values'] );
					array_walk( $features, function ( &$value, $key ) use ( $urls ) {
						$value .= " " . $urls;
					} );
					$headers = 'Feature-Policy: ' . implode( '; ', $features );
					break;
				case 'none':
					array_walk( $features, function ( &$value, $key ) {
						$value .= " 'none'";
					} );
					$headers = 'Feature-Policy: ' . implode( '; ', $features );
					break;
				default:
					break;
			}
			if ( strlen( $headers ) > 0 ) {
				header( trim( $headers ) );
			}
		}
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return __( "Feature-Policy Security Header", wp_defender()->domain );
	}

	/**
	 * Store a flag that we enable this
	 * @return mixed|void
	 */
	public function process() {
		//calling the service
		$this->getService()->process();
		Settings::instance()->addToResolved( self::$slug );
	}

	/**
	 * @return Sh_Feature_Policy_Service
	 */
	public function getService() {
		if ( self::$service == null ) {
			self::$service = new Sh_Feature_Policy_Service();
		}

		return self::$service;
	}
}
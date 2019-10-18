<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use Hammer\Helper\HTTP_Helper;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Hardener\Rule;

class Sh_XSS_Protection extends Rule {
	const KEY = 'sh_xss_protection';
	static $slug = 'sh-xss-protection';
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
		$data     = $settings->getDValues( self::KEY );

		return [
			'mode' => is_array( $data ) && isset( $data['mode'] ) ? $data['mode'] : 'sanitize',
		];
	}

	/**
	 * This will return the short summary why this rule show up as issue
	 *
	 * @return string
	 */
	function getErrorReason() {
		return __( "The X-XSS-Protection header isn't enforced. Older browsers are at risk of XSS attacks.", wp_defender()->domain );
	}

	/**
	 * This will return a short summary to show why this rule works
	 * @return mixed
	 */
	function getSuccessReason() {
		return __( "You've enforced X-XSS-Protection, good stuff.", wp_defender()->domain );
	}

	public function addHooks() {
		$this->addAction( 'processingHardener' . self::$slug, 'process' );
		$this->addAction( 'wp_loaded', 'appendHeader', 999 );
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
		$data     = $settings->getDValues( self::KEY );
		if ( ! $this->maybeSubmitHeader( 'X-XSS-Protection', isset( $data['somewhere'] ) ? $data['somewhere'] : false ) ) {
			$data['overrideable'] = false;

			return;
		}
		if ( is_array( $data ) && in_array( $data['mode'], [ 'sanitize', 'block', 'none' ] ) ) {
			$headers = '';
			switch ( $data['mode'] ) {
				case 'sanitize':
					$headers = 'X-XSS-Protection: 1';
					break;
				case 'block':
					$headers = 'X-XSS-Protection: 1; mode=block';
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
		return __( "X-XSS-Protection Security Header", wp_defender()->domain );
	}

	/**
	 * Store a flag that we enable this
	 * @return mixed|void
	 */
	public function process() {
		$ret = $this->getService()->process();
		if ( is_wp_error( $ret ) ) {
			wp_send_json_error( [ 'message' => $ret->get_error_message() ] );
		}
		Settings::instance()->addToResolved( self::$slug );
	}

	/**
	 * @return Sh_XSS_Protection_Service
	 */
	public function getService() {
		if ( self::$service == null ) {
			self::$service = new Sh_XSS_Protection_Service();
		}

		return self::$service;
	}
}
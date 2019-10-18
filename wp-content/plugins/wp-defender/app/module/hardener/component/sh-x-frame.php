<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use Hammer\Helper\HTTP_Helper;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Hardener\Rule;

class Sh_X_Frame extends Rule {
	const KEY = 'sh_xframe';
	static $slug = 'sh-xframe';
	static $service;

	public function getDescription() {
		$this->renderPartial( 'rules/security-headers-x-frame' );
	}

	public function check() {
		return $this->getService()->check();
	}

	public function getMiscData() {
		$settings = Settings::instance();
		$data     = $settings->getDValues( self::KEY );

		return [
			'intro_text' => esc_html__( "The X-Frame-Options HTTP response header controls whether or not a browser can render a webpage inside a <frame>, <iframe> or <object> tag. Websites can avoid clickjacking attacks by ensuring that their content isn't embedded into other websites.", wp_defender()->domain ),
			'mode'       => is_array( $data ) && isset( $data['mode'] ) ? $data['mode'] : 'sameorigin',
			'values'     => is_array( $data ) && isset( $data['values'] ) ? $data['values'] : array(),
		];
	}

	/**
	 * This will return the short summary why this rule show up as issue
	 *
	 * @return string
	 */
	function getErrorReason() {
		return __( "The X-Frame-Option header isn't enforced, so anyone can embed your web pages.", wp_defender()->domain );
	}

	/**
	 * This will return a short summary to show why this rule works
	 * @return mixed
	 */
	function getSuccessReason() {
		return __( "You've enforced X-Frame-Options, good stuff.", wp_defender()->domain );
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
		$data     = $settings->getDValues( self::KEY );

		if ( ! $this->maybeSubmitHeader( 'X-Frame-Options', isset( $data['somewhere'] ) ? $data['somewhere'] : false ) ) {
			$data['overrideable'] = false;

			return;
		}

		if ( is_array( $data ) && in_array( $data['mode'], [ 'sameorigin', 'allow-from', 'deny' ] ) ) {
			if ( ! isset( $data['values'] ) ) {
				$data['values'] = '';
			}
			$headers = 'X-Frame-Options: ' . $data['mode'];
			if ( $data['mode'] == 'allow-from' ) {
				$headers .= ' ' . $data['values'];
			}
			header( trim( $headers ) );
		}
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return __( "X-Frame-Options Security Header", wp_defender()->domain );
	}

	/**
	 * Store a flag that we enable this
	 * @return mixed|void
	 */
	public function process() {
		$ret = $this->getService()->process();
		if ( is_wp_error( $ret ) ) {
			wp_send_json_error( [
				'message' => $ret->get_error_message()
			] );
		}
		Settings::instance()->addToResolved( self::$slug );
	}

	/**
	 * @return Sh_X_Frame_Service
	 */
	public function getService() {
		if ( self::$service == null ) {
			self::$service = new Sh_X_Frame_Service();
		}

		return self::$service;
	}
}
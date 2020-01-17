<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use Hammer\Helper\HTTP_Helper;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Hardener\Rule;

class Sh_Referrer_Policy extends Rule {
	static $slug = 'sh-referrer-policy';
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
		$data     = $settings->getDValues( Sh_Referrer_Policy_Service::KEY );

		return [
			'mode'   => is_array( $data ) && isset( $data['mode'] ) ? $data['mode'] : 'origin-when-cross-origin',
			'values' => is_array( $data ) && isset( $data['values'] ) ? $data['values'] : [],
		];
	}

	/**
	 * This will return the short summary why this rule show up as issue
	 *
	 * @return string
	 */
	function getErrorReason() {
		return __( "Referrer policy header isn't active. We recommend you choose a policy from the options below.", wp_defender()->domain );
	}

	/**
	 * This will return a short summary to show why this rule works
	 * @return mixed
	 */
	function getSuccessReason() {
		return __( "You've enforced Referrer policy, well done!", wp_defender()->domain );
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
		$data     = $settings->getDValues( Sh_Referrer_Policy_Service::KEY );
		if ( ! $this->maybeSubmitHeader( 'Referrer-Policy', isset( $data['somewhere'] ) ? $data['somewhere'] : false ) ) {
			//this mean Defender can't override the already output, marked to show notification
			$data['overrideable'] = false;
			$settings->setDValues( Sh_Referrer_Policy_Service::KEY, $data );

			return;
		}
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
			$headers = 'Referrer-Policy: ' . $data['mode'];
			header( $headers );
		}
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return __( "Referrer Policy Security Header", wp_defender()->domain );
	}

	/**
	 * Store a flag that we enable this
	 * @return mixed|void
	 */
	public function process() {
		//calling the service
		$mode              = HTTP_Helper::retrievePost( 'mode' );
		$scenario          = HTTP_Helper::retrievePost( 'scenario' );
		$service           = $this->getService();
		$service->mode     = $mode;
		$service->scenario = $scenario;
		$ret               = $service->process();
		if ( ! is_wp_error( $ret ) ) {
			Settings::instance()->addToResolved( self::$slug );
		} else {
			wp_send_json_error( [
				'message' => $ret->get_error_message()
			] );
		}
	}

	/**
	 * @return Sh_Referrer_Policy_Service
	 */
	public function getService() {
		if ( self::$service == null ) {
			self::$service = new Sh_Referrer_Policy_Service();
		}

		return self::$service;
	}
}
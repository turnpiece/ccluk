<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use Hammer\Helper\HTTP_Helper;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Hardener\Rule;

class Sh_Content_Type_Options extends Rule {
	static $slug = 'sh-content-type-options';
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
		$data     = $settings->getDValues( Sh_Content_Type_Options_Service::KEY );

		return [
			'mode' => is_array( $data ) && isset( $data['mode'] ) ? $data['mode'] : 'nosniff',
		];
	}

	/**
	 * This will return the short summary why this rule show up as issue
	 *
	 * @return string
	 */
	function getErrorReason() {
		return __( "The X-Content-Type-Options header isn't enforced. Your site is at risk of MIME type sniffing and XSS attacks.", wp_defender()->domain );
	}

	/**
	 * This will return a short summary to show why this rule works
	 * @return mixed
	 */
	function getSuccessReason() {
		return __( "You've enforced X-Content-Type-Options, well done!", wp_defender()->domain );
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
		$data     = $settings->getDValues( Sh_Content_Type_Options_Service::KEY );
		if ( ! $this->maybeSubmitHeader( 'X-Content-Type-Options', isset( $data['somewhere'] ) ? $data['somewhere'] : false ) ) {
			//this mean Defender can't override the already output, marked to show notification
			$data['overrideable'] = false;
			$settings->setDValues( Sh_Content_Type_Options_Service::KEY, $data );

			return;
		}
		if ( is_array( $data ) && $data['mode'] == 'nosniff' ) {
			header( 'X-Content-Type-Options: nosniff' );
		}
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return __( "X-Content-Type-Options Security Header", wp_defender()->domain );
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
	 * @return Sh_Content_Type_Options_Service
	 */
	public function getService() {
		if ( self::$service == null ) {
			self::$service = new Sh_Content_Type_Options_Service();
		}

		return self::$service;
	}
}
<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use Hammer\Helper\HTTP_Helper;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Hardener\Rule;

class Sh_Strict_Transport extends Rule {
	static $slug = 'sh-strict-transport';
	static $service;

	public function getDescription() {

	}

	public function check() {
		return $this->getService()->check();
	}

	public function getMiscData() {
		$settings = Settings::instance();
		$data     = $settings->getDValues( Sh_Strict_Transport_Service::KEY );

		return [
			'hsts_preload'        => is_array( $data ) && isset( $data['hsts_preload'] ) ? $data['hsts_preload'] : 0,
			'include_subdomain'   => is_array( $data ) && isset( $data['include_subdomain'] ) ? $data['include_subdomain'] : 0,
			'hsts_cache_duration' => is_array( $data ) && isset( $data['hsts_cache_duration'] ) ? $data['hsts_cache_duration'] : '7 days',
			'somewhere'           => is_array( $data ) && isset( $data['somewhere'] ) ? $data['somewhere'] : false,
		];
	}

	/**
	 * This will return the short summary why this rule show up as issue
	 *
	 * @return string
	 */
	function getErrorReason() {
		return __( "The Strict Transport Security header isn't enforced. Visitors and bots can access your site without https.", wp_defender()->domain );
	}

	/**
	 * This will return a short summary to show why this rule works
	 * @return mixed
	 */
	function getSuccessReason() {
		return __( "You've enforced Strict Transport, well done!", wp_defender()->domain );
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
		$data     = $settings->getDValues( Sh_Strict_Transport_Service::KEY );
		if ( ! $this->maybeSubmitHeader( 'Strict-Transport-Security', isset( $data['somewhere'] ) ? $data['somewhere'] : false ) ) {
			$data['overrideable'] = false;
			$settings->setDValues( Sh_Strict_Transport_Service::KEY, $data );

			return;
		}
		if ( is_array( $data ) && isset( $data['hsts_cache_duration'] ) ) {
			$arr     = [
				'1 hour'    => 1 * 3600,
				'24 hours'  => 86400,
				'7 days'    => 7 * 86400,
				'3 months'  => ( 3 * 30 + 1 ) * 86400,
				'6 months'  => ( 6 * 30 + 3 ) * 86400,
				'12 months' => 365 * 86400
			];
			$seconds = isset( $arr[ $data['hsts_cache_duration'] ] ) ? $arr[ $data['hsts_cache_duration'] ] : null;
			if ( $seconds === null ) {
				return;
			}
			$headers = 'Strict-Transport-Security: max-age=' . $seconds;
			if ( $data['include_subdomain'] == 1 ) {
				$headers .= ' ; includeSubDomains';
			}
			if ( $data['hsts_preload'] == 1 ) {
				$headers .= ' ; preload';
			}

			header( $headers );
		}
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return __( "Strict Transport Security Header", wp_defender()->domain );
	}

	/**
	 * Store a flag that we enable this
	 * @return mixed|void
	 */
	public function process() {
		$this->getService()->process();
		Settings::instance()->addToResolved( Sh_Strict_Transport::$slug );
	}

	/**
	 * @return Sh_Strict_Transport_Service
	 */
	public function getService() {
		if ( self::$service == null ) {
			self::$service = new Sh_Strict_Transport_Service();
		}

		return self::$service;
	}
}
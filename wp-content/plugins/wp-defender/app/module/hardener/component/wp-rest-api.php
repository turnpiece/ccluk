<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use Hammer\Helper\HTTP_Helper;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Hardener\Rule;

class WP_Rest_Api extends Rule {
	static $slug = 'wp-rest-api';
	static $service;

	function getDescription() {

	}

	/**
	 * @return bool
	 */
	function check() {
		return $this->getService()->check();
	}

	/**
	 * This will return the short summary why this rule show up as issue
	 *
	 * @return string
	 */
	function getErrorReason() {
		return __( "The WordPress Rest API is publicly accessible. You may want to prevent unauthorized requests.", wp_defender()->domain );
	}

	/**
	 * This will return a short summary to show why this rule works
	 * @return mixed
	 */
	function getSuccessReason() {
		$mode = $this->getMiscData()['mode'];
		if ( $mode == 'allow-all' ) {
			return __( "You are currently allowing all requests to the REST API, good job!", wp_defender()->domain );
		}

		return __( "You are currently blocking unauthorized requests to the REST API, good job!", wp_defender()->domain );
	}

	public function getTitle() {
		return __( "WordPress REST API", wp_defender()->domain );
	}

	function addHooks() {
		$this->addAction( 'processingHardener' . self::$slug, 'process' );
		$this->addAction( 'processRevert' . self::$slug, 'revert' );
		if ( in_array( self::$slug, (array) Settings::instance()->fixed ) ) {
			$this->addFilter( 'rest_authentication_errors', 'maybeAllow' );
		}
	}

	public function maybeAllow( $result ) {
		if ( ! empty( $result ) ) {
			return $result;
		}

		$mode = $this->getMiscData()['mode'];
		if ( $mode == 'allow-auth' && ! is_user_logged_in() ) {
			return new \WP_Error( 'rest_not_logged_in', __( 'The WordPress Rest API has been locked to authorized access only. Log in to use the API.', wp_defender()->domain ), array( 'status' => 401 ) );
		}

		//delegate to other

		return $result;
	}

	function getMiscData() {
		$data = Settings::instance()->getDValues( WP_Rest_API_Service::KEY );
		$mode = 'allow-all';
		if ( is_array( $data ) && isset( $data['mode'] ) && in_array( $data['mode'], [ 'allow-auth', 'block-all' ] ) ) {
			$mode = $data['mode'];
		}

		return [
			'mode' => $mode
		];
	}

	function revert() {
		$ret = $this->getService()->revert();
		if ( ! is_wp_error( $ret ) ) {
			Settings::instance()->addToIssues( self::$slug );
		} else {
			wp_send_json_error( array(
				'message' => $ret->get_error_message()
			) );
		}
	}

	function process() {
		$ret = $this->getService()->process();
		if ( ! is_wp_error( $ret ) ) {
			Settings::instance()->addToResolved( self::$slug );
		} else {
			wp_send_json_error( array(
				'message' => $ret->get_error_message()
			) );
		}
	}

	/**
	 * @return WP_Rest_API_Service
	 */
	public function getService() {
		if ( self::$service == null ) {
			self::$service = new WP_Rest_API_Service();
		}

		return self::$service;
	}
}
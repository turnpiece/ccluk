<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Hardener\Rule;

class Prevent_Enum_Users extends Rule {
	static $slug = 'prevent-enum-users';
	static $service;

	/**
	 * Return this rule content, we will try to use renderPartial
	 *
	 * @return mixed
	 */
	function getDescription() {

	}

	/**
	 * This will return the short summary why this rule show up as issue
	 *
	 * @return string
	 */
	function getErrorReason() {
		return __( "User enumeration is currently allowed.", wp_defender()->domain );
	}

	/**
	 * This will return a short summary to show why this rule works
	 * @return mixed
	 */
	function getSuccessReason() {
		return __( "User enumeration is currently blocked, nice work!", wp_defender()->domain );
	}

	/**
	 * @return mixed
	 */
	function check() {
		return $this->getService()->check();
	}

	/**
	 * implement the revert function
	 *
	 * @return mixed
	 */
	function revert() {
		$this->getService()->revert();
		Settings::instance()->addToIssues( self::$slug );
	}

	/**
	 * implement the process function
	 * @return mixed
	 */
	function process() {
		$this->getService()->process();
		Settings::instance()->addToResolved( self::$slug );
	}

	/**
	 * @return mixed
	 */
	function getTitle() {
		return __( "Prevent user enumeration", wp_defender()->domain );
	}

	/**
	 * Return Service class
	 * @return Prevent_Enum_Users_Service
	 */
	function getService() {
		if ( self::$service == null ) {
			self::$service = new Prevent_Enum_Users_Service();
		}

		return self::$service;
	}

	/**
	 * @return mixed
	 */
	function addHooks() {
		$this->addAction( 'processingHardener' . self::$slug, 'process' );
		$this->addAction( 'processRevert' . self::$slug, 'revert' );
		$flag = Settings::instance()->getDValues( Prevent_Enum_Users_Service::CACHE_KEY );
		if ( php_sapi_name() == 'cli' ) {
			//in cli, ignore this
			$flag = 0;
		}
		if ( $flag == 1 ) {
			if ( ! is_admin() ) {
				// default URL format
				if ( ! isset( $_SERVER['QUERY_STRING'] ) ) {
					return;
				}

				if ( preg_match( '/author=([0-9]*)/i', $_SERVER['QUERY_STRING'] ) ) {
					wp_die( __( 'Sorry, you are not allowed to access this page', wp_defender()->domain ) );
				}
				$this->addFilter( 'redirect_canonical', 'checkEnum', 10, 2 );
			}
		}
	}

	/**
	 * @param $redirect
	 * @param $request
	 *
	 * @return mixed
	 */
	public function checkEnum( $redirect, $request ) {
		if ( preg_match( '/\?author=([0-9]*)(\/*)/i', $request ) ) {
			wp_die( __( 'Sorry, you are not allowed to access this page', wp_defender()->domain ) );
		}

		return $redirect;
	}
}
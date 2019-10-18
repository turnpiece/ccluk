<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use Hammer\Helper\HTTP_Helper;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Hardener\Rule;

class Disable_Trackback extends Rule {
	static $slug = 'disable-trackback';
	static $service;

	function getDescription() {
		$this->renderPartial( 'rules/disable-trackback' );
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
		return __( "Trackbacks and pingbacks are currently enabled.", wp_defender()->domain );
	}

	/**
	 * This will return a short summary to show why this rule works
	 * @return mixed
	 */
	function getSuccessReason() {
		return __( "Trackbacks and pingbacks are disabled, nice work!", wp_defender()->domain );
	}

	public function getTitle() {
		return __( "Disable trackbacks and pingbacks", wp_defender()->domain );
	}

	function addHooks() {
		$this->addAction( 'processingHardener' . self::$slug, 'process' );
		$this->addAction( 'processRevert' . self::$slug, 'revert' );
		if ( in_array( self::$slug, (array) Settings::instance()->fixed ) ) {
			$this->addFilter( 'wp_headers', 'removePingback' );
		}
	}

	/**
	 * @param $headers
	 *
	 * @return mixed
	 */
	public function removePingback( $headers ) {
		unset( $headers['X-Pingback'] );

		return $headers;
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
		$process_posts                     = HTTP_Helper::retrievePost( 'updatePosts' );
		$this->getService()->process_posts = $process_posts;

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
	 * @return Disable_Trackback_Service
	 */
	public function getService() {
		if ( self::$service == null ) {
			self::$service = new Disable_Trackback_Service();
		}

		return self::$service;
	}
}
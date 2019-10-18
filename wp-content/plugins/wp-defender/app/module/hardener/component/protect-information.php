<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use Hammer\Helper\WP_Helper;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Hardener\Rule;

class Protect_Information extends Rule {
	static $slug = 'protect-information';
	static $service;

	function getDescription() {
		$this->renderPartial( 'rules/protect-information' );
	}

	/**
	 * This will return the short summary why this rule show up as issue
	 *
	 * @return string
	 */
	function getErrorReason() {
		return __( "You don't have information disclosure protection active.", wp_defender()->domain );
	}

	/**
	 * This will return a short summary to show why this rule works
	 * @return mixed
	 */
	function getSuccessReason() {
		return __( "You've automatically enabled information disclosure protection.", wp_defender()->domain );
	}

	public function getMiscData() {
		$settings = Settings::instance();

		return [
			'active_server' => $settings->active_server,
			'nginx_rules'   => $this->getService()->getNginxRules(),
		];
	}

	/**
	 * @return bool|false|mixed|null
	 */
	function check() {
		return $this->getService()->check();
	}

	public function getTitle() {
		return __( "Prevent Information Disclosure", wp_defender()->domain );
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

	function addHooks() {
		$this->addAction( 'processingHardener' . self::$slug, 'process' );
		$this->addAction( 'processRevert' . self::$slug, 'revert' );
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
	 * @return Protect_Information_Service
	 */
	public function getService() {
		if ( self::$service == null ) {
			self::$service = new Protect_Information_Service();
		}

		return self::$service;
	}
}
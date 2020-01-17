<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use Hammer\Helper\HTTP_Helper;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Hardener\Rule;

class Disable_Xml_Rpc extends Rule {
	static $slug = 'disable-xml-rpc';
	static $service;

	function getDescription() {
		$this->renderPartial( 'rules/disable-xml-rpc' );
	}

	/**
	 * @return bool
	 */
	function check() {
		return $this->getService()->check();
	}

	public function getTitle() {
		return __( "Disable XML RPC", wp_defender()->domain );
	}

	function addHooks() {
		$this->addAction( 'processingHardener' . self::$slug, 'process' );
		$this->addAction( 'processRevert' . self::$slug, 'revert' );
		if ( in_array( self::$slug, Settings::instance()->fixed ) ) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
			$this->addFilter( 'xmlrpc_methods', 'block_xmlrpc_attacks' );
		}
	}

	function block_xmlrpc_attacks( $methods ) {
		unset( $methods['pingback.ping'] );
		unset( $methods['pingback.extensions.getPingbacks'] );

		return $methods;
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
	 * @return Disable_Xml_Rpc_Service
	 */
	public function getService() {
		if ( self::$service == null ) {
			self::$service = new Disable_Xml_Rpc_Service();
		}

		return self::$service;
	}

	/**
	 * This will return the short summary why this rule show up as issue
	 *
	 * @return string
	 */
	function getErrorReason() {
		return __( "XML-RPC is currently enabled.", wp_defender()->domain );
	}

	/**
	 * This will return a short summary to show why this rule works
	 * @return mixed
	 */
	function getSuccessReason() {
		return __( "XML-RPC is disabled.", wp_defender()->domain );
	}
}
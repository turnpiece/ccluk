<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use Hammer\Helper\HTTP_Helper;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Hardener\Rule;

class Disable_Xml_Rpc extends Rule {
	static $slug = 'disable_xml_rpc';
	static $service;

	function getDescription() {
		$this->renderPartial( 'rules/disable-xml-rpc' );
	}

	function getSubDescription() {
		return __( "XML-RPC is currently enabled.", wp_defender()->domain );
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
		$this->add_action( 'processingHardener' . self::$slug, 'process' );
		$this->add_action( 'processRevert' . self::$slug, 'revert' );
		if ( in_array( self::$slug, Settings::instance()->fixed ) ) {
			$this->add_filter( 'xmlrpc_enabled', 'return_false' );
			$this->add_filter( 'xmlrpc_methods', 'block_xmlrpc_attacks' );
		}
	}

	function return_false() {
		return false;
	}

	function block_xmlrpc_attacks( $methods ) {
		unset( $methods['pingback.ping'] );
		unset( $methods['pingback.extensions.getPingbacks'] );
		return $methods;
	}

	function revert() {
		if ( ! $this->verifyNonce() ) {
			return;
		}

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
		if ( ! $this->verifyNonce() ) {
			return;
		}

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

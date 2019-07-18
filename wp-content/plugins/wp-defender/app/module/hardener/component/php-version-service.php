<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use WP_Defender\Module\Hardener\IRule_Service;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Hardener\Rule_Service;

class PHP_Version_Service extends Rule_Service implements IRule_Service {

	/**
	 * @return bool
	 */
	public function check() {
		$this->queryVersion();
		if ( version_compare( phpversion(), Settings::instance()->min_php_version, '<=' ) ) {
			return false;
		}

		return true;
	}

	public function process() {

	}

	public function revert() {

	}

	public function listen() {

	}

	protected function queryVersion() {
		$lastCheck = get_site_transient( 'defender_last_check_php_versions' );
		if ( ! $lastCheck || strtotime( '+24 hours', $lastCheck ) < time() ) {
			$html = wp_remote_get( 'http://php.net/supported-versions.php' );
			if ( is_wp_error( $html ) ) {
				delete_site_transient( 'defender_last_check_php_versions' );

				return false;
			}
			if ( class_exists( '\DOMDocument' ) ) {
				$dom = new \DOMDocument;
				libxml_use_internal_errors( true );
				$dom->loadHTML( $html['body'] );
				$finder       = new \DOMXPath( $dom );
				$classname    = "security";
				$securityNode = $finder->query( "//*[contains(@class, '$classname')]/td[1]/a" );
				$securityNode = $securityNode->item( 0 )->nodeValue;
				$classname    = "stable";
				$lastStable   = $finder->query( "//*[contains(@class, '$classname')][2]/td[1]/a" );;
				$lastStable                   = $lastStable->item( 0 )->nodeValue;
				$settings                     = Settings::instance();
				$settings->stable_php_version = $lastStable;
				$settings->min_php_version    = $securityNode;
				$settings->save();
				set_site_transient( 'defender_last_check_php_versions', time(), 60 * 60 * 24 );
			} else {
				//do it manually
				$settings                     = Settings::instance();
				$settings->stable_php_version = 7.3;
				$settings->min_php_version    = 7.1;
				$settings->save();
			}
		}
	}
}
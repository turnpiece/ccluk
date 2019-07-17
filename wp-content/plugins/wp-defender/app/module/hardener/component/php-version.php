<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Hardener\Rule;

class PHP_Version extends Rule {
	static $slug = 'php_version';
	static $service;

	function getDescription() {
		$this->renderPartial( 'rules/php-version' );
	}

	function getSubDescription() {
		$settings = Settings::instance();

		return sprintf( __( "PHP versions older than %s are no longer supported. For security and stability we strongly recommend you upgrade your PHP version to version %s or newer as soon as possible. ", wp_defender()->domain ), $settings->min_php_version, $settings->min_php_version );
	}

	/**
	 * @return bool
	 */
	function check() {
		return $this->getService()->check();
	}

	function revert() {
		// TODO: Implement revert() method.
	}

	public function getTitle() {
		return __( "Update PHP to latest version", wp_defender()->domain );
	}

	function addHooks() {
		// TODO: Implement addHooks() method.
	}

	function process() {
		// TODO: Implement process() method.
	}

	/**
	 * @return PHP_Version_Service
	 */
	function getService() {
		if ( static::$service == null ) {
			static::$service = new PHP_Version_Service();
		}

		return static::$service;
	}
}
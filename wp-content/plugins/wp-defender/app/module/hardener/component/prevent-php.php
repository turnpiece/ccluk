<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use Hammer\Helper\WP_Helper;
use Hammer\Helper\HTTP_Helper;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Hardener\Rule;
use WP_Defender\Module\Hardener\Component\Servers\Apache_Service;
use WP_Defender\Module\Hardener\Component\Servers\Iis_Service;

class Prevent_Php extends Rule {
	static $slug = 'prevent-php-executed';
	static $service;
	static $apache_service;
	static $iis_service;

	function getDescription() {
		$this->renderPartial( 'rules/prevent-php-executed' );
	}

	/**
	 * This will return the short summary why this rule show up as issue
	 *
	 * @return string
	 */
	function getErrorReason() {
		return __( "PHP execution is currently allowed in all directories.", wp_defender()->domain );
	}

	/**
	 * This will return a short summary to show why this rule works
	 * @return mixed
	 */
	function getSuccessReason() {
		return __( "You've disabled PHP execution, good stuff.", wp_defender()->domain );
	}

	/**
	 * @return bool|false|mixed|null
	 */
	function check() {
		return $this->getService()->check();
	}

	public function getMiscData() {
		$settings = Settings::instance();

		return [
			'active_server'  => $settings->active_server,
			'nginx_rules'    => $this->getService()->getNginxRules(),
			'wp_content_dir' => WP_CONTENT_DIR
		];
	}

	/**
	 * @return string|void
	 */
	public function getTitle() {
		return __( "Prevent PHP execution", wp_defender()->domain );
	}


	function revert() {
		$settings = Settings::instance();
		$server   = $settings->active_server;
		if ( in_array( $settings->active_server, array( 'apache', 'litespeed' ) ) ) {
			$service = $this->getApacheService();
			$service->setHtConfig( $settings->getNewHtConfig() );
		} else if ( $server == 'iis-7' ) {
			$service = $this->getIisService();
		} else {
			$service = $this->getService();
		}
		$ret = $service->revert();
		if ( ! is_wp_error( $ret ) ) {
			if ( in_array( $settings->active_server, array( 'apache', 'litespeed' ) ) ) {
				$settings->saveExcludedFilePaths( array() );
				$settings->saveNewHtConfig( array() );
			}
			$url = WP_Helper::getUploadUrl();
			$url = $url . '/wp-defender/index.php';
			$this->getService()->clearHeadRequest( $url );
			$settings->addToIssues( self::$slug );
		} else {
			wp_send_json_error( array(
				'message' => $ret->get_error_message()
			) );
		}
	}

	function addHooks() {
		$this->addAction( 'processingHardener' . self::$slug, 'process', 10, 2 );
		$this->addAction( 'processRevert' . self::$slug, 'revert' );
		$this->addAction( 'processUpdate' . self::$slug, 'update', 10, 2 );
	}

	function process() {
		$file_paths = HTTP_Helper::retrievePost( 'file_paths' ); //File paths to ignore. Apache and litespeed mainly
		if ( $file_paths ) {
			$file_paths = sanitize_textarea_field( $file_paths );
		} else {
			$file_paths = '';
		}
		$server = HTTP_Helper::retrievePost( 'current_server' ); //Current server

		if ( in_array( $server, array( 'apache', 'litespeed' ) ) ) {
			$service = $this->getApacheService();
			$service->setExcludeFilePaths( $file_paths ); //Set the paths
		} else if ( $server == 'iis-7' ) {
			$service = $this->getIisService();
		} else {
			$service = $this->getService();
		}
		$ret = $service->process();
		if ( ! is_wp_error( $ret ) ) {
			$settings = Settings::instance();
			$url      = WP_Helper::getUploadUrl();
			$url      = $url . '/wp-defender/index.php';
			$this->getService()->clearHeadRequest( $url );
			$ret = $this->getService()->check();
			if ( $ret == false ) {
				wp_send_json_error( [
					'message' => __( "The rules can't apply to your host. This can because of your host doesn't allow for overriding, or you apply for the wrong webserver", wp_defender()->domain )
				] );
			}
			$settings->saveExcludedFilePaths( $service->getExcludedFilePaths() );
			$settings->saveNewHtConfig( $service->getNewHtConfig() );
			$settings->setActiveServer( $server );
			$settings->addToResolved( self::$slug );
			$this->getService()->clearHeadRequest( $url );
		} else {
			wp_send_json_error( array(
				'message' => $ret->get_error_message()
			) );
		}
	}

	function update() {
		if ( ! $this->verifyNonce() ) {
			return;
		}
		$settings = Settings::instance();

		$file_paths = HTTP_Helper::retrievePost( 'file_paths' ); //File paths to ignore. Apache and litespeed mainly
		if ( $file_paths ) {
			$file_paths = sanitize_textarea_field( $file_paths );
		} else {
			$file_paths = '';
		}

		$server = HTTP_Helper::retrievePost( 'current_server' ); //Current server

		if ( in_array( $server, array( 'apache', 'litespeed' ) ) ) {
			$service = $this->getApacheService();
			$service->setHtConfig( $settings->getNewHtConfig() ); //Set the previous template
			$service->unProtectContentDir(); //revert first
			$service->setExcludeFilePaths( $file_paths ); //Set the paths
		} else {
			$service = $this->getService();
		}
		$ret = $service->process();
		if ( ! is_wp_error( $ret ) ) {
			if ( in_array( $server, array( 'apache', 'litespeed' ) ) ) {
				$settings->saveExcludedFilePaths( $service->getExcludedFilePaths() );
				$settings->saveNewHtConfig( $service->getNewHtConfig() );
			}
			$settings->setActiveServer( $server );
			$settings->save();
		} else {
			wp_send_json_error( array(
				'message' => $ret->get_error_message()
			) );
		}
	}

	/**
	 * @return Prevent_PHP_Service
	 */
	public function getService() {
		if ( self::$service == null ) {
			self::$service = new Prevent_PHP_Service();
		}

		return self::$service;
	}

	/**
	 * @return Apache_Service
	 */
	public function getApacheService() {
		if ( self::$apache_service == null ) {
			self::$apache_service = new Apache_Service();
		}

		return self::$apache_service;
	}

	/**
	 * @return Iis_Service
	 */
	public function getIisService() {
		if ( self::$iis_service == null ) {
			self::$iis_service = new Iis_Service();
		}

		return self::$iis_service;
	}
}
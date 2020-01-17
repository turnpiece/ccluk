<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Hardener\Component;

use Hammer\Helper\HTTP_Helper;
use Hammer\Helper\WP_Helper;
use WP_Defender\Behavior\Endpoint;
use WP_Defender\Behavior\Utils;
use WP_Defender\Module\Advanced_Tools\Component\Mask_Api;
use WP_Defender\Module\Hardener;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Hardener\Rule;

class Security_Key extends Rule {
	static $slug = 'security-key';
	static $service;

	function getDescription() {
		$settings = Settings::instance();
		$time     = $settings->getDValues( Security_Key_Service::CACHE_KEY );
		$interval = $settings->getDValues( 'securityReminderDuration' );
		if ( ! $interval ) {
			$interval = Security_Key_Service::DEFAULT_DAYS;
		}
		if ( $time ) {
			$daysAgo = ( time() - $time ) / ( 60 * 60 * 24 );
		} else {
			$daysAgo = __( "unknown", wp_defender()->domain );
		}

		$this->renderPartial( 'rules/security-key', array(
			'interval' => $interval,
			'daysAgo'  => $daysAgo
		) );
	}

	private function calculateDaysApplied() {
		$settings  = Settings::instance();
		$time      = $settings->getDValues( Security_Key_Service::CACHE_KEY );
		$timestamp = filemtime( ABSPATH . '/' . WPINC . '/general-template.php' );
		$interval  = $settings->getDValues( 'securityReminderDuration' );
		if ( ! $interval ) {
			$interval = Security_Key_Service::DEFAULT_DAYS;
		}
		$daysAgo = __( "unknown", wp_defender()->domain );
		if ( $time ) {
			$daysAgo = ( time() - $time ) / ( 60 * 60 * 24 );
		} elseif ( $timestamp != false ) {
			$daysAgo = ( time() - $timestamp ) / ( 60 * 60 * 24 );
		}
		if ( $daysAgo != __( "unknown", wp_defender()->domain ) ) {
			$daysAgo = round( $daysAgo );
			if ( $daysAgo == 0 ) {
				$daysAgo = 1;
			}
		}

		return $daysAgo;
	}

	/**
	 * This will return the short summary why this rule show up as issue
	 *
	 * @return string
	 */
	function getErrorReason() {
		if ( $this->calculateDaysApplied() == __( "unknown", wp_defender()->domain ) ) {
			return __( "We can’t tell how old your security keys are, perhaps it’s time to update them?", wp_defender()->domain );
		}

		return sprintf( __( "Your current security keys are %s days old. Time to update them!", wp_defender()->domain ), $this->calculateDaysApplied() );
	}

	/**
	 * This will return a short summary to show why this rule works
	 * @return mixed
	 */
	function getSuccessReason() {
		return sprintf( __( "Your security keys are less than %s days old, nice work.", wp_defender()->domain ), $this->calculateDaysApplied() );
	}

	/**
	 * @return string
	 */
	function getTitle() {
		return __( "Update old security keys", wp_defender()->domain );
	}

	function check() {
		return $this->getService()->check();
	}

	function addHooks() {
		$this->addAction( 'processingHardener' . self::$slug, 'process' );
		$namespace = 'wp-defender/v1';
		$namespace .= '/tweaks';
		$routes    = [
			$namespace . '/updateSecurityReminder' => 'updateSecurityReminder',
		];
		$this->registerEndpoints( $routes, Hardener::getClassName() );
	}

	public function getMiscData() {
		$settings = Settings::instance();
		$reminder = $settings->getDValues( 'securityReminderDuration' );
		if ( $reminder == null ) {
			$reminder = Security_Key_Service::DEFAULT_DAYS;
		}

		return [
			'reminder' => $reminder,
		];
	}

	public function updateSecurityReminder() {
		if ( ! Utils::instance()->checkPermission() ) {
			return;
		}

		$reminder = HTTP_Helper::retrievePost( 'remind_date', null );

		if ( $reminder ) {
			$settings = Settings::instance();
			$settings->setDValues( 'securityReminderDuration', $reminder );
			$settings->setDValues( 'securityReminderDate', strtotime( '+' . $reminder, current_time( 'timestamp' ) ) );
		}
	}

	function revert() {

	}

	function process() {
		$ret = $this->getService()->process();
		if ( is_wp_error( $ret ) ) {
			wp_send_json_error( array(
				'message' => $ret->get_error_message()
			) );
		} else {
			Settings::instance()->addToResolved( self::$slug );
			$url = wp_login_url( network_admin_url( 'admin.php?page=wdf-hardener' ) );
			if ( Mask_Api::isEnabled() ) {
				$url = Mask_Api::getNewLoginUrl();
			}
			wp_send_json_success( array(
				'message' => sprintf( __( 'All key salts have been regenerated. You will now need to <a href="%s"><strong>re-login</strong></a>.<br/>This will auto reload after <span class="hardener-timer">3</span> seconds.', wp_defender()->domain ), wp_login_url( network_admin_url( 'admin.php?page=wdf-hardener' ) ) ),
				'reload'  => 3,
				'url'     => $url
			) );
		}
	}

	/**
	 * @return Security_Key_Service
	 */
	public function getService() {
		if ( self::$service == null ) {
			self::$service = new Security_Key_Service();
		}

		return self::$service;
	}
}
<?php
/**
 * @author Paul Kevin
 */

namespace WP_Defender\Module\Hardener\Component;

use Hammer\Helper\WP_Helper;
use Hammer\Helper\HTTP_Helper;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\Hardener\Rule;
use WP_Defender\Behavior\Utils;

class Login_Duration extends Rule {

	static $slug = 'login-duration';

	static $service;

	/**
	 * @return Login_Duration_Service
	 */
	public function getService() {
		if ( self::$service == null ) {
			self::$service = new Login_Duration_Service();
		}

		return self::$service;
	}

	function getDescription() {
		$this->renderPartial( 'rules/login-duration' );
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return __( "Manage Login Duration", wp_defender()->domain );
	}

	/**
	 * This will return the short summary why this rule show up as issue
	 *
	 * @return string
	 */
	function getErrorReason() {
		$days = $this->getService()->getDuration();

		return sprintf( __( "Your current login duration is the default %d days.", wp_defender()->domain ), $days );
	}

	/**
	 * This will return a short summary to show why this rule works
	 * @return mixed
	 */
	function getSuccessReason() {
		$days = $this->getService()->getDuration();

		return sprintf( __( "You've adjusted the default login duration to %d days.", wp_defender()->domain ), $days );
	}

	/**
	 * @return bool
	 */
	function check() {
		return $this->getService()->check();
	}

	function addHooks() {
		$this->addAction( 'processingHardener' . self::$slug, 'process' );
		$this->addAction( 'processRevert' . self::$slug, 'revert' );
		$this->addAction( 'wp_login', 'login_action_handler', 9, 2 );
		if ( $this->check() ) {
			$this->addFilter( 'auth_cookie_expiration', 'cookie_duration', 10, 3 );
			$this->addFilter( 'login_message', 'login_message' );
		}

	}

	function revert() {
		$settings = Settings::instance();
		$service  = $this->getService();
		$ret      = $service->revert();
		if ( ! is_wp_error( $ret ) ) {
			Settings::instance()->addToIssues( self::$slug );
		} else {
			wp_send_json_error( array(
				'message' => $ret->get_error_message()
			) );
		}
	}

	function process() {
		$service  = $this->getService();
		$duration = HTTP_Helper::retrievePost( 'duration' );
		if ( is_numeric( $duration ) && intval( $duration ) > 0 ) {
			$service->setDuration( $duration );
			$ret = $service->process();
			if ( ! is_wp_error( $ret ) ) {
				Settings::instance()->addToResolved( self::$slug );
			} else {
				wp_send_json_error( array(
					'message' => $ret->get_error_message()
				) );
			}
		} else {
			wp_send_json_error( array(
				'message' => __( 'Duration can only be a number and greater than 0', wp_defender()->domain )
			) );
		}
	}

	/**
	 * Set the last login user meta
	 */
	function login_action_handler( $user_login, $user = '' ) {
		if ( $user == '' ) {
			$user = get_user_by( 'login', $user_login );
		}
		if ( ! $user ) {
			return;
		}
		$last_login_time = current_time( 'mysql' );
		update_user_meta( $user->ID, 'last_login_time', $last_login_time );
	}

	public function getMiscData() {
		return [
			'duration' => $this->getService()->getDuration()
		];
	}

	/**
	 * Handle the custom login message
	 *
	 */
	function login_message( $message = '' ) {
		$login_msg = HTTP_Helper::retrieveGet( 'defender_login_message', false );
		if ( $login_msg ) {
			$logout_msg = strip_tags( $login_msg );
			if ( $logout_msg == 'session_expired' ) {
				$duration = $this->getService()->getDuration( false );
				$msg      = sprintf( __( 'Your session has expired because it has been over %d days since your last login. Please log back in to continue.', wp_defender()->domain ), $duration );
				$msg      = htmlspecialchars( $msg, ENT_QUOTES, 'UTF-8' );
				$message  .= '<p class="login message">' . $msg . '</p>';
			}
		}

		return $message;
	}

	/**
	 * Cookie duration in days in seconds
	 *
	 * @param Integer $duration - default duration
	 * @param Integer $user_id - current user id
	 * @param Boolean $remember - remember me login
	 *
	 * @return Integer $duration
	 */
	function cookie_duration( $duration, $user_id, $remember ) {
		$dur = $this->getService()->getDuration( true );
		if ( $dur < 2 ) {
			//duration set smaller than 2 days, use the custom for both remember & non remeber
			return $dur;
		} elseif ( $remember ) {
			//this case only
			return $dur;
		}

		//return default
		return $duration;
	}

}
<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Two_Factor\Component;

use Hammer\Helper\HTTP_Helper;
use Hammer\WP\Component;
use WP_Defender\Behavior\Utils;
use WP_Defender\Module\Two_Factor\Model\Auth_Settings;

class Auth_Listener extends Component {
	protected $sessionToken;

	public function __construct() {
		$this->addAction( 'update_option_jetpack_active_modules', 'listenForJetpackOption', 10, 3 );
		$setting = Auth_Settings::instance();
		if ( $setting->enabled ) {
			//prepare for the login part
			$isJetpackSSO = Auth_API::isJetPackSSO();
			$isTML        = Auth_API::isTML();
			if ( ! defined( 'DOING_AJAX' ) && ! $isJetpackSSO && ! $isTML ) {
				/**
				 * hook into wordpress login, can't use authenticate hook as that badly conflict
				 */
				$this->addAction( 'wp_login', 'maybeShowOTPLogin', 9, 2 );
				$this->addAction( 'login_form_defenderVerifyOTP', 'defenderVerifyOTP' );
				$this->addAction( 'set_logged_in_cookie', 'storeSessionKey' );
				/**
				 * end
				 */
			} else {
				if ( $isJetpackSSO ) {
					wp_defender()->global['compatibility'][] = __( "We've detected a conflict with Jetpack's Wordpress.com Log In feature. Please disable it and return to this page to continue setup.", wp_defender()->domain );
				}
				if ( $isTML ) {
					wp_defender()->global['compatibility'][] = __( "We've detected a conflict with Theme my login. Please disable it and return to this page to continue setup.", wp_defender()->domain );
				}
			}
			$this->addFilter( 'ms_shortcode_ajax_login', 'm2NoAjax' );
			$this->addAction( 'show_user_profile', 'showUsers2FactorActivation' );
			$this->addAction( 'profile_update', 'saveBackupEmail' );
			//$this->add_action( 'wp_login', 'markAsForceAuth', 10, 2 );
			$this->addFilter( 'login_redirect', 'loginRedirect', 99 );
			$this->addAction( 'current_screen', 'forceProfilePage', 1 );
			$this->addAjaxAction( 'defVerifyOTP', 'verifyConfigOTP' );
			$this->addAjaxAction( 'defDisableOTP', 'disableOTP' );
			$this->addAjaxAction( 'defRetrieveOTP', 'retrieveOTP', false, true );
			if ( Utils::instance()->isActivatedSingle() ) {
				$this->addFilter( 'manage_users_columns', 'alterUsersTable' );
				$this->addFilter( 'manage_users_custom_column', 'alterUsersTableRow', 10, 3 );
			} else {
				$this->addFilter( 'wpmu_users_columns', 'alterUsersTable' );
				$this->addFilter( 'manage_users_custom_column', 'alterUsersTableRow', 10, 3 );
			}
		}
	}

	/**
	 * If user have flag then force enable
	 */
	public function forceProfilePage() {
		$user = wp_get_current_user();
		if ( ! is_object( $user ) ) {
			return;
		}

		$settings = Auth_Settings::instance();
		if ( $settings->force_auth != true ) {
			return;
		}

		//not enable for this role oass
		if ( ! Auth_API::isEnableForCurrentRole( $user ) ) {
			return;
		}

		//check if this role is forced
		if ( ! Auth_API::isForcedRole( $user ) ) {
			return;
		}

		//user already enable OTP
		if ( Auth_API::isUserEnableOTP( $user->ID ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen->id != 'profile' ) {
			wp_safe_redirect( admin_url( 'profile.php' ) . '#show2AuthActivator' );
			exit;
		}
	}

	public function loginRedirect( $url ) {
		$settings = Auth_Settings::instance();
		if ( $settings->force_auth != true ) {
			return $url;
		}

		return $url;
	}

	/**
	 * @param $userLogin
	 * @param $user
	 */
	public function markAsForceAuth( $userLogin, $user ) {
		$settings = Auth_Settings::instance();
		if ( $settings->force_auth != true ) {
			return;
		}
		//not enable for this role oass
		if ( ! Auth_API::isEnableForCurrentRole( $user ) ) {
			return;
		}
		//user already enable OTP
		if ( Auth_API::isUserEnableOTP( $user->ID ) ) {
			return;
		}
		//if this is normal user, force them
//		if ( ! current_user_can( 'subscriber' ) ) {
//			return;
//		}
		$flag = get_user_meta( $user->ID, 'defenderForceAuth', true );
		if ( $flag === '' ) {
			update_user_meta( $user->ID, 'defenderForceAuth', 1 );
		}
	}

	/**
	 * We have some feature conflict with jetpack, so listen to know when Defender can on
	 *
	 * @param $old_value
	 * @param $value
	 * @param $option
	 */
	public function listenForJetpackOption( $old_value, $value, $option ) {
		$settings = Auth_Settings::instance();
		if ( array_search( 'sso', $value ) !== false ) {
			$settings->markAsConflict( 'jetpack/jetpack.php' );
		} else {
			$settings->markAsUnConflict( 'jetpack/jetpack.php' );
		}
	}

	/**
	 * Stop ajax login on membership 2
	 * @return bool
	 */
	public function m2NoAjax() {
		return false;
	}

	/**
	 * Return 2 factor auth status
	 *
	 * @param $val
	 * @param $column_name
	 * @param $user_id
	 *
	 * @return string
	 */
	public function alterUsersTableRow( $val, $column_name, $user_id ) {
		if ( $column_name != 'defAuth' ) {
			return $val;
		}

		if ( Auth_API::isUserEnableOTP( $user_id ) ) {
			return '<span class="def-oval oval-green"></span>';
		}

		return '<span class="def-oval"></span>';
	}

	/**
	 * Add the auth column inside users on single site
	 *
	 * @param $columns
	 *
	 * @return mixed
	 *
	 */
	public function alterUsersTable( $columns ) {
		$columns = array_slice( $columns, 0, count( $columns ) - 1 ) + array(
				'defAuth' => __( "Two Factor", wp_defender()->domain )
			) + array_slice( $columns, count( $columns ) - 1 );

		return $columns;
	}

	/**
	 * Generate an email for backup otp
	 */
	public function retrieveOTP() {
		if ( ! wp_verify_nonce( HTTP_Helper::retrieveGet( 'nonce' ), 'defRetrieveOTP' ) ) {
			wp_send_json_error( array() );
		}

		$token = HTTP_Helper::retrieveGet( 'token' );
		$query = new \WP_User_Query( array(
			'meta_key'   => 'defOTPLoginToken',
			'meta_value' => $token,
			'blog_id'    => 0
		) );
		$res   = $query->get_results();
		if ( empty( $res ) ) {
			//no user
			wp_send_json_error( array(
				'message' => __( "Your token is invalid", wp_defender()->domain )
			) );
		}

		$user = $res[0];
		//create a backup code for this user
		$code = Auth_API::createBackupCode( $user->ID );
		//send email
		$backupEmail = Auth_API::getBackupEmail( $user->ID );

		$settings = Auth_Settings::instance();
		$subject  = ! empty( $settings->email_subject ) ? esc_attr( $settings->email_subject ) : __( 'Your OTP code', wp_defender()->domain );
		$sender   = ! empty( $settings->email_sender ) ? esc_attr( $settings->email_sender ) : false;
		$body     = ! empty( $settings->email_body ) ? $settings->email_body : $settings->two_factor_opt_email_default_body();
		$params   = [
			'display_name' => $user->display_name,
			'passcode'     => $code,
		];
		foreach ( $params as $key => $val ) {
			$body = str_replace( '{{' . $key . '}}', $val, $body );
		}
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		if ( $sender ) {
			$from_email = get_bloginfo( 'admin_email' );
			$headers[]  = sprintf( 'From: %s <%s>', $sender, $from_email );
		}

		//send
		wp_mail( $backupEmail, $subject, $body, $headers );

		wp_send_json_success( array(
			'message' => __( "Your code has been sent to your email.", wp_defender()->domain )
		) );
	}

	/**
	 * disable OTP feature
	 */
	public function disableOTP() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		update_user_meta( get_current_user_id(), 'defenderAuthOn', 0 );
		delete_user_meta( get_current_user_id(), 'defenderAuthSecret' );
		wp_send_json_success();
	}

	/**
	 * Saving backup email when profile saved
	 *
	 * @param $userID
	 */
	public function saveBackupEmail( $userID ) {
		$email = HTTP_Helper::retrievePost( 'def_backup_email' );
		if ( $email && get_current_user_id() == $userID ) {
			update_user_meta( $userID, 'defenderAuthEmail', $email );
		}
	}

	/**
	 * An ajax function for verify the OTP user input when configuring the 2 factors
	 */
	public function verifyConfigOTP() {
		if ( ! wp_verify_nonce( HTTP_Helper::retrievePost( 'nonce' ), 'defVerifyOTP' ) ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			return;
		}

		$otp = HTTP_Helper::retrievePost( 'otp' );
		$otp = trim( $otp );
		if ( strlen( $otp ) == 0 ) {
			wp_send_json_error( array(
				'message' => __( "Please input a valid OTP code", wp_defender()->domain )
			) );
		}

		$secret = Auth_API::getUserSecret();
		//at this stage, secret should have value, do not need to check
		$res = Auth_API::compare( $secret, $otp );
		if ( $res ) {
			//save it
			update_user_meta( get_current_user_id(), 'defenderAuthOn', 1 );
			update_user_meta( get_current_user_id(), 'defenderForceAuth', 0 );
			wp_send_json_success();
		} else {
			//now need to check if the current user have backup otp
			wp_send_json_error( array(
				'message' => __( "Your OTP code is incorrect. Please try again.", wp_defender()->domain )
			) );
		}
	}

	/**
	 * Show an section inside my profile page for user can activate 2 factor login
	 *
	 * @param $profileuser
	 */
	public function showUsers2FactorActivation( $profileuser ) {
		if ( ! Auth_API::isEnableForCurrentRole() ) {
			return;
		}

		$isOn = get_user_meta( $profileuser->ID, 'defenderAuthOn', true );
		wp_enqueue_style( 'defAuth', wp_defender()->getPluginUrl() . 'app/module/advanced-tools/css/login-admin.css' );
		$secretKey = Auth_API::createSecretForCurrentUser();
		if ( $isOn && $isOn == 1 ) {
			$email = Auth_API::getBackupEmail( $profileuser->ID );
			$this->renderPartial( 'login/enabled', array(
				'email' => $email
			) );
		} else {
			//show the screen
			$this->renderPartial( 'login/disabled', array(
				'secretKey' => $secretKey
			) );
		}
	}

	/**
	 * We will check and show the OTP screen if user signon successfully
	 *
	 * @param $userLogin
	 * @param $user
	 */
	public function maybeShowOTPLogin( $userLogin, $user ) {
		if ( ! Auth_API::isUserEnableOTP( $user->ID ) ) {
			//no enable, then just return
			return;
		}

		//clean up session and auth cookies for preventing
		$token = $this->sessionToken;
		if ( $token ) {
			$sManager = \WP_Session_Tokens::get_instance( $user->ID );
			$sManager->destroy( $token );
		}
		wp_clear_auth_cookie();

		$this->showOTPScreen( $user );
	}

	/**
	 * verify OTP code which user input in order to login
	 */
	public function defenderVerifyOTP() {
		if ( ( $otp = HTTP_Helper::retrievePost( 'otp', null ) ) != null ) {
			$params = array();
			if ( ! wp_verify_nonce( HTTP_Helper::retrievePost( '_wpnonce' ), 'DefOtpCheck' ) ) {
				$params['error'] = new \WP_Error( 'security_fail', __( "Some error happen", wp_defender()->domain ) );
			}

			$login_token = HTTP_Helper::retrievePost( 'login_token' );
			$query       = new \WP_User_Query( array(
				'meta_key'   => 'defOTPLoginToken',
				'meta_value' => $login_token,
				'blog_id'    => 0
			) );
			$res         = $query->get_results();
			if ( empty( $res ) ) {
				//no users, redirect to the login page immediatly
				wp_redirect( site_url( 'wp-login.php', 'login_post' ) );
				exit;
			} else {
				$user     = $res[0];
				$secret   = Auth_API::getUserSecret( $user->ID );
				$redirect = HTTP_Helper::retrievePost( 'redirect_to', admin_url() );
				if ( Auth_API::compare( $secret, $otp ) ) {
					//sign in
					delete_user_meta( $user->ID, 'defOTPLoginToken' );
					wp_set_current_user( $user->ID, $user->user_login );
					wp_set_auth_cookie( $user->ID, true );
					$redirect = apply_filters( 'login_redirect', $redirect, isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '', $user );
					wp_safe_redirect( $redirect );
					exit;
				} else {
					$backupCode = get_user_meta( $user->ID, 'defenderBackupCode', true );
					if ( $backupCode && $backupCode['code'] == $otp && strtotime( '+3 minutes', $backupCode['time'] ) > time() ) {
						delete_user_meta( $user->ID, 'defOTPLoginToken' );
						delete_user_meta( $user->ID, 'defenderBackupCode' );
						wp_set_current_user( $user->ID, $user->user_login );
						wp_set_auth_cookie( $user->ID, true );
						$redirect = apply_filters( 'login_redirect', $redirect, isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '', $user );
						wp_safe_redirect( $redirect );
						exit;
					} else {
						$params['error'] = new \WP_Error( 'opt_fail', __( "Whoops, the passcode you entered was incorrect or expired.", wp_defender()->domain ) );
						$this->showOTPScreen( $user, $params );
					}
				}
			}
		}
	}

	/**
	 * Show the OTP screen
	 *
	 * @param $user
	 * @param $params
	 */
	private function showOTPScreen( $user, $params = array() ) {
		//now show the OTP screen
		$this->addAction( 'login_enqueue_scripts', 'includeAuthStyles' );
		wp_enqueue_script( 'jquery' );
		$params['loginToken']  = $this->createLoginToken( $user );
		$params['redirect_to'] = HTTP_Helper::retrievePost( 'redirect_to' );
		if ( ! isset( $params['error'] ) ) {
			$params['error'] = null;
		}
		//if this goes here then the current user is ok, need to show the 2 auth
		$this->renderPartial( 'login/otp', $params );
		exit;
	}

	/**
	 * We will empty all auth cookies or session, so should not rely on wp_get_session_token
	 *
	 * @param $cookie
	 */
	public function storeSessionKey( $cookie ) {
		$cookie             = wp_parse_auth_cookie( $cookie, 'logged_in' );
		$this->sessionToken = ! empty( $cookie['token'] ) ? $cookie['token'] : '';
	}

	/**
	 * Create a unique token to retrieve user later
	 *
	 * @param $user
	 *
	 * @return string
	 */
	private function createLoginToken( $user ) {
		$tmp = uniqid();
		// create and store a login token so we can query this user again
		update_user_meta( $user->ID, 'defOTPLoginToken', $tmp );

		return $tmp;
	}

	/**
	 * add css for OTP page
	 */
	public function includeAuthStyles() {
		//enqueue css here
		wp_enqueue_style( 'defAuth', wp_defender()->getPluginUrl() . 'app/module/advanced-tools/css/login.css' );
	}
}
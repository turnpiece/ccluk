<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Advanced_Tools\Controller;

use Hammer\Helper\HTTP_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Controller;
use WP_Defender\Module\Advanced_Tools\Component\Auth_API;
use WP_Defender\Module\Advanced_Tools\Component\Mask_Api;
use WP_Defender\Module\Advanced_Tools\Model\Mask_Settings;

class Mask_Login extends Controller {
	public $layout = 'layout';
	protected $slug = 'wdf-advanced-tools';

	/**
	 * @return array
	 */
	public function behaviors() {
		return array(
			'utils' => '\WP_Defender\Behavior\Utils'
		);
	}

	public function __construct() {
		if ( $this->isInPage() || $this->isDashboard() ) {
			$this->add_action( 'defender_enqueue_assets', 'scripts', 11 );
		}
		$this->add_action( 'defenderATMaskLogin', array( &$this, 'renderIndex' ) );
		$this->add_ajax_action( 'saveATMaskLoginSettings', 'saveSettings' );
		$settings        = Mask_Settings::instance();
		$emergencySwitch = apply_filters( 'wpd_masklogin_disabled', 0 );
		if ( $settings->isEnabled() == true && $emergencySwitch == 0 ) {
			$isJetpackSSO = Auth_API::isJetPackSSO();
			$isTML        = Auth_API::isTML();
			if ( ! $isJetpackSSO && ! $isTML ) {
				$this->add_action( 'init', 'handleLoginRequest', 9999 );
				$this->add_filter( 'wp_redirect', 'filterWPRedirect', 10, 2 );
				$this->add_filter( 'site_url', 'filterSiteUrl', 9999, 4 );
				$this->add_filter( 'network_site_url', 'filterNetworkSiteUrl', 9999, 3 );
//				$this->add_filter( 'network_admin_url', 'filterAdminUrl', 9999, 2 );
//				$this->add_filter( 'admin_url', 'filterAdminUrl', 9999, 2 );
				remove_action( 'template_redirect', 'wp_redirect_admin_locations' );
				//if prosite is activate and useremail is not defined, we need to update the
				//email to match the new login URL
				$this->add_filter( 'update_welcome_email', 'updateWelcomeEmailPrositeCase', 9999, 6 );
			} else {
				if ( $isJetpackSSO ) {
					wp_defender()->global['compatibility'][] = __( "We’ve detected a conflict with Jetpack’s Wordpress.com Log In feature. Please disable it and return to this page to continue setup.", wp_defender()->domain );
				}
				if ( $isTML ) {
					wp_defender()->global['compatibility'][] = __( "We’ve detected a conflict with Theme my login. Please disable it and return to this page to continue setup.", wp_defender()->domain );
				}
			}
		}
	}

	public function handleLoginRequest() {
		//need to check if the current request is for signup, login, if those is not the slug, then we redirect
		//to the 404 redirect, or 403 wp die
		$requestPath = Mask_Api::getRequestPath();
		$settings    = Mask_Settings::instance();
		if ( '/' . ltrim( $settings->maskUrl, '/' ) == $requestPath ) {
			//we need to redirect this one to wp-login and open it
			$this->_showLoginPage();
		} elseif ( substr( $requestPath, 0, 9 ) == '/wp-admin' ) {
			//this one try to login to wp-admin, redirect or lock it
			$this->_handleRequestToAdmin();
		} elseif ( $requestPath == '/wp-login.php' || $requestPath == '/login' ) {
			//this one want to login, redirect or lock
			$this->_handleRequestToLoginPage();
		}
	}

	/**
	 * @param $welcome_email
	 * @param $blog_id
	 * @param $user_id
	 * @param $password
	 * @param $title
	 * @param $meta
	 *
	 * @return mixed
	 */
	public function updateWelcomeEmailPrositeCase( $welcome_email, $blog_id, $user_id, $password, $title, $meta ) {
		$url = get_blogaddress_by_id( $blog_id );
		$welcome_email = str_replace( $url . 'wp-login.php', Mask_Api::getNewLoginUrl( rtrim( $url, '/' ) ), $welcome_email );

		return $welcome_email;
	}

	/**
	 * @param $url
	 * @param $path
	 * @param $scheme
	 *
	 * @return string
	 */
	public function filterNetworkSiteUrl( $url, $path, $scheme ) {
		return $this->alterLoginUrl( $url, $scheme );
	}

	/**
	 * @param $url
	 * @param $path
	 * @param $scheme
	 * @param $blog_id
	 *
	 * @return string
	 */
	public function filterSiteUrl( $url, $path, $scheme, $blog_id ) {
		return $this->alterLoginUrl( $url, $scheme );
	}

	/**
	 * @param $location
	 * @param $status
	 *
	 * @return string
	 */
	public function filterWPRedirect( $location, $status ) {
		return $this->alterLoginUrl( $location );
	}

	/**
	 * @param $currentUrl
	 * @param null $scheme
	 *
	 * @return string
	 */
	private function alterLoginUrl( $currentUrl, $scheme = null ) {
		if ( stristr( $currentUrl, 'wp-login.php' ) !== false ) {
			//this is URL go to old wp-login.php
			$parts = parse_url( $currentUrl );
			if ( isset( $parts['query'] ) ) {
				parse_str( $parts['query'], $strings );

				return add_query_arg( $strings, Mask_Api::getNewLoginUrl() );
			} else {
				return Mask_Api::getNewLoginUrl();
			}
		} else {
			//this case when admin map a domain into subsite, we need to update the new domain/masked-login into the list
			if ( ! function_exists( 'get_current_screen' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/screen.php' );
			}
			$screen = get_current_screen();
			if ( ! is_object( $screen ) ) {
				return $currentUrl;
			}
			if ( $screen->id == 'sites-network' ) {
				//case URLs inside sites list, need to check those with custom domain cause when redirect, it will require re-loggin
				$requestPath = Mask_Api::getRequestPath( $currentUrl );
				if ( $requestPath == '/wp-admin' ) {
					$currentDomain = $_SERVER['HTTP_HOST'];
					$subDomain     = parse_url( $currentUrl, PHP_URL_HOST );
					if ( stristr( $subDomain, $currentDomain ) === false ) {
						return Mask_Api::getNewLoginUrl( $subDomain );
					}
				}
			} elseif ( $screen->id == 'my-sites' ) {
				//case inside my sites page, sometime the login session does not share between sites and we get block
				//we will add an OTP key for redirect to wp-admin without get block
				$otp = Mask_Api::createOTPKey();

				return add_query_arg( array(
					'otp' => $otp
				), $currentUrl );
			}
		}

		return $currentUrl;
	}

	/**
	 * Filter admin URL when sync with HUB
	 *
	 * @param $currentUrl
	 * @param null $scheme
	 *
	 * @return mixed
	 */
	public function filterAdminUrl( $currentUrl, $scheme = null ) {

		return $currentUrl;
	}

	/**
	 * Catch any request to wp-admin/*, block or redirect it base on settings.
	 * This wont apply for logged in user
	 */
	private function _handleRequestToAdmin() {
		global $pagenow;
		if ( defined( 'DOING_AJAX' ) ) {
			//we need to allow ajax access for other tasks
			return;
		}
		if ( is_user_logged_in() ) {
			return;
		}

		if ( ( $key = HTTP_Helper::retrieve_get( 'otp', false ) ) !== false
		     && Mask_Api::verifyOTP( $key ) ) {
			return;
		}

		$this->_maybeLock();
	}

	private function _handleRequestToLoginPage() {
		$this->_maybeLock();
	}

	private function _showLoginPage() {
		global $error, $interim_login, $action, $user_login, $user, $redirect_to;
		require_once ABSPATH . 'wp-login.php';
		die;
	}

	private function _maybeLock() {
		$settings = Mask_Settings::instance();
		if ( $settings->isRedirect() == true ) {
			wp_safe_redirect( Mask_Api::getRedirectUrl() );
			die;
		} else {
			wp_die( __( "This feature is disabled", wp_defender()->domain ) );
		}
	}

	public function renderIndex() {
		$settings = Mask_Settings::instance();
		if ( $settings->enabled == false ) {
			$this->render( 'mask-login/disabled', array(
				'settings' => $settings
			) );
		} else {
			$this->render( 'mask-login/enabled', array(
				'settings' => $settings
			) );
		}
	}

	public function saveSettings() {
		if ( ! $this->checkPermission() ) {
			return;
		}

		if ( ! wp_verify_nonce( HTTP_Helper::retrieve_post( '_wpnonce' ), 'saveATMaskLoginSettings' ) ) {
			return;
		}

		$data    = $_POST;
		$setting = Mask_Settings::instance();
		if ( isset( $data['maskUrl'] ) && $setting->maskUrl != $data['maskUrl']
		     && is_wp_error( $error = Mask_Api::isValidMaskSlug( $data['maskUrl'] ) ) ) {
			//validate
			$res = array(
				'message' => $error->get_error_message()
			);
			wp_send_json_error( $res );
		}
		if ( isset( $data['redirectTrafficUrl'] ) && $setting->redirectTrafficUrl != $data['redirectTrafficUrl']
		     && is_wp_error( $error = Mask_Api::isValidMaskSlug( $data['redirectTrafficUrl'], 'redirect' ) ) ) {
			//validate
			$res = array(
				'message' => $error->get_error_message()
			);
			wp_send_json_error( $res );
		}
		if ( ! empty( $data['redirectTrafficUrl'] ) && ! empty( $data['maskUrl'] ) && $data['redirectTrafficUrl'] === $data['maskUrl'] && strlen( $data['maskUrl'] ) > 0 ) {
			$res = array(
				'message' => __( "Login and 404 redirect URLs can't be the same. Please use different URLs.", wp_defender()->domain )
			);
			wp_send_json_error( $res );
		}
		$setting->import( $data );
		$setting->save();
		$res           = array(
			'message' => __( "Your settings have been updated.", wp_defender()->domain )
		);
		$res['reload'] = 1;
		Utils::instance()->submitStatsToDev();
		wp_send_json_success( $res );
	}

	/**
	 * Enqueue scripts & styles
	 */
	public function scripts() {
		if ( $this->isInPage() || $this->isDashboard() ) {
			wp_enqueue_script( 'defender' );
			wp_enqueue_style( 'defender' );
			wp_enqueue_script( 'adtools', wp_defender()->getPluginUrl() . 'app/module/advanced-tools/js/scripts.js' );
		}
	}
}

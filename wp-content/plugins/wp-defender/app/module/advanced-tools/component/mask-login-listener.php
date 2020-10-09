<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Advanced_Tools\Component;

use Hammer\Helper\HTTP_Helper;
use Hammer\WP\Component;
use WP_Defender\Module\Advanced_Tools\Model\Mask_Settings;
use WP_Defender\Module\Two_Factor\Component\Auth_API;

class Mask_Login_Listener extends Component {
	public function __construct() {
		$settings        = Mask_Settings::instance();
		$emergencySwitch = apply_filters( 'wpd_masklogin_disabled', 0 );

		if ( true === $settings->isEnabled() && 0 === $emergencySwitch ) {
			$isJetpackSSO = Auth_API::isJetPackSSO();
			$isTML        = Auth_API::isTML();
			if ( ! $isJetpackSSO && ! $isTML ) {
				$this->addAction( 'init', 'handleLoginRequest', 9999 );
				$this->addFilter( 'wp_redirect', 'filterWPRedirect', 10, 2 );
				$this->addFilter( 'site_url', 'filterSiteUrl', 9999, 4 );
				$this->addFilter( 'network_site_url', 'filterNetworkSiteUrl', 9999, 3 );
				$this->addFilter( 'wp_mail', 'filterEmailBody', 999 );
				//$this->addFilter( 'network_admin_url', 'filterAdminUrl', 9999, 2 );
				//$this->add_filter( 'adminUrl', 'filterAdminUrl', 9999, 2 );
				remove_action( 'template_redirect', 'wp_redirect_admin_locations' );
				//if prosite is activate and useremail is not defined, we need to update the
				//email to match the new login URL
				$this->addFilter( 'update_welcome_email', 'updateWelcomeEmailPrositeCase', 10, 6 );
				$this->addFilter( 'report_email_logs_link', 'updateReportLogsLink', 10, 2 );
			} else {
				if ( $isJetpackSSO ) {
					wp_defender()->global['compatibility'][] = __( "We've detected a conflict with Jetpack's Wordpress.com Log In feature. Please disable it and return to this page to continue setup.", wp_defender()->domain );
				}
				if ( $isTML ) {
					wp_defender()->global['compatibility'][] = __( "We've detected a conflict with Theme my login. Please disable it and return to this page to continue setup.", wp_defender()->domain );
				}
			}
		}
	}

	public function filterEmailBody( $args ) {
		$patterns = array(
			//approve comment
			'/(' . preg_quote( site_url(), '/' ) . '\/wp-admin\/comment\.php\?.+)/',
			//all comments
			'/(' . preg_quote( site_url(), '/' ) . '\/wp-admin\/edit-comments\.php\?.+$)/',
		);

		$message = $args['message'];
		foreach ( $patterns as $pattern ) {
			if ( preg_match_all( $pattern, $message, $matches ) ) {
				foreach ( $matches[0] as $url ) {
					parse_str( parse_url( $url, PHP_URL_QUERY ), $parts );
					if ( ! isset( $parts['ticket'] ) ) {
						$new_url = add_query_arg( 'ticket', Mask_Api::generateTicket(), $url );
						$message = str_replace( $url, $new_url, $message );
					}
				}
			}
		}
		$args['message'] = $message;

		return $args;
	}

	/**
	 * @param $logs_url
	 * @param $email
	 *
	 * @return string
	 */
	public function updateReportLogsLink( $logs_url, $email ) {
		$user = get_user_by( 'email', $email );
		if ( is_object( $user ) ) {
			$logs_url = Mask_Api::maybeAppendTicketToUrl( $logs_url );
		} else {
			$logs_url = add_query_arg( 'redirect_to', $logs_url, Mask_Api::getNewLoginUrl() );
		}

		return $logs_url;
	}

	public function handleLoginRequest() {
		$ticket = HTTP_Helper::retrieveGet( 'ticket', false );
		if ( false !== $ticket && Mask_Api::redeemTicket( $ticket ) ) {
			//we have an express ticket
			return true;
		}
		//need to check if the current request is for signup, login, if those is not the slug, then we redirect
		//to the 404 redirect, or 403 wp die
		$requestPath = Mask_Api::getRequestPath();
		$settings    = Mask_Settings::instance();
		if ( '/' . ltrim( $settings->mask_url, '/' ) === $requestPath ) {
			//we need to redirect this one to wp-login and open it
			$this->_showLoginPage();
		}
		if ( is_user_logged_in() ) {
			//do nothing
			return;
		}

		if ( defined( 'DOING_AJAX' ) ) {
			//we listen on normal requests, not ajax
			return;
		}
		$requestPath = ltrim( $requestPath, '/' );
		if ( ! $requestPath ) {
			return;
		}
		// decoded url path, e.g. for case 'wp-%61dmin'
		$requestPath = rawurldecode( strtolower( $requestPath ) );
		/**
		 * Cases:
		 * /wp-admin/admin.php
		 * /login/
		 * /wp-login.php
		 * /wp-signup.php and etc.
		 */
		$loginSlugs = apply_filters(
			'wd_login_slugs',
			array(
				'wp-admin',
				'wp-login',
				'wp-login.php',
				'login',
				'dashboard',
				'admin',
				'wp-signup.php',
			)
		);
		// check the request path contains default login text
		if ( in_array( $requestPath, $loginSlugs, true ) ) {
			return $this->_maybeLock();
		}
		// or the request path starts from 'wp-admin/' or 'login/'
		if (
			0 === strpos( $requestPath, 'wp-admin/' )
			|| 0 === strpos( $requestPath, 'login/' )
		) {
			return $this->_maybeLock();
		}
		// for case '/something/wp-login.php' or '/something/wp-login.php'
		if (
			preg_match( '/wp-login\.php/i', $requestPath )
			|| preg_match( '/wp-signup\.php/i', $requestPath )
		) {
			return $this->_maybeLock();
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
		$url           = get_blogaddress_by_id( $blog_id );
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
			if ( 'sites-network' === $screen->id ) {
				//case URLs inside sites list, need to check those with custom domain cause when redirect, it will require re-loggin
				$requestPath = Mask_Api::getRequestPath( $currentUrl );
				if ( '/wp-admin' === $requestPath ) {
					$currentDomain = $_SERVER['HTTP_HOST'];
					$subDomain     = parse_url( $currentUrl, PHP_URL_HOST );
					if ( ! empty( $subDomain ) && false === stristr( $subDomain, $currentDomain ) ) {
						return Mask_Api::getNewLoginUrl( $subDomain );
					}
				}
			} elseif ( 'my-sites' === $screen->id ) {
				//case inside my sites page, sometime the login session does not share between sites and we get block
				//we will add an OTP key for redirect to wp-admin without get block
				$otp = Mask_Api::createOTPKey();

				return add_query_arg(
					array(
						'otp' => $otp,
					),
					$currentUrl
				);
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
		//we just need to add a otp if not any
		parse_str( parse_url( $currentUrl, PHP_URL_QUERY ), $parts );
		if ( ! isset( $parts['ticket'] ) ) {

		}

		return $currentUrl;
	}

	private function _showLoginPage() {
		global $error, $interim_login, $action, $user_login, $user, $redirect_to;
		require_once ABSPATH . 'wp-login.php';
		die;
	}

	private function _maybeLock() {
		$settings = Mask_Settings::instance();
		if ( true === $settings->isRedirect() ) {
			wp_safe_redirect( Mask_Api::getRedirectUrl() );
			die;
		} else {
			wp_die( __( 'This feature is disabled', wp_defender()->domain ) );
		}
	}
}
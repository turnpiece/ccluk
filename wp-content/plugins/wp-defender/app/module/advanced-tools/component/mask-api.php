<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Advanced_Tools\Component;

use Hammer\Helper\HTTP_Helper;
use Hammer\WP\Component;
use WP_Defender\Behavior\Utils;
use WP_Defender\Component\Error_Code;
use WP_Defender\Module\Advanced_Tools\Model\Mask_Settings;

class Mask_Api extends Component {
	/**
	 * This will filter all the scheme, domain, params, only path return
	 *
	 * @param null $requestUri
	 *
	 * @return mixed|string
	 */

	public static function getRequestPath( $requestUri = null ) {
		if ( empty( $requestUri ) ) {
			$requestUri = $_SERVER['REQUEST_URI'];
		}
		//
		$requestUri = '/' . ltrim( $requestUri, '/' );
		$prefix = parse_url( self::site_url(), PHP_URL_PATH );;
		$requestPath = parse_url( $requestUri, PHP_URL_PATH );
		//clean it a bit
		if ( Utils::instance()->isActivatedSingle() == false
		     && defined( 'SUBDOMAIN_INSTALL' )
		     && constant( 'SUBDOMAIN_INSTALL' ) == false
		     && get_current_blog_id() != 1
		) {
			$prefix = parse_url( self::network_site_url(), PHP_URL_PATH );
			//get the prefix
			$siteInfo = get_blog_details();
			$path     = $siteInfo->path;
			if ( ! empty( $path ) && strpos( $requestPath, $path ) === 0 ) {
				$requestPath = substr( $requestPath, strlen( $path ) );
				$requestPath = '/' . ltrim( $requestPath, '/' );
			}
		}
		if ( strpos( $requestPath, $prefix ) === 0 ) {
			$requestPath = substr( $requestPath, strlen( $prefix ) );
		}
		$requestPath = untrailingslashit( $requestPath );
		if ( substr( $requestPath, 0, 1 ) != '/' ) {
			$requestPath = '/' . $requestPath;
		}

		return $requestPath;
	}

	/**
	 * A clone of network_site_url but remove the filter
	 *
	 * @param string $path
	 * @param null $scheme
	 *
	 * @return string
	 */
	private static function site_url( $path = '', $scheme = null ) {
		if ( empty( $blog_id ) || ! is_multisite() ) {
			$url = get_option( 'siteurl' );
		} else {
			switch_to_blog( $blog_id );
			$url = get_option( 'siteurl' );
			restore_current_blog();
		}

		$url = set_url_scheme( $url, $scheme );

		if ( $path && is_string( $path ) ) {
			$url .= '/' . ltrim( $path, '/' );
		}

		return $url;
	}

	/**
	 * A clone of network_site_url but remove the filter
	 *
	 * @param string $path
	 * @param null $scheme
	 *
	 * @return string
	 */
	private static function network_site_url( $path = '', $scheme = null ) {
		$current_network = get_network();

		if ( 'relative' == $scheme ) {
			$url = $current_network->path;
		} else {
			$url = set_url_scheme( 'http://' . $current_network->domain . $current_network->path, $scheme );
		}

		if ( $path && is_string( $path ) ) {
			$url .= ltrim( $path, '/' );
		}

		return $url;
	}

	/**
	 * @return string
	 */
	public static function getRedirectUrl() {
		$settings = Mask_Settings::instance();

		return untrailingslashit( network_site_url() ) . '/' . ltrim( $settings->redirectTrafficUrl, '/' );
	}

	/**
	 * @return string
	 */
	public static function getNewLoginUrl( $domain = null ) {
		$settings = Mask_Settings::instance();
		if ( $domain == null ) {
			$domain = site_url();
		}

		return untrailingslashit( $domain . '/' . ltrim( $settings->maskUrl, '/' ) );
	}

	/**
	 * @param null $slug
	 *
	 * @return bool|\WP_Error
	 */
	public static function isValidMaskSlug( $slug = null, $context = 'login' ) {
		if ( empty( $slug ) ) {
			return true;
		}

		if ( $context == 'redirect' && $slug == '/' ) {
			//redirect to home
			return true;
		}
		//validate slug, only allow a-z,0-9 and -
		if ( preg_match( '|[^a-z0-9-]|i', $slug ) ) {
			return new \WP_Error( Error_Code::VALIDATE, __( "The URL is invalid", wp_defender()->domain ) );
		}
		//if context is login, we will check for exists page
		if ( $context == 'login' ) {
			if ( in_array( $slug, array( 'admin', 'backend', 'wp-login', 'wp-login.php' ) ) ) {
				return new \WP_Error( Error_Code::VALIDATE, __( "A page already exists at this URL, please pick a unique page for your new login area.", wp_defender()->domain ) );
			}

			//check if any URL appear
			$post = get_posts( array(
				'name'        => $slug,
				'post_type'   => array( 'post', 'page' ),
				'post_status' => 'publish',
				'numberposts' => 1
			) );
			if ( $post ) {
				return new \WP_Error( Error_Code::VALIDATE, __( "A page already exists at this URL, please pick a unique page for your new login area.", wp_defender()->domain ) );
			}
		}

		return true;
	}

	/**
	 * @return null|string
	 */
	public static function createOTPKey() {
		if ( ! is_user_logged_in() ) {
			return null;
		}

		$secret = Auth_API::getUserSecret();
		$otp    = uniqid();
		$key    = md5( $otp . $secret );
		set_site_transient( $key, $otp, 300 );

		return $otp;
	}

	/**
	 * @param $otp
	 *
	 * @return bool
	 */
	public static function verifyOTP( $otp ) {
		$key    = HTTP_Helper::retrieve_get( 'otp' );
		$secret = Auth_API::getUserSecret();
		$key    = md5( $key . $secret );
		$check  = get_site_transient( $key );
		if ( $check == $otp ) {
			delete_site_transient( $key );

			return true;
		}

		return false;
	}
}
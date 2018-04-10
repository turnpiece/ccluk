<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Advanced_Tools\Component;

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
		//todo fix the case subfolder
		$prefix      = parse_url( network_site_url(), PHP_URL_PATH );
		$requestPath = parse_url( $requestUri, PHP_URL_PATH );
		//clean it a bit
		if ( Utils::instance()->isActivatedSingle() == false
		     && defined( 'SUBDOMAIN_INSTALL' )
		     && constant( 'SUBDOMAIN_INSTALL' ) == false
		     && get_current_blog_id() != 1
		) {
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
	 * @return string
	 */
	public static function getRedirectUrl() {
		$settings = Mask_Settings::instance();

		return untrailingslashit( network_site_url() ) . '/' . ltrim( $settings->redirectTrafficUrl, '/' );
	}

	/**
	 * @return string
	 */
	public static function getNewLoginUrl() {
		$settings = Mask_Settings::instance();

		return untrailingslashit( site_url() ) . '/' . ltrim( $settings->maskUrl, '/' );
	}

	/**
	 * @param null $slug
	 *
	 * @return bool|\WP_Error
	 */
	public static function isValidMaskSlug( $slug = null ) {
		if ( empty( $slug ) ) {
			return true;
		}
		if ( preg_match( '|[^a-z0-9_]|i', $slug ) ) {
			return new \WP_Error( Error_Code::VALIDATE, __( "The URL is invalid", wp_defender()->domain ) );
		}
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

		return true;
	}
}
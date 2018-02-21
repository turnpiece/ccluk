<?php
/**
 * @author: WPMUDEV, Ignacio Cruz (igmoweb)
 * @version:
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Hummingbird_API_Request_Minify extends WP_Hummingbird_API_Request {

	public function get_api_key() {
		global $wpmudev_un;

		if ( ! is_object( $wpmudev_un )  && class_exists( 'WPMUDEV_Dashboard' ) && method_exists( 'WPMUDEV_Dashboard', 'instance' ) ) {
			$wpmudev_un = WPMUDEV_Dashboard::instance();
		}

		if ( defined( 'WPHB_API_KEY' ) ) {
			$api_key = WPHB_API_KEY;
		} elseif ( is_object( $wpmudev_un ) && method_exists( $wpmudev_un, 'get_apikey' ) ) {
			$api_key = $wpmudev_un->get_apikey();
		} elseif ( class_exists( 'WPMUDEV_Dashboard' ) && is_object( WPMUDEV_Dashboard::$api ) && method_exists( WPMUDEV_Dashboard::$api, 'get_key' ) ) {
			$api_key = WPMUDEV_Dashboard::$api->get_key();
		} else {
			$api_key = '';
		}

		return $api_key;
	}

	public function get_api_url( $path = '' ) {
		$url = 'https://m9gnuc7j4d.execute-api.us-east-1.amazonaws.com/hummingbird/';
		return trailingslashit( $url . $path );

	}

	protected function sign_request() {
		if ( $this->get_api_key() ) {
			$this->add_header_argument( 'Authorization', 'Basic ' . $this->get_api_key() );
		}
	}

	/**
	 * Get the current Site URL
	 *
	 * @return string
	 */
	public function get_this_site() {
		if ( defined( 'WPHB_API_DOMAIN' ) ) {
			$domain = WPHB_API_DOMAIN;
		} else {
			$domain = network_site_url();
		}

		return $domain;
	}

}
<?php
/**
 * Provides connection to WPMU API to perform queries agains performance endpoint.
 *
 * @package Hummingbird
 */

/**
 * Class WP_Hummingbird_API_Service_Performance extends WP_Hummingbird_API_Service.
 */
class WP_Hummingbird_API_Service_Performance extends WP_Hummingbird_API_Service {

	/**
	 * Endpoint name.
	 *
	 * @var string $name
	 */
	public $name = 'performance';

	/**
	 * API version.
	 *
	 * @access private
	 * @var    string $version
	 */
	private $version = 'v1';

	/**
	 * WP_Hummingbird_API_Service_Performance constructor.
	 */
	public function __construct() {
		$this->request = new WP_Hummingbird_API_Request_WPMUDEV( $this );
	}

	/**
	 * Getter method for api version.
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Check if performance test has finished on server.
	 *
	 * @return array|mixed|object|WP_Error
	 */
	public function check() {
		return $this->request->post( 'site/check/', array(
			'domain' => $this->request->get_this_site(),
		) );
	}

	/**
	 * Ping to Performance Module so it starts to gather data.
	 *
	 * @return array|mixed|object|WP_Error
	 */
	public function ping() {
		$this->request->set_timeout( 0.1 );
		return $this->request->post( 'site/check/', array(
			'domain' => $this->request->get_this_site(),
		));
	}

	/**
	 * Get the latest performance test results.
	 *
	 * @return array|mixed|object|WP_Error
	 */
	public function results() {
		return $this->request->get( 'site/result/latest/', array(
			'domain' => $this->request->get_this_site(),
		));
	}

	/**
	 * Test if GZIP is enabled.
	 *
	 * @since 1.6.0
	 * @return array|mixed|object|WP_Error
	 */
	public function check_gzip() {
		$domain = $this->request->get_this_site();

		$params = array(
			'html'       => $domain,
			'javascript' => wphb_plugin_url() . 'core/modules/dummy/dummy-js.js',
			'css'        => wphb_plugin_url() . 'core/modules/dummy/dummy-style.css',
		);

		return $this->request->post( 'test/gzip/', array(
			'domain' => $domain,
			'tests'  => wp_json_encode( $params ),
		));
	}

	/**
	 * Test if caching is enabled.
	 *
	 * @since 1.6.0
	 * @return array|mixed|object|WP_Error
	 */
	public function check_cache() {
		$dummy_url = wphb_plugin_url() . 'core/modules/dummy/';

		$params = array(
			'javascript' => $dummy_url . 'dummy-js.js',
			'css'        => $dummy_url . 'dummy-style.css',
			'media'      => $dummy_url . 'dummy-media.mp3',
			'images'     => $dummy_url . 'dummy-image.png',
		);

		return $this->request->post( 'test/cache/', array(
			'domain' => $this->request->get_this_site(),
			'tests'  => wp_json_encode( $params ),
		));
	}
}
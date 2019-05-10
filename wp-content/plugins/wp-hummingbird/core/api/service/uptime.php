<?php

/**
 * Class WP_Hummingbird_API_Service_Uptime
 */
class WP_Hummingbird_API_Service_Uptime extends WP_Hummingbird_API_Service {

	/**
	 * API module name.
	 *
	 * @var string $name
	 */
	protected $name = 'uptime';

	/**
	 * API version.
	 *
	 * @var string $version
	 */
	private $version = 'v1';

	/**
	 * WP_Hummingbird_API_Service_Uptime constructor.
	 *
	 * @throws WP_Hummingbird_API_Exception
	 */
	public function __construct() {
		$this->request = new WP_Hummingbird_API_Request_WPMUDEV( $this );
	}

	/**
	 * Get API version.
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Get Uptime data for a given segment of time
	 *
	 * @param string $time  day|week|month.
	 *
	 * @return mixed
	 */
	public function check( $time = 'day' ) {
		$this->request->set_timeout( 20 );
		return $this->request->get(
			'stats/' . $time,
			array(
				'domain' => $this->request->get_this_site(),
			)
		);
	}

	/**
	 * Check if Uptime is enabled remotely
	 *
	 * @return mixed|WP_Error
	 */
	public function is_enabled() {
		$this->request->set_timeout( 30 );
		$results = $this->request->get(
			'stats/week/',
			array(
				'domain' => $this->request->get_this_site(),
			)
		);

		if ( is_wp_error( $results ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Enable Uptime remotely
	 *
	 * @return mixed|WP_Error
	 */
	public function enable() {
		$this->request->set_timeout( 30 );
		$results = $this->request->post(
			'monitoring',
			array(
				'domain' => $this->request->get_this_site(),
			)
		);

		if ( true !== $results ) {
			if ( is_wp_error( $results ) ) {
				return $results;
			}

			if ( isset( $results->code ) && isset( $results->message ) ) {
				return new WP_Error( 500, $results->message );
			}

			return new WP_Error( 500, __( 'Unknown Error', 'wphb' ) );
		}

		return $results;
	}

	/**
	 * Disable Uptime remotely
	 *
	 * @return mixed|WP_Error
	 */
	public function disable() {
		$this->request->set_timeout( 30 );
		$results = $this->request->delete(
			'monitoring',
			array(
				'domain' => $this->request->get_this_site(),
			)
		);

		if ( true !== $results ) {
			return new WP_Error( 500, __( 'Unknown Error', 'wphb' ) );
		}

		return $results;
	}
}

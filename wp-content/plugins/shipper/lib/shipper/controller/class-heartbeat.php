<?php
/**
 * Shipper controllers: heartbeat abstraction
 *
 * All heartbeat implementations will inherit from this.
 *
 * @package shipper
 */

/**
 * Heartbeat abstraction class
 */
abstract class Shipper_Controller_Heartbeat extends Shipper_Controller {

	/**
	 * Process Heartbeat API request
	 *
	 * Used for progress page updates.
	 *
	 * @param array $response Heartbeat response data to pass back to front end.
	 * @param array $data Data received from the front end (unslashed).
	 *
	 * @return array Response
	 */
	abstract public function heartbeat( $response, $data );

	/**
	 * Boots the runner and binds hook listeners
	 */
	public function boot() {
		add_filter( 'heartbeat_received', array( $this, 'heartbeat' ), 10, 2 );

		if ( Shipper_Model_Env::is_auth_requiring_env() ) {
			// Allow heartbeat API on WPEngine.
			add_filter( 'wpe_heartbeat_allowed_pages', array( $this, 'allow_shipper_page' ) );
		}

		add_action( 'shipper_runner_ping', array( $this, 'on_ping' ) );
		add_action( 'shipper_runner_pre_request_tick', array( $this, 'on_pre_request_tick' ) );
	}

	/**
	 * Log action
	 *
	 * @since 1.2.6
	 */
	public function on_ping() {
		$ping = new Shipper_Model_Stored_Ping();
		$ping->log_action();
	}

	/**
	 * Clear registered action on ajax request
	 *
	 * @since 1.2.6
	 */
	public function on_pre_request_tick() {
		$ping = new Shipper_Model_Stored_Ping();
		$ping->clear_action();
	}

	/**
	 * Force-allow heartbeat for Shipper page on WP Engine sites.
	 *
	 * Naive approach, by forcing heartbeat API on all admin.php pages
	 *
	 * @param array $pages Allowed pages.
	 *
	 * @return array
	 */
	public function allow_shipper_page( $pages = array() ) {
		if ( ! is_array( $pages ) ) {
			$pages = array();
		}
		$pages[] = 'admin.php';
		return $pages;
	}

}
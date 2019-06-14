<?php
/**
 * Shipper stubs: destinations API stub
 *
 * Used in local testing.
 * Sets up destinations handling API stub
 *
 * @package shipper
 */

/**
 * Destinations API stub class
 */
class Shipper_Stub_Api_Destinations extends Shipper_Stub_Api {

	/**
	 * Boots and sets up the stub controller
	 */
	public function boot() {
		add_action( $this->get_api( 'destinations-get' ), array( $this, 'json_destinations_get' ) );
		add_action( $this->get_api( 'destinations-add' ), array( $this, 'json_destination_add' ) );

		add_action( $this->get_api( 'destinations-ping' ), array( $this, 'json_destination_ping' ) );
		add_action( $this->get_api_nopriv( 'destinations-ping' ), array( $this, 'json_destination_ping' ) );
	}

	/**
	 * Stubs get destination ping call
	 */
	public function json_destination_ping() {
		// @codingStandardsIgnoreLine Is a stub
		$data = stripslashes_deep( $_GET );
		$domain = ! empty( $data['domain'] )
			? $data['domain']
			: false
		;

		if ( empty( $domain ) ) {
			return wp_send_json_error( 'Invalid API call, missing domain info' );
		}

		$known = wp_list_pluck( Shipper_Stub_Main::get()->get_known_domains(), 'domain' );
		if ( ! in_array( $domain, $known, true ) ) {
			return wp_send_json_error( 'Invalid API call, you can not access this domain info' );
		}

		// Can't do import/export on one site at the same time.
		return wp_send_json_error();
	}

	/**
	 * Get known destinations list
	 */
	public function json_destinations_get() {
		$destinations = Shipper_Stub_Main::get()->get_known_domains();
		return wp_send_json_success( $destinations );
	}

	/**
	 * Add a destination to a list of known destinations
	 */
	public function json_destination_add() {
		// @codingStandardsIgnoreLine Is a stub
		$data = stripslashes_deep( $_POST );
		$url = ! empty( $data['domain'] ) ? $data['domain'] : false;

		return ! empty( $url ) && Shipper_Model_Stored_Destinations::get_current_domain() === $url
			? wp_send_json_success()
			: wp_send_json_error();
	}
}

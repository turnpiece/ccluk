<?php
/**
 * AJAX Shipper controllers: Hub controller class
 *
 * @package shipper
 */

/**
 * Hub AJAX controller class
 */
class Shipper_Controller_Ajax_Hub extends Shipper_Controller_Ajax {

	/**
	 * Boots the controller and sets up event listeners.
	 */
	public function boot() {
		if ( ! is_admin() ) { return false; }

		add_action( 'wp_ajax_shipper_check_connection', array( $this, 'json_check_connection' ) );
		add_action( 'wp_ajax_shipper_list_hub_sites', array( $this, 'json_list_hub_sites' ) );
		add_action( 'wp_ajax_shipper_prepare_hub_site', array( $this, 'json_prepare_hub_site' ) );
		add_action( 'wp_ajax_shipper_clear_cache', array( $this, 'json_clear_cache' ) );

		add_action(
			'wp_ajax_shipper_remove_destination',
			array( $this, 'json_remove_destination' )
		);
	}

	/**
	 * Removes a site from the destinations list
	 *
	 * As a side-effect, also clears the destinations cache.
	 */
	public function json_remove_destination() {
		$this->do_request_sanity_check();

		$destinations = new Shipper_Model_Stored_Destinations;
		$destinations->clear()->save();

		$site_id = ! empty( $_POST['site_id'] )
			? (int) sanitize_text_field( $_POST['site_id'] )
			: false;

		if ( ! empty( $site_id ) ) {
			$task = new Shipper_Task_Api_Destinations_Remove;
			$task->apply( array( 'site_id' => $site_id ) );
		}


		return wp_send_json_success();
	}

	/**
	 * Checks the Hub connection
	 *
	 * Basically, checks to see if there are differences between
	 * what we already have destinations-wise and the Hub.
	 */
	public function json_check_connection() {
		$this->do_request_sanity_check();
		$destinations = new Shipper_Model_Stored_Destinations;
		$stored = $destinations->get_data();

		$task = new Shipper_Task_Api_Destinations_Get;
		$hub = $task->apply();

		return (empty( $hub ) || count( $hub ) <= count( $stored ))
			? wp_send_json_error()
			: wp_send_json_success();
	}

	/**
	 * Lists Hub-connected sites
	 */
	public function json_list_hub_sites() {
		$this->do_request_sanity_check( 'shipper_list_hub_sites', self::TYPE_POST );

		$task = new Shipper_Task_Api_Destinations_Hublist;
		$list = $task->apply();

		if ( ! empty( $list ) ) {
			$destinations = new Shipper_Model_Stored_Destinations;
			$current = $destinations->get_current();

			if ( ! empty( $current['site_id'] ) && ! empty( $list[ $current['site_id'] ] ) ) {
				// Remove current from the list.
				unset( $list[ $current['site_id'] ] );
			}

			return wp_send_json_success( $list );
		}

		return wp_send_json_error();
	}

	/**
	 * Prepare a Hub-connected site for migration
	 */
	public function json_prepare_hub_site() {
		$this->do_request_sanity_check( 'shipper_prepare_hub_site', self::TYPE_POST );

		$site = sanitize_text_field( @$_POST['site'] );

		$task = new Shipper_Task_Api_Destinations_Hubprepare;
		$result = $task->apply( array( $site ) );
		if ( ! empty( $result ) ) {
			// Also clear cache, we want to re-populate.
			return $this->json_clear_cache();
		}

		// OK, so install failed. How about triggering add action?
		// Scenario: plugin already installed, but user didn't visit the main page.
		$task = new Shipper_Task_Api_Destinations_Remoteadd;
		$result = $task->apply( array( $site ) );
		if ( ! empty( $result ) ) {
			// Also clear cache, we want to re-populate.
			return $this->json_clear_cache();
		}

		return wp_send_json_error();
	}

	/**
	 * Clears Hub domains cache
	 */
	public function json_clear_cache() {
		$this->do_request_sanity_check();
		$destinations = new Shipper_Model_Stored_Destinations;
		$destinations->clear();

		return $destinations->save()
			? wp_send_json_success()
			: wp_send_json_error();
	}

}

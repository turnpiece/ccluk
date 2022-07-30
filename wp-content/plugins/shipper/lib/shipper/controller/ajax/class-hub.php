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
		if ( ! is_admin() ) {
			return false;
		}

		add_action( 'wp_ajax_shipper_check_connection', array( $this, 'json_check_connection' ) );
		add_action( 'wp_ajax_shipper_list_hub_sites', array( $this, 'json_list_hub_sites' ) );
		add_action( 'wp_ajax_shipper_prepare_hub_site', array( $this, 'json_prepare_hub_site' ) );
		add_action( 'wp_ajax_shipper_clear_cache', array( $this, 'json_clear_cache' ) );

		add_action( 'wp_ajax_shipper_is_shippable', array( $this, 'json_is_shippable' ) );
		add_action( 'wp_ajax_shipper_install_activate', array( $this, 'json_install_activate' ) );
		add_action( 'wp_ajax_shipper_add_to_api', array( $this, 'json_add_to_api' ) );

		add_action( 'wp_ajax_shipper_confirm_wpmudev_password', array( $this, 'json_confirm_password' ) );

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

		$destinations = new Shipper_Model_Stored_Destinations();
		$destinations->clear()->save();

		$post    = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- already checked
		$site_id = ! empty( $post['site_id'] )
			? (int) sanitize_text_field( $post['site_id'] )
			: false;

		if ( ! empty( $site_id ) ) {
			$task = new Shipper_Task_Api_Destinations_Remove();
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
		$destinations = new Shipper_Model_Stored_Destinations();
		$stored       = $destinations->get_data();

		$task = new Shipper_Task_Api_Destinations_Get();
		$hub  = $task->apply();

		return ( empty( $hub ) || count( $hub ) <= count( $stored ) )
			? wp_send_json_error()
			: wp_send_json_success();
	}

	/**
	 * Lists Hub-connected sites
	 */
	public function json_list_hub_sites() {
		$this->do_request_sanity_check( 'shipper_list_hub_sites', self::TYPE_POST );

		$task  = new Shipper_Task_Api_Destinations_Hublist();
		$list  = $task->apply();
		$model = false;

		if ( empty( $list ) ) {
			$list = $task->apply( array( 'priority_cache' => false ) );
		}

		if ( empty( $list ) ) {
			// No response, let's use cached list.
			$model = new Shipper_Model_Stored_Hublist();
			$list  = $model->get_data();
		}

		if ( ! empty( $list ) ) {
			$destinations = new Shipper_Model_Stored_Destinations();
			$current      = $destinations->get_current();
			if ( ! empty( $current['site_id'] ) && ! empty( $list[ $current['site_id'] ] ) ) {
				// Remove current from the list.
				unset( $list[ $current['site_id'] ] );
			}

			if ( empty( $model ) ) {
				// Empty model, we're not working with cached list.
				// So, update the cache while the going is good.
				$model = new Shipper_Model_Stored_Hublist();
				$model->set_data( $list )->save();
			}

			return wp_send_json_success( $list );
		}

		return wp_send_json_error(
			array(
				'msg' => __( 'There has been an error listing your Hub sites, please try again later', 'shipper' ),
			)
		);
	}

	/**
	 * Prepare a Hub-connected site for migration
	 *
	 * @deprecated v1.0.3
	 */
	public function json_prepare_hub_site() {
		$this->do_request_sanity_check( 'shipper_prepare_hub_site', self::TYPE_POST );

		$post = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- already tested
		$site = ! empty( $post['site'] ) ?
			sanitize_text_field( $post['site'] )
			: '';

		$task   = new Shipper_Task_Api_Destinations_Hubprepare();
		$result = $task->apply( array( $site ) );
		if ( ! empty( $result ) ) {
			// Also clear cache, we want to re-populate.
			return $this->json_clear_cache();
		}

		// OK, so install failed. How about triggering add action?
		// Scenario: plugin already installed, but user didn't visit the main page.
		$task   = new Shipper_Task_Api_Destinations_Remoteadd();
		$result = $task->apply( array( $site ) );
		if ( ! empty( $result ) ) {
			// Also clear cache, we want to re-populate.
			return $this->json_clear_cache();
		}

		return wp_send_json_error();
	}

	/**
	 * Check if the remote site is already present in Shipper API
	 *
	 * If it is, we already have Shipper installed there.
	 * We don't have to try and install the plugin.
	 *
	 * @uses Shipper_Controller_Admin
	 * @since v1.0.3
	 */
	public function json_is_shippable() {
		$this->do_request_sanity_check( 'shipper_prepare_hub_site', self::TYPE_POST );

		$this->clear_destinations_cache();
		Shipper_Controller_Admin::get()->update_destinations_cache();

		$post         = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- already tested
		$site         = ! empty( $post['site'] ) ? sanitize_text_field( $post['site'] ) : '';
		$destinations = new Shipper_Model_Stored_Destinations();
		$data         = $destinations->get_by_site_id( $site );

		return ! empty( $data['domain'] )
			? wp_send_json_success()
			: wp_send_json_error();
	}

	/**
	 * Install and activate Shipper on remote site
	 *
	 * @since v1.0.3
	 */
	public function json_install_activate() {
		$this->do_request_sanity_check( 'shipper_prepare_hub_site', self::TYPE_POST );

		$post = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- already tested
		$site = ! empty( $post['domain'] ) && trim( sanitize_text_field( $post['domain'] ) );

		$task   = new Shipper_Task_Api_Destinations_Hubprepare();
		$result = $task->apply( array( $site ) );
		if ( ! empty( $result ) ) {
			// Also clear cache, we want to re-populate.
			return $this->json_clear_cache();
		}

		return wp_send_json_error();
	}

	/**
	 * Add remote site to the API
	 *
	 * @uses Shipper_Controller_Admin
	 * @since v1.0.3
	 */
	public function json_add_to_api() {
		$this->do_request_sanity_check( 'shipper_prepare_hub_site', self::TYPE_POST );

		$post = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- already tested.
		$site = ! empty( $post['domain'] ) && trim( sanitize_text_field( $post['domain'] ) );

		$task   = new Shipper_Task_Api_Destinations_Remoteadd();
		$result = $task->apply( array( $site ) );
		if ( ! empty( $result ) ) {
			$this->reset_api_fails();
			$this->clear_destinations_cache();
			Shipper_Controller_Admin::get()->update_destinations_cache();

			return wp_send_json_success();
		}

		return wp_send_json_error();
	}

	/**
	 * Clears Hub domains cache
	 */
	public function json_clear_cache() {
		$this->do_request_sanity_check();

		return $this->clear_destinations_cache()
			? wp_send_json_success()
			: wp_send_json_error();
	}

	/**
	 * Resets API failures
	 *
	 * @uses Shipper_Model_Api
	 * @since v1.0.3
	 */
	public function reset_api_fails() {
		$model = new Shipper_Model_Api();
		$model->reset_api_fails();
	}

	/**
	 * Clears destination caches
	 *
	 * @return bool
	 * @uses Shipper_Model_Api
	 * @since v1.0.3
	 *
	 * @uses Shipper_Model_Stored_Destinations
	 */
	public function clear_destinations_cache() {
		$destinations = new Shipper_Model_Stored_Destinations();
		$destinations->clear();

		$model = new Shipper_Model_Api();
		$model->clear_cached_api_response( 'destinations-get' );
		// have to wipe it out so the screen for network not conflict.
		$model->clear_cached_api_response( 'info-get' );

		return $destinations->save();
	}

	/**
	 * Confirm wpmu dev password
	 *
	 * @since 1.1.4
	 *
	 * @return void
	 */
	public function json_confirm_password() {
		$this->do_request_sanity_check( 'shipper_confirm_wpmudev_password', self::TYPE_POST );

		$task = new Shipper_Task_Api_Authentication_Check();

		if ( $task->apply( wp_unslash( $_POST ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- already tested.
			wp_send_json_success();
		}

		wp_send_json_error();
	}
}
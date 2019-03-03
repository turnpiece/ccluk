<?php
/**
 * Shipper stubs: info API stub
 *
 * Used in local testing.
 * Sets up info handling API stub
 *
 * @package shipper
 */

/**
 * Destinations API stub class
 */
class Shipper_Stub_Api_Info extends Shipper_Stub_Api {

	/**
	 * Boots and sets up the stub controller
	 */
	public function boot() {
		add_action( $this->get_api( 'info-get' ), array( $this, 'json_get_info' ) );
		add_action( $this->get_api( 'info-set' ), array( $this, 'json_set_info' ) );
	}

	/**
	 * Stubs get destination info call
	 */
	public function json_get_info() {
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

		// Actually stub the model info!
		$data = get_site_option( sprintf( 'shipper-info-%s', $domain ), array() );
		if ( empty( $data ) ) {
			return wp_send_json_error( 'No data, please re-run system check task on the target system' );
		}

		$model = new Shipper_Model_System;
		foreach ( $data as $mdl => $info ) {
			$model->get( $mdl )->set_data( $info );
		}
		$model->get( Shipper_Model_System::INFO_PHP )->set( 'version_major', 7 );
		if ( 'example.com' === $domain ) {
			$model->get( Shipper_Model_System::INFO_WP )->set( 'MULTISITE', 0 );
		}

		return wp_send_json_success( $model->get_data() );
	}

	/**
	 * Stubs set destination info call
	 */
	public function json_set_info() {
		// @codingStandardsIgnoreLine Is a stub
		$data = stripslashes_deep( $_POST );
		$domain = ! empty( $data['dst'] )
			? $data['dst']
			: false
		;

		if ( empty( $domain ) ) {
			return wp_send_json_error( 'Invalid API call, missing domain info' );
		}

		$known = wp_list_pluck( Shipper_Stub_Main::get()->get_known_domains(), 'domain' );
		if ( ! in_array( $domain, $known, true ) ) {
			return wp_send_json_error( 'Invalid API call, you can not access this domain info' );
		}

		$info = ! empty( $data['info'] )
			? json_decode( $data['info'], true )
			: array()
		;
		if ( empty( $info ) ) {
			return wp_send_json_error( 'Invalid API call, missing arguments: info' );
		}

		update_site_option( sprintf( 'shipper-info-%s', $domain ), $info );

		return wp_send_json_success();
	}
}
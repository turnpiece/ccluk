<?php
/**
 * Shipper stubs: migrations API stub
 *
 * Used in local testing.
 * Stubs the migrations API for a given domain.
 *
 * @package shipper
 */

/**
 * Migrations API stub class
 */
class Shipper_Stub_Api_Migrations extends Shipper_Stub_Api {

	/**
	 * Boots and sets up the stub controller
	 */
	public function boot() {
		add_action( $this->get_api( 'migration-get' ), array( $this, 'json_get_migration_status' ) );
		add_action( $this->get_api_nopriv( 'migration-get' ), array( $this, 'json_get_migration_status' ) );

		add_action( $this->get_api( 'migration-set' ), array( $this, 'json_set_migration_status' ) );
		add_action( $this->get_api_nopriv( 'migration-set' ), array( $this, 'json_set_migration_status' ) );

		add_action( $this->get_api( 'migration-start' ), array( $this, 'json_migration_start' ) );
		add_action( $this->get_api_nopriv( 'migration-start' ), array( $this, 'json_migration_start' ) );
	}

	/**
	 * Starts migration process remotely
	 */
	public function json_migration_start() {
		// @codingStandardsIgnoreLine Is a stub
		$data = stripslashes_deep( $_POST );
		$status = $this->get_data_verification_status( $data );
		if ( is_wp_error( $status ) ) {
			return wp_send_json_error( $status->get_error_message() );
		}
		$ctrl = Shipper_Controller_Hub::get();
		return $ctrl->json_migration_start(
			(object) $data,
			Shipper_Controller_Hub::ACTION_MIGRATION_START
		);
	}

	/**
	 * Gets current migration status
	 */
	public function json_get_migration_status() {
		$data = stripslashes_deep( $_GET );
		$status = $this->get_data_verification_status( $data );
		if ( is_wp_error( $status ) ) {
			return wp_send_json_error( $status->get_error_message() );
		}

		$migration = array(
			'file' => '',
			'status' => 0,
		);
		$stored = get_site_option( sprintf( 'shipper-migration-status-%s', $data['domain'] ), array() );
		if ( ! empty( $stored ) ) {
			$migration = $stored;
		}

		return wp_send_json_success( $migration );
	}

	/**
	 * Sets current migration status
	 */
	public function json_set_migration_status() {
		// @codingStandardsIgnoreLine Is a stub
		$data = stripslashes_deep( $_POST );
		$status = $this->get_data_verification_status( $data );
		if ( is_wp_error( $status ) ) {
			return wp_send_json_error( $status->get_error_message() );
		}

		$file = ! empty( $data['file'] )
			? 'present'
			: false
		;
		$status = ! empty( $data['status'] )
			? $data['status']
			: false
		;

		$model = new Shipper_Model_Stored_Migration;
		$domain = $model->get_destination();
		if ( empty( $domain ) ) { $domain = $data['domain']; }

		$migration = array(
			'file' => $file,
			'status' => (int) $status,
		);
		update_site_option( sprintf( 'shipper-migration-status-%s', $domain ), $migration );

		return wp_send_json_success( $migration );
	}


	/**
	 * Gets data verification status
	 *
	 * @param array $data Data array to verify.
	 *
	 * @return bool|WP_Error
	 */
	public function get_data_verification_status( $data = array() ) {
		$domain = ! empty( $data['domain'] )
			? $data['domain']
			: false
		;
		return $this->get_domain_verification_status( $domain );
	}

	/**
	 * Gets domain status verification
	 *
	 * @param string $domain Domain to verify.
	 *
	 * @return bool|WP_Error
	 */
	public function get_domain_verification_status( $domain = '' ) {
		$err = 'domain_verification';

		if ( empty( $domain ) ) {
			return new WP_Error( $err, 'Invalid API call, missing domain info' );
		}

		$known = wp_list_pluck( Shipper_Stub_Main::get()->get_known_domains(), 'domain' );
		if ( ! in_array( $domain, $known, true ) ) {
			return new WP_Error(
				$err,
				sprintf( 'Invalid API call, you can not access this domain info: %s', $domain )
			);
		}

		return true;
	}

}

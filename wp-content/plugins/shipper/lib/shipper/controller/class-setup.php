<?php
/**
 * Shipper controllers: setup controller abstraction
 *
 * Implementations handling plugin activation/deactivation/deletion inherit
 * from this.
 *
 * @package shipper
 */

/**
 * Setup class
 */
abstract class Shipper_Controller_Setup extends Shipper_Controller {

	/**
	 * Satisfy the interface
	 */
	public function boot() {
	}

	/**
	 * Deletes intermediate tables created by the mock-import process
	 *
	 * @uses $wpdb
	 *
	 * @return object Shipper_Controller_Setup instance
	 */
	public function clear_intermediate_tables() {
		global $wpdb;

		$intermediate = $wpdb->esc_like( Shipper_Task_Import::PREFIX ) . '%';

		$tables = $wpdb->get_col(
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $intermediate )
		); // db call ok, cache ok.

		if ( ! empty( $tables ) ) {
			// phpcs:disable
			$tables = array_filter( array_unique( array_values( $tables ) ) );
			$wpdb->query(
				'DROP TABLE IF EXISTS ' . join( ',', $tables )
			);
			// phpcs:enable
		}

		return $this;
	}

	/**
	 * Dispatches storage cleanup
	 *
	 * @return object Shipper_Controller_Setup instance
	 */
	public function clear_fs_storage() {
		$this->clear_storage_fs_transient();
		$this->clear_storage_fs_exposed();

		return $this;
	}

	/**
	 * Clears the transient filesystem storage
	 *
	 * This is the auto-generated storage, it is always cleared.
	 *
	 * @return object Shipper_Controller_Setup instance
	 */
	public function clear_storage_fs_transient() {
		$working = Shipper_Helper_Fs_Path::get_working_dir();

		Shipper_Helper_Fs_Path::rmdir_r( $working, '' );
		@rmdir( $working ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		return $this;
	}

	/**
	 * Clears user-exposed filesystem storage
	 *
	 * This is things like logs - it is cleared depending on settings.
	 *
	 * @param object $model Optional Shipper_Model_Stored_Options instance (used in tests).
	 *
	 * @return object Shipper_Controller_Setup instance
	 */
	public function clear_storage_fs_exposed( $model = false ) {
		if ( ! is_object( $model ) ) {
			$model = new Shipper_Model_Stored_Options();
		}
		if ( $model->get( Shipper_Model_Stored_Options::KEY_DATA ) ) {
			// Preserve exposed data - move on.
			return $this;
		}
		$log = Shipper_Helper_Fs_Path::get_log_dir();

		Shipper_Helper_Fs_Path::rmdir_r( $log, '' );
		@rmdir( $log ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		return $this;
	}

	/**
	 * Clears the storage used by stubs
	 *
	 * @uses $wpdb
	 *
	 * @return object Shipper_Controller_Setup instance
	 */
	public function clear_stub_storage() {
		global $wpdb;

		$info = $wpdb->esc_like( 'shipper-info-' ) . '%';
		$migr = $wpdb->esc_like( 'shipper-migration-' ) . '%';

		// phpcs:disable
		if ( ! empty( $wpdb->sitemeta ) ) {
			$wpdb->query(
				$wpdb->prepare( "DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE %s", $info )
			);
			$wpdb->query(
				$wpdb->prepare( "DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE %s", $migr )
			);
		}
		$wpdb->query(
			$wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $info )
		);
		$wpdb->query(
			$wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $migr )
		);
		// phpcs:enable

		return $this;
	}

}
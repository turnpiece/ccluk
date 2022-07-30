<?php
/**
 * Shipper controllers: uninstall setup controller
 *
 * Handles plugin uninstall.
 *
 * @package shipper
 */

/**
 * Setup uninstall class
 */
class Shipper_Controller_Setup_Uninstall extends Shipper_Controller_Setup {

	/**
	 * Runs on plugin uninstall
	 */
	public static function uninstall() {
		self::get()
			->clear_intermediate_tables()
			->clear_fs_storage()
			->clear_stub_storage();
		self::get()
			->clear_db_storage()
			->unregister_from_api();

		// Black Friday banner.
		delete_site_option( 'shipper_bf_banner_seen' );
	}

	/**
	 * Clears the DB storage
	 *
	 * @param object $model Optional Shipper_Model_Stored_Options instance (used in tests).
	 *
	 * @return object Shipper_Controller_Setup instance
	 * @uses $wpdb
	 */
	public function clear_db_storage( $model = false ) {
		global $wpdb;

		if ( ! is_object( $model ) ) {
			$model = new Shipper_Model_Stored_Options();
		}
		if ( $model->get( Shipper_Model_Stored_Options::KEY_SETTINGS ) ) {
			// Preserve settings.
			return $this;
		}

		$storage = $wpdb->esc_like( Shipper_Helper_Storage::DEFAULT_NAMESPACE ) . '%';

		// phpcs:disable
		if ( ! empty( $wpdb->sitemeta ) ) {
			$wpdb->query(
				$wpdb->prepare( "DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE %s", $storage )
			);
		}
		$wpdb->query(
			$wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $storage )
		);

		$wpdb->query(
			$wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name = %s", 'shipper_version' )
		);
		// phpcs:enable

		shipper_flush_cache();

		return $this;
	}

	/**
	 * Unregisters the current site from Shipper API
	 *
	 * @return object Shipper_Controller_Setup instance
	 */
	public function unregister_from_api() {
		$site_id = false;

		$model = new Shipper_Model_Stored_Destinations();
		$site  = $model->get_current();

		if ( empty( $site['site_id'] ) ) {
			// Attempt to re-acquire the data.
			$task  = new Shipper_Task_Api_Destinations_Get();
			$sites = $task->apply();
			$model->set_data( $sites );
			$site = $model->get_current();
			$model->clear(); // This is only temporary.
		}
		$site_id = ! empty( $site['site_id'] )
			? $site['site_id']
			: false;

		if ( empty( $site_id ) ) {
			return $this;
		}

		$task = new Shipper_Task_Api_Destinations_Remove();
		$task->apply(
			array(
				'site_id' => $site_id,
			)
		);

		return $this;
	}
}
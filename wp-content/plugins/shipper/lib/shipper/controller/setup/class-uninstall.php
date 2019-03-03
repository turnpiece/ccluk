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
		self::get()->clear_db_storage();
	}

	/**
	 * Clears the DB storage
	 *
	 * @uses $wpdb
	 *
	 * @param object $model Optional Shipper_Model_Stored_Options instance (used in tests).
	 *
	 * @return object Shipper_Controller_Setup instance
	 */
	public function clear_db_storage( $model = false ) {
		global $wpdb;

		if ( ! is_object( $model ) ) {
			$model = new Shipper_Model_Stored_Options;
		}
		if ( $model->get( Shipper_Model_Stored_Options::KEY_SETTINGS ) ) {
			// Preserve settings.
			return $this;
		}

		$storage = $wpdb->esc_like( Shipper_Helper_Storage::DEFAULT_NAMESPACE ) . '%';

		if ( ! empty( $wpdb->sitemeta ) ) {
			$wpdb->query(
				$wpdb->prepare( "DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE %s", $storage )
			);
		}
		$wpdb->query(
			$wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $storage )
		);

		shipper_flush_cache();

		return $this;
	}
}
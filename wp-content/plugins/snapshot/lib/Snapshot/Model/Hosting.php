<?php // phpcs:ignore

class Snapshot_Model_Hosting {

	/**
	 * Create a new model instance
	 *
	 */
	public function __construct() {
	}

	/**
	 * Gets model type
	 *
	 * Used in filtering implementation
	 *
	 * @return string Model type tag
	 */
	public function get_model_type() {
 		return 'hosting';
 	}

	/**
	 * Filter/action name getter
	 *
	 * @param string $filter Filter name to convert
	 *
	 * @return string Full filter name
	 */
	public function get_filter( $filter = false ) {
		if ( empty( $filter ) ) {
			return false;
		}
		if ( ! is_string( $filter ) ) {
			return false;
		}
		return 'snapshot-model-hosting-' . $this->get_model_type() . '-' . $filter;
	}

	/**
	 * Returns WPMUDEV's hosting site id.
	 *
	 * @return string Site ID.
	 */
	public function get_site_id() {
		return WPMUDEV_HOSTING_SITE_ID;
	}

	/**
	 * Checks if user is logged in the WPMUDEV Dashboard
	 *
	 * @return WP_Error|string Error if something went wrong, api key otherwise.
	 */
	public function active_dashboard_key() {
		if ( ! class_exists( 'WPMUDEV_Dashboard' ) ) {
			return new WP_Error(
				get_class(),
				'The WPMU DEV Dashboard plugin must be installed and activated.'
			);
		}
		$api_key = WPMUDEV_Dashboard::$api->get_key();
		if ( empty( $api_key ) ) {
			return new WP_Error(
				get_class(),
				'Please login to the WPMU DEV Dashboard plugin.'
			);
		}

		return $api_key;
	}

	/**
	 * Checks whether we're on WPMU DEV Hosting
	 *
	 * @return bool
	 */
	public function is_wpmu_hosting() {
		$is_wpmu_hosting = Snapshot_Helper_Utility::is_wpmu_hosting();
		return apply_filters(
			$this->get_filter( 'is_wpmu_hosting' ),
			$is_wpmu_hosting
		);
	}
}
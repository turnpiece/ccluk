<?php
/**
 * Hub API readiness check
 *
 * @package shipper
 */

/**
 * Hub API check class
 */
class Shipper_Task_Check_Hub extends Shipper_Task {

	const ERR_DASH_PRESENT = 'dash_present';
	const ERR_DASH_ACTIVE  = 'dash_active';
	const ERR_DASH_APIKEY  = 'dash_api_key';

	/**
	 * Checks whether we're overall Hub connection-ready
	 *
	 * @param array $args Not used.
	 *
	 * @return bool True if we are ready, false if one of pre-conditions failed (see errors).
	 */
	public function apply( $args = array() ) {
		if ( ! $this->has_dashboard_present() ) {
			$this->add_error( self::ERR_DASH_PRESENT );
			return false;
		}

		if ( ! $this->is_dashboard_active() ) {
			$this->add_error( self::ERR_DASH_ACTIVE );
			return false;
		}

		if ( ! $this->has_api_key() ) {
			$this->add_error( self::ERR_DASH_APIKEY );
			return false;
		}

		return true;
	}

	/**
	 * Checks whether we have WPMU DEV Dashboard plugin available (albeit possibly not installed)
	 *
	 * @return bool
	 */
	public function has_dashboard_present() {
		$present = false;

		// Do the faster check first.
		if ( ! $this->is_dashboard_active() ) {
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$plugins = get_plugins();
			if ( ! is_array( $plugins ) || empty( $plugins ) ) {
				$present = false;
			} else {
				$present = ! empty( $plugins['wpmudev-updates/update-notifications.php'] );
			}
		} else {
			$present = true;
		}

		/**
		 * Dashboard present check filter
		 *
		 * @param $result Whether the dash is present.
		 *
		 * @return bool
		 */
		return apply_filters(
			'shipper_checks_hub_dashboard_present',
			$present
		);
	}

	/**
	 * Checks if we have WPMU DEV Dashboard plugin installed
	 *
	 * @return bool
	 */
	public function is_dashboard_active() {
		$result = class_exists( 'WPMUDEV_Dashboard' );

		/**
		 * Dashboard active check filter
		 *
		 * @param $result Whether the dash is active.
		 *
		 * @return bool
		 */
		return apply_filters(
			'shipper_checks_hub_dashboard_active',
			$result
		);
	}

	/**
	 * Checks whether we have WPMU DEV API key present
	 *
	 * @return bool
	 */
	public function has_api_key() {
		$model = new Shipper_Model_Api();

		/**
		 * Dashboard API key presence check filter
		 *
		 * @param $result Whether the dash API key is present.
		 *
		 * @return bool
		 */
		return apply_filters(
			'shipper_checks_hub_dashboard_apikey',
			$model->get( 'api_key' )
		);
	}
}
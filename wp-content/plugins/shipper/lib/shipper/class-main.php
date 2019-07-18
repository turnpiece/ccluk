<?php
/**
 * Plugin main class and entry point.
 *
 * @package shipper
 */

/**
 * Main class
 */
class Shipper_Main extends Shipper_Helper_Singleton {

	/**
	 * Boots the controller and sets up event listeners.
	 */
	public function boot() {
		$controllers = array(
			'runner_preflight',
			'heartbeat_preflight',
			'runner_migration',
			'heartbeat_migration',
			'override_debug',
			'override_mocks',
			'override_paths',
			'override_remote',
			'override_tables',
			'override_timeouts',
			'admin',
			'admin_migrate',
			'admin_tools',
			'admin_settings',
			'ajax_admin',
			'ajax_hub',
			'ajax_migration',
			'ajax_preflight',
			'ajax_notifications',
			'ajax_permissions',
			'ajax_settings',
			'hub_migration',
			'hub_destination',
			'hub_util',
			'notifications',
			'updates',
			'data',
		);
		foreach ( $controllers as $ctrl ) {
			$cname = 'Shipper_Controller_' . ucfirst( $ctrl );
			if ( class_exists( $cname ) ) {
				$controller = call_user_func( array( $cname, 'get' ) );
				$controller->boot();
			}
		}
	}

}

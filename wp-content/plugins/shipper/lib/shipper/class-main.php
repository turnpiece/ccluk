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
			'override_migration_files',
			'override_migration_tables',
			'override_migration_advanced',
			'override_package_files',
			'override_package_tables',
			'override_package_advanced',
			'override_package_settings',
			'admin',
			'admin_dashboard',
			'admin_migrate',
			'admin_packages',
			'admin_tools',
			'admin_settings',
			'admin_tutorials',
			'ajax_admin',
			'ajax_hub',
			'ajax_migration',
			'ajax_meta',
			'ajax_preflight',
			'ajax_notifications',
			'ajax_permissions',
			'ajax_settings',
			'ajax_packages_meta',
			'ajax_packages_preflight',
			'ajax_packages_build',
			'ajax_dashboard',
			'hub_migration',
			'hub_destination',
			'hub_util',
			'notifications',
			'updates',
			'data',
			'wpcli',
		);

		foreach ( $controllers as $ctrl ) {
			$cname = 'Shipper_Controller_' . ucwords( $ctrl, '_' );
			if ( class_exists( $cname ) ) {
				$cname::get()->boot();
			}
		}
	}
}
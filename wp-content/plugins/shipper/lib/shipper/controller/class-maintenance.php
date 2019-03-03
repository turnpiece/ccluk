<?php
/**
 * Shipper controllers: maintenance
 *
 * Responsible for optional firing up of maintenance mode during migration.
 *
 * @package shipper
 */

/**
 * Maintenance controller class
 */
class Shipper_Controller_Maintenance extends Shipper_Controller {

	public function boot() {
		if ( ! $this->is_maintenance_allowed() ) {
			return false;
		}

		add_action(
			'shipper_migration_start',
			array( $this, 'enable_maintenance_mode' )
		);

		add_action(
			'shipper_migration_complete',
			array( $this, 'disable_maintenance_mode' )
		);
		add_action(
			'shipper_migration_cancel',
			array( $this, 'disable_maintenance_mode' )
		);

		if ( $this->is_in_maintenance_mode() ) {
			add_action(
				'template_redirect',
				array( $this, 'maintenance_mode' )
			);
		}

		return true;
	}

	/**
	 * Checks whether we're in maintenance mode
	 *
	 * @return bool
	 */
	public function is_in_maintenance_mode() {
		return file_exists( $this->get_maintenance_file() );
	}

	/**
	 * Checks whether the maintenance mode is even allowed at all
	 *
	 * Checks for any maint mode being allowed.
	 *
	 * @return bool
	 */
	public function is_maintenance_allowed() {
		return (bool) apply_filters(
			'shipper_is_maintenance_allowed',
			false
		);
	}

	/**
	 * Returns path to the maintenance file
	 *
	 * @return string
	 */
	public function get_maintenance_file() {
		return trailingslashit(
			Shipper_Helper_Fs_Path::get_log_dir()
		) . '.maintenance';
	}

	/**
	 * Enables maintenance mode
	 *
	 * @return bool
	 */
	public function enable_maintenance_mode() {
		if ( $this->is_in_maintenance_mode() ) {
			return false;
		}

		return (bool) @file_put_contents(
			$this->get_maintenance_file(),
			'<?php $upgrading = ' . time() . '; ?>'
		);
	}

	/**
	 * Disables maintenance mode
	 *
	 * @return bool
	 */
	public function disable_maintenance_mode() {
		if ( ! $this->is_in_maintenance_mode() ) {
			return false;
		}

		return @unlink(
			$this->get_maintenance_file()
		);
	}

	/**
	 * Shows maintenance mode page
	 */
	public function maintenance_mode() {
		if ( is_admin() ) { return false; }

		status_header( 503 );

		$override = locate_template( 'shipper-maintenance.php' );
		if ( ! empty( $override ) ) {
			load_template( $override );
		} else {
			$template = new Shipper_Helper_Template;
			$template->render( 'pages/maintenance' );
		}

		die;
	}
}
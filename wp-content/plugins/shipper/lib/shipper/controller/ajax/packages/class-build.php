<?php
/**
 * Shipper AJAX controllers: package build controller class
 *
 * @since v1.1
 * @package shipper
 */

/**
 * Packages building AJAX controller class
 */
class Shipper_Controller_Ajax_Packages_Build extends Shipper_Controller_Ajax {

	/**
	 * Boot method.
	 *
	 * @return false
	 */
	public function boot() {
		if ( ! is_admin() ) {
			return false;
		}

		$this->add_handler( 'cancel', array( $this, 'json_cancel_package' ) );
		$this->add_handler( 'prepare', array( $this, 'json_prepare_package' ) );
		$this->add_handler( 'build', array( $this, 'json_build_package' ) );
		$this->add_handler( 'done', array( $this, 'json_finish_package' ) );
		$this->add_handler( 'failed', array( $this, 'on_package_failure' ) );
		$this->add_handler( 'logs', array( $this, 'get_logs' ) );
	}

	/**
	 * Handle cancel package.
	 */
	public function json_cancel_package() {
		$this->do_request_sanity_check();
		$this->clear_package_data();

		return wp_send_json_success();
	}

	/**
	 * Clear package data
	 *
	 * @since 1.2.5
	 */
	public function clear_package_data() {
		( new Shipper_Model_Stored_Migration() )->clear()->save();
		( new Shipper_Model_Stored_Package() )->clear()->save();
		( new Shipper_Model_Stored_Exclusions() )->clear()->save();
	}

	/**
	 * Finish packages.
	 */
	public function json_finish_package() {
		$this->do_request_sanity_check();

		$model = new Shipper_Model_Stored_Package();
		$model->set(
			Shipper_Model_Stored_Package::KEY_CREATED,
			strtotime( current_time( 'mysql' ) )
		);
		$model->save();

		Shipper_Helper_Log::write(
			__( 'Package migration complete', 'shipper' )
		);

		return wp_send_json_success();
	}

	/**
	 * Prepare package.
	 */
	public function json_prepare_package() {
		$this->do_request_sanity_check();

		$migration = new Shipper_Model_Stored_Migration();
		if ( $migration->is_active() ) {
			$migration->clear()->save();
		}

		Shipper_Helper_Log::clear();

		$storage = new Shipper_Model_Stored_Filelist();
		$storage->clear()->save();

		$files             = new Shipper_Model_Dumped_Filelist();
		$filelist_manifest = $files->get_file_path();
		if ( file_exists( $filelist_manifest ) ) {
			unlink( $filelist_manifest );
		}

		$files             = new Shipper_Model_Dumped_Largelist();
		$filelist_manifest = $files->get_file_path();
		if ( file_exists( $filelist_manifest ) ) {
			unlink( $filelist_manifest );
		}

		$tablelist = new Shipper_Model_Stored_Tablelist();
		$tablelist->clear()->save();

		$dump_model = new Shipper_Model_Stored_Dump();
		$dump_model->clear()->save();

		$migration->prepare(
			Shipper_Model_Stored_Destinations::get_current_domain(),
			'example.org', // Stub destination - we don't need this.
			Shipper_Model_Stored_Migration::TYPE_EXPORT
		);
		$migration->make_it_package_migration();
		$migration->begin();
		Shipper_Helper_Log::write(
			__( 'Package migration start', 'shipper' )
		);

		return wp_send_json_success();
	}

	/**
	 * Build package
	 */
	public function json_build_package() {
		$this->do_request_sanity_check();
		register_shutdown_function( array( $this, 'handle_core_error' ) );

		$migration = new Shipper_Model_Stored_Migration();
		if ( ! $migration->is_active() ) {
			wp_send_json_error(
				__( 'Packaging failed, concurrent process is running', 'shipper' )
			);
		}

		Shipper_Helper_System::optimize();

		$task = new Shipper_Task_Package_All();

		/**
		 * Fires just before package migration building process
		 *
		 * @since v1.1
		 */
		do_action( 'shipper_package_migration_tick_before' );

		$status = $task->apply();

		/**
		 * Fires just after package migration building process
		 *
		 * @since v1.1
		 */
		do_action( 'shipper_package_migration_tick_after' );

		if ( ! $status ) {
			// Not done yet.
			wp_send_json_success(
				$task->get_status_percentage()
			);
		}

		$migration->complete();

		wp_send_json_success( 100 );
	}

	/**
	 * Handles any errors halting the runner process.
	 *
	 * @return bool|void
	 */
	public function handle_core_error() {
		$error = error_get_last();

		if ( null === $error || ! is_array( $error ) ) {
			// No error, we're good here.
			return false;
		}

		$fatals = array(
			// Plain-old fatal.
			E_ERROR,
			// Probably won't reach us, but still...
			E_CORE_ERROR,
			E_COMPILE_ERROR,
			// Userland errors.
			E_USER_ERROR,
			E_RECOVERABLE_ERROR,
		);

		if ( in_array( $error['type'], $fatals, true ) ) {
			Shipper_Helper_Log::write(
				sprintf(
					/* translators: %1$s %2$s %3$s: message, file and line number. */
					__( 'Encountered a FATAL ERROR: %1$s in %2$s on line %3$d', 'shipper' ),
					$error['message'],
					$error['file'],
					$error['line']
				)
			);

			return wp_send_json_error( $error['message'] );
		}
	}

	/**
	 * Clear package data on failed package migration.
	 *
	 * @since 1.2.5
	 */
	public function on_package_failure() {
		Shipper_Helper_Log::write( __( 'Package migration failed', 'shipper' ) );
		$this->clear_package_data();
		exit;
	}

	/**
	 * Get logs
	 *
	 * @since 1.2.7
	 */
	public function get_logs() {
		$logs     = (array) Shipper_Helper_Log::get_lines();
		$last_log = array_pop( $logs );

		wp_send_json_success( $last_log );
	}
}
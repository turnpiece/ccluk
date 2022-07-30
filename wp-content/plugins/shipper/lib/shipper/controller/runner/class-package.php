<?php
/**
 * Shipper controllers: package runner
 *
 * Package migrations are a migration subtype that produces a package instead
 * of using API to shuttle migration data.
 *
 * @since v1.1
 *
 * @package shipper
 */

/**
 * Package controller runner implementation
 */
class Shipper_Controller_Runner_Package extends Shipper_Controller_Runner_Migration {

	/**
	 * Constructor
	 */
	protected function __construct() {
		parent::__construct( 'package' );
		$this->process = 'package';
	}

	/**
	 * Cancels the active migration
	 *
	 * Actually cancels the active migration.
	 * Is meant to be triggered from the processing thread.
	 * The processing thread reaches the cancellation lock set by `attempt_cancel`, then
	 * calls this instead of processing further.
	 *
	 * @return bool
	 */
	public function process_cancel() {
		$migration = new Shipper_Model_Stored_Migration();

		if ( ! $migration->is_active() ) {
			Shipper_Helper_Log::write(
				__( 'Migration inactive', 'shipper' )
			);
		} else {
			Shipper_Helper_Log::write(
				__( 'Attempting to cancel packaging', 'shipper' )
			);

			// Do early cleanup.
			$task = new Shipper_Task_Package_Cleanup();
			$task->apply();

			// Re-initialize the storages.
			$this->reset_all();

			/**
			 * Fires on migration cancel, just before sending out any remote cancel requests
			 *
			 * @param object Shipper_Model_Stored_Migration instance.
			 */
			do_action(
				'shipper_migration_cancel_local',
				$migration
			);

			Shipper_Helper_Log::write(
				__( 'Migration cancelled', 'shipper' )
			);
		}

		/**
		 * Fires on migration cancel
		 *
		 * @param object Shipper_Model_Stored_Migration instance.
		 */
		do_action(
			'shipper_migration_cancel',
			$migration
		);

		if ( $migration->is_active() ) {
			$migration->clear();
			$migration->save();
		}

		return true;
	}

	/**
	 * Actually process the migration step
	 */
	public function process_tick() {
		$migration = new Shipper_Model_Stored_Migration();
		if ( ! $migration->is_active() ) {
			return false; }

		$task = new Shipper_Task_Package_All();
		Shipper_Helper_System::optimize();

		$status = $task->apply();

		// Do not update Hub on package progress.
		add_filter( 'shipper_migration_api_status_update', '__return_false' );

		/**
		 * Fires on each completed tick
		 *
		 * @param object $migration Migration model instance.
		 * @param object $task Overall task instance.
		 * @param bool $status Migration completion status.
		 */
		do_action(
			'shipper_migration_tick',
			$migration,
			$task,
			$status
		);

		remove_filter( 'shipper_migration_api_status_update', '__return_false' );

		if ( $status ) {
			// Migration is done, one way or another.
			$locks = new Shipper_Helper_Locks();
			if ( ! $locks->has_lock( Shipper_Helper_Locks::LOCK_CANCEL ) ) {
				// Not a lock-cancelled process. We're ticking and completing.
				// If we are cancel-locked, let cancel take over in next tick.
				$this->complete();
			}
		}

		return ! $status;
	}
}
<?php
/**
 * Shipper controllers: migration runner
 *
 * @package shipper
 */

/**
 * Migration controller runner implementation
 */
class Shipper_Controller_Runner_Migration extends Shipper_Controller_Runner {

	/**
	 * Overridden to inject WPMU DEV Dash correction fix
	 */
	public function boot() {
		parent::boot();

		if ( ! defined( 'WPMUDEV_LIMIT_TO_USER' ) ) {
			$this->attempt_dash_fix();
		}
	}

	/**
	 * Implements Dash fix
	 *
	 * @return bool Whether the fix has been applied
	 */
	public function attempt_dash_fix() {
		if ( defined( 'WPMUDEV_LIMIT_TO_USER' ) ) {
			return false;
		}

		$migration = new Shipper_Model_Stored_Migration();
		if (
			$migration->is_active() &&
			Shipper_Model_Stored_Migration::TYPE_IMPORT === $migration->get_type()
		) {
			define( 'WPMUDEV_LIMIT_TO_USER', true );

			return true;
		}

		return false;
	}

	/**
	 * Constructor
	 */
	protected function __construct() {
		parent::__construct( 'migration' );
	}

	/**
	 * Process lock getter
	 *
	 * @return string Migration process lock name
	 */
	public function get_process_lock() {
		return Shipper_Helper_Locks::LOCK_MIGRATION;
	}

	/**
	 * Prepares the migration
	 *
	 * @param string $type Migration type to prepare.
	 * @param int    $site_id Destination site ID.
	 * @param string $origin Optional migration origin.
	 */
	public function prepare( $type, $site_id, $origin = false ) {
		$storage = new Shipper_Model_Stored_Filelist();
		$storage->clear();
		$storage->save();

		$migration    = new Shipper_Model_Stored_Migration();
		$destinations = new Shipper_Model_Stored_Destinations();

		// clear the old data.
		$meta = new Shipper_Model_Stored_MigrationMeta();
		$meta->set( Shipper_Task_Import_Syncfiles::HAS_INIT, false );
		$meta->save();
		/**
		 * Whether to clear the log on migration prep
		 *
		 * @param bool $clear_log Whether to clear the log or not.
		 * @param object $migration Shipper_Model_Stored_Migration instance.
		 *
		 * @return bool
		 */
		$clear_log = apply_filters(
			'shipper_migration_log_clear',
			true,
			$migration
		);
		if ( ! ! $clear_log ) {
			Shipper_Helper_Log::clear();
		}

		$target = $destinations->get_by_site_id( $site_id );
		$domain = ! empty( $target['domain'] ) ? $target['domain'] : '';
		$migration->prepare(
			Shipper_Model_Stored_Destinations::get_current_domain(),
			$domain,
			$type,
			$origin
		);
		Shipper_Helper_Log::write(
			sprintf(
				/* translators: %s: migration description. */
				__( 'Migration start -- %s', 'shipper' ),
				$migration->get_description()
			)
		);
	}

	/**
	 * Begin prepared migration
	 */
	public function begin() {
		// Preflight check passed/ignored - log this.
		Shipper_Helper_Log::write(
			__( 'Preflight check complete', 'shipper' )
		);

		// Re-initialize the storages.
		$this->reset_all();

		$ctrl      = Shipper_Controller_Runner_Preflight::get();
		$preflight = $ctrl->get_proxied_results();
		$warnings  = ! empty( $preflight['warnings'] )
			? (int) $preflight['warnings']
			: 0;

		// Also reset preflight here.
		$ctrl->clear();

		// Reset locks.
		$locks = new Shipper_Helper_Locks();
		$locks->clear_locks();

		$migration = new Shipper_Model_Stored_Migration();
		$migration->begin();

		$migration->set( 'preflight_warnings', $warnings )->save();

		/**
		 * Fires on migration start
		 *
		 * @param object $migration Shipper_Model_Stored_Migration instance.
		 */
		do_action(
			'shipper_migration_start',
			$migration
		);
	}

	/**
	 * Run migration process
	 *
	 * Only if the migration is actually active
	 */
	public function run() {
		$migration = new Shipper_Model_Stored_Migration();
		if ( $migration->is_active() ) {
			return $this->ping();
		}
	}

	/**
	 * Complete the active migration
	 */
	public function complete() {
		$migration = new Shipper_Model_Stored_Migration();
		$migration->complete();

		$locks = new Shipper_Helper_Locks();

		if ( $locks->has_lock( Shipper_Helper_Locks::LOCK_CANCEL ) ) {
			// Completing during cancel - update migration errors.
			$migration->set(
				'errors',
				array(
					__( 'Cancelled', 'shipper' ),
				)
			);
		}

		Shipper_Helper_Log::write(
			__( 'Migration complete', 'shipper' )
		);

		/**
		 * Fires on migration complete.
		 *
		 * @param object Shipper_Model_Stored_Migration instance.
		 */
		do_action(
			'shipper_migration_complete',
			$migration
		);
	}

	/**
	 * Resets used models
	 */
	public function reset_all() {
		$storage     = new Shipper_Model_Stored_Filelist();
		$total_steps = $storage->get( Shipper_Model_Stored_Filelist::KEY_TOTAL );
		$storage->clear();
		$storage->set( Shipper_Model_Stored_Filelist::KEY_TOTAL, $total_steps );
		$storage->save();

		$health = new Shipper_Model_Stored_Healthcheck();
		$health->clear()->save();

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

		$model = new Shipper_Model_Stored_Multipart_Uploads();
		$model->clear()->save();
		$model = new Shipper_Model_Stored_Multipart_Downloads();
		$model->clear()->save();

		$model = new Shipper_Model_Stored_Updates();
		$model->clear()->save();

		$tablelist = new Shipper_Model_Stored_Tablelist();
		$tablelist->clear();
		$tablelist->save();

		$package_list      = new Shipper_Model_Dumped_Packagelist();
		$package_list_path = $package_list->get_file_path();
		if ( file_exists( $package_list_path ) ) {
			unlink( $package_list_path );
		}
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
		$migration   = new Shipper_Model_Stored_Migration();
		$remote      = $migration->get_destination();
		$type        = $migration->get_type();
		$autostarted = ! ! $migration->is_from_hub();

		if ( ! $migration->is_active() ) {
			Shipper_Helper_Log::write(
				__( 'Migration inactive', 'shipper' )
			);
		} else {
			Shipper_Helper_Log::write(
				/* translators: %s: migration descriptions. */
				sprintf( __( 'Attempting to cancel %s migration', 'shipper' ), $type )
			);

			// Do early cleanup.
			$task = Shipper_Model_Stored_Migration::TYPE_EXPORT === $migration->get_type()
				? new Shipper_Task_Export_Cleanup()
				: new Shipper_Task_Import_Cleanup();
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

			if ( ! $autostarted ) {
				Shipper_Helper_Log::write(
					/* translators: %s: migration descriptions. */
					sprintf( __( 'Attempting cancel on %s migration', 'shipper' ), $remote )
				);
				$task   = new Shipper_Task_Api_Migrations_Cancel();
				$result = $task->apply(
					array(
						'domain' => $remote,
					)
				);
				if ( empty( $result ) ) {
					Shipper_Helper_Log::write(
						__( 'Canceling remote migration failed', 'shipper' )
					);
					foreach ( $task->get_errors() as $error ) {
						Shipper_Helper_Log::write( $error->get_error_message() );
					}
				}
			} else {
				Shipper_Helper_Log::write(
					__( 'This is Hub-initiated migration, no remote to cancel', 'shipper' )
				);
			}

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
			return false;
		}

		$task = Shipper_Model_Stored_Migration::TYPE_EXPORT === $migration->get_type()
			? new Shipper_Task_Export_All()
			: new Shipper_Task_Import_All();
		$migration->set( 'memory_limit', ini_get( 'memory_limit' ) );
		$migration->save();
		Shipper_Helper_System::optimize();

		/**
		 *  We're adding this hook here, so that we can filter out data on api migration kick off
		 *
		 * @since 1.1.4
		 */
		do_action( 'shipper_migration_before_process_tick' );

		$status = $task->apply();

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
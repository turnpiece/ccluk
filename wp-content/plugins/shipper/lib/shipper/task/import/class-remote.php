<?php
/**
 * Shipper tasks: import, remote migration check/trigger
 *
 * This is the initial task in the import process.
 * Checks the remote system migration presence, and triggers
 * the export process if there's none.
 *
 * @package shipper
 */

/**
 * Shipper import download class
 */
class Shipper_Task_Import_Remote extends Shipper_Task_Import {

	const STATUS_CHECKING = 'checking';
	const STATUS_TRACKING = 'tracking';

	/**
	 * Holds cached exported percentage value
	 *
	 * @var int
	 */
	private $percentage_exported;

	/**
	 * Remote processing flag
	 *
	 * @var string
	 */
	private $status;

	/**
	 * Checks whether we're tracking or initing import
	 *
	 * @return bool
	 */
	public function is_tracking() {
		return self::STATUS_TRACKING === $this->status;
	}

	/**
	 * Gets total steps for this task
	 *
	 * @return int
	 */
	public function get_total_steps() {
		return 1;
	}

	/**
	 * Gets current progress marker for this task
	 *
	 * @return int
	 */
	public function get_current_step() {
		return 1;
	}

	/**
	 * Gets import task label
	 *
	 * @return string
	 */
	public function get_work_description() {
		if ( $this->is_tracking() ) {
			return ! empty( $this->percentage_exported )
				? sprintf(
					/* translators: %d: percentage count. */
					__( 'Export remote system ( %d%% done )', 'shipper' ),
					$this->percentage_exported
				)
				: __( 'Export remote system', 'shipper' );
		}

		return __( 'Check remote system export status', 'shipper' );
	}

	/**
	 * Task runner method
	 *
	 * Returns (bool)true on completion.
	 *
	 * @param array $args Not used.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$migration = new Shipper_Model_Stored_Migration();
		$domain    = $migration->get_source();

		// Check if we already have a migration source ready.
		$remote = new Shipper_Helper_Fs_Remote();

		$status = false;
		try {
			if ( $remote->exists( $domain ) ) {
				Shipper_Helper_Log::write(
					sprintf(
						/* translators: %s: domain name. */
						__( 'Found an existing remote export for %s.', 'shipper' ),
						$domain
					)
				);
				$status = true;
			}
		} catch ( Exception $e ) {
			// Yeah, so that went well...
			// Okay, so never mind that. Let's carry on as we normally would.
			// Pretend there's no previous export existing and start it up.
			$status = false;
		}

		// Okay, so we do have a previously exported remote file - we're good.
		if ( $status ) {
			return $status;
		}

		// No? Do the standard remote check dance.
		return $this->ping_get();
	}

	/**
	 * Checks remote migration state and attempts start
	 *
	 * @return bool
	 * @uses shipper_await_cancel
	 */
	public function ping_get() {
		$this->status = self::STATUS_CHECKING;
		$migration    = new Shipper_Model_Stored_Migration();
		$this_site    = $migration->get_source();
		$remote_site  = $migration->get_destination();

		$task = new Shipper_Task_Api_Migrations_Get();
		$mgr  = $task->apply(
			array(
				'domain' => $remote_site,
			)
		);

		$progress = 0;
		$status   = true;

		if ( empty( $mgr['file'] ) || Shipper_Task_Import::ARCHIVE === $mgr['file'] ) {
			Shipper_Helper_Log::write( __( 'No migration file present', 'shipper' ) );
			// We don't have a migration file.
			if ( empty( $mgr['status'] ) || Shipper_Task_Import::ARCHIVE === $mgr['file'] ) {
				// Can we auto-start it?
				Shipper_Helper_Log::write( __( 'Attempting remote export auto-start', 'shipper' ) );
				$status = $this->attempt_remote_export_start( $this_site, $remote_site );
			} elseif ( Shipper_Task_Import::ARCHIVE !== $mgr['file'] ) {
				Shipper_Helper_Log::write( __( 'Tracking remote export status', 'shipper' ) );
				$this->status = self::STATUS_TRACKING;
				// We do have a migration, but it's incomplete.
				// Let's update progress percentage.
				$progress                  = (int) $mgr['status'];
				$this->percentage_exported = $progress;
				$status                    = false; // Not done yet.
			}
		} elseif ( Shipper_Task_Import::ARCHIVE !== $mgr['file'] ) {
			Shipper_Helper_Log::write(
				__( 'No migration file present, remote export started', 'shipper' )
			);
			$this->status = self::STATUS_TRACKING;
			// We have a file.
			// Let's also update progress percentage (should be 100% though).
			$progress                  = (int) $mgr['status'];
			$this->percentage_exported = $progress;
			$status                    = $progress > 99;
		}

		if ( empty( $status ) ) {
			// If we're not done, let's give it some time now...
			$has_lock = shipper_await_cancel( Shipper_Model_Stored_Migration::TYPE_IMPORT );
			// If we encountered lock while waiting, we're done here.
			if ( $has_lock ) {
				return true;
			}
		}

		return $status;
	}

	/**
	 * Actually attempts remote export
	 *
	 * @param string $this_site Site to export to.
	 * @param string $remote_site Site to trigger export on.
	 *
	 * @return bool Whether we're done with the task
	 */
	public function attempt_remote_export_start( $this_site, $remote_site ) {
		// Assume we're not done yet at first.
		$status   = false;
		$task     = new Shipper_Task_Api_Destinations_Ping();
		$ping_arg = array(
			'domain' => $remote_site,
		);
		$arg      = array(
			'source' => $remote_site,
			'target' => $this_site,
			'type'   => Shipper_Model_Stored_Migration::TYPE_EXPORT,
		);

		Shipper_Helper_Log::write( __( 'Pinging remote domain', 'shipper' ) );

		// Can we reach the remote site?
		if ( $task->apply( $ping_arg ) ) {
			// Yes, we can. Let's try starting export.
			Shipper_Helper_Log::write( __( 'Remote domain reachable, attempting export', 'shipper' ) );
			$task = new Shipper_Task_Api_Migrations_Start();

			$status = $task->apply( $arg );
			if ( $task->has_errors() ) {
				// Well, that failed.
				$status = true; // We're done!
			} else {
				$status = ! $status; // If task succeeded, we're not done.

				// upload the nesesary info at first step.
				$meta = new Shipper_Model_Stored_MigrationMeta();
				if ( $meta->get_mode() === 'subsite' && $meta->get_site_id() ) {
					Shipper_Helper_MS::transmit_subsite_id();
				}
			}

			// Log any errors.
			foreach ( $task->get_errors() as $err ) {
				Shipper_Helper_Log::write( $err->get_error_message() );
				$this->add_error(
					self::ERR_REMOTE,
					sprintf(
						/* translators: %1$s: remote site. */
						__( 'Unable to remotely export from %1$s. Please visit %1$s and start export there manually.', 'shipper' ),
						$remote_site
					)
				);
			}
		} else {
			// No, we can't auto-start the migration.
			// We can't even reach the remote site.
			// Ask the user to do it.
			$this->add_error(
				self::ERR_REMOTE,
				sprintf(
					/* translators: %1$s: remote site. */
					__( 'Error starting export on %1$s. Please visit %1$s and start export there manually.', 'shipper' ),
					$remote_site
				)
			);
			if ( $task->has_errors() ) {
				foreach ( $task->get_errors() as $err ) {
					Shipper_Helper_Log::write( $err->get_error_message() );
				}
			}
			$status = true; // We're done!
		}

		return $status;
	}

	/**
	 * Overriddent to take into account remote export progress
	 *
	 * @return float
	 */
	public function get_status_percentage() {
		if ( $this->percentage_exported ) {
			return $this->percentage_exported;
		}

		return $this->is_tracking()
			? parent::get_status_percentage()
			: 10;
	}
}
<?php
/**
 * Shipper export tasks: remote import trigger
 *
 * Triggers (and monitors) destination import.
 * Runs as the last step of the successful export.
 *
 * @package shipper
 */

/**
 * Remote import trigger class
 */
class Shipper_Task_Export_Remote extends Shipper_Task_Export {

	const STATUS_TRACKING = 'tracking';
	const STATUS_CHECKING = 'checking';

	/**
	 * Holds cached imported percentage value
	 *
	 * @var int
	 */
	private $_percentage_imported;

	/**
	 * Checks whether we're tracking or initing import
	 *
	 * @return bool
	 */
	public function is_tracking() {
		if ( ! $this->has_done_anything() ) { return false; }

		return self::STATUS_TRACKING === $this->_status;
	}

	/**
	 * Actually triggers the remote import
	 *
	 * @uses shipper_await_cancel
	 *
	 * @param array $args Not used.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$this->_status = self::STATUS_CHECKING;
		$this->_has_done_anything = true;

		$migration = new Shipper_Model_Stored_Migration;
		$this_site = $migration->get_source();
		$remote_site = $migration->get_destination();

		$task = new Shipper_Task_Api_Migrations_Get;
		$mgr = $task->apply( array(
			'domain' => $remote_site,
		));

		if ( $task->has_errors() ) {
			// We're not done, not sure how to proceed - let's try again in a bit.
			$status = Shipper_Model_Env::is_phpunit_test(); // True - bail out if tests.
		} else {
			// We have status, carry on.
			$progress = 0;
			$status = true;

			$file = ! empty( $mgr['file'] ) ? $mgr['file'] : '';
			$type = ! empty( $mgr['type'] ) ? $mgr['type'] : '';

			if ( Shipper_Model_Stored_Migration::TYPE_EXPORT === $type ) {
				// Previous export migration, null file.
				// This is because we *want* to auto-start it in this scenario.
				$file = '';
			} elseif (
				Shipper_Model_Stored_Migration::TYPE_IMPORT === $type
				&&
				empty( $file )
				&&
				! empty( $mgr['status'] )
			) {
				// We're done! Dear god this is ugly.
				// @TODO make this sane, please.
				$file = (int) $mgr['status'] > 99 ? Shipper_Task_Import::ARCHIVE : '';
			}

			if ( Shipper_Task_Import::ARCHIVE === $file ) {
				$this->_status = self::STATUS_TRACKING;
				// We already started an import there.
				// Let's update the progress percentage.
				$progress = (int) $mgr['status'];
				$status = $progress > 99;
				$this->_percentage_imported = $progress;
			} elseif ( empty( $file ) ) {
				// Can we auto-start it?
				$status = $this->attempt_remote_import_start( $this_site, $remote_site );
			}
		}

		if ( empty( $status ) ) {
			// If we're not done, let's give it some time now...
			$has_lock = shipper_await_cancel( Shipper_Model_Stored_Migration::TYPE_EXPORT );
			// If we encountered lock while waiting, we're done here.
			if ( $has_lock ) { return true; }
		}

		return $status;
	}

	/**
	 * Actually attempts remote import
	 *
	 * @param string $this_site Site to import from.
	 * @param string $remote_site Site to trigger import on.
	 *
	 * @return bool Whether we're done with the task
	 */
	public function attempt_remote_import_start( $this_site, $remote_site ) {
		// Assume we're not done yet at first.
		$status = false;
		$task = new Shipper_Task_Api_Destinations_Ping;
		$ping_arg = array(
			'domain' => $remote_site,
		);
		$arg = array(
			'source' => $remote_site,
			'target' => $this_site,
			'type' => Shipper_Model_Stored_Migration::TYPE_IMPORT,
		);

		// Can we reach the remote site?
		if ( $task->apply( $ping_arg ) ) {
			// Yes, we can. Let's try starting export.
			$task = new Shipper_Task_Api_Migrations_Start;
			$status = $task->apply( $arg );
			if ( $task->has_errors() ) {
				// Well, that failed.
				$status = true; // We're done!
			} else {
				$status = ! $status; // If task succeeded, we're not done.
			}
			// Log any errors.
			foreach ( $task->get_errors() as $err ) {
				Shipper_Helper_Log::write( $err->get_error_message() );
				$this->add_error(
					self::ERR_REMOTE,
					sprintf(
						__( 'Unable to remotely import on %1$s. Please visit %1$s and start import there manually.', 'shipper' ),
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
					__( 'Error starting import on %1$s. Please visit %1$s and start import there manually.', 'shipper' ),
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
	 * Gets total steps for this task.
	 *
	 * @return int
	 */
	public function get_total_steps() {
		return 1;
	}

	/**
	 * Gets current step for this task
	 *
	 * @return int
	 */
	public function get_current_step() {
		return $this->has_done_anything() ? 1 : 0;
	}

	/**
	 * Gets the task source path
	 *
	 * Unused.
	 *
	 * @param string $path Unused.
	 * @param object $migration Shipper_Model_Stored_Migration instance.
	 *
	 * @return string.
	 */
	public function get_source_path( $path, $migration ) {
		return $migration->get( 'source' );
	}

	/**
	 * Satisfy interface
	 *
	 * @return string
	 */
	public function get_destination_type() {
		return '';
	}

	/**
	 * Gets task job description
	 *
	 * @return string
	 */
	public function get_work_description() {
		if ( ! $this->is_tracking() ) {
			return __( 'Trigger remote import', 'shipper' );
		}
		$progress = max(
			(int) $this->_percentage_imported,
			1
		);
		return sprintf(
			__( 'Import to remote location ( at %d%% )', 'shipper' ),
			$progress
		);
	}

	/**
	 * Overriddent to take into account remote import progress
	 *
	 * @return float
	 */
	public function get_status_percentage() {
		if ( $this->_percentage_imported ) {
			return $this->_percentage_imported;
		}
		return $this->is_tracking()
			? parent::get_status_percentage()
			: 10
		;
	}
}
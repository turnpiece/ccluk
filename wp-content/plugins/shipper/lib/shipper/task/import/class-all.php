<?php
/**
 * Shipper tasks: import hub task
 *
 * This is a hub task for all tasks in import process.
 * It dictates how the import process works and oversees it.
 *
 * @package shipper
 */

/**
 * Import all task class
 */
class Shipper_Task_Import_All extends Shipper_Task_Import {

	/**
	 * Holds reference to the task currently being processed
	 *
	 * @var object Shipper_Task_Import instance
	 */
	private $current_task;

	/**
	 * Holds a flag reference to whether we completed a task in this run
	 *
	 * @var bool
	 */
	private $task_completed = false;

	/**
	 * Task runner method
	 *
	 * Applies all child tasks in turn, and returns (bool)true on completion.
	 *
	 * @param array $args Not used.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		if ( $this->is_done() ) {
			return true;
		}

		$this->task_completed = false;

		$migration = new Shipper_Model_Stored_Migration();

		// we should at least stop here if found out it not compatibility.
		if ( ! $this->is_signal_come_from_compatibility_version() ) {
			// check if this is import.
			$err = __( 'Shipper version difference, please update both source and destination to latest version and try again.', 'shipper' );
			Shipper_Controller_Runner_Migration::get()->attempt_cancel();
			Shipper_Helper_Log::write(
				/* translators: %s: error message. */
				sprintf( __( 'Export issue: %s', 'shipper' ), $err )
			);
			$migration->set( 'errors', array( $err ) );

			return $this->mark_all_tasks_done( $migration ); // We're done because we had errors.
		}

		$incomplete = $this->get_incomplete_tasks( $migration );

		foreach ( $incomplete as $type => $task ) {
			$this->current_task = $task;

			/**
			 * Fires just before the current task processing
			 *
			 * @param object $task Task instance.
			 */
			do_action(
				'shipper_migration_before_task',
				$task
			);
			$migration->set( 'tasks_completed', false );

			$status = $task->apply();

			/**
			 * Fires just after the current task processing
			 *
			 * @param object $task Task instance.
			 * @param bool $status Done or not
			 */
			do_action(
				'shipper_migration_after_task',
				$task,
				$status
			);

			Shipper_Helper_Log::write(
				sprintf(
					/* translators: %1$d %2$d %3$s: current step, total steps and descriptions. */
					__( '[Task %1$d of %2$d]: %3$s', 'shipper' ),
					$this->get_current_step(),
					$this->get_total_steps(),
					$task->get_work_description()
				)
			);
			$migration->set(
				'progress',
				array(
					'message'    => sprintf(
						/* translators: %1$s %2$d %3$d: descriptions, current step, total steps. */
						__( '%1$s: %2$d of %3$d', 'shipper' ),
						$task->get_work_description(),
						$this->get_current_step(),
						$this->get_total_steps()
					),
					'percentage' => $this->get_status_percentage(),
				)
			);

			if ( $task->has_errors() ) {
				$errors = array();
				// If we had any errors, say so next.
				foreach ( $task->get_errors() as $error ) {
					$err = $error->get_error_message();
					Shipper_Helper_Log::write(
						/* translators: %s: import message. */
						sprintf( __( 'Import issue: %s', 'shipper' ), $err )
					);
					$errors[] = $err;
				}
				$migration->set( 'errors', $errors );

				return $this->mark_all_tasks_done( $migration ); // We're done because we had errors.
			}

			if ( ! empty( $status ) ) {
				// Task done, mark it so.
				$this->mark_task_done( $migration, $type );

				/**
				 * Task is done, so clean up the saved data so that it doesn't interfere to other migration
				 *
				 * @since 1.1.4
				 *
				 * @see https://incsub.atlassian.net/browse/SHI-127
				 */
				if ( is_callable( array( $task, 'cleanup' ) ) ) {
					$task->cleanup();
				}

				unset( $incomplete[ $type ] );
			}

			// GTFO while going is good.
			break;
		}

		if ( empty( $incomplete ) ) {
			// All done, say so.
			$migration->set( 'tasks_completed', true );
		}

		$migration->save();

		return empty( $incomplete );
	}

	/**
	 * Checks if we're all done.
	 *
	 * As defined by having any incomplete tasks.
	 *
	 * @return bool
	 */
	public function is_done() {
		$migration  = new Shipper_Model_Stored_Migration();
		$incomplete = $this->get_incomplete_tasks( $migration );

		return empty( $incomplete );
	}

	/**
	 * Gets the list of import tasks
	 *
	 * Ordering of the tasks is very much significant.
	 * Caches tasks list as a side-effect.
	 *
	 * @return array
	 */
	public function get_tasks() {
		if ( empty( $this->tasks ) ) {
			$this->tasks = array();
			$migration   = new Shipper_Model_Stored_Migration();

			if ( ! $migration->is_from_hub() ) {
				$this->tasks['hubclean'] = new Shipper_Task_Import_Hubclean();
			}

			if ( ! $migration->is_from_hub() ) {
				$this->tasks['remote'] = new Shipper_Task_Import_Remote();
			}

			$this->tasks['prepare']     = new Shipper_Task_Import_Prepare();
			$this->tasks['download']    = new Shipper_Task_Import_Download();
			$this->tasks['parse']       = new Shipper_Task_Import_Parse();
			$this->tasks['uncompress']  = new Shipper_Task_Import_Uncompress();
			$this->tasks['files']       = new Shipper_Task_Import_Files();
			$this->tasks['large']       = new Shipper_Task_Import_Large();
			$this->tasks['active']      = new Shipper_Task_Import_Active();
			$this->tasks['tables']      = new Shipper_Task_Import_Tables();
			$this->tasks['config']      = new Shipper_Task_Import_Config();
			$this->tasks['dbprefix']    = new Shipper_Task_Import_DBPrefix();
			$this->tasks['scrubremote'] = new Shipper_Task_Import_Scrubremote();
			$this->tasks['postporcess'] = new Shipper_Task_Import_Postprocess();
			$this->tasks['cleanup']     = new Shipper_Task_Import_Cleanup();
		}

		return $this->tasks;
	}

	/**
	 * Check if we have errors
	 *
	 * As defined by errors from subtasks.
	 *
	 * @return bool
	 */
	public function has_errors() {
		foreach ( $this->get_tasks() as $task ) {
			if ( $task->has_errors() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Gets a list of errors.
	 *
	 * As defined by errors from subtasks.
	 *
	 * @return array
	 */
	public function get_errors() {
		$errors = array();
		foreach ( $this->get_tasks() as $task ) {
			$errors = array_merge( $errors, $task->get_errors() );
		}

		return $errors;
	}

	/**
	 * Gets total steps for this task
	 *
	 * @return int
	 */
	public function get_total_steps() {
		return count( $this->get_tasks() ) + 1;
	}

	/**
	 * Gets current progress marker for this task
	 *
	 * @return int
	 */
	public function get_current_step() {
		$migration  = new Shipper_Model_Stored_Migration();
		$incomplete = $this->get_incomplete_tasks( $migration );
		$not_done   = count( $incomplete ) + 1;

		return ( $this->get_total_steps() - $not_done );
	}

	/**
	 * Gets current active task
	 *
	 * @return object Shipper_Task_Import instance
	 */
	public function get_current_task() {
		return isset( $this->current_task )
			? $this->current_task
			: $this;
	}

	/**
	 * Get status percentage.
	 *
	 * @return float|int
	 */
	public function get_status_percentage() {
		$old     = parent::get_status_percentage();
		$current = $this->get_current_task();
		if ( $this === $current || ! is_callable( array( $current, 'get_total_steps' ) ) ) {
			return $old;
		}
		$step_size = 100 / $this->get_total_steps();

		$task_step = ( $step_size * $current->get_status_percentage() ) / 100;

		return ( $old - $step_size ) + $task_step;
	}

	/**
	 * Gets a list of incomplete tasks
	 *
	 * Tasks don't have to finish to be complete - they can also be skipped/errored out.
	 *
	 * @param object $migration Shipper_Model_Stored_Migration instance.
	 *
	 * @return array
	 */
	public function get_incomplete_tasks( $migration ) {
		$incomplete = array();
		$done       = $migration->get( 'done', array() );
		foreach ( $this->get_tasks() as $type => $task ) {
			if ( ! in_array( $type, $done, true ) ) {
				$incomplete[ $type ] = $task;
			}
		}

		return $incomplete;
	}

	/**
	 * Marks a task as completed
	 *
	 * @param object $migration Shipper_Model_Stored_Migration instance.
	 * @param string $type Task type.
	 *
	 * @return bool
	 */
	public function mark_task_done( $migration, $type ) {
		$done   = $migration->get( 'done', array() );
		$done[] = $type;
		$migration->set( 'done', array_unique( $done ) );
		$migration->save();
		$this->task_completed = true;

		return true;
	}

	/**
	 * Whether we completed a task in this run
	 *
	 * @return bool
	 */
	public function has_completed_task() {
		return (bool) $this->task_completed;
	}

	/**
	 * Marks all tasks in this migration as completed
	 *
	 * @param object $migration Shipper_Model_Stored_Migration instance.
	 *
	 * @return bool
	 */
	public function mark_all_tasks_done( $migration ) {
		$migration->set( 'done', array_keys( $this->get_tasks() ) );
		$migration->set( 'tasks_completed', true );
		$migration->save();
		$this->task_completed = true;

		return true;
	}

	/**
	 * Gets import task label
	 *
	 * @return string
	 */
	public function get_work_description() {
		return __( 'Import the migration package', 'shipper' );
	}
}
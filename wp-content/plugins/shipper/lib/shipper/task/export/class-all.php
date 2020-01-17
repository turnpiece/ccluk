<?php
/**
 * Shipper tasks: export grouping task
 *
 * This task is a hub task, responsible for the entire export process.
 *
 * @package shipper
 */

/**
 * Export all class
 */
class Shipper_Task_Export_All extends Shipper_Task_Export {

	/**
	 * Holds reference to the task currently being processed
	 *
	 * @var object Shipper_Task_Export instance
	 */
	private $_current_task;

	/**
	 * Holds a flag reference to whether we completed a task in this run
	 *
	 * @var bool
	 */
	private $_task_completed = false;

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

		$this->_task_completed = false;

		$is_done   = true;
		$migration = new Shipper_Model_Stored_Migration;

		foreach ( $this->get_incomplete_tasks( $migration ) as $type => $task ) {
			$this->_current_task = $task;
			//it should be safe if we cancel the export when upgrade, if we found that the version incompatibility
//			if ( ! $this->is_signal_come_from_compatibility_version() ) {
//				//check if this is import
//				Shipper_Helper_Log::debug( $type );
//				if ( $type != 'remote' ) {
//					//cancel now
//					$err = __( "Shipper version difference, please try again.", 'shipper' );
//					Shipper_Helper_Log::write(
//						sprintf( __( 'Export issue: %s', 'shipper' ), $err )
//					);
//					$migration->set( 'errors', [ $err ] );
//
//					return $this->mark_all_tasks_done( $migration ); // We're done because we had errors.
//				}
//			}
			/**
			 * Fires just before the current task processing
			 *
			 * @param object $task Task instance.
			 */
			do_action(
				'shipper_migration_before_task',
				$task, $type
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

			if ( $task->has_done_anything() ) {
				$size        = $this->get_archive_size( $migration );
				$size_string = ! empty( $size )
					? '(' . size_format( $size ) . ')'
					: '';
				// If we haven't skipped over this, log progress line first.
				Shipper_Helper_Log::write(
					sprintf(
						__( '%1$s - at %2$d%% (step %3$d of %4$d) - total progress: %5$d%% %6$s', 'shipper' ),
						$task->get_work_description(),
						$task->get_status_percentage(),
						$task->get_current_step(),
						$task->get_total_steps(),
						$this->get_status_percentage(),
						$size_string
					)
				);
				$migration->set( 'progress', array(
					'message'    => sprintf(
						__( '%1$s: %2$d of %3$d', 'shipper' ),
						$task->get_work_description(),
						$task->get_current_step(),
						$task->get_total_steps()
					),
					'percentage' => $this->get_status_percentage(),
				) );
			}

			if ( $task->has_errors() ) {
				$errors = array();
				// If we had any errors, say so next.
				foreach ( $task->get_errors() as $error ) {
					$err = $error->get_error_message();
					Shipper_Helper_Log::write(
						sprintf( __( 'Export issue: %s', 'shipper' ), $err )
					);
					$errors[] = $err;
				}
				$migration->set( 'errors', $errors );
				$migration->set(
					'has_remote_error',
					shipper_has_error( Shipper_Task_Export::ERR_REMOTE, $task->get_errors() )
				);

				return $this->mark_all_tasks_done( $migration ); // We're done because we had errors.
			}

			if ( ! empty( $status ) ) {
				// This task is done with. Carry on to the next one.
				$this->mark_task_done( $migration, $type );
				continue;
			}

			// We have run a task, it didn't error out and it's not done.
			// Therefore, we're not done yet.
			$is_done = false;

			// Break while going is good.
			break;
		}

		if ( $is_done ) {
			$migration->set( 'tasks_completed', true );
		}
		$migration->save();

		return $is_done;
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
		$this->_task_completed = true;

		return true;
	}

	/**
	 * Whether we completed a task in this run
	 *
	 * @return bool
	 */
	public function has_completed_task() {
		return (bool) $this->_task_completed;
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
		$this->_task_completed = true;

		return true;
	}

	/**
	 * Checks if a task has done anything this far
	 *
	 * As defined by child tasks doing anything.
	 *
	 * @return bool
	 */
	public function has_done_anything() {
		foreach ( $this->get_tasks() as $task ) {
			if ( $task->has_done_anything() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if we're all done.
	 *
	 * As defined by having any incomplete tasks.
	 *
	 * @return bool
	 */
	public function is_done() {
		$migration  = new Shipper_Model_Stored_Migration;
		$incomplete = $this->get_incomplete_tasks( $migration );

		return empty( $incomplete );
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
	 * Gets task list
	 *
	 * @return array
	 */
	public function get_tasks() {
		if ( empty( $this->_tasks ) ) {
			$migration    = new Shipper_Model_Stored_Migration;
			$this->_tasks = array();

			if ( ! $migration->is_from_hub() ) {
				$this->_tasks['hubclean'] = new Shipper_Task_Export_Hubclean;
			}

			$this->_tasks['files']    = new Shipper_Task_Export_Files;
			$this->_tasks['compress'] = new Shipper_Task_Export_Compress();
			$this->_tasks['upload']   = new Shipper_Task_Export_PackageUpload();
			$this->_tasks['large']    = new Shipper_Task_Export_Large;
			$this->_tasks['tables']   = new Shipper_Task_Export_Tables;
			$this->_tasks['meta']     = new Shipper_Task_Export_Meta;
			$this->_tasks['cleanup']  = new Shipper_Task_Export_Cleanup;

			if ( ! $migration->is_from_hub() ) {
				$this->_tasks['remote'] = new Shipper_Task_Export_Remote;
			}
		}

		return $this->_tasks;
	}

	/**
	 * Gets total steps for this task
	 *
	 * @return int
	 */
	public function get_total_steps() {
		$total = count( $this->get_tasks() );

		return $total + 1;
	}

	/**
	 * Gets current progress marker for this task
	 *
	 * @return int
	 */
	public function get_current_step() {
		$total = count( $this->get_tasks() );

		$migration = new Shipper_Model_Stored_Migration;
		$done      = count( $this->get_incomplete_tasks( $migration ) );

		return ( $total - $done ) + 1;
	}

	/**
	 * Gets current active task
	 *
	 * @return object Shipper_Task_Export instance
	 */
	public function get_current_task() {
		return isset( $this->_current_task )
			? $this->_current_task
			: $this;
	}

	/**
	 * Satisfy interface.
	 *
	 * @return string
	 */
	public function get_destination_type() {
		return '';
	}

	/**
	 * Actually proxies the archive path.
	 *
	 * @param string $path Unused.
	 * @param object $migration Shipper_Model_Stored_Migration instance.
	 *
	 * @return string
	 */
	public function get_source_path( $path, $migration ) {
		return $this->get_archive_path( $migration->get( 'destination' ) );
	}

	/**
	 * Gets export task label
	 *
	 * @return string
	 */
	public function get_work_description() {
		return __( 'Generate export package', 'shipper' );
	}

	/**
	 * Overridden to take the current task progress into account
	 *
	 * @return float
	 */
	public function get_status_percentage() {
		$current = $this->get_current_task();
		$old     = parent::get_status_percentage();
		if ( $this === $current ) {
			return $old;
		}

		$step_size = 100 / $this->get_total_steps();
		$task_step = ( $step_size * $current->get_status_percentage() ) / 100;

		return ( $old - $step_size ) + $task_step;
	}
}
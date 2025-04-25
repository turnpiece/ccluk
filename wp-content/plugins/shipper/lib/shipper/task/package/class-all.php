<?php
/**
 * Shipper tasks: package generation hub task
 *
 * @since v1.1
 * @package shipper
 */

/**
 * Package root task class
 */
class Shipper_Task_Package_All extends Shipper_Task_Package {

	/**
	 * Migration model holder.
	 *
	 * @var $migration
	 */
	private $migration;

	/**
	 * @var mixed
	 */
	protected $current_task;

	/**
	 * Get migration
	 *
	 * @return \Shipper_Model_Stored_Migration
	 */
	public function get_migration() {
		if ( empty( $this->migration ) ) {
			$this->migration = new Shipper_Model_Stored_Migration();
		}

		return $this->migration;
	}

	/**
	 * Get total steps
	 *
	 * @return int|void
	 */
	public function get_total_steps() {
		return count( $this->get_tasks() );
	}

	/**
	 * Gets current task progress percentage
	 *
	 * @return float
	 */
	public function get_status_percentage() {
		if ( empty( $this->current_task ) ) {
			return 1;
		}

		$tasks    = $this->get_tasks();
		$task_pos = array_search(
			get_class( $this->current_task ),
			array_map( 'get_class', array_values( $tasks ) ),
			true
		);
		if ( false === $task_pos ) {
			return 1;
		}

		$total    = $this->get_total_steps();
		$per_task = 100 / $total;

		$percentage = $task_pos * $per_task;

		if ( $this->current_task ) {
			$task_percentage = $this->current_task->get_status_percentage();
			if ( $task_percentage < 1 ) {
				$task_percentage = 1;
			}
			$percentage += $per_task * ( $task_percentage / 100 );
		}

		if ( $percentage >= 100 && ! $this->get_migration()->is_completed() ) {
			return 99;
		}

		return $percentage;
	}

	/**
	 * Apply method
	 *
	 * @param array $args array of arguments.
	 *
	 * @return bool|mixed
	 */
	public function apply( $args = array() ) {
		if ( $this->is_done() ) {
			return true;
		}

		$migration  = $this->get_migration();
		$incomplete = $this->get_incomplete_tasks();

		foreach ( $incomplete as $type => $task ) {
			$migration->set( 'tasks_completed', false );
			$this->current_task = $task;

			$status = $task->apply();
			$msg    = sprintf(
				/* translators: %1$s %2$d %3$d %4$d %5$d: task type, step and total steps. */
				__( 'Running %1$s task, %2$d of %3$d ( %4$d%% / %5$d%% )', 'shipper' ),
				$type,
				$task->get_current_step(),
				$task->get_total_steps(),
				$task->get_status_percentage(),
				$this->get_status_percentage()
			);
			Shipper_Helper_Log::write( $msg );

			if ( ! empty( $status ) ) {
				// Task done, mark it so.
				$this->mark_task_done( $type );
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
	 * Gets the list of all package generating tasks, in order
	 *
	 * @return array
	 */
	public function get_tasks() {
		return array(
			'prepare' => new Shipper_Task_Package_Prepare(),
			'gather'  => new Shipper_Task_Package_Gather(),
			'files'   => new Shipper_Task_Package_Files(),
			'wpmudev' => new Shipper_Task_Package_Wpmudev(),
			'tables'  => new Shipper_Task_Package_Tables(),
			'meta'    => new Shipper_Task_Package_Meta(),
			'cleanup' => new Shipper_Task_Package_Cleanup(),
		);
	}

	/**
	 * Checks if we're all done.
	 *
	 * As defined by having any incomplete tasks.
	 *
	 * @return bool
	 */
	public function is_done() {
		$incomplete = $this->get_incomplete_tasks();

		return empty( $incomplete );
	}

	/**
	 * Gets a list of incomplete tasks
	 *
	 * Tasks don't have to finish to be complete - they can also be skipped/errored out.
	 *
	 * @return array
	 */
	public function get_incomplete_tasks() {
		$incomplete = array();
		$done       = $this->get_migration()->get( 'done', array() );
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
	 * @param string $type Task type.
	 *
	 * @return bool
	 */
	public function mark_task_done( $type ) {
		$migration = $this->get_migration();
		$done      = $migration->get( 'done', array() );
		$done[]    = $type;
		$migration->set( 'done', array_unique( $done ) );
		$migration->save();

		return true;
	}

	/**
	 * Marks all tasks in this migration as completed
	 *
	 * @param object $migration migration model instance.
	 * @return bool
	 */
	public function mark_all_tasks_done( $migration ) {
		$migration = $this->get_migration();
		$migration->set( 'done', array_keys( $this->get_tasks() ) );
		$migration->set( 'tasks_completed', true );
		$migration->save();

		return true;
	}
}
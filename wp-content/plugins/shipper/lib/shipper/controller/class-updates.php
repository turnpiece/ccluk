<?php
/**
 * Shipper controllers: API updater class
 *
 * Updates the Hub API on migration progress.
 *
 * @package shipper
 */

/**
 * API updater class
 */
class Shipper_Controller_Updates extends Shipper_Controller {

	/**
	 * Overridden updater task class name
	 *
	 * Used in tests.
	 *
	 * @var string
	 */
	private $updater_task_class_name;

	/**
	 * Overridden data_model class name
	 *
	 * Used in tests.
	 *
	 * @var string
	 */
	private $data_model_class_name;

	/**
	 * Boot event listeners
	 */
	public function boot() {
		add_action(
			'shipper_migration_tick',
			array( $this, 'send_updates_tick' ),
			10,
			2
		);
		add_action(
			'shipper_migration_complete',
			array( $this, 'send_updates_complete' )
		);
		add_action(
			'shipper_migration_cancel',
			array( $this, 'send_updates_cancel' )
		);
	}

	/**
	 * Send updates on tick complete
	 *
	 * @param object $migration Migration model object instance.
	 * @param object $task Migration wrapper task object instance.
	 */
	public function send_updates_tick( $migration, $task ) {
		/**
		 * Whether to update the DEV API on migration progress status
		 *
		 * @param bool $do_update Whether to send status update.
		 * @param object $task Overall task (which can be queried for `has_completed_task`).
		 *
		 * @return bool
		 */
		$do_update = apply_filters(
			'shipper_migration_api_status_update',
			true,
			$task
		);
		if ( empty( $do_update ) ) {
			return false;
		}

		$percentage = $task->get_status_percentage();

		if ( $this->has_updated_with_percentage( $task->get_status_percentage() ) ) {
			// We already updated at this percent, carry on.
			return false;
		}

		// Actually send tick update now.
		$status = $this->send_update(
			$migration,
			$task->get_status_percentage(),
			$this->get_updater_file_name( $migration, $task )
		);

		if ( $status ) {
			$cname = $this->get_data_model_class_name();
			$model = new $cname();
			$model
				->set(
					Shipper_Model_Stored_Updates::KEY_PERCENT,
					$this->get_rounded_percentage( $percentage )
				)
				->set_timestamp( time() )
				->save();
		}

		return $status;
	}

	/**
	 * Sends update when the migration has been completed
	 *
	 * Successful, or otherwise.
	 *
	 * @param object $migration Migration model instance to use.
	 *
	 * @return bool
	 */
	public function send_updates_complete( $migration ) {
		$data    = $migration->get_data();
		$success = empty( $data['errors'] );

		if ( empty( $success ) ) {
			return $this->send_updates_cancel( $migration );
		}

		$task = Shipper_Model_Stored_Migration::TYPE_EXPORT === $migration->get_type()
			? new Shipper_Task_Export_All()
			: new Shipper_Task_Import_All();

		$status = $this->send_update(
			$migration,
			100,
			$this->get_updater_file_name( $migration, $task )
		);
		$cname  = $this->get_data_model_class_name();
		$model  = new $cname();
		$model->clear()->save();

		return $status;
	}

	/**
	 * Sends updates on migration cancel/failure
	 *
	 * @param object $migration Migration model instance to use.
	 *
	 * @return bool
	 */
	public function send_updates_cancel( $migration ) {
		$status = $this->send_update(
			$migration,
			0,
			''
		);
		$cname  = $this->get_data_model_class_name();
		$model  = new $cname();
		$model->clear()->save();

		return $status;
	}

	/**
	 * Checks to see if we already updated with this percentage
	 *
	 * @param int|float $percentage Percentage to check for.
	 *
	 * @return bool
	 */
	public function has_updated_with_percentage( $percentage ) {
		if ( 0 === $percentage ) {
			return false;
		}

		$rounded = $this->get_rounded_percentage( $percentage );
		$cname   = $this->get_data_model_class_name();
		$model   = new $cname();
		if ( $model->is_expired() ) {
			$model->clear()->save();
		}
		$updated = $model->get( Shipper_Model_Stored_Updates::KEY_PERCENT, 0 );

		return $updated >= $rounded;
	}

	/**
	 * Rounds percentages to nearest multiple of 10
	 *
	 * @param int|float $percentage Percentage to round.
	 *
	 * @return int
	 */
	public function get_rounded_percentage( $percentage ) {
		return (int) ( $percentage / 10 ) * 10;
	}

	/**
	 * Sets updater task class name
	 *
	 * Used in tests.
	 *
	 * @param string $cname Class name to use as updater task.
	 */
	public function set_updater_task_class_name( $cname ) {
		if ( class_exists( $cname ) ) {
			$this->updater_task_class_name = $cname;
		}
	}

	/**
	 * Gets updater task class name
	 *
	 * @return string
	 */
	public function get_updater_task_class_name() {
		$cname = 'Shipper_Task_Api_Migrations_Set';
		if ( $this->updater_task_class_name && class_exists( $this->data_model_class_name ) ) {
			$cname = $this->updater_task_class_name;
		}

		return $cname;
	}

	/**
	 * Sets data model class name
	 *
	 * Used in tests.
	 *
	 * @param string $cname Class name to use as data model.
	 */
	public function set_data_model_class_name( $cname ) {
		if ( class_exists( $cname ) ) {
			$this->data_model_class_name = $cname;
		}
	}

	/**
	 * Gets data_model class name
	 *
	 * @return string
	 */
	public function get_data_model_class_name() {
		$cname = 'Shipper_Model_Stored_Updates';
		if ( $this->data_model_class_name && class_exists( $this->data_model_class_name ) ) {
			$cname = $this->data_model_class_name;
		}

		return $cname;
	}

	/**
	 * Actually sends out an update
	 *
	 * @param object $migration Migration model instance.
	 * @param int    $status Status update.
	 * @param string $filename Filename.
	 *
	 * @return bool
	 */
	public function send_update( $migration, $status, $filename ) {
		$cname  = $this->get_updater_task_class_name();
		$task   = new $cname();
		$status = $task->apply(
			array(
				'domain' => $migration->get_source(),
				'status' => $status,
				'type'   => $migration->get_type(),
				'file'   => $filename,
			)
		);

		if ( $task->has_errors() ) {
			foreach ( $task->get_errors() as $err ) {
				Shipper_Helper_Log::write( $err->get_error_message() );
			}
		}

		return $status;
	}

	/**
	 * Resolves file name to be sent to updater task
	 *
	 * File name depends on the migration context.
	 *
	 * @param object $migration Shipper_Model_Stored_Migration instance.
	 * @param object $task Shipper_Task instance (Shipper_Task_Import or Shipper_Task_Export).
	 *
	 * @return string
	 */
	public function get_updater_file_name( $migration, $task ) {
		if ( Shipper_Model_Stored_Migration::TYPE_EXPORT === $migration->get_type() ) {
			return basename( $task->get_archive_path( $migration->get_destination() ) );
		}

		return Shipper_Task_Import::ARCHIVE;
	}
}
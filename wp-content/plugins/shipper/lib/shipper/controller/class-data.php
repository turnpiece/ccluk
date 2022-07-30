<?php
/**
 * Shipper controllers: data recording controller class
 *
 * @package shipper
 */

/**
 * Data controller class
 */
class Shipper_Controller_Data extends Shipper_Controller {

	/**
	 * Boots the controller and sets up event listeners.
	 */
	public function boot() {
		if ( ! $this->is_data_recording_enabled() ) {
			return false;
		}

		add_action(
			'shipper_migration_before_task',
			array( $this, 'start_task_timers' )
		);
		add_action(
			'shipper_migration_after_task',
			array( $this, 'record_task_timers' ),
			10,
			2
		);

		add_action(
			'shipper_migration_start',
			array( $this, 'reset_timers' )
		);

		add_action( 'shipper_migration_complete', array( $this, 'on_complete_migration' ) );
		add_action( 'shipper_migration_cancel', array( $this, 'on_cancel_migration' ) );
	}

	/**
	 * Is data recording enabled
	 *
	 * @return bool
	 */
	public function is_data_recording_enabled() {
		return (bool) apply_filters(
			'shipper_enable_data_recording',
			true
		);
	}

	/**
	 * Reset timers.
	 */
	public function reset_timers() {
		Shipper_Helper_Timer_Basic::get()->reset_all();

		$timer = Shipper_Helper_Timer_Persistent::get();
		$timer->reset_all();
		$timer->start( 'migration' );
	}

	/**
	 * Start the task timers.
	 *
	 * @param object $task task name.
	 */
	public function start_task_timers( $task ) {
		$timer = get_class( $task );

		$helper = Shipper_Helper_Timer_Basic::get();
		if ( ! $helper->is_started( $timer ) ) {
			$helper->start( $timer );
		}

		$helper = Shipper_Helper_Timer_Persistent::get();
		if ( ! $helper->is_started( $timer ) ) {
			$helper->start( $timer );
		}
	}

	/**
	 * Record task timers.
	 *
	 * @param object $task task name.
	 * @param bool   $status task status.
	 */
	public function record_task_timers( $task, $status ) {
		$timer  = get_class( $task );
		$helper = Shipper_Helper_Timer_Persistent::get();

		$msg = array(
			'name'  => $timer,
			'step'  => Shipper_Helper_Timer_Basic::get()->diff( $timer ),
			'total' => $helper->diff( 'migration' ),
		);

		if ( $status ) {
			$helper->stop( $timer );
			$msg['task'] = $helper->diff( $timer );
		}

		if ( $task instanceof Shipper_Task_Export_Tables ) {
			$msg['size'] = $task->get_current_step();
			$msg['unit'] = 'rows';
		}

		if ( $task instanceof Shipper_Task_Import_Tables ) {
			$msg['size'] = $task->get_current_step();
			$msg['unit'] = 'tables';
		}

		if ( $task instanceof Shipper_Task_Export_Upload ) {
			$msg['size'] = $task->get_current_step();
			$msg['unit'] = 'files';
		}

		if ( $task instanceof Shipper_Task_Import_Files ) {
			$msg['size'] = $task->get_current_step();
			$msg['unit'] = 'files';
		}

		Shipper_Helper_Log::data(
			$this->format_data( $msg )
		);
	}

	/**
	 * On complete migration.
	 *
	 * @param object $migration migration model.
	 */
	public function on_complete_migration( $migration ) {
		$data    = $migration->get_data();
		$success = empty( $data['errors'] );

		$this->finish_data( $success );
	}

	/**
	 * On cancel migration
	 */
	public function on_cancel_migration() {
		return $this->finish_data( false );
	}

	/**
	 * Finish data.
	 *
	 * @param string $success task status.
	 */
	public function finish_data( $success ) {
		$timer = Shipper_Helper_Timer_Persistent::get();
		$timer->stop( 'migration' );

		$data = array(
			'name'  => 'migration',
			'total' => $timer->diff( 'migration' ),
		);

		if ( ! empty( $success ) ) {
			$estimate     = new Shipper_Model_Stored_Estimate();
			$data['size'] = $estimate->get( 'package_size' );
			$data['unit'] = 'bytes';
		}

		Shipper_Helper_Log::data( $this->format_data( $data ) );
	}

	/**
	 * Format data.
	 *
	 * @param array $raw an array of raw data.
	 *
	 * @return array
	 */
	public function format_data( $raw ) {
		$format = array(
			'name',
			'total',
			'task',
			'step',
			'size',
			'unit',
		);
		$data   = array();
		foreach ( $format as $type ) {
			$data[ $type ] = isset( $raw[ $type ] )
				? $raw[ $type ]
				: '';
		}

		return $data;
	}
}
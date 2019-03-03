<?php
/**
 * Shipper controllers: preflight runner
 *
 * @package shipper
 */

/**
 * Preflight controller runner implementation
 */
class Shipper_Controller_Runner_Preflight extends Shipper_Controller_Runner {

	/**
	 * Constructor
	 */
	protected function __construct() {
		parent::__construct( 'preflight' );
	}

	/**
	 * Gets process lock for the implementation
	 *
	 * @return string One of the Shipper_Helper_Locks constants
	 */
	public function get_process_lock() {
		return Shipper_Helper_Locks::LOCK_PREFLIGHT;
	}

	/**
	 * Implements preflight-specific cancellation cleanup
	 */
	public function process_cancel() {
		$system_task = new Shipper_Task_Check_System;
		$remote_task = new Shipper_Task_Check_Sysdiff;
		$files_task = new Shipper_Task_Check_Files;
		$system_task->restart();
		$remote_task->restart();
		$files_task->restart();

		Shipper_Helper_Log::write( __( 'Preflight check cancel', 'shipper' ) );

		$this->clear();
	}

	/**
	 * Starts actual preflight
	 *
	 * @param string $target Remote domain.
	 */
	public function start( $target ) {
		shipper_flush_cache();
		$preflight = $this->get_status();
		$data = $preflight->get_data();
		if ( ! empty( $data ) ) {
			return true; // Already running.
		}

		$locks = new Shipper_Helper_Locks;

		if ( $locks->has_lock( Shipper_Helper_Locks::LOCK_PREFLIGHT ) ) {
			return false;
		}

		$system_task = new Shipper_Task_Check_System;
		$remote_task = new Shipper_Task_Check_Sysdiff;
		$files_task = new Shipper_Task_Check_Files;
		$system_task->restart();
		$remote_task->restart();
		$files_task->restart();

		$system = array();
		$remote = array();

		$all_done = true;
		$errors = array();

		$model = new Shipper_Model_System;

		Shipper_Helper_Log::write( __( 'Preflight check start', 'shipper' ) );

		$system_task->apply( $model->get_data() );
		$all_done = $all_done && $system_task->is_done();
		foreach ( $system_task->get_checks() as $check ) {
			$system[] = $check->get_data();
		}

		$request = new Shipper_Task_Api_Info_Get( array( 'domain' => $target ) );
		$info = $request->apply();
		if ( $request->has_errors() ) {
			$errors = array_merge( $errors, $request->get_errors() );
		}
		$remote_task->apply( $info );
		$all_done = $all_done && $remote_task->is_done();
		foreach ( $remote_task->get_checks() as $check ) {
			$remote[] = $check->get_data();
		}

		$preflight = $this->get_status();
		$preflight
			->start( $all_done )
			->set_check( Shipper_Model_Stored_Preflight::KEY_CHECKS_SYSTEM, $system )
			->add_errors(
				Shipper_Model_Stored_Preflight::KEY_CHECKS_SYSTEM,
				$system_task->get_errors()
			)
			->set_check( Shipper_Model_Stored_Preflight::KEY_CHECKS_REMOTE, $remote )
			->add_errors(
				Shipper_Model_Stored_Preflight::KEY_CHECKS_REMOTE,
				array_merge( $errors, $remote_task->get_errors() )
			)
			->save();

		$call = $this->process_tick();

		if ( $call ) {
			$this->ping();
		}
	}

	/**
	 * Actually performs a process tick
	 *
	 * @return bool Whether to continue with processing
	 */
	public function process_tick() {
		$preflight = $this->get_status();
		$data = $preflight->get_data();
		if ( empty( $data ) ) {
			return false;
		}

		$is_done = false;

		$files = array();

		$files_task = new Shipper_Task_Check_Files;
		$files_task->apply();
		$is_done = $files_task->is_done();
		foreach ( $files_task->get_checks() as $check ) {
			$files[] = $check->get_data();
		}

		$preflight->set_check( Shipper_Model_Stored_Preflight::KEY_CHECKS_FILES, $files );
		$preflight->add_errors(
			Shipper_Model_Stored_Preflight::KEY_CHECKS_FILES,
			$files_task->get_errors()
		);
		$preflight->set( Shipper_Model_Stored_Preflight::KEY_DONE, $is_done );

		$preflight->save();

		if ( ! $is_done ) {
			return true;
		} else {
			$this->log_errors();
			return false;
		}
	}

	/**
	 * Logs preflight errors at the end of the process
	 */
	public function log_errors() {
		$data = $this->get_status();
		$is_done = $data->get( Shipper_Model_Stored_Preflight::KEY_DONE );
		if ( empty( $is_done ) ) { return false; }

		$check_types = $data->get_check_types();
		foreach ( $check_types as $type ) {
			$check = $data->get_check( $type );
			foreach ( $check as $chk ) {
				if ( Shipper_Model_Check::STATUS_OK === $chk['status'] ) { continue; }
				Shipper_Helper_Log::write(
					sprintf( __( 'Preflight check issue: %s', 'shipper' ), $chk['title'] )
				);
			}
			$errors = $data->get_check_errors( $type );
			foreach ( $errors as $error ) {
				Shipper_Helper_Log::write(
					sprintf( __( 'Preflight check error: %s', 'shipper' ), $error )
				);
			}
		}
	}

	/**
	 * Gets preflight status this far
	 *
	 * @return array
	 */
	public function get_status() {
		if ( ! isset( $this->_model ) ) {
			$this->_model = new Shipper_Model_Stored_Preflight;
		}
		return $this->_model;
	}

	/**
	 * Clears preflight status data
	 *
	 * @return object
	 */
	public function clear() {
		return $this->get_status()
			->clear()
			->save();
	}

	/**
	 * Gets intermediate data representation
	 *
	 * Used for both heartbeat and hub responses.
	 *
	 * @return array
	 */
	public function get_proxied_results() {
		$preflight = $this->get_status();
		$result = array(
			'is_done' => ! ! $preflight->get( Shipper_Model_Stored_Preflight::KEY_DONE ),
			'checks' => array(),
			'errors' => 0,
			'warnings' => 0,
		);
		$check_types = $preflight->get_check_types();

		foreach ( $check_types as $type ) {
			$checks = $preflight->get_check( $type );
			$error_messages = $preflight->get_check_errors( $type );
			$errors = count( $error_messages );
			$warnings = 0;
			foreach ( $checks as $chk ) {
				if ( Shipper_Model_Check::STATUS_WARNING === $chk['status'] ) {
					$warnings++;
				}
				if ( Shipper_Model_Check::STATUS_ERROR === $chk['status'] ) {
					$errors++;
				}
			}
			$result['warnings'] += $warnings;
			$result['errors'] += $errors;
			$issues = $errors + $warnings;
			$result['checks'][ $type ] = array(
				'type' => $type,
				'errors_count' => $issues,
				'breaking_errors_count' => $errors,
				'errors' => $error_messages,
				'checks' => $checks,
				'is_done' => $result['is_done'],
			);
		}

		return $result;
	}

	/**
	 * Checks if the current preflight is done
	 *
	 * @return bool
	 */
	public function is_done() {
		return ! ! $this->get_status()->get(
			Shipper_Model_Stored_Preflight::KEY_DONE
		);
	}

	/**
	 * Checks if we have any issues detected this far
	 *
	 * Checks for both warnings and errors.
	 *
	 * @return bool
	 */
	public function has_issues() {
		$preflight = $this->get_proxied_results();
		return ! empty( $preflight['errors'] ) || ! empty( $preflight['warnings'] );
	}
}
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
		$system_task  = new Shipper_Task_Check_System();
		$remote_task  = new Shipper_Task_Check_Rsystem();
		$sysdiff_task = new Shipper_Task_Check_Sysdiff();
		$files_task   = new Shipper_Task_Check_Files();
		$rpkg_task    = new Shipper_Task_Check_Rpkg();
		$system_task->restart();
		$remote_task->restart();
		$sysdiff_task->restart();
		$files_task->restart();
		$rpkg_task->restart();

		Shipper_Helper_Log::write( __( 'Preflight check cancel', 'shipper' ) );

		$this->clear();

		return true;
	}

	/**
	 * Starts actual preflight
	 */
	public function start() {
		shipper_flush_cache();
		$preflight = $this->get_status();
		$data      = $preflight->get_data();
		if ( ! empty( $data ) ) {
			return true; // Already running.
		}

		$locks = new Shipper_Helper_Locks();

		if ( $locks->has_lock( $this->get_process_lock() ) ) {
			return false;
		}

		$files_task = new Shipper_Task_Check_Files();
		$rpkg_task  = new Shipper_Task_Check_Rpkg();
		$files_task->restart();
		$rpkg_task->restart();

		$api_model = new Shipper_Model_Api();
		$api_model->clear_cached_api_response( 'info-get' );
		$api_model->clear_cached_api_response( 'info-preflight' );

		Shipper_Helper_Log::write( __( 'Preflight check start', 'shipper' ) );

		$this->get_status()->start( false )->save();
		$this->process_system(); // Do this on start because it's fast.
		$this->ping();
	}

	/**
	 * Performs the (local) system check
	 *
	 * @return bool Whether to continue with processing (opposite of all done)
	 * @since v1.0.3
	 */
	public function process_system() {
		$system_task = new Shipper_Task_Check_System();
		$system_task->restart();
		$system = array();

		$model = new Shipper_Model_System();
		$system_task->apply( $model->get_data() );
		foreach ( $system_task->get_checks() as $check ) {
			$system[] = $check->get_data();
		}

		$this->get_status()
			->set_check( Shipper_Model_Stored_Preflight::KEY_CHECKS_SYSTEM, $system )
			->add_errors( Shipper_Model_Stored_Preflight::KEY_CHECKS_SYSTEM, $system_task->get_errors() )
			->save();

		return ! $this->maybe_complete();
	}

	/**
	 * Performs the remote system checks
	 *
	 * This does both the remote system check, as well as remote vs local
	 * system difference one. This is because both are done on the same data,
	 * provided by the info-get endpoint.
	 *
	 * @return bool Whether to continue with processing (opposite of all done)
	 * @since v1.0.3
	 */
	public function process_remote() {
		do_action( 'shipper_before_process_remote' );
		$remote_task = new Shipper_Task_Check_Rsystem();
		$remote_task->restart();
		$sysdiff_task = new Shipper_Task_Check_Sysdiff();
		$sysdiff_task->restart();
		$remote  = array();
		$sysdiff = array();
		$errors  = array();

		$migration = new Shipper_Model_Stored_Migration();
		$target    = $migration->get_destination();

		$request = new Shipper_Task_Api_Info_Get();
		$info    = $request->apply( array( 'domain' => $target ) );
		if ( $request->has_errors() ) {
			$errors = array_merge( $errors, $request->get_errors() );
		}
		$remote_task->apply( $info );
		foreach ( $remote_task->get_checks() as $check ) {
			$remote[] = $check->get_data();
		}
		$sysdiff_task->apply( $info );
		foreach ( $sysdiff_task->get_checks() as $check ) {
			$sysdiff[] = $check->get_data();
		}

		$this->get_status()
			->set_check( Shipper_Model_Stored_Preflight::KEY_CHECKS_SYSDIFF, $sysdiff )
			->add_errors( Shipper_Model_Stored_Preflight::KEY_CHECKS_SYSDIFF, array_merge( $errors, $sysdiff_task->get_errors() ) )
			->set_check( Shipper_Model_Stored_Preflight::KEY_CHECKS_REMOTE, $remote )
			->add_errors( Shipper_Model_Stored_Preflight::KEY_CHECKS_REMOTE, array_merge( $errors, $remote_task->get_errors() ) )
			->save();

		return ! $this->maybe_complete();
	}

	/**
	 * Actually performs a process tick
	 *
	 * @return bool Whether to continue with processing
	 */
	public function process_tick() {
		$preflight = $this->get_status();

		$system        = $preflight->get_check( Shipper_Model_Stored_Preflight::KEY_CHECKS_SYSTEM );
		$system_errors = $preflight->get_check_errors(
			Shipper_Model_Stored_Preflight::KEY_CHECKS_SYSTEM
		);
		if ( empty( $system ) && empty( $system_errors ) ) {
			return $this->process_system();
		}

		$remote        = $preflight->get_check( Shipper_Model_Stored_Preflight::KEY_CHECKS_REMOTE );
		$remote_errors = $preflight->get_check_errors(
			Shipper_Model_Stored_Preflight::KEY_CHECKS_REMOTE
		);
		if ( empty( $remote ) && empty( $remote_errors ) ) {
			return $this->process_remote();
		}
		if ( ! $this->has_package_or_files() ) {
			return $this->process_package_or_files();
		}

		return ! $preflight->get( Shipper_Model_Stored_Preflight::KEY_DONE );
	}

	/**
	 * Checks whether we have completed package size check
	 *
	 * The corresponding remote package size/local files package size will be checked.
	 *
	 * @return bool
	 * @since v1.0.3
	 */
	public function has_package_or_files() {
		$migration = new Shipper_Model_Stored_Migration();
		$key       = Shipper_Model_Stored_Migration::TYPE_IMPORT === $migration->get_type()
			? Shipper_Model_Stored_Preflight::KEY_CHECKS_RPKG
			: Shipper_Model_Stored_Preflight::KEY_CHECKS_FILES;
		$stored    = $this->get_status()->get( $key );

		return ! empty( $stored['is_done'] );
	}

	/**
	 * Performs the package size check
	 *
	 * The corresponding remote package size/local files package size will be run.
	 *
	 * @return bool Whether to continue with processing (opposite of all done)
	 * @since v1.0.3
	 */
	public function process_package_or_files() {
		do_action( 'shipper_before_process_package_or_files' );
		$migration = new Shipper_Model_Stored_Migration();
		if ( Shipper_Model_Stored_Migration::TYPE_IMPORT === $migration->get_type() ) {
			return $this->process_remote_package();
		}

		return $this->process_files();
	}

	/**
	 * Performs the remote package size check
	 *
	 * This is done for import migrations.
	 *
	 * @return bool Whether to continue with processing (opposite of all done)
	 * @since v1.0.3
	 */
	public function process_remote_package() {
		$task   = new Shipper_Task_Api_Info_Preflight();
		$result = $task->apply();

		$preflight = $this->get_status();

		if ( $task->has_errors() || ! isset( $result['estimated_package_size'] ) ) {
			$result['is_done'] = true; // We're done eiter way.
			// OK, so we haven't been able to check for remote package size.
			// However, the export might have still gone through from the remote site.
			// If so, we will be able to continue the migration.
			// So, let's check for this explicitly, before erroring out.
			$domain   = Shipper_Model_Stored_Destinations::get_current_domain();
			$remote   = new Shipper_Helper_Fs_Remote();
			$is_error = $task->has_errors();
			try {
				if ( $remote->exists( $domain ) ) {
					// The remote site exported something for us.
					// Let's use that.
					$is_error = false;
					$result   = array(
						'is_done'                => true,
						'estimated_package_size' => 0,
						'existing_export'        => true,
					);
				}
			} catch ( Exception $e ) {
				$is_error = true;
			}

			if ( $is_error ) {
				Shipper_Helper_Log::write( 'Errors in remote preflight!' );
				$preflight
					->set_check(
						Shipper_Model_Stored_Preflight::KEY_CHECKS_RPKG,
						$result
					)
					->add_errors(
						Shipper_Model_Stored_Preflight::KEY_CHECKS_RPKG,
						$task->get_errors()
					)
					->save();

				return ! $this->maybe_complete();
			}
		}

		$rpkg_task = new Shipper_Task_Check_Rpkg();
		$rpkg_task->apply( $result );
		$data = array();
		foreach ( $rpkg_task->get_checks() as $check ) {
			$data[] = $check->get_data();
		}
		$preflight->set_check( Shipper_Model_Stored_Preflight::KEY_CHECKS_RPKG, $data );
		$preflight->save();

		if ( empty( $result['is_done'] ) ) {
			$has_lock = shipper_await_cancel( Shipper_Model_Stored_Migration::TYPE_IMPORT );
			if ( $has_lock ) {
				return true;
			}
		} else {
			// So, we got the remote package size, let's store it so that we can calculate the ETA.
			$estimated_model = new Shipper_Model_Stored_Estimate();
			$estimated_model->set( Shipper_Model_Stored_Migration::PACKAGE_SIZE, $result['estimated_package_size'] );
			$estimated_model->save();
			$this->maybe_complete();
		}

		return empty( $result['is_done'] );
	}

	/**
	 * Performs the local files and package size check
	 *
	 * This is done for export migrations.
	 *
	 * @return bool Whether to continue with processing (opposite of all done)
	 */
	public function process_files() {
		$preflight = $this->get_status();
		$data      = $preflight->get_data();
		if ( empty( $data ) ) {
			return false;
		}

		$is_done = false;

		$files = array();

		$files_task = new Shipper_Task_Check_Files();
		$files_task->apply();
		$is_done = $files_task->is_done();
		foreach ( $files_task->get_checks() as $check ) {
			$files[] = $check->get_data();
		}
		$files['is_done'] = $is_done;

		$preflight->set_check( Shipper_Model_Stored_Preflight::KEY_CHECKS_FILES, $files );
		$preflight->add_errors(
			Shipper_Model_Stored_Preflight::KEY_CHECKS_FILES,
			$files_task->get_errors()
		);

		$preflight->save();

		if ( ! $is_done ) {
			return true;
		} else {
			$this->maybe_complete();
			$this->log_errors();

			return false;
		}
	}

	/**
	 * Updates the preflight model final done state
	 *
	 * If all of the tasks are done, sets the final preflight model state to done.
	 *
	 * @return bool All done or not
	 * @since v1.0.3
	 */
	public function maybe_complete() {
		$preflight = $this->get_status();
		$is_done   = $preflight->get( Shipper_Model_Stored_Preflight::KEY_DONE );
		if ( $is_done ) {
			return true;
		}

		$is_done = true;

		foreach ( $preflight->get_check_types() as $check_type ) {
			$check      = $preflight->get_check( $check_type );
			$errors     = $preflight->get_check_errors( $check_type );
			$check_done = isset( $check['is_done'] )
				? (bool) $check['is_done']
				: ! empty( $check ) || ! empty( $errors );

			if ( ! $check_done ) {
				$is_done = false;
				break;
			}
		}

		if ( $is_done ) {
			$preflight->set( Shipper_Model_Stored_Preflight::KEY_DONE, true )->save();
		}

		return $is_done;
	}

	/**
	 * Logs preflight errors at the end of the process
	 */
	public function log_errors() {
		$data    = $this->get_status();
		$is_done = $data->get( Shipper_Model_Stored_Preflight::KEY_DONE );
		if ( empty( $is_done ) ) {
			return false;
		}

		$check_types = $data->get_check_types();
		foreach ( $check_types as $type ) {
			$check = $data->get_check( $type );
			foreach ( $check as $chk ) {
				if ( ! isset( $chk['status'] ) || Shipper_Model_Check::STATUS_OK === $chk['status'] ) {
					continue;
				}
				Shipper_Helper_Log::write(
					/* translators: %s: preflight issue title. */
					sprintf( __( 'Preflight check issue: %s', 'shipper' ), $chk['title'] )
				);
			}
			$errors = $data->get_check_errors( $type );
			foreach ( $errors as $error ) {
				Shipper_Helper_Log::write(
					/* translators: %s: preflight issue title. */
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
		if ( ! isset( $this->model ) ) {
			$this->model = new Shipper_Model_Stored_Preflight();
		}

		return $this->model;
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
		$preflight   = $this->get_status();
		$result      = array(
			'is_done'  => ! ! $preflight->get( Shipper_Model_Stored_Preflight::KEY_DONE ),
			'checks'   => array(),
			'errors'   => 0,
			'warnings' => 0,
		);
		$check_types = $preflight->get_check_types();

		$file_warnings  = 0;
		$other_warnings = 0;
		foreach ( $check_types as $type ) {
			$checks         = $preflight->get_check( $type );
			$error_messages = $preflight->get_check_errors( $type );
			$errors         = count( $error_messages );
			$warnings       = 0;
			foreach ( $checks as $chk ) {

				if ( 'files' !== $type || empty( $chk['count'] ) ) {
					if ( isset( $chk['status'] ) && Shipper_Model_Check::STATUS_WARNING === $chk['status'] ) {
						$warnings ++;
						$other_warnings ++;
					}
				} else {
					$warnings      += $chk['count'];
					$file_warnings += $chk['count'];
				}

				if ( isset( $chk['status'] ) && Shipper_Model_Check::STATUS_ERROR === $chk['status'] ) {
					$errors ++;
				}
			}
			$result['warnings']       += $warnings;
			$result['errors']         += $errors;
			$issues                    = $errors + $warnings;
			$result['checks'][ $type ] = array(
				'type'                  => $type,
				'errors_count'          => $issues,
				'breaking_errors_count' => $errors,
				'errors'                => $error_messages,
				'checks'                => $checks,
				'is_done'               => $result['is_done'],
			);
		}

		if ( $file_warnings ) {
			$exclusions = new Shipper_Model_Stored_Exclusions();
			$cnt        = count( $exclusions->get_data() );
			if ( empty( $other_warnings ) ) {
				$result['warnings'] -= min( $result['warnings'], $cnt );
				foreach ( $result['checks']['files']['checks'] as $idx => $check ) {
					if ( isset( $check['count'] ) && $check['count'] >= $cnt ) {
						$check['count'] -= $file_warnings;
					}
					$result['checks']['files']['checks'][ $idx ] = $check;
				}
			}
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
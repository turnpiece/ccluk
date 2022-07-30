<?php
/**
 * Shipper tasks: general system prerequisites check (local)
 *
 * @package shipper
 */

/**
 * System prerequisites check task
 */
class Shipper_Task_Check_System extends Shipper_Task_Check {

	/**
	 * Holds checks processed
	 *
	 * @var array
	 */
	protected $checks = array();

	/**
	 * Gets the domain of the current system check
	 *
	 * @return string
	 */
	public function get_domain() {
		return Shipper_Model_Stored_Destinations::get_current_domain();
	}

	/**
	 * Runs the checks suite.
	 *
	 * @param array $data System info, as created by Shipper_Model_System::get_data.
	 *
	 * @return bool
	 */
	public function apply( $data = array() ) {
		if ( empty( $data ) ) {
			$this->add_error(
				Shipper_Task_Check::ERR_BLOCKING,
				__( 'No data to process', 'shipper' )
			);
			return false;
		}

		foreach ( $data as $section => $info ) {
			if ( ! is_array( $info ) ) {
				$this->add_error(
					Shipper_Task_Check::ERR_BLOCKING,
					/* translators: %s: section name. */
					sprintf( __( 'Invalid data for section %s', 'shipper' ), $section )
				);
				return false;
			}
			foreach ( $info as $key => $value ) {
				$check  = strtolower( "{$section}_{$key}" );
				$method = "is_{$check}_valid";

				if ( is_callable( array( $this, $method ) ) ) {
					$check = call_user_func( array( $this, $method ), $value );
					$check->set( 'check_id', md5( get_class( $this ) . $method ) );
					$this->add_check( $check );
				}
			}
		}

		$this->update_hub( $data );

		return true;
	}

	/**
	 * Updates the remote Hub data
	 *
	 * @since v1.0.3
	 *
	 * @param array $data Data to send.
	 */
	public function update_hub( $data ) {
		// Make sure we have the remote system info updated.
		$task = new Shipper_Task_Api_Info_Set();
		$task->apply( $data );
	}

	/**
	 * Checks whether the site is password protected
	 *
	 * @param bool $value Whether the password protection is on.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_server_access_protected_valid( $value ) {
		$check  = new Shipper_Model_Check( __( 'Password protection is disabled', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;
		$msg    = __( 'No password protection detected.', 'shipper' );

		if ( ! empty( $value ) ) {
			$status = Shipper_Model_Check::STATUS_ERROR;

			$tpl = new Shipper_Helper_Template();
			$check->set( 'title', __( 'Password protection is enabled', 'shipper' ) );
			$check->set(
				'message',
				$tpl->get( 'checks/password-protection', array( 'domain' => $this->get_domain() ) )
			);
		}

		return $check->complete( $status );
	}

	/**
	 * Check potential conflicting AWS version
	 *
	 * @param bool $value AWS support level.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_php_aws_support_valid( $value ) {
		$check  = new Shipper_Model_Check( __( 'AWS SDK is loaded successfully', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;

		if ( ! $value ) {
			$status = Shipper_Model_Check::STATUS_ERROR;

			$tpl = new Shipper_Helper_Template();
			$check->set( 'title', __( 'Unable to load AWS SDK', 'shipper' ) );
			$check->set(
				'message',
				$tpl->get( 'checks/aws-sdk', array( 'domain' => $this->get_domain() ) )
			);
		}

		return $check->complete( $status );
	}

	/**
	 * Check zip support presence
	 *
	 * @param int $value Major PHP version.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_php_zip_archive_valid( $value ) {
		$check  = new Shipper_Model_Check( __( 'ZIP support is available', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;

		if ( ! $value ) {
			$status = Shipper_Model_Check::STATUS_ERROR;

			$tpl = new Shipper_Helper_Template();
			$check->set( 'title', __( 'ZIP support is not found', 'shipper' ) );
			$check->set(
				'message',
				$tpl->get( 'checks/zip-archive', array( 'domain' => $this->get_domain() ) )
			);
		}

		return $check->complete( $status );
	}

	/**
	 * Checks whether we have met minimum PHP requirements
	 *
	 * @param string $value PHP version to check.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_php_version_valid( $value ) {
		$check  = new Shipper_Model_Check( __( 'PHP v5.5 or newer is available', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;

		if ( version_compare( $value, '5.5', 'lt' ) ) {
			$status = Shipper_Model_Check::STATUS_ERROR;

			$tpl = new Shipper_Helper_Template();
			$check->set( 'title', __( 'PHP v5.5 or newer is required', 'shipper' ) );
			$check->set(
				'message',
				$tpl->get(
					'checks/php-version',
					array(
						'domain' => $this->get_domain(),
						'value'  => $value,
					)
				)
			);
		}

		return $check->complete( $status );
	}

	/**
	 * Checks if max execution time is large enough
	 *
	 * @param int $value Time to check.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_php_max_execution_time_valid( $value ) {
		$value  = (int) $value;
		$check  = new Shipper_Model_Check( __( 'Max Execution Time is 120 or above', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;

		if ( $value > 0 && $value < 120 ) {
			$status = Shipper_Model_Check::STATUS_WARNING;
			$check->set( 'title', __( 'Max Execution Time is low', 'shipper' ) );
			$tpl = new Shipper_Helper_Template();
			$check->set(
				'message',
				$tpl->get(
					'checks/exec-time',
					array(
						'value'  => $value,
						'domain' => $this->get_domain(),
					)
				)
			);
		}

		return $check->complete( $status );
	}

	/**
	 * Checks if open basedir is active
	 *
	 * @param bool $value Whether open_basedir is active.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_php_open_basedir_valid( $value ) {
		$check  = new Shipper_Model_Check( __( 'Open_basedir restriction is disabled', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;

		if ( ! ! $value ) {
			$server    = new Shipper_Model_System_Server();
			$path_data = $server->get( Shipper_Model_System_Server::WORKING_DIR . '_writable' ) &&
				$server->get( Shipper_Model_System_Server::WORKING_DIR . '_writable' ) &&
				$server->get( Shipper_Model_System_Server::STORAGE_DIR . '_writable' ) &&
				$server->get( Shipper_Model_System_Server::TEMP_DIR . '_writable' ) &&
				$server->get( Shipper_Model_System_Server::LOG_DIR . '_writable' );
			$status    = ! empty( $path_data )
				? Shipper_Model_Check::STATUS_WARNING
				: Shipper_Model_Check::STATUS_ERROR;

			$tpl = new Shipper_Helper_Template();
			$check->set( 'title', __( 'Open_basedir restriction in effect', 'shipper' ) );
			$check->set(
				'message',
				$tpl->get( 'checks/open-basedir', array( 'domain' => $this->get_domain() ) )
			);
		}

		return $check->complete( $status );
	}

	/**
	 * Checks if working directory is writable
	 *
	 * @param bool $value Whether directory is writable.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_server_working_directory_writable_valid( $value ) {
		$check  = new Shipper_Model_Check( __( 'Working directory is writable', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;

		if ( ! $value ) {
			$status = Shipper_Model_Check::STATUS_ERROR;

			$tpl = new Shipper_Helper_Template();
			$check->set( 'title', __( 'Working directory is not writable', 'shipper' ) );
			$check->set(
				'message',
				$tpl->get(
					'checks/workdir-writable',
					array(
						'domain' => $this->get_domain(),
						'value'  => Shipper_Helper_Fs_Path::get_working_dir(),
					)
				)
			);
		}

		return $check->complete( $status );
	}

	/**
	 * Checks if log directory is writable
	 *
	 * @param bool $value Whether directory is writable.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_server_log_directory_writable_valid( $value ) {
		$check  = new Shipper_Model_Check( __( 'Log directory is writable', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;

		if ( ! $value ) {
			$status = Shipper_Model_Check::STATUS_ERROR;

			$tpl = new Shipper_Helper_Template();
			$check->set( 'title', __( 'Log directory is not writable', 'shipper' ) );
			$check->set(
				'message',
				$tpl->get(
					'checks/logdir-writable',
					array(
						'domain' => $this->get_domain(),
						'value'  => Shipper_Helper_Fs_Path::get_log_dir(),
					)
				)
			);
		}

		return $check->complete( $status );
	}

	/**
	 * Checks if working directory is web-accessible
	 *
	 * @param bool $value Whether directory is web-accessible.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_server_working_directory_accessible_valid( $value ) {
		$check  = new Shipper_Model_Check( __( 'Working directory is not web visible and can\'t be accessed externally', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;

		if ( $value ) {
			$status = Shipper_Model_Check::STATUS_ERROR;

			$tpl = new Shipper_Helper_Template();
			$check->set( 'title', __( 'Working directory is web visible and can be accessed externally', 'shipper' ) );
			$check->set(
				'message',
				$tpl->get(
					'checks/workdir-accessible',
					array(
						'domain' => $this->get_domain(),
						'value'  => Shipper_Helper_Fs_Path::get_working_dir(),
					)
				)
			);
		}

		return $check->complete( $status );
	}

	/**
	 * Checks if storage directory is web-accessible
	 *
	 * @param bool $value Whether directory is web-accessible.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_server_storage_directory_accessible_valid( $value ) {
		$check  = new Shipper_Model_Check( __( 'Storage directory is not web visible and can\'t be accessed externally', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;

		if ( $value ) {
			$status = Shipper_Model_Check::STATUS_ERROR;

			$tpl = new Shipper_Helper_Template();
			$check->set( 'title', __( 'Storage directory is web visible and can be accessed externally', 'shipper' ) );
			$check->set(
				'message',
				$tpl->get(
					'checks/storagedir-accessible',
					array(
						'domain' => $this->get_domain(),
						'value'  => Shipper_Helper_Fs_Path::get_storage_dir(),
					)
				)
			);
		}

		return $check->complete( $status );
	}

	/**
	 * Checks if temp directory is web-accessible
	 *
	 * @param bool $value Whether directory is web-accessible.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_server_temp_directory_accessible_valid( $value ) {
		$check  = new Shipper_Model_Check( __( 'Temp directory is not web visible and can\'t be accessed externally', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;

		if ( $value ) {
			$status = Shipper_Model_Check::STATUS_ERROR;

			$tpl = new Shipper_Helper_Template();
			$check->set( 'title', __( 'Temp directory is web visible and can be accessed externally', 'shipper' ) );
			$check->set(
				'message',
				$tpl->get(
					'checks/tempdir-accessible',
					array(
						'domain' => $this->get_domain(),
						'value'  => Shipper_Helper_Fs_Path::get_temp_dir(),
					)
				)
			);
		}

		return $check->complete( $status );
	}
}
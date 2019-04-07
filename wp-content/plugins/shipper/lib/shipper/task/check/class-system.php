<?php
/**
 * Shipper tasks: general system prerequisites check
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
	private $_checks = array();

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
					sprintf( __( 'Invalid data for section %s', 'shipper' ), $section )
				);
				return false;
			}
			foreach ( $info as $key => $value ) {
				$check = strtolower( "{$section}_{$key}" );
				$method = "is_{$check}_valid";

				if ( is_callable( array( $this, $method ) ) ) {
					$check = call_user_func( array( $this, $method ), $value );
					$this->add_check( $check );
				}
			}
		}

		// Make sure we have the remote system info updated.
		$task = new Shipper_Task_Api_Info_Set;
		$task->apply( $data );

		return true;
	}

	/**
	 * Checks whether the site is password protected
	 *
	 * @paeam bool $value Whether the password protection is on.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_server_access_protected_valid( $value ) {
		$check = new Shipper_Model_Check( __( 'Password protection', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;
		$msg = __('No password protection detected.', 'shipper');

		if ( ! empty( $value ) ) {
			$status = Shipper_Model_Check::STATUS_ERROR;
			$msg = join(' ', array(
				__( 'Password protection detected.', 'shipper' ),
				__( 'This can prevent migration from working properly.', 'shipper' ),
				__( 'Please, make sure you disable password protection.', 'shipper' ),
			));
		}

		$check = $this->set_check_message( $check, $msg );

		return $check->complete( $status );
	}

	/**
	 * Check if we have suhosin extension
	 *
	 * @param bool $value Whether we have Suhosin.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_php_has_suhosin_valid( $value ) {
		$check = new Shipper_Model_Check( __( 'Suhosin', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;

		if ( $value ) {
			$status = Shipper_Model_Check::STATUS_ERROR;
			$check->set('message', '<p>' . join(' ', array(
				__( '<b>Suhosin extension loaded!</b>', 'shipper' ),
				__( 'This can prevent us from loading our AWS PHP SDK.', 'shipper' ),
				__( 'Please, make sure you either disable it, or add phar to its whitelist.', 'shipper' ),
			)) . '</p>');
		} else {
			$check = $this->set_check_message(
				$check,
				__('Suhosin extension is not loaded.', 'shipper')
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
		$check = new Shipper_Model_Check( __( 'AWS Support', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;

		if ( ! $value ) {
			$status = Shipper_Model_Check::STATUS_ERROR;
			$check->set('message', '<p>' . join(' ', array(
				__( '<b>Detected a problem with AWS PHP SDK!</b>', 'shipper' ),
				__( 'Either there is a conflicting version present, or we were not able to load our version.', 'shipper' ),
			)) . '</p>');
		} else {
			$check = $this->set_check_message(
				$check,
				__('No AWS PHP SDK conflict detected.', 'shipper')
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
		$check = new Shipper_Model_Check( __( 'ZIP Support', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;

		if ( ! $value ) {
			$status = Shipper_Model_Check::STATUS_ERROR;
			$check->set('message', '<p>' . join(' ', array(
				__( '<b>No built-in ZIP support detected!</b>', 'shipper' ),
				__( 'PHP\'s built-in <code>ZipArchive</code> class seems to be missing. We will not be able to proceed without that.', 'shipper' ),
			)) . '</p>');
		} else {
			$check = $this->set_check_message(
				$check,
				__('Built-in PHP archiving detected.', 'shipper')
			);
		}

		return $check->complete( $status );
	}

	/**
	 * Check major PHP version for sanity issues
	 *
	 * @param int $value Major PHP version.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_php_version_major_valid( $value ) {
		$check = new Shipper_Model_Check( __( 'Basic Sanity', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;

		if ( (int) $value < 5 ) {
			$status = Shipper_Model_Check::STATUS_ERROR;
			$check->set( 'title', __( 'Ancient PHP detected', 'shipper' ) );
			$check->set('message', '<p>' . join(' ', array(
				__( '<b>This is a very important security issue!</b>', 'shipper' ),
				sprintf( __( 'PHP 5.2 did not have a release for over %d years now, which makes it very insecure.', 'shipper' ), ( (int) date( 'Y' ) - 2011 ) ),
				__( 'Not only that we will not be progressing further, but you shouldn\'t be either - addressing this issue should be a priority.', 'shipper' ),
			)) . '</p>');
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
		$check = new Shipper_Model_Check( __( 'PHP Version', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;

		if ( version_compare( $value, '5.5', 'lt' ) ) {
			$status = Shipper_Model_Check::STATUS_ERROR;
			$check->set('message', '<p>' . join(' ', array(
				sprintf( __( 'Your PHP version is %s.', 'shipper' ), $value ),
				__( 'Minimum recommended PHP version is PHP 5.5', 'shipper' ),
			)) . '</p>');
		} else {
			$check = $this->set_check_message(
				$check,
				sprintf(
					__('Your PHP version is %s, which meets our requirements.', 'shipper'),
					$value
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
		$value = (int) $value;
		$check = new Shipper_Model_Check( __( 'Max Execution Time', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;

		if ( $value > 0 && $value < 150 ) {
			$status = Shipper_Model_Check::STATUS_WARNING;
			$check->set('message', '<p>' . join(' ', array(
				sprintf( __( '<b>Your current max PHP execution time is set to %d seconds.</b>', 'shipper' ), $value ),
				__( 'For small websites this will work in most cases, however for large sites we recommend increasing your <b>max_execution_time</b> to 150s', 'shipper' ),
			)) . '</p>');
		} else {
			$check = $this->set_check_message(
				$check,
				sprintf(
					__('Your max execution time is set to %s.', 'shipper'),
					$value
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
		$check = new Shipper_Model_Check( __( 'Open Basedir', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;

		if ( ! ! $value ) {
			$server = new Shipper_Model_System_Server;
			$path_data = $server->get( Shipper_Model_System_Server::WORKING_DIR . '_writable' ) &&
				$server->get( Shipper_Model_System_Server::WORKING_DIR . '_writable' ) &&
				$server->get( Shipper_Model_System_Server::STORAGE_DIR . '_writable' ) &&
				$server->get( Shipper_Model_System_Server::TEMP_DIR . '_writable' ) &&
				$server->get( Shipper_Model_System_Server::LOG_DIR . '_writable' )
			;
			$status = !empty( $path_data )
				? Shipper_Model_Check::STATUS_WARNING
				: Shipper_Model_Check::STATUS_ERROR;

			$check->set('message', '<p>' . join(' ', array(
				__( 'It appears that <b>open_basedir</b> rule is in effect.', 'shipper' ),
				__( 'We will likely not be able to do what we need to with this PHP setting enabled', 'shipper' ),
			)) . '</p>');
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
		$check = new Shipper_Model_Check( __( 'Working directory writable', 'shipper' ) );
		return empty( $value )
			? $this->dir_not_writable( $check )
			: $this->dir_writable( $check );
	}

	/**
	 * Checks if log directory is writable
	 *
	 * @param bool $value Whether directory is writable.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_server_log_directory_writable_valid( $value ) {
		$check = new Shipper_Model_Check( __( 'Log directory writable', 'shipper' ) );
		return empty( $value )
			? $this->dir_not_writable( $check )
			: $this->dir_writable( $check );

	}

	/**
	 * Checks if working directory is web-visible
	 *
	 * @param bool $value Whether directory is web-visible.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_server_working_directory_visible_valid( $value ) {
		$check = new Shipper_Model_Check( __( 'Working directory not web-visible', 'shipper' ) );
		return ! empty( $value )
			? $this->private_dir_visible( $check )
			: $this->private_dir_not_visible( $check );
	}

	/**
	 * Checks if working directory is web-accessible
	 *
	 * @param bool $value Whether directory is web-accessible.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_server_working_directory_accessible_valid( $value ) {
		$check = new Shipper_Model_Check( __( 'Working directory not publicly accessible', 'shipper' ) );
		return ! empty( $value )
			? $this->private_dir_accessible( $check )
			: $this->private_dir_not_accessible( $check );
	}

	/**
	 * Checks if storage directory is web-visible
	 *
	 * @param bool $value Whether directory is web-visible.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_server_storage_directory_visible_valid( $value ) {
		$check = new Shipper_Model_Check( __( 'Storage directory not web-visible', 'shipper' ) );
		return ! empty( $value )
			? $this->private_dir_visible( $check )
			: $this->private_dir_not_visible( $check );
	}

	/**
	 * Checks if storage directory is web-accessible
	 *
	 * @param bool $value Whether directory is web-accessible.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_server_storage_directory_accessible_valid( $value ) {
		$check = new Shipper_Model_Check( __( 'Storage directory not publicly accessible', 'shipper' ) );
		return ! empty( $value )
			? $this->private_dir_accessible( $check )
			: $this->private_dir_not_accessible( $check );
	}

	/**
	 * Checks if temp directory is web-visible
	 *
	 * @param bool $value Whether directory is web-visible.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_server_temp_directory_visible_valid( $value ) {
		$check = new Shipper_Model_Check( __( 'Temp directory not web-visible', 'shipper' ) );
		return ! empty( $value )
			? $this->private_dir_visible( $check )
			: $this->private_dir_not_visible( $check );
	}

	/**
	 * Checks if temp directory is web-accessible
	 *
	 * @param bool $value Whether directory is web-accessible.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_server_temp_directory_accessible_valid( $value ) {
		$check = new Shipper_Model_Check( __( 'Temp directory not publicly accessible', 'shipper' ) );
		return ! empty( $value )
			? $this->private_dir_accessible( $check )
			: $this->private_dir_not_accessible( $check );
	}

	/**
	 * Directory not writeable failure helper
	 *
	 * @param object $check Shipper_Model_Check instance.
	 *
	 * @return object Completed Shipper_Model_Check instance.
	 */
	protected function dir_not_writable( $check ) {
		$check->set(
			'message',
			'<p>' . join(' ', array(
				'<b>' . __( 'This directory has to be writable.', 'shipper' ) . '</b>',
				__( 'However, we were not able to write to it.', 'shipper' ),
			)) . '</p>'
		);
		return $check->complete( Shipper_Model_Check::STATUS_ERROR );
	}

	/**
	 * Directory writeable success helper
	 *
	 * @param object $check Shipper_Model_Check instance.
	 *
	 * @return object Completed Shipper_Model_Check instance.
	 */
	protected function dir_writable( $check ) {
		$check = $this->set_check_message(
			$check,
			__('We are able to write to this directory', 'shipper')
		);
		return $check->complete( Shipper_Model_Check::STATUS_OK );
	}

	/**
	 * Directory web visible failure helper
	 *
	 * @param object $check Shipper_Model_Check instance.
	 *
	 * @return object Completed Shipper_Model_Check instance.
	 */
	protected function private_dir_visible( $check ) {
		$check->set(
			'message',
			'<p>' . join(' ', array(
				'<b>' . __( 'This directory may be exposed publicly.', 'shipper' ) . '</b>',
				__( 'We will do our best to try to prevent access to it, though.', 'shipper' ),
			)) . '</p>'
		);
		return $check->complete( Shipper_Model_Check::STATUS_WARNING );
	}

	/**
	 * Directory web visible success helper
	 *
	 * @param object $check Shipper_Model_Check instance.
	 *
	 * @return object Completed Shipper_Model_Check instance.
	 */
	protected function private_dir_not_visible( $check ) {
		$check = $this->set_check_message(
			$check,
			__('Directory is not visible from the web.', 'shipper')
		);
		return $check->complete( Shipper_Model_Check::STATUS_OK );
	}

	/**
	 * Directory web accessible failure helper
	 *
	 * @param object $check Shipper_Model_Check instance.
	 *
	 * @return object Completed Shipper_Model_Check instance.
	 */
	protected function private_dir_accessible( $check ) {
		$check->set(
			'message',
			'<p>' . join(' ', array(
				'<b>' . __( 'This directory is to be private.', 'shipper' ) . '</b>',
				__( 'However, we were able to access it externally.', 'shipper' ),
				'<i>' . __( 'This means that the whole internet can, too.', 'shipper' ) . '</i>',
				__( 'This may expose sensitive info about your setup during migration.', 'shipper' ),
			)) . '</p>'
		);
		return $check->complete( Shipper_Model_Check::STATUS_ERROR );
	}

	/**
	 * Directory web accessible success helper
	 *
	 * @param object $check Shipper_Model_Check instance.
	 *
	 * @return object Completed Shipper_Model_Check instance.
	 */
	protected function private_dir_not_accessible( $check ) {
		$check = $this->set_check_message(
			$check,
			__('Directory can\'t be accessed from the outside web.', 'shipper')
		);
		return $check->complete( Shipper_Model_Check::STATUS_OK );
	}
}
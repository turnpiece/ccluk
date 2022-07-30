<?php
/**
 * Shipper models: preflight data cache
 *
 * Stores cached preflight data.
 *
 * @package shipper
 */

/**
 * Preflight model class
 */
class Shipper_Model_Stored_Preflight extends Shipper_Model_Stored {

	const KEY_DONE   = 'is_done';
	const KEY_ERRORS = 'errors';
	const KEY_CHECKS = 'checks';

	const KEY_CHECKS_SYSTEM  = 'local';
	const KEY_CHECKS_REMOTE  = 'remote';
	const KEY_CHECKS_SYSDIFF = 'sysdiff';
	const KEY_CHECKS_FILES   = 'files';
	const KEY_CHECKS_RPKG    = 'remote_package';

	/**
	 * Constructor
	 *
	 * Sets up appropriate storage namespace
	 */
	public function __construct() {
		// Store the model in the database.
		parent::__construct( 'preflight', true );
	}

	/**
	 * Initialize a new preflight cache
	 *
	 * @param bool $status Current status.
	 *
	 * @return object
	 */
	public function start( $status ) {
		$this->set_data(
			array(
				self::KEY_DONE   => ! ! $status,
				self::KEY_ERRORS => array(),
				self::KEY_CHECKS => array(),
			)
		);
		$this->save();

		return $this;
	}

	/**
	 * Add a list of preflight errors
	 *
	 * @param string $check Check to add errors to.
	 * @param array  $errors Errors to add.
	 *
	 * @return object
	 */
	public function add_errors( $check, $errors ) {
		$errors = ! empty( $errors ) && is_array( $errors )
			? $errors
			: array();
		foreach ( $errors as $error ) {
			$this->add_error( $check, $error );
		}
		return $this;
	}

	/**
	 * Add a single error
	 *
	 * @param string         $check Check to add errors to.
	 * @param WP_Error|array $error Error to add.
	 *
	 * @return object
	 */
	public function add_error( $check, $error ) {
		$message = is_wp_error( $error )
			? $error->get_error_message()
			: ( ! empty( $error['message'] ) ? $error['message'] : '' );

		if ( empty( $message ) ) {
			$message = __( 'Generic preflight error', 'shipper' );
		}

		$errs = $this->get( self::KEY_ERRORS, array() );
		$errs = ! empty( $errs ) && is_array( $errs )
			? $errs
			: array();

		if ( empty( $errs[ $check ] ) ) {
			$errs[ $check ] = array();
		}

		$errs[ $check ][] = $message;
		$this->set( self::KEY_ERRORS, $errs );

		return $this;
	}

	/**
	 * Sets check data
	 *
	 * @param string $check Check to set.
	 * @param array  $data Optional data to set.
	 *
	 * @return object
	 */
	public function set_check( $check, $data = array() ) {
		$data = ! empty( $data ) && is_array( $data )
			? $data
			: array();

		$checks           = $this->get( self::KEY_CHECKS, array() );
		$checks[ $check ] = $data;

		$this->set( self::KEY_CHECKS, $checks );

		return $this;
	}

	/**
	 * Gets particular check data
	 *
	 * @param string $check Check to get.
	 *
	 * @return array
	 */
	public function get_check( $check ) {
		if ( self::KEY_CHECKS_FILES === $check ) {
			return $this->get_files_check();
		}

		$checks = $this->get( self::KEY_CHECKS, array() );
		$data   = ! empty( $checks[ $check ] ) && is_array( $checks[ $check ] )
			? $checks[ $check ]
			: array();

		return $data;
	}

	/**
	 * Special-case files check getter.
	 *
	 * This is used so we can get the package sizes check updated dynamically.
	 *
	 * @return array
	 */
	public function get_files_check() {
		$checks = $this->get( self::KEY_CHECKS, array() );
		$key    = self::KEY_CHECKS_FILES;
		$data   = ! empty( $checks[ $key ] ) && is_array( $checks[ $key ] )
			? $checks[ $key ]
			: array();

		$check  = new Shipper_Task_Check_Files();
		$data[] = $check->get_package_size_check()->get_data();
		if ( ! isset( $data['is_done'] ) ) {
			$data['is_done'] = false;
		}

		return $data;
	}

	/**
	 * Gets particular check errors
	 *
	 * @param string $check Check to get.
	 *
	 * @return array
	 */
	public function get_check_errors( $check ) {
		$errs = $this->get( self::KEY_ERRORS, array() );
		return ! empty( $errs[ $check ] ) && is_array( $errs[ $check ] )
			? $errs[ $check ]
			: array();
	}

	/**
	 * Clears all check errors pertaining to an individual check.
	 *
	 * @param string $check Check for the errors to be reset for.
	 */
	public function clear_check_errors( $check ) {
		$errs           = $this->get( self::KEY_ERRORS, array() );
		$errs[ $check ] = array();
		return $this->set( self::KEY_ERRORS, $errs );
	}

	/**
	 * Gets known check types
	 *
	 * @return array
	 */
	public function get_check_types() {
		$types = array(
			self::KEY_CHECKS_SYSTEM,
			self::KEY_CHECKS_REMOTE,
			self::KEY_CHECKS_SYSDIFF,
		);

		$migration = new Shipper_Model_Stored_Migration();
		if ( Shipper_Model_Stored_Migration::TYPE_IMPORT === $migration->get_type() ) {
			$types[] = self::KEY_CHECKS_RPKG;
		} else {
			$types[] = self::KEY_CHECKS_FILES;
		}

		return $types;
	}
}
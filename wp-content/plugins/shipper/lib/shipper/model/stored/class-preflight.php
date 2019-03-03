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

	const KEY_DONE = 'is_done';
	const KEY_ERRORS = 'errors';
	const KEY_CHECKS = 'checks';

	const KEY_CHECKS_SYSTEM = 'local';
	const KEY_CHECKS_REMOTE = 'remote';
	const KEY_CHECKS_FILES = 'files';

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
		$this->set_data(array(
			self::KEY_DONE => ! ! $status,
			self::KEY_ERRORS => array(),
			self::KEY_CHECKS => array(),
		));
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
			: array()
		;
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
			: ( ! empty( $error['message'] ) ? $error['message'] : '')
		;
		if ( empty( $message ) ) {
			$message = __( 'Generic preflight error', 'shipper' );
		}
		$errs = $this->get( self::KEY_ERRORS, array() );
		$errs = ! empty( $errs ) && is_array( $errs )
			? $errs
			: array()
		;
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
			: array()
		;

		$checks = $this->get( self::KEY_CHECKS, array() );
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
		$checks = $this->get( self::KEY_CHECKS, array() );
		$data = ! empty( $checks[ $check ] ) && is_array( $checks[ $check ] )
			? $checks[ $check ]
			: array()
		;
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
			: array()
		;
	}

	/**
	 * Gets known check types
	 *
	 * @return array
	 */
	public function get_check_types() {
		return array(
			self::KEY_CHECKS_SYSTEM,
			self::KEY_CHECKS_REMOTE,
			self::KEY_CHECKS_FILES,
		);
	}
}
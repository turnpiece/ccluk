<?php
/**
 * Shipper models: options storage
 *
 * All Shipper options set from the UI end up here.
 *
 * @package shipper
 */

/**
 * Options model class
 */
class Shipper_Model_Stored_Options extends Shipper_Model_Stored {

	const KEY_SEND      = 'send_email';
	const KEY_SEND_FAIL = 'failure_send';
	const KEY_EMAILS    = 'emails';

	const KEY_A11N = 'use_a11n';

	const KEY_SETTINGS = 'preserve_settings';
	const KEY_DATA     = 'preserve_data';

	const KEY_UPLOADS    = 'use_uploads_dir';
	const KEY_SKIPCONFIG = 'skip_wp_config';
	const KEY_SKIPEMAILS = 'skip_email_replacement';

	const KEY_PER_PAGE    = 'entries_per_page';
	const KEY_USER_ACCESS = 'allow_user_ids';

	const KEY_PACKAGE_DB_BINARY  = 'package_db_use_binary';
	const KEY_PACKAGE_DB_LIMIT   = 'package_db_query_limit';
	const KEY_PACKAGE_ZIP_BINARY = 'package_zip_use_binary';
	const KEY_PACKAGE_ZIP_LIMIT  = 'package_zip_file_limit';
	const KEY_PACKAGE_SAFE_MODE  = 'package_safe_mode';

	/**
	 * Constructor
	 *
	 * Sets up appropriate storage namespace
	 */
	public function __construct() {
		parent::__construct( 'options', true );
	}

	/**
	 * Gets a list of emails to notify
	 *
	 * @return array Emails list, as email => name pairs
	 */
	public function get_emails() {
		$emails = $this->get( self::KEY_EMAILS, array() );

		return $this->get_valid_emails( $emails );
	}

	/**
	 * Sets a list of emails to notify
	 *
	 * @param array $emails List of emails to notify, as email => name pairs.
	 *
	 * @return object Shipper_Model_Stored_Options instance
	 */
	public function set_emails( $emails ) {
		$emails = $this->get_valid_emails( $emails );
		return $this->set( self::KEY_EMAILS, $emails );
	}

	/**
	 * Adds a single email to storage
	 *
	 * Saves the model storage as a side-effect.
	 *
	 * @param string $email Email address to add.
	 * @param string $name Name to associate the email with.
	 *
	 * @return bool
	 */
	public function add_email( $email, $name = '' ) {
		if ( empty( $email ) || ! is_email( $email ) ) {
			return false;
		}

		$emails = $this->get_emails();

		if ( in_array( $email, array_keys( $emails ), true ) ) {
			return false;
		}

		$emails[ $email ] = $name;
		$this->set_emails( $emails );

		return $this->save();
	}

	/**
	 * Removes a single email from storage
	 *
	 * Saves the model storage as a side-effect.
	 *
	 * @param string $email Email address to add.
	 *
	 * @return bool
	 */
	public function drop_email( $email ) {
		if ( empty( $email ) || ! is_email( $email ) ) {
			return false;
		}

		$emails = $this->get_emails();

		if ( ! in_array( $email, array_keys( $emails ), true ) ) {
			return false;
		}

		unset( $emails[ $email ] );
		$this->set_emails( $emails );

		return $this->save();
	}

	/**
	 * Validates a list of email => name pairs
	 *
	 * @param array $emails List of emails to notify, as email => name pairs.
	 *
	 * @return array Emails list, as email => name pairs
	 */
	public function get_valid_emails( $emails = array() ) {
		$result = array();
		if ( empty( $emails ) ) {
			return $result;
		}

		foreach ( $emails as $email => $name ) {
			$email = is_email( $email );
			if ( empty( $email ) ) {
				continue; }

			$name = ! empty( $name )
				? $name
				: __( 'Anonymous', 'shipper' );

			$result[ $email ] = $name;
		}

		return $result;
	}

	/**
	 * Gets an alternate storage method
	 *
	 * Returns a filesystem storage instance.
	 * Used in data jetissoning on import, in order to preserve the options.
	 *
	 * @return object Shipper_Helper_Storage
	 */
	public function get_alternate_storage() {
		$storage   = $this->get_storage();
		$namespace = sprintf( '%s-alternate', $storage->get_namespace() );
		return Shipper_Helper_Storage::get( $namespace, false );
	}

	/**
	 * Pushes data as is to the alternate storage
	 *
	 * This is being done in an effort to preserve options during import.
	 *
	 * @return bool
	 */
	public function jettison_data() {
		$storage       = $this->get_alternate_storage();
		$storage->data = $this->get_data();

		return $storage->save();
	}

	/**
	 * Loads up the jettisoned data and restores it
	 *
	 * This is being done in an effort to preserve options during import.
	 *
	 * @return bool
	 */
	public function retrogress_data() {
		$storage = $this->get_alternate_storage();
		if ( $storage->load() ) {
			$this->set_data( $storage->data );
			$this->save();
			return true;
		}
		return false;
	}
}
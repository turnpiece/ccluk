<?php
/**
 * Shipper models: cached files list class
 *
 * Holds list of cached files for Shipper migrations.
 *
 * @package shipper
 */

/**
 * Stored filelist model class
 */
class Shipper_Model_Stored_Filelist extends Shipper_Model_Stored {

	const KEY_CONFIG_FILES = 'config_files';
	const KEY_ACTIVE_FILES = 'active_files';

	const KEY_DONE = 'is_done';
	const KEY_PATHS = 'paths';
	const KEY_STEP = 'current_step';
	const KEY_TOTAL = 'total_steps';

	const KEY_CURSOR = 'shipper-position';
	const KEY_ACTIVE_DONE = 'shipper-active-done';
	const KEY_SCRUB_CURSOR = 'shipper-scrub-position';

	/**
	 * Constructor
	 *
	 * Sets up appropriate storage namespace
	 */
	public function __construct() {
		parent::__construct( 'filelist' );
	}

	/**
	 * Gets time to live for this storage bucket
	 *
	 * @return int Time to live, in seconds
	 */
	public function get_ttl() {
		return Shipper_Model_Stored::TTL_LONG;
	}
}
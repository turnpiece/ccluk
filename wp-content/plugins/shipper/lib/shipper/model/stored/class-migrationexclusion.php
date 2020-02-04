<?php
/**
 * Author: Hoang Ngo
 */

class Shipper_Model_Stored_MigrationExclusion extends Shipper_Model_Stored {
	const KEY_EXCLUSIONS_FS = 'fs_exclusions';
	const KEY_EXCLUSIONS_DB = 'db_exclusions';
	const KEY_EXCLUSIONS_XX = 'xx_exclusions';
	/**
	 * Constructor
	 *
	 * Sets up appropriate storage namespace
	 */
	public function __construct() {
		parent::__construct( 'migrationexclusion', true );
	}
}
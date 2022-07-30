<?php
/**
 * Shipper models: modals state list
 *
 * Holds a list of modal states
 *
 * @package shipper
 */

/**
 * Stored exclusions model class
 */
class Shipper_Model_Stored_Modals extends Shipper_Model_Stored {

	const STATE_OPEN   = 'open';
	const STATE_CLOSED = 'closed';

	/**
	 * Constructor
	 *
	 * Sets up appropriate storage namespace
	 */
	public function __construct() {
		// Store the model in the database.
		parent::__construct( 'modals', true );
	}
}
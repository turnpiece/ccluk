<?php
/**
 * Handle renamed classes and methods within classes.
 *
 * @package Give_Recurring
 */

/**
 * Give_DB_Customers Class (deprecated)
 *
 * This class is for interacting with the customers' database table.
 *
 * @since 1.0
 */
class Give_Recurring_Subscriber_Deprecated_Methods extends Give_Recurring_Subscriber {

	/**
	 * Give_Recurring_Subscriber constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * There are certain responsibility of this function:
	 *  1. handle backward compatibility for deprecated functions
	 *
	 * @since 1.4
	 *
	 * @param $name
	 * @param $arguments
	 *
	 * @return mixed
	 */
	public function __call( $name, $arguments ) {
		$deprecated_function_arr = array(
			'get_recurring_customer_id',
		);

		if ( in_array( $name, $deprecated_function_arr ) ) {
			switch ( $name ) {
				case 'get_recurring_customer_id':
					$args = ! empty( $arguments[0] ) ? $arguments[0] : '';
					return $this->get_recurring_donor_id( $args );

				case 'get_recurring_customer_ids':
					return $this->get_recurring_donor_ids();
			}
		}
	}

}
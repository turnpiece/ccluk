<?php
/**
 * Donors DB
 *
 * @package     Give
 * @subpackage  Classes/Give_DB_Donors
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_DB_Donors Class
 *
 * This class is for interacting with the donor database table.
 *
 * @since 1.0
 */
class Give_DB_Donors extends Give_DB {

	/**
	 * Give_DB_Donors constructor.
	 *
	 * Set up the Give DB Donor class.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function __construct() {
		/* @var WPDB $wpdb */
		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'give_customers';
		$this->primary_key = 'id';
		$this->version     = '1.0';

		// Set hooks and register table only if instance loading first time.
		if( ! ( Give()->donors instanceof Give_DB_Donors ) ) {
			// Setup hook.
			add_action( 'profile_update', array( $this, 'update_donor_email_on_user_update' ), 10, 2 );

			// Install table.
			$this->register_table();
		}

	}

	/**
	 * Get columns and formats
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return array  Columns and formats.
	 */
	public function get_columns() {
		return array(
			'id'             => '%d',
			'user_id'        => '%d',
			'name'           => '%s',
			'email'          => '%s',
			'payment_ids'    => '%s',
			'purchase_value' => '%f',
			'purchase_count' => '%d',
			'notes'          => '%s',
			'date_created'   => '%s',
		);
	}

	/**
	 * Get default column values
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return array  Default column values.
	 */
	public function get_column_defaults() {
		return array(
			'user_id'        => 0,
			'email'          => '',
			'name'           => '',
			'payment_ids'    => '',
			'purchase_value' => 0.00,
			'purchase_count' => 0,
			'notes'          => '',
			'date_created'   => date( 'Y-m-d H:i:s' ),
		);
	}

	/**
	 * Add a donor
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $data
	 *
	 * @return int|bool
	 */
	public function add( $data = array() ) {

		$defaults = array(
			'payment_ids' => ''
		);

		$args = wp_parse_args( $data, $defaults );

		if ( empty( $args['email'] ) ) {
			return false;
		}

		if ( ! empty( $args['payment_ids'] ) && is_array( $args['payment_ids'] ) ) {
			$args['payment_ids'] = implode( ',', array_unique( array_values( $args['payment_ids'] ) ) );
		}

		$donor = $this->get_donor_by( 'email', $args['email'] );

		// update an existing donor.
		if ( $donor ) {

			// Update the payment IDs attached to the donor
			if ( ! empty( $args['payment_ids'] ) ) {

				if ( empty( $donor->payment_ids ) ) {

					$donor->payment_ids = $args['payment_ids'];

				} else {

					$existing_ids          = array_map( 'absint', explode( ',', $donor->payment_ids ) );
					$payment_ids           = array_map( 'absint', explode( ',', $args['payment_ids'] ) );
					$payment_ids           = array_merge( $payment_ids, $existing_ids );
					$donor->payment_ids = implode( ',', array_unique( array_values( $payment_ids ) ) );

				}

				$args['payment_ids'] = $donor->payment_ids;

			}

			$this->update( $donor->id, $args );

			return $donor->id;

		} else {

			return $this->insert( $args, 'donor' );

		}

	}

	/**
	 * Delete a donor.
	 *
	 * NOTE: This should not be called directly as it does not make necessary changes to
	 * the payment meta and logs. Use give_donor_delete() instead.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  bool|string|int $_id_or_email
	 *
	 * @return bool|int
	 */
	public function delete( $_id_or_email = false ) {

		if ( empty( $_id_or_email ) ) {
			return false;
		}

		$column   = is_email( $_id_or_email ) ? 'email' : 'id';
		$donor = $this->get_donor_by( $column, $_id_or_email );

		if ( $donor->id > 0 ) {

			global $wpdb;

			return $wpdb->delete( $this->table_name, array( 'id' => $donor->id ), array( '%d' ) );

		} else {
			return false;
		}

	}

	/**
	 * Delete a donor.
	 *
	 * NOTE: This should not be called directly as it does not make necessary changes to
	 * the payment meta and logs. Use give_donor_delete() instead.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  int $user_id
	 *
	 * @return bool|int
	 */
	public function delete_by_user_id( $user_id = false ) {

		if ( empty( $user_id ) ) {
			return false;
		}
		global $wpdb;

		return $wpdb->delete( $this->table_name, array( 'user_id' => $user_id ), array( '%d' ) );
	}

	/**
	 * Checks if a donor exists
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  string $value The value to search for. Default is empty.
	 * @param  string $field The Donor ID or email to search in. Default is 'email'.
	 *
	 * @return bool          True is exists, false otherwise.
	 */
	public function exists( $value = '', $field = 'email' ) {
		
		$columns = $this->get_columns();
		if ( ! array_key_exists( $field, $columns ) ) {
			return false;
		}

		return (bool) $this->get_column_by( 'id', $field, $value );

	}

	/**
	 * Attaches a payment ID to a donor
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  int $donor_id Donor ID.
	 * @param  int $payment_id  Payment ID.
	 *
	 * @return bool
	 */
	public function attach_payment( $donor_id = 0, $payment_id = 0 ) {

		$donor = new Give_Donor( $donor_id );

		if ( empty( $donor->id ) ) {
			return false;
		}

		// Attach the payment, but don't increment stats, as this function previously did not
		return $donor->attach_payment( $payment_id, false );

	}

	/**
	 * Removes a payment ID from a donor.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  int $donor_id Donor ID.
	 * @param  int $payment_id  Payment ID.
	 *
	 * @return bool
	 */
	public function remove_payment( $donor_id = 0, $payment_id = 0 ) {

		$donor = new Give_Donor( $donor_id );

		if ( ! $donor ) {
			return false;
		}

		// Remove the payment, but don't decrease stats, as this function previously did not
		return $donor->remove_payment( $payment_id, false );

	}

	/**
	 * Increments donor's donation stats.
	 *
	 * @access public
	 *
	 * @param int   $donor_id Donor ID.
	 * @param float $amount      Amoumt.
	 *
	 * @return bool
	 */
	public function increment_stats( $donor_id = 0, $amount = 0.00 ) {

		$donor = new Give_Donor( $donor_id );

		if ( empty( $donor->id ) ) {
			return false;
		}

		$increased_count = $donor->increase_purchase_count();
		$increased_value = $donor->increase_value( $amount );

		return ( $increased_count && $increased_value ) ? true : false;

	}

	/**
	 * Decrements donor's donation stats.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  int   $donor_id Donor ID.
	 * @param  float $amount      Amount.
	 *
	 * @return bool
	 */
	public function decrement_stats( $donor_id = 0, $amount = 0.00 ) {

		$donor = new Give_Donor( $donor_id );

		if ( ! $donor ) {
			return false;
		}

		$decreased_count = $donor->decrease_donation_count();
		$decreased_value = $donor->decrease_value( $amount );

		return ( $decreased_count && $decreased_value ) ? true : false;

	}

	/**
	 * Updates the email address of a donor record when the email on a user is updated
	 *
	 * @since  1.4.3
	 * @access public
	 *
	 * @param  int          $user_id       User ID.
	 * @param  WP_User|bool $old_user_data User data.
	 *
	 * @return bool
	 */
	public function update_donor_email_on_user_update( $user_id = 0, $old_user_data = false ) {

		$donor = new Give_Donor( $user_id, true );

		if( ! $donor ) {
			return false;
		}

		$user = get_userdata( $user_id );

		if( ! empty( $user ) && $user->user_email !== $donor->email ) {

			if( ! $this->get_donor_by( 'email', $user->user_email ) ) {

				$success = $this->update( $donor->id, array( 'email' => $user->user_email ) );

				if( $success ) {
					// Update some payment meta if we need to
					$payments_array = explode( ',', $donor->payment_ids );

					if( ! empty( $payments_array ) ) {

						foreach ( $payments_array as $payment_id ) {

							give_update_payment_meta( $payment_id, 'email', $user->user_email );

						}

					}

					/**
					 * Fires after updating donor email on user update.
					 *
					 * @since 1.4.3
					 * 
					 * @param  WP_User       $user     WordPress User object.
					 * @param  Give_Donor $donor Give donor object.
					 */
					do_action( 'give_update_donor_email_on_user_update', $user, $donor );

				}

			}

		}

	}
	
	/**
	 * Retrieves a single donor from the database
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  string $field ID or email. Default is 'id'.
	 * @param  mixed  $value The Customer ID or email to search. Default is 0.
	 *
	 * @return mixed         Upon success, an object of the donor. Upon failure, NULL
	 */
	public function get_donor_by( $field = 'id', $value = 0 ) {
		/* @var WPDB $wpdb */
		global $wpdb;

		if ( empty( $field ) || empty( $value ) ) {
			return null;
		}

		if ( 'id' == $field || 'user_id' == $field ) {
			// Make sure the value is numeric to avoid casting objects, for example,
			// to int 1.
			if ( ! is_numeric( $value ) ) {
				return false;
			}

			$value = intval( $value );

			if ( $value < 1 ) {
				return false;
			}

		} elseif ( 'email' === $field ) {

			if ( ! is_email( $value ) ) {
				return false;
			}

			$value = trim( $value );
		}

		if ( ! $value ) {
			return false;
		}

		switch ( $field ) {
			case 'id':
				$db_field = 'id';
				break;
			case 'email':
				$value    = sanitize_text_field( $value );
				$db_field = 'email';
				break;
			case 'user_id':
				$db_field = 'user_id';
				break;
			default:
				return false;
		}

		if ( ! $donor = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $db_field = %s LIMIT 1", $value ) ) ) {

			// Look for donor from an additional email.
			if( 'email' === $field ) {
				$meta_table  = Give()->donor_meta->table_name;
				$donor_id = $wpdb->get_var( $wpdb->prepare( "SELECT customer_id FROM {$meta_table} WHERE meta_key = 'additional_email' AND meta_value = %s LIMIT 1", $value ) );

				if( ! empty( $donor_id ) ) {
					return $this->get( $donor_id );
 				}
 			}

			return false;
		}

		return $donor;
	}

	/**
	 * Retrieve donors from the database.
	 *
	 * @since  1.0
	 * @access public
     *
     * @param  array $args
     *
     * @return array|object|null Customers array or object. Null if not found.
	 */
	public function get_donors( $args = array() ) {
        /* @var WPDB $wpdb */
		global $wpdb;

		$defaults = array(
			'number'  => 20,
			'offset'  => 0,
			'user_id' => 0,
			'orderby' => 'id',
			'order'   => 'DESC'
		);

		$args = wp_parse_args( $args, $defaults );

		if ( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		$where = ' WHERE 1=1 ';

		// specific donors.
		if ( ! empty( $args['id'] ) ) {

			if ( is_array( $args['id'] ) ) {
				$ids = implode( ',', array_map( 'intval', $args['id'] ) );
			} else {
				$ids = intval( $args['id'] );
			}

			$where .= " AND `id` IN( {$ids} ) ";

		}

		// donors for specific user accounts
		if ( ! empty( $args['user_id'] ) ) {

			if ( is_array( $args['user_id'] ) ) {
				$user_ids = implode( ',', array_map( 'intval', $args['user_id'] ) );
			} else {
				$user_ids = intval( $args['user_id'] );
			}

			$where .= " AND `user_id` IN( {$user_ids} ) ";

		}

		//specific donors by email
		if( ! empty( $args['email'] ) ) {

			if( is_array( $args['email'] ) ) {

				$emails_count       = count( $args['email'] );
				$emails_placeholder = array_fill( 0, $emails_count, '%s' );
				$emails             = implode( ', ', $emails_placeholder );

				$where .= $wpdb->prepare( " AND `email` IN( $emails ) ", $args['email'] );
			} else {
				$where .= $wpdb->prepare( " AND `email` = %s ", $args['email'] );
			}
		}

		// specific donors by name
		if( ! empty( $args['name'] ) ) {
			$where .= $wpdb->prepare( " AND `name` LIKE '%%%%" . '%s' . "%%%%' ", $args['name'] );
		}

		// Donors created for a specific date or in a date range
		if ( ! empty( $args['date'] ) ) {

			if ( is_array( $args['date'] ) ) {

				if ( ! empty( $args['date']['start'] ) ) {

					$start = date( 'Y-m-d H:i:s', strtotime( $args['date']['start'] ) );

					$where .= " AND `date_created` >= '{$start}'";

				}

				if ( ! empty( $args['date']['end'] ) ) {

					$end = date( 'Y-m-d H:i:s', strtotime( $args['date']['end'] ) );

					$where .= " AND `date_created` <= '{$end}'";

				}

			} else {

				$year  = date( 'Y', strtotime( $args['date'] ) );
				$month = date( 'm', strtotime( $args['date'] ) );
				$day   = date( 'd', strtotime( $args['date'] ) );

				$where .= " AND $year = YEAR ( date_created ) AND $month = MONTH ( date_created ) AND $day = DAY ( date_created )";
			}

		}

		$args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? 'id' : $args['orderby'];

		if ( 'purchase_value' == $args['orderby'] ) {
			$args['orderby'] = 'purchase_value+0';
		}

		$cache_key = md5( 'give_donors_' . serialize( $args ) );

		$donors = wp_cache_get( $cache_key, 'donors' );

		$args['orderby'] = esc_sql( $args['orderby'] );
		$args['order']   = esc_sql( $args['order'] );

		if ( $donors === false ) {
			$donors = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  $this->table_name $where ORDER BY {$args['orderby']} {$args['order']} LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) ) );
			wp_cache_set( $cache_key, $donors, 'donors', 3600 );
		}

		return $donors;

	}


	/**
	 * Count the total number of donors in the database
	 *
	 * @since  1.0
	 * @access public
     *
     * @param  array $args
     *
     * @return int         Total number of donors.
	 */
	public function count( $args = array() ) {
        /* @var WPDB $wpdb */
		global $wpdb;

		$where = ' WHERE 1=1 ';

		if ( ! empty( $args['date'] ) ) {

			if ( is_array( $args['date'] ) ) {

				$start = date( 'Y-m-d H:i:s', strtotime( $args['date']['start'] ) );
				$end   = date( 'Y-m-d H:i:s', strtotime( $args['date']['end'] ) );

				$where .= " AND `date_created` >= '{$start}' AND `date_created` <= '{$end}'";

			} else {

				$year  = date( 'Y', strtotime( $args['date'] ) );
				$month = date( 'm', strtotime( $args['date'] ) );
				$day   = date( 'd', strtotime( $args['date'] ) );

				$where .= " AND $year = YEAR ( date_created ) AND $month = MONTH ( date_created ) AND $day = DAY ( date_created )";
			}

		}


		$cache_key = md5( 'give_donors_count' . serialize( $args ) );

		$count = wp_cache_get( $cache_key, 'donors' );

		if ( $count === false ) {
			$count = $wpdb->get_var( "SELECT COUNT($this->primary_key) FROM " . $this->table_name . "{$where};" );
			wp_cache_set( $cache_key, $count, 'donors', 3600 );
		}

		return absint( $count );

	}

	/**
	 * Create the table
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function create_table() {

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE " . $this->table_name . " (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		user_id bigint(20) NOT NULL,
		email varchar(50) NOT NULL,
		name mediumtext NOT NULL,
		purchase_value mediumtext NOT NULL,
		purchase_count bigint(20) NOT NULL,
		payment_ids longtext NOT NULL,
		notes longtext NOT NULL,
		date_created datetime NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY email (email),
		KEY user (user_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}
	
	/**
	 * Check if the Customers table was ever installed
	 *
	 * @since  1.4.3
	 * @access public
	 *
	 * @return bool Returns if the donors table was installed and upgrade routine run.
	 */
	public function installed() {
		return $this->table_exists( $this->table_name );
	}

}

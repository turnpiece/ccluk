<?php
/**
 * Give Recurring Upgrades functionality.
 *
 * @package     Give
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Recurring_Upgrades
 */
class Give_Recurring_Upgrades {

	/**
	 * Give_Recurring_Upgrades constructor.
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'automatic_upgrades' ) );

	}

	/**
	 * Automatic upgrades.
	 */
	public function automatic_upgrades() {

		$did_v12_upgrade = give_get_option( 'recurring_v12_upgraded' );

		if ( empty( $did_v12_upgrade ) ) {
			$this->update_v12_upgrades();
		}

	}

	/**
	 * Update 1.2 Upgrade.
	 *
	 * Creates the transaction_id column
	 */
	private function update_v12_upgrades() {

		global $wpdb;

		$wpdb->query( "ALTER TABLE " . $wpdb->prefix . "give_subscriptions ADD `transaction_id` varchar(60) NOT NULL" );

		give_update_option( 'recurring_v12_upgraded', true );

	}

}

new Give_Recurring_Upgrades();
<?php
/**
 * Give Recurring - Stripe iDEAL Gateway
 *
 * @package   Give
 * @copyright Copyright (c) 2016, GiveWP
 * @license   https://opensource.org/licenses/gpl-license GNU Public License
 * @since     1.8
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Recurring_Stripe_Ideal
 *
 * @since 1.8
 */
class Give_Recurring_Stripe_Ideal extends Give_Recurring_Gateway {

	/**
	 * Initialize.
	 *
	 * @since  1.8
	 * @access public
	 *
	 * @return void
	 */
	public function init() {

		// Set ID for Recurring.
		$this->id = 'stripe_ideal';

		// Bailout, if gateway is not active.
		if ( ! give_is_gateway_active( $this->id ) ) {
			return;
		}
	}

	/**
	 * Process Recurring iDEAL payments.
	 *
	 * @since  1.8
	 * @access public
	 *
	 * @param array $donation_data Donation Data.
	 *
	 * @return void
	 */
	public function process_checkout( $donation_data ) {

		// If a recurring iDEAL donation then don't proceed.
		if ( Give_Recurring()->is_donation_recurring( $donation_data ) ) {
			give_set_error( 'not_supported_recurring_stripe_ideal', __( 'Currently, we are not supporting recurring donations with iDEAL.', 'give-recurring' ) );
			give_send_back_to_checkout( "?payment-mode={$this->id}" );
		}
	}

}

new Give_Recurring_Stripe_Ideal();

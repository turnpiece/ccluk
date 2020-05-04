<?php
/**
 * The [give_subscriptions] Shortcode Generator class
 *
 * @package     Give
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Check if Give_Shortcode_Generator exists
//@see: https://github.com/impres-org/give-recurring-donations/issues/175
if ( ! class_exists( 'Give_Shortcode_Generator' ) ) {
	return;
}

/**
 * Class Give_Shortcode_Subscriptions
 */
class Give_Shortcode_Subscriptions extends Give_Shortcode_Generator {

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->shortcode['label'] = __( 'Give Subscriptions', 'give-recurring' );

		parent::__construct( 'give_subscriptions' );
	}


	/**
	 * Define the shortcode attribute fields
	 *
	 * @return array
	 */
	public function define_fields() {

		return array(

			array(
				'type'    => 'listbox',
				'name'    => 'show_status',
				'label'   => __( 'Show Status Column:', 'give-recurring' ),
				'tooltip' => __( 'Do you want to display the subscriptions status column?', 'give-recurring' ),
				'default' => 'true',
				'options' => array(
					'true'  => __( 'Show', 'give-recurring' ),
					'false' => __( 'Hide', 'give-recurring' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'show_renewal_date',
				'label'   => __( 'Show Renewal Date Column:', 'give-recurring' ),
				'tooltip' => __( 'Do you want to display the subscription renewal date column?', 'give-recurring' ),
				'options' => array(
					'true'  => __( 'Show', 'give-recurring' ),
					'false' => __( 'Hide', 'give-recurring' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'show_progress',
				'label'   => __( 'Display Progress Column:', 'give-recurring' ),
				'tooltip' => __( 'Do you want to display the subscription progress column?', 'give-recurring' ),
				'options' => array(
					'true'  => __( 'Show', 'give-recurring' ),
					'false' => __( 'Hide', 'give-recurring' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'show_start_date',
				'label'   => __( 'Display Start Date:', 'give-recurring' ),
				'tooltip' => __( 'Do you want to display the subscription start date column?', 'give-recurring' ),
				'options' => array(
					'true'  => __( 'Show', 'give-recurring' ),
					'false' => __( 'Hide', 'give-recurring' ),
				),
			),
			array(
				'type'    => 'listbox',
				'name'    => 'show_end_date',
				'label'   => __( 'Display End Date:', 'give-recurring' ),
				'tooltip' => __( 'Do you want to display the subscription end date column?', 'give-recurring' ),
				'options' => array(
					'true'  => __( 'Show', 'give-recurring' ),
					'false' => __( 'Hide', 'give-recurring' ),
				),
			),
			array(
				'type'    => 'textbox',
				'name'    => 'subscriptions_per_page',
				'label'   => __( 'Subscriptions per page:', 'give-recurring' ),
				'tooltip' => __( 'How many subscriptions do you want to display per page?', 'give-recurring' ),
				'value'   => 30,
			),
			array(
				'type'    => 'listbox',
				'name'    => 'pagination_type',
				'label'   => __( 'Pagination Type:', 'give-recurring' ),
				'tooltip' => __( 'Select the type pf pagination', 'give-recurring' ),
				'options' => array(
					'next_and_previous'  => __( 'Next and Previous', 'give-recurring' ),
					'numbered'           => __( 'Numbered', 'give-recurring' ),
				),
			),
		);
	}


}

new Give_Shortcode_Subscriptions;

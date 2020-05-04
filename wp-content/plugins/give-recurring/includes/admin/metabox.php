<?php
/**
 * Admin Metabox
 *
 * Adds additional recurring specific information to existing metaboxes and in some cases creates metaboxes.
 *
 * @package     Give_Recurring
 * @subpackage  Admin
 * @copyright   Copyright (c) 2017, GiveWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta box is recurring yes/no field
 *
 * @access      public
 *
 * @param $settings
 *
 * @since       1.0
 * @return      array
 */
function give_donation_levels_recurring_fields( $settings ) {

	$prefix = '_give_';

	// ensure the $settings are in an array we can merge into
	$recurring_select_field = array(
		array(
			'name'        => __( 'Recurring', 'give-recurring' ),
			'id'          => $prefix . 'recurring',
			'type'        => 'radio_inline',
			'row_classes' => 'give-recurring-multi-el give-recurring-option give-recurring-admin-choice-level-row',
			'attributes'  => array(
				'class' => 'give-recurring-admin-choice-level',
			),
			'options'     => array(
				'yes' => __( 'Enabled', 'give-recurring' ),
				'no'  => __( 'Disabled', 'give-recurring' ),
			),
			'default'     => 'no',
		),
	);

	return array_merge( $settings, $recurring_select_field );

}

add_filter( 'give_donation_levels_table_row', 'give_donation_levels_recurring_fields' );

/**
 * Meta box recurring period field.
 *
 * @access public
 *
 * @param array $settings
 *
 * @since  1.6.0
 * @return array
 */

function give_recurring_metabox_interval( $settings ) {

	$intervals = Give_Recurring()->interval();
	$prefix    = '_give_';

	$recurring_select_field = array(
		array(
			'name'        => 'Recurring Options',
			'id'          => $prefix . 'period_interval',
			'type'        => 'select',
			'options'     => $intervals,
			'default'     => '1',
			'row_classes' => 'give-recurring-multi-el give-hidden give-inline-row-control give-inline-row-interval',
		),
	);

	return array_merge( $settings, $recurring_select_field );

}

add_filter( 'give_donation_levels_table_row', 'give_recurring_metabox_interval' );

/**
 * Meta box recurring period field.
 *
 * @access      public
 *
 * @param $settings
 *
 * @since       1.0
 * @return      array
 */

function give_recurring_metabox_period( $settings ) {

	$periods = Give_Recurring()->periods();
	$prefix  = '_give_';

	$recurring_select_field = array(
		array(
			'name'        => '',
			'id'          => $prefix . 'period',
			'type'        => 'select',
			'options'     => $periods,
			'default'     => 'month',
			'row_classes' => 'give-recurring-multi-el give-hidden give-inline-row-control give-inline-row-period',
			'attributes'  => array(
				'class' => 'give-period-field',
			),
		),
	);

	return array_merge( $settings, $recurring_select_field );

}

add_filter( 'give_donation_levels_table_row', 'give_recurring_metabox_period' );


/**
 * Meta box recurring times field.
 *
 * @access      public
 *
 * @param $settings
 *
 * @since       1.0
 * @return      array
 */

function give_recurring_metabox_times( $settings ) {

	$prefix = '_give_';

	$recurring_select_field = array(
		array(
			'name'        => __( 'For', 'give-recurring' ),
			'default'     => '0',
			'id'          => $prefix . 'times',
			'type'        => 'select',
			'options'     => Give_Recurring()->times( 'month' ),
			'row_classes' => 'give-recurring-multi-el give-hidden give-inline-row-control give-inline-row-limit',
			'attributes'  => array(
				'class' => 'give-billing-time-field',
			),
		),
	);

	return array_merge( $settings, $recurring_select_field );

}

add_filter( 'give_donation_levels_table_row', 'give_recurring_metabox_times' );

/**
 * Meta box is recurring yes/no field.
 *
 * @access      public
 *
 * @param array $settings
 *
 * @since       1.0
 * @return      array
 */
function give_single_level_recurring_fields( $settings ) {

	$prefix = '_give_';

	$all_recurring_fields = array(
		array(
			'name'        => __( '', 'give-recurring' ),
			'id'          => $prefix . 'period_interval',
			'type'        => 'select',
			'options'     => Give_Recurring()->interval(),
			'default'     => '1',
			'row_classes' => 'give-recurring-row give-recurring-interval give-hidden',
			'attributes'  => array(
				'class' => 'give-interval-field',
			),
		),
		array(
			'name'        => __( '', 'give-recurring' ),
			'id'          => $prefix . 'period',
			'type'        => 'select',
			'options'     => Give_Recurring()->periods(),
			'default'     => 'month',
			'row_classes' => 'give-recurring-row give-recurring-period-choice-common give-recurring-period give-hidden',
			'attributes'  => array(
				'class' => 'give-period-field',
			),
		),
		array(
			'name'        => __( '', 'give-recurring' ),
			'id'          => $prefix . 'period_default_donor_choice',
			'type'        => 'select',
			'options'     => Give_Recurring()->periods(),
			'default'     => 'month',
			'row_classes' => 'give-recurring-row give-recurring-period-choice-common give-recurring-period-default-choice give-hidden',
			'attributes'  => array(
				'class' => 'give-period-field',
			),
		),
		array(
			'name'        => __( 'for', 'give-recurring' ),
			'default'     => '0',
			'id'          => $prefix . 'times',
			'type'        => 'select',
			'options'     => Give_Recurring()->times( 'month' ),
			'row_classes' => 'give-recurring-row give-recurring-times give-hidden',
			'attributes'  => array(
				'class' => 'give-billing-time-field',
			),
		),
	);
	// Ensure the $settings are in an array we can merge into.
	$recurring_select_field = array(
		array(
			'name'        => __( 'Recurring Donations', 'give-recurring' ),
			'id'          => $prefix . 'recurring',
			'type'        => 'radio',
			'options'     => array(
				'no'        => __( 'Disabled - Not Recurring', 'give-recurring' ),
				'yes_donor' => __( 'Yes - Donor\'s Choice', 'give-recurring' ),
				'yes_admin' => __( 'Yes - Admin Defined', 'give-recurring' ),
			),
			'default'     => 'no',
			'description' => sprintf( __( 'Select which kind of recurring donation form you would like to create. The "Donor\'s Choice" method is the most common option to accept recurring donations. <a href="%s" target="_blank">Read the docs &raquo;</a>', 'give-recurring' ), 'http://docs.givewp.com/recurring-getting-started' ),
			'row_classes' => 'give-recurring-row',
		),
		array(
			'name'        => __( 'Recurring Period', 'give-recurring' ),
			'id'          => $prefix . 'period_functionality',
			'type'        => 'radio_inline',
			'options'     => array(
				'donors_choice' => __( 'Donor\'s Choice', 'give-recurring' ),
				'admin_choice'  => __( 'Preset Period', 'give-recurring' ),
			),
			'default'     => 'admin_choice',
			'description' => __( 'The "Donor\'s Choice" option allows the donor to select the time period (commonly also referred as the "frequency") of their subscription. The "Preset Period" option provides only the selected period for the donor\'s subscription.', 'give-recurring' ),
			'row_classes' => 'give-recurring-row give-recurring-period-functionality give-hidden',
			'attributes'  => array(
				'class' => 'give-period-functionality-field',
			),
		),
		array(
			'name'                 => __( 'Recurring Options', 'give-recurring' ),
			'id'                   => $prefix . 'recurring_options',
			'type'                 => 'all_recurring_options',
			'description'          => __( 'Choose the recurring billing interval, period, and length of time.', 'give-recurring' ),
			'row_classes'          => 'give-recurring-row give-recurring-options give-hidden',
			'all_recurring_fields' => $all_recurring_fields,
			'attributes'           => array(
				'class' => 'give-recurring-options',
			),
		),
		array(
			'name'        => __( 'Recurring Opt-in Checkbox Default', 'give-recurring' ),
			'default'     => 'no',
			'id'          => $prefix . 'checkbox_default',
			'type'        => 'radio_inline',
			'description' => __( 'Would you like the donation form\'s subscription checkbox checked by default?', 'give-recurring' ),
			'row_classes' => 'give-recurring-row give-recurring-checkbox-option give-hidden',
			'options'     => array(
				'yes' => 'Checked',
				'no'  => 'Unchecked',
			),
		),
	);
	array_splice( $settings, 1, 0, $recurring_select_field );

	return $settings;

}

add_filter( 'give_forms_donation_form_metabox_fields', 'give_single_level_recurring_fields' );


/**
 * Show subscription payment statuses in Payment History.
 *
 * @param $value
 * @param $payment_id
 * @param $column_name
 *
 * @return string
 */
function give_recurring_subscription_status_column( $value, $payment_id, $column_name ) {

	if ( 'status' === $column_name && 'give_subscription' === get_post_status( $payment_id ) ) {
		$value = '<div class="give-donation-status status-subscription"><span class="give-donation-status-icon"></span>&nbsp;' . __( 'Renewal', 'give-recurring' ) . '</div>';
	}

	return $value;
}

add_filter( 'give_payments_table_column', 'give_recurring_subscription_status_column', 800, 3 );


/**
 * Display Subscription Payment Notice
 *
 * Adds a subscription payment indicator within the single payment view "Update Payment" metabox (top)
 *
 * @since       1.0
 *
 * @param $payment_id
 */
function give_display_subscription_payment_meta( $payment_id ) {

	$is_sub      = give_get_payment_meta( $payment_id, '_give_subscription_payment' );

	if ( $is_sub ) :
		$subs_db = new Give_Subscriptions_DB();
		$sub_id  = $subs_db->get_column_by( 'id', 'parent_payment_id', $payment_id );
		$sub_url = admin_url( 'edit.php?post_type=give_forms&page=give-subscriptions&id=' . $sub_id );
		?>
		<div id="give-donation-subscription-payments" class="postbox">
			<h3 class="hndle">
				<span><?php _e( 'Subscription Information', 'give-recurring' ); ?></span>
			</h3>

			<div class="inside give-recurring-parent-inside">
				<p class="give-donation-sub-id-p"><span class="give-donation-status-recurring give-tooltip"
														data-tooltip="<?php _e( 'This is a recurring donation and the initial payment made by this donor. All payments made hereafter for the profile are marked as renewal payments.', 'give-recurring' ); ?>"> <?php echo give_recurring_symbol_img(); ?> </span> <?php printf( __( 'Subscription ID: <a href="%1$s">%2$d</a>', 'give-recurring' ), $sub_url, $sub_id ); ?>
				</p>

				<div class="give-donation-renewals-wrap">
					<?php
					$payments = get_posts(
						array(
							'post_status'    => 'give_subscription',
							'post_type'      => 'give_payment',
							'post_parent'    => $payment_id,
							'posts_per_page' => - 1,
						)
					);

					if ( $payments ) :
						?>
						<p><strong><?php _e( 'Renewals:', 'give-recurring' ); ?></strong></p>
						<ul id="give-recurring-sub-payments">
							<?php foreach ( $payments as $payment ) : ?>
								<li>
									<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=' . $payment->ID ) );
									?>"><?php echo give_get_payment_number( $payment->ID ); ?></a> &nbsp;&ndash;&nbsp;
									<span><?php echo date_i18n( get_option( 'date_format' ), strtotime( $payment->post_date ) ); ?>
										&nbsp;&ndash;&nbsp;</span>
									<span>
									<?php
									echo give_donation_amount(
										$payment->ID, array(
											'currency' => true,
											'amount'   => true,
										)
									);
									?>
										</span>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div><!--/.give-donation-renewals-wrap -->
			</div><!--/.inside -->
		</div>
	<?php
	endif;
}

add_action( 'give_view_donation_details_sidebar_before', 'give_display_subscription_payment_meta', 10, 1 );


/**
 * List subscription (sub) payments of a particular parent payment.
 *
 * The parent payment ID is the very first payment made. All payments made after for the profile are sub.
 *
 * @since  1.0
 *
 * @param int $payment_id
 *
 * @return void
 */
function give_recurring_display_parent_payment( $payment_id = 0 ) {

	$payment       = get_post( $payment_id );
	$is_sub        = give_get_payment_meta( $payment->post_parent, '_give_subscription_payment' );

	if ( $is_sub && $payment_id !== $payment->post_parent ) :

		$parent_url = admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=' . $payment->post_parent );
		$parent_id = give_get_payment_number( $payment->post_parent );
		?>
		<div id="give-donation-subscription-payments" class="postbox">
			<h3 class="hndle">
				<span><?php _e( 'Subscription Information', 'give-recurring' ); ?></span>
			</h3>

			<div class="inside give-recurring-renewal-payment-inside">
				<p class="give-recurring-parent-payment-id"><?php printf( __( 'Parent Payment: <a href="%1$s">%2$s</a>', 'give-recurring' ), $parent_url, $parent_id ); ?></p>
			</div>
			<!-- /.inside -->
		</div><!-- /#give-donation-subscription-payments -->
	<?php
	endif;
}

add_action( 'give_view_donation_details_sidebar_before', 'give_recurring_display_parent_payment', 10 );


/**
 * Show Recurring Donation Transaction Message.
 *
 * Displays a message above the "Update Donation" metabox that this payment is a renewal or parent payment.
 *
 * @param $payment_id
 */
function give_show_donation_metabox_notification( $payment_id ) {

	$form_id      = give_get_payment_form_id( $payment_id );
	$subscription = new Give_Subscription();
	$has_parent   = $subscription->is_parent_payment( $payment_id );

	// This is a recurring parent payment (has subscription parent payment).
	if ( $has_parent ) {

		// Parent payment (initial transaction)
		echo '<div class="give-notice give-recurring-notice"><span class="give-donation-status-recurring">' . give_recurring_symbol_img() . '</span>' . __( 'This is a recurring donation and the initial payment made by the donor. All payments made hereafter for the profile are marked as renewal payments.', 'give-recurring' ) . '</div>';

	} elseif ( wp_get_post_parent_id( $payment_id ) ) {

		// Subscription Payment
		echo '<div class="give-notice give-recurring-notice"><span class="give-donation-status-recurring">' . give_recurring_symbol_img() . '</span>' . __( 'This is a renewal payment for the donation form:', 'give-recurring' ) . ' "' . get_the_title( $form_id ) . '"</div>';

	}

}

add_filter( 'give_view_donation_details_totals_after', 'give_show_donation_metabox_notification', 10, 1 );


/**
 * Give Show Parent Payment Table Icon.
 *
 * @param $value
 * @param $payment_id
 * @param $column_name
 *
 * @return string
 */
function give_recurring_show_parent_payment_table_icon( $value, $payment_id, $column_name ) {

    // Bailout, if column is not "status".
	if ( 'status' !== $column_name ) {
        return $value;
    }

	$status = give_recurring_is_parent_donation( $payment_id );

	if ( true === $status ) {

		$value = sprintf(
			'%1$s<span class="give-donation-status-recurring give-tooltip" data-tooltip="%2$s">%3$s</span>',
			$value,
			__('This is a recurring donation and the initial payment made by this donor. All payments made hereafter for the profile are marked as renewal payments.', 'give-recurring'),
			give_recurring_symbol_img()

		);
	}

	return $value;

}


add_filter( 'give_payments_table_column', 'give_recurring_show_parent_payment_table_icon', 10, 3 );

/**
 * Display Subscription transaction IDs for parent payments.
 *
 * @since 1.2
 *
 * @param $payment_id
 */
function give_display_subscription_txn_ids( $payment_id ) {

	$is_sub                      = give_get_payment_meta( $payment_id, '_give_subscription_payment' );
	$payment_meta_transaction_id = give_get_payment_transaction_id( $payment_id );

	// Don't display if payment transaction already set.
	if ( ! empty( $payment_meta_transaction_id ) && intval( $payment_meta_transaction_id ) !== intval( $payment_id ) ) {
		return;
	}

	if ( $is_sub ) :

		$subs_db = new Give_Subscriptions_DB();
		$subs                    = $subs_db->get_subscriptions(
			array(
				'parent_payment_id' => $payment_id,
			)
		);

		// Need subs to continue.
		if ( ! $subs ) {
			return;
		}

		/**
		 * Loop through subs.
		 */
		foreach ( $subs as $sub ) :

			// Need a sub transaction ID to continue.
			if ( ! $sub->get_transaction_id() ) {
				continue;
			}
			?>
			<div class="give-subscription-tx-id give-admin-box-inside">
				<p>
					<span class="label"><?php _e( 'Subscription TXN ID:', 'give-recurring' ); ?></span>&nbsp;
					<span><?php echo apply_filters( 'give_payment_details_subscription_transaction_id-' . $sub->gateway, $sub->get_transaction_id(), $payment_id ); ?></span>
				</p>
			</div>
		<?php
		endforeach;

	endif;
}

add_action( 'give_view_donation_details_payment_meta_after', 'give_display_subscription_txn_ids', 10, 1 );

/**
 * List out the all of the recurring field options.
 *
 * @since 1.6.0
 *
 * @param array $field All recurring fields.
 */
function give_all_recurring_options( $field ) {

	global $thepostid, $post;

	// Get the current donation form ID.
	$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;

	// Get the field value by field key and post id.
	$field['value'] = give_get_field_value( $field, $thepostid );

	// Recurring all fields.
	$all_recurring_fields = $field['all_recurring_fields'];

	?>
	<div id="give-recurring-options" <?php echo ! empty( $field['wrapper_class'] ) ? 'class="' . $field['wrapper_class'] . '"' : ''; ?>>
		<fieldset class="give_recurring_all_options">
			<legend><?php echo $field['name']; ?></legend>
			<?php
			// Loop for the recurring field.
			foreach ( $all_recurring_fields as $recurring_field_key => $recurring_field ) {
				// Output all recurring fields.
				give_render_field( $recurring_field );

			}
			echo give_get_field_description( $field );
			?>
		</fieldset>
	</div>
	<?php

}

/**
 * Save Recurring options.
 *
 * @since  1.6.0
 *
 * @param int $post_id Give Form ID.
 *
 * @return bool
 */
function give_recurring_options_save( $post_id ) {
	// Return if Post is not set.
	if ( ! isset( $_POST ) ) {
		return false;
	}

	// Bail out, if recurring option is not set as donor.
	if ( isset( $_POST['_give_recurring'] ) && ! in_array(
			$_POST['_give_recurring'], array(
			'yes_donor',
			'yes_admin',
		), true
		) ) {
		return false;
	}

	$recurring_options = array(
		'_give_period_interval',
		'_give_period',
		'_give_period_default_donor_choice',
		'_give_times',
	);

	$recurring_options = apply_filters( 'give_recurring_options_keys', $recurring_options );

	$options = give_clean( wp_parse_args( $_POST ) );

	give_recurring_save_custom_field_options( $post_id, $options, $recurring_options );

	return true;
}

// Handle the save action in Recurring options fields settings.
add_action( 'give_pre_process_give_forms_meta', 'give_recurring_options_save', 10, 1 );

/**
 * Add Recurring Settings for Custom Amount, when admin defined is selected.
 *
 * @param array $settings Metabox settings.
 *
 * @since 1.5.6
 * @since 1.6.0 Add new Interval and improve Times options.
 *
 * @return mixed
 */
function give_custom_amount_admin_defined_recurring_fields( $settings ) {

	$prefix = '_give_';

	$all_custom_recurring_fields = array(
		array(
			'name'        => '',
			'id'          => $prefix . 'recurring_custom_amount_interval',
			'type'        => 'select',
			'options'     => Give_Recurring()->interval(),
			'default'     => '1',
			'row_classes' => 'give-recurring-row give-recurring-custom-amount-interval give-hidden give-inline-row-control',
			'attributes'  => array(
				'class' => 'give-interval-field',
			),
		),
		array(
			'name'        => '',
			'id'          => $prefix . 'recurring_custom_amount_period',
			'type'        => 'select',
			'options'     => array(
				'once'    => __( 'One Time', 'give-recurring' ),
				'day'     => __( 'Day', 'give-recurring' ),
				'week'    => __( 'Week', 'give-recurring' ),
				'month'   => __( 'Month', 'give-recurring' ),
				'quarter' => __( 'Quarter', 'give-recurring' ),
				'year'    => __( 'Year', 'give-recurring' ),
			),
			'default'     => 'month',
			'row_classes' => 'give-recurring-row give-recurring-custom-amount-period give-hidden give-inline-row-control give-inline-row-period',
			'attributes'  => array(
				'class' => 'give-period-functionality-field give-period-field',
			),
		),
		array(
			'name'        => __( 'For', 'give-recurring' ),
			'default'     => '0',
			'id'          => $prefix . 'recurring_custom_amount_times',
			'type'        => 'select',
			'options'     => Give_Recurring()->times( 'month' ),
			'row_classes' => 'give-recurring-row give-recurring-custom-amount-times give-hidden give-inline-row-control give-inline-row-limit',
			'attributes'  => array(
				'class' => 'give-billing-time-field',
			),
		),
	);

	$new_settings = array(
		array(
			'name'                        => __( 'Recurring Options', 'give-recurring' ),
			'id'                          => $prefix . 'recurring_custom_options',
			'type'                        => 'all_custom_recurring_options',
			'description'                 => __( 'Choose the recurring billing interval, period, and length of time.', 'give-recurring' ),
			'row_classes'                 => 'give-recurring-row give-recurring-options give-hidden',
			'all_custom_recurring_fields' => $all_custom_recurring_fields,
			'attributes'                  => array(
				'class' => 'give-custom_recurring-options',
			),
		),
	);

	array_splice( $settings, - 3, 0, $new_settings );

	return $settings;
}

add_filter( 'give_forms_donation_form_metabox_fields', 'give_custom_amount_admin_defined_recurring_fields' );

/**
 * List out the all of the custom recurring field options.
 *
 * @since 1.6.0
 *
 * @param array $field All custom recurring fields.
 */
function give_all_custom_recurring_options( $field ) {

	global $thepostid, $post;

	// Get the current donation form ID.
	$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;

	// Get the field value by field key and post id.
	$field['value'] = give_get_field_value( $field, $thepostid );

	// Recurring all fields.
	$all_custom_recurring_fields = $field['all_custom_recurring_fields'];

	?>
	<fieldset class="give_custom_recurring_all_options give-recurring-row give-hidden">
		<legend><?php echo $field['name']; ?></legend>
		<?php
		// Loop for the recurring field.
		foreach ( $all_custom_recurring_fields as $recurring_field_key => $recurring_field ) {
			// Output all recurring fields.
			give_render_field( $recurring_field );

		}
		echo give_get_field_description( $field );
		?>
	</fieldset>
	<?php

}

/**
 * Save Recurring options.
 *
 * @since 1.6.0
 *
 * @param int $post_id Give Form ID.
 *
 * @return bool
 */
function give_recurring_custom_amount_options_save( $post_id ) {
	// Return if Post is not set.
	if ( ! isset( $_POST ) ) {
		return false;
	}

	// Bail out, if recurring option is not set as donor.
	if ( isset( $_POST['_give_recurring'] ) && ! give_is_setting_enabled( $_POST['_give_custom_amount'] ) ) {
		return false;
	}

	$recurring_options = array(
		'_give_recurring_custom_amount_interval',
		'_give_recurring_custom_amount_period',
		'_give_recurring_custom_amount_times',
	);

	$recurring_options = apply_filters( 'give_custom_recurring_options_keys', $recurring_options );

	$options = give_clean( wp_parse_args( $_POST ) );

	give_recurring_save_custom_field_options( $post_id, $options, $recurring_options );

	return true;
}

// Handle the save action in Recurring custom amount options fields settings.
add_action( 'give_pre_process_give_forms_meta', 'give_recurring_custom_amount_options_save', 10, 1 );

/**
 * Save custom recurring field options.
 *
 * @since 1.6.0
 *
 * @param int   $post_id
 * @param array $options
 * @param array $recurring_options
 */
function give_recurring_save_custom_field_options( $post_id, $options, $recurring_options ) {
	// Save Per-Form Settings.
	foreach ( $options as $option_key => $option_value ) {

		// Check if option key is related to gateway field setting.
		if ( in_array( $option_key, $recurring_options ) ) {

			// Clean the value.
			$form_meta_value = give_clean( $_POST[ $option_key ] );

			// Update field value inside the post id.
			update_post_meta( $post_id, $option_key, $form_meta_value );

		}
	}// End foreach().
}

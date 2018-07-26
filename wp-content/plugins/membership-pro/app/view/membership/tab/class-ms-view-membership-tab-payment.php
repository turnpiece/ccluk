<?php
/**
 * Tab: Payment options (paid membership)
 *      Access options (free membership)
 *
 * @since  1.0.0
 * @package Membership2
 * @subpackage View
 */
class MS_View_Membership_Tab_Payment extends MS_View {

	/**
	 * Create view output.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function to_html() {
		$membership = $this->data['membership'];
		$fields = $this->get_fields();

		$gateways_available = 0 != count( $fields['gateways'] );

		if ( isset( $this->data['is_global_payments_set'] ) ) {
			if ( ! $this->data['is_global_payments_set'] ) {
				$gateways_available = false;
			}
		}

		ob_start();
		?>
		<div class="ms-payment-form">
			<?php if ( ! $membership->can_change_payment() && ! $membership->is_free() ) : ?>
				<div class="error below-h2">
					<p>
						<?php _e( 'This membership already has some paying members.', 'membership2' ); ?>
					</p>
					<p>
						<?php _e( 'Any changes will affect new invoices but not existing ones.', 'membership2' ); ?>
					</p>
				</div>
			<?php endif; ?>
			<div class="cf">
				<div class="ms-payment-structure-wrapper ms-half space">
					<?php
					MS_Helper_Html::html_element( $fields['payment_type'] );
					MS_Helper_Html::html_element( $fields['price'] );
					if ( isset( $fields['payment_type_val' ] ) ) {
						MS_Helper_Html::html_element( $fields['payment_type_val'] );
					}
					?>
				</div>
				<div class="ms-payment-types-wrapper ms-half">
					<div class="ms-payment-type-wrapper ms-payment-type-finite ms-period-wrapper">
						<?php
						MS_Helper_Html::html_element( $fields['period_unit'] );
						MS_Helper_Html::html_element( $fields['period_type'] );
						?>
					</div>
					<div class="ms-payment-type-wrapper ms-payment-type-recurring ms-period-wrapper">
						<?php
						MS_Helper_Html::html_element( $fields['pay_cycle_period_unit'] );
						MS_Helper_Html::html_element( $fields['pay_cycle_period_type'] );
						MS_Helper_Html::html_element( $fields['pay_cycle_repetitions'] );
						?>
					</div>
					<div class="ms-payment-type-wrapper ms-payment-type-date-range">
						<?php
						MS_Helper_Html::html_element( $fields['period_date_start'] );
						MS_Helper_Html::html_element( $fields['period_date_end'] );
						?>
					</div>
					<div class="ms-after-end-wrapper">
						<?php MS_Helper_Html::html_element( $fields['on_end_membership_id'] );?>
					</div>
				</div>
			</div>

			<?php /* Only show the trial option for PAID memberships */ ?>
			<?php if ( ! $membership->is_free ) : ?>
			<div class="cf">
				<?php
				$show_trial_note = MS_Plugin::instance()->settings->is_first_paid_membership;
				if ( ! empty( $_GET['edit'] ) ) { $show_trial_note = false; }
				if ( MS_Model_Addon::is_enabled( MS_Model_Addon::ADDON_TRIAL ) ) :
					?>
					<div class="ms-trial-wrapper">
						<?php
						MS_Helper_Html::html_separator();
						MS_Helper_Html::html_element( $fields['trial_period_enabled'] );
						$style = $membership->trial_period_enabled ? '' : 'style="display:none"';
						?>
						<div class="ms-trial-period-details" <?php echo '' . $style; ?>>
							<?php
							MS_Helper_Html::html_element( $fields['trial_period_unit'] );
							MS_Helper_Html::html_element( $fields['trial_period_type'] );
							?>
						</div>
					</div>
					<?php
				else : if ( $show_trial_note ) :
					?>
					<div class="ms-trial-wrapper">
						<?php MS_Helper_Html::html_separator(); ?>
						<h4>
							<?php _e( 'Well done, you just created your first paid membership!', 'membership2' ); ?>
						</h4>
						<p>
							<?php _e( 'To give visitors an extra incentive to register for this Membership you can offer a free trial period for a limited time. Do you want to enable this feature now?', 'membership2' ); ?>
						</p>
						<p>
							<?php MS_Helper_Html::html_element( $fields['enable_trial_addon'] ); ?><br />
							<em><?php _e( 'This message is only displayed once. Ignore it if you do not want to use trial memberships.', 'membership2' ); ?></em><br />
							<em><?php _e( 'You can change this feature anytime by visiting the Add-ons section.', 'membership2' ); ?></em>
						</p>
					</div>
					<?php
				endif; endif;
				?>
			</div>

			<?php if ( $gateways_available ) : ?>
			<div class="cf ms-payment-gateways">
				<?php MS_Helper_Html::html_separator(); ?>
				<p><strong><?php _e( 'Allowed payment gateways', 'membership2' ); ?></strong></p>
				<?php foreach ( $fields['gateways'] as $field ) {
					MS_Helper_Html::html_element( $field );
				} ?>
			</div>
			<?php endif; ?>

			<?php endif; ?>

			<?php
			/**
			 * This action allows other add-ons or plugins to display custom
			 * options in the payment dialog.
			 *
			 * @since  1.0.0
			 */
			do_action(
				'ms_view_membership_tab_payment_form',
				$this,
				$membership
			);

			// Legacy action.
			do_action(
				'ms_view_membership_payment_form',
				$this,
				$membership
			);
			?>
		</div>
		<?php
		$html = ob_get_clean();

		echo $html;
	}

	/**
	 * Returns field definitions to render the payment box for the specified
	 * membership.
	 *
	 * @since  1.0.0
	 *
	 * @return array An array containing all field definitions.
	 */
	private function get_fields() {
		global $wp_locale;

		$membership = $this->data['membership'];
		$action = MS_Controller_Membership::AJAX_ACTION_UPDATE_MEMBERSHIP;
		$nonce = wp_create_nonce( $action );

		$fields = array();
		$fields['price'] = array(
			'id' => 'price',
			'title' => __( 'Payment Amount', 'membership2' ),
			'type' => MS_Helper_Html::INPUT_TYPE_NUMBER,
			'before' => MS_Plugin::instance()->settings->currency_symbol,
			'value' => $membership->price, // Without taxes
			'class' => 'ms-text-smallish',
			'config' => array(
				'step' => 'any',
				'min' => 0,
			),
			'placeholder' => '0' . $wp_locale->number_format['decimal_point'] . '00',
			'ajax_data' => array( 1 ),
		);

		$fields['payment_type'] = array(
			'id' => 'payment_type',
			'title' => __( 'This Membership requires', 'membership2' ),
			'type' => MS_Helper_Html::INPUT_TYPE_SELECT,
			'value' => $membership->payment_type,
			'field_options' => MS_Model_Membership::get_payment_types(),
			'ajax_data' => array( 1 ),
		);

		$fields['period_unit'] = array(
			'id' => 'period_unit',
			'title' => __( 'Grant access for', 'membership2' ),
			'name' => '[period][period_unit]',
			'type' => MS_Helper_Html::INPUT_TYPE_NUMBER,
			'value' => $membership->period_unit,
			'class' => 'ms-text-small',
			'config' => array(
				'step' => 1,
				'min' => 1,
			),
			'placeholder' => '1',
			'ajax_data' => array( 1 ),
		);

		$fields['period_type'] = array(
			'id' => 'period_type',
			'name' => '[period][period_type]',
			'type' => MS_Helper_Html::INPUT_TYPE_SELECT,
			'value' => $membership->period_type,
			'field_options' => MS_Helper_Period::get_period_types( 'plural' ),
			'ajax_data' => array( 1 ),
		);

		$fields['pay_cycle_period_unit'] = array(
			'id' => 'pay_cycle_period_unit',
			'title' => __( 'Payment Frequency', 'membership2' ),
			'name' => '[pay_cycle_period][period_unit]',
			'type' => MS_Helper_Html::INPUT_TYPE_NUMBER,
			'value' => $membership->pay_cycle_period_unit,
			'class' => 'ms-text-small',
			'config' => array(
				'step' => 1,
				'min' => 1,
			),
			'placeholder' => '1',
			'ajax_data' => array( 1 ),
		);

		$fields['pay_cycle_period_type'] = array(
			'id' => 'pay_cycle_period_type',
			'name' => '[pay_cycle_period][period_type]',
			'type' => MS_Helper_Html::INPUT_TYPE_SELECT,
			'value' => $membership->pay_cycle_period_type,
			'field_options' => MS_Helper_Period::get_period_types( 'plural' ),
			'ajax_data' => array( 1 ),
		);

		$fields['pay_cycle_repetitions'] = array(
			'id' => 'pay_cycle_repetitions',
			'title' => __( 'Total Payments', 'membership2' ),
			'name' => '[pay_cycle_repetitions]',
			'type' => MS_Helper_Html::INPUT_TYPE_NUMBER,
			'after' => __( 'payments (0 = unlimited)', 'membership2' ),
			'value' => $membership->pay_cycle_repetitions,
			'class' => 'ms-text-small',
			'config' => array(
				'step' => '1',
				'min' => 0,
			),
			'placeholder' => '0',
			'ajax_data' => array( 1 ),
		);

		$fields['period_date_start'] = array(
			'id' => 'period_date_start',
			'title' => __( 'Grant access from', 'membership2' ),
			'type' => MS_Helper_Html::INPUT_TYPE_DATEPICKER,
			'value' => $membership->period_date_start,
			'placeholder' => __( 'Start Date...', 'membership2' ),
			'ajax_data' => array( 1 ),
		);

		$fields['period_date_end'] = array(
			'id' => 'period_date_end',
			'type' => MS_Helper_Html::INPUT_TYPE_DATEPICKER,
			'value' => $membership->period_date_end,
			'before' => _x( 'to', 'date range', 'membership2' ),
			'placeholder' => __( 'End Date...', 'membership2' ),
			'ajax_data' => array( 1 ),
		);

		$fields['on_end_membership_id'] = array(
			'id' => 'on_end_membership_id',
			'type' => MS_Helper_Html::INPUT_TYPE_SELECT,
			'title' => __( 'After this membership ends', 'membership2' ),
			'value' => $membership->on_end_membership_id,
			'field_options' => $membership->get_after_ms_ends_options(),
			'ajax_data' => array( 1 ),
		);

		$fields['enable_trial_addon'] = array(
			'id' => 'enable_trial_addon',
			'type' => MS_Helper_Html::INPUT_TYPE_BUTTON,
			'value' => __( 'Yes, enable Trial Memberships!', 'membership2' ),
			'button_value' => 1,
			'ajax_data' => array(
				'action' => MS_Controller_Addon::AJAX_ACTION_TOGGLE_ADDON,
				'_wpnonce' => wp_create_nonce( MS_Controller_Addon::AJAX_ACTION_TOGGLE_ADDON ),
				'addon' => MS_Model_Addon::ADDON_TRIAL,
				'field' => 'active',
			),
		);

		$fields['trial_period_enabled'] = array(
			'id' => 'trial_period_enabled',
			'type' => MS_Helper_Html::INPUT_TYPE_RADIO_SLIDER,
			'title' => '<strong>' . __( 'Trial Period', 'membership2' ) . '</strong>',
			'after' => __( 'Offer Free Trial', 'membership2' ),
			'value' => $membership->trial_period_enabled,
			'ajax_data' => array( 1 ),
		);

		$fields['trial_period_unit'] = array(
			'id' => 'trial_period_unit',
			'name' => '[trial_period][period_unit]',
			'before' => __( 'The Trial is free and lasts for', 'membership2' ),
			'type' => MS_Helper_Html::INPUT_TYPE_NUMBER,
			'value' => $membership->trial_period_unit,
			'class' => 'ms-text-small',
			'config' => array(
				'step' => 1,
				'min' => 1,
			),
			'placeholder' => '1',
			'ajax_data' => array( 1 ),
		);

		$fields['trial_period_type'] = array(
			'id' => 'trial_period_type',
			'name' => '[trial_period][period_type]',
			'type' => MS_Helper_Html::INPUT_TYPE_SELECT,
			'value' => $membership->trial_period_type,
			'field_options' => MS_Helper_Period::get_period_types( 'plural' ),
			'ajax_data' => array( 1 ),
		);

		$fields['membership_id'] = array(
			'id' => 'membership_id',
			'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
			'value' => $membership->id,
		);

		$fields['action'] = array(
			'id' => 'action',
			'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
			'value' => $action,
		);

		// Get a list of all payment gateways.
		$gateways = MS_Model_Gateway::get_gateways();
		$fields['gateways'] = array();
		foreach ( $gateways as $gateway ) {
			if ( 'free' == $gateway->id ) { continue; }
			if ( ! $gateway->active ) { continue; }

			$payment_types = $gateway->supported_payment_types();
			$wrapper_class = 'ms-payment-type-' . implode( ' ms-payment-type-', array_keys( $payment_types ) );

			$fields['gateways'][$gateway->id] = array(
				'id' => 'disabled-gateway-' . $gateway->id,
				'type' => MS_Helper_Html::INPUT_TYPE_RADIO_SLIDER,
				'title' => $gateway->name,
				'before' => __( 'Available', 'membership2' ),
				'after' => __( 'Not available', 'membership2' ),
				'value' => ! $membership->can_use_gateway( $gateway->id ),
				'class' => 'reverse',
				'wrapper_class' => 'ms-payment-type-wrapper ' . $wrapper_class,
				'ajax_data' => array(
					'field' => 'disabled_gateways[' . $gateway->id . ']',
					'_wpnonce' => $nonce,
					'action' => $action,
					'membership_id' => $membership->id,
				),
			);
		}

		// Modify some fields for free memberships.
		if ( $membership->is_free ) {
			$fields['price'] = '';
			$fields['payment_type'] = array(
				'id' => 'payment_type',
				'title' => __( 'Access Structure:', 'membership2' ),
				'type' => MS_Helper_Html::INPUT_TYPE_SELECT,
				'value' => $membership->payment_type,
				'field_options' => MS_Model_Membership::get_payment_types( 'free' ),
				'ajax_data' => array( 1 ),
			);
		}

		// Process the fields and add missing default attributes.
		foreach ( $fields as $key => $field ) {
			if ( ! empty( $field['ajax_data'] ) ) {
				if ( ! empty( $field['ajax_data']['action'] ) ) {
					continue;
				}

				if ( ! isset( $fields[ $key ]['ajax_data']['field'] ) ) {
					$fields[ $key ]['ajax_data']['field'] = $fields[ $key ]['id'];
				}
				$fields[ $key ]['ajax_data']['_wpnonce'] = $nonce;
				$fields[ $key ]['ajax_data']['action'] = $action;
				$fields[ $key ]['ajax_data']['membership_id'] = $membership->id;
			}
		}

		return apply_filters(
			'ms_view_membership_tab_payment_fields',
			$fields
		);
	}

}
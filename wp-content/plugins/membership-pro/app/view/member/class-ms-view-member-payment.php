<?php

/**
 * Dialog: Member Payment Infos
 *
 * Extends MS_View for rendering methods and magic methods.
 *
 * @since  1.0.0
 * @package Membership2
 * @subpackage View
 */
class MS_View_Member_Payment extends MS_Dialog {

	/**
	 * Generate/Prepare the dialog attributes.
	 *
	 * @since  1.0.0
	 */
	public function prepare() {
		$subscription_id = $_POST['subscription_id'];
		$subscription = MS_Factory::load( 'MS_Model_Relationship', $subscription_id );

		$data = array(
			'model' => $subscription,
		);

		$data = apply_filters( 'ms_view_member_payment_data', $data );

		// Dialog Title
		$this->title = sprintf(
			__( 'Subscription Details: %1$s', 'membership2' ),
			esc_html( $subscription->get_membership()->name )
		);

		// Dialog Size
		$this->width = 940;
		$this->height = 600;

		// Contents
		$this->content = $this->get_contents( $data );

		// Make the dialog modal
		$this->modal = true;
	}

	/**
	 * Save the dialog details.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function submit() {
		// Does nothing...
	}

	/**
	 * Returns the contens of the dialog
	 *
	 * @since  1.0.0
	 *
	 * @return object
	 */
	public function get_contents( $data ) {
		$subscription = $data['model'];
		$gateways = MS_Model_Gateway::get_gateway_names();
		$invoices = $subscription->get_invoices();

		$pay_details = array();
		$inv_details = array();

		foreach ( $subscription->get_payments() as $payment ) {
			if ( isset( $gateways[ $payment['gateway'] ] ) ) {
				$gateway = $gateways[ $payment['gateway'] ];
			} else {
				$gateway = '(' . $payment['gateway'] . ')';
			}

			$pay_details[] = array(
				'title' => __( 'Recorded Payment', 'membership2' ),
				'type' => MS_Helper_Html::TYPE_HTML_TABLE,
				'value' => array(
					0 => array( 'Payment Date', $payment['date'] ),
					1 => array( 'Payment Gateway', $gateway ),
					2 => array( 'Amount', $payment['amount'] ),
					3 => array( 'External ID', $payment['external_id'] ),
				),
				'field_options' => array(
					'head_col' => true,
				),
			);
		}

		$invoice_list = array();
		foreach ( $invoices as $invoice ) {
			if ( isset( $gateways[ $invoice->gateway_id ] ) ) {
				$gateway = $gateways[ $invoice->gateway_id ];
			} else {
				$gateway = '(' . $invoice->gateway_id . ')';
			}

			$transaction_log = sprintf(
				' <small>- <a href="%s" target="_blank">%s</a></small>',
				MS_Controller_Plugin::get_admin_url(
					'billing',
					array( 'show' => 'logs', 'invoice' => $invoice->id )
				),
				__( 'Show Transaction', 'membership2' )
			);
			$invoice_list[] = $invoice->id;

			$inv_details[$invoice->id] = array(
				'title' => sprintf( __( 'Invoice %s', 'membership2' ), $invoice->id ),
				'type' => MS_Helper_Html::TYPE_HTML_TABLE,
				'value' => array(
					0 => array( 'Invoice ID', $invoice->id . $transaction_log ),
					2 => array( 'Create Date', $invoice->invoice_date ),
					1 => array( 'Payment Gateway', $gateway ),
					2 => array( 'Due Date', $invoice->due_date ),
					3 => array( 'Regular amount', $invoice->amount ),
					4 => array( 'Total billed', $invoice->total ),
					5 => array( 'Status', $invoice->status ),
					6 => array( 'Notes', $invoice->description ),
					7 => array( 'Checkout IP', $invoice->checkout_ip ),
					8 => array( 'Checkout Date', $invoice->checkout_date ),
				),
				'field_options' => array(
					'head_col' => true,
				),
			);

			if ( $invoice->amount == $invoice->total ) {
				unset( $inv_details[$invoice->id]['value'][3] );
				$inv_details[$invoice->id]['value'] = array_values( $inv_details[$invoice->id]['value'] );
			}
		}

		$transaction_url = MS_Controller_Plugin::get_admin_url(
			'billing',
			array( 'show' => 'logs', 'invoice' => implode( ',', $invoice_list ) )
		);
		$transaction_log = array(
			'type' => MS_Helper_Html::TYPE_HTML_LINK,
			'value' => __( 'Show all Transactions for this subscription', 'membership2' ),
			'url' => $transaction_url,
			'target' => '_blank',
		);

		ob_start();
		?>
		<div class="wpmui-grid-8 ms-payment-infos">
			<div class="col-5">
				<?php
				foreach ( $inv_details as $detail ) {
					MS_Helper_Html::html_element( $detail );
				}
				?>
			</div>
			<div class="col-3">
				<?php
				MS_Helper_Html::html_element( $transaction_log );
				MS_Helper_Html::html_separator();
				foreach ( $pay_details as $detail ) {
					MS_Helper_Html::html_element( $detail );
				}
				?>
			</div>
		</div>
		<?php
		$html = ob_get_clean();
		return apply_filters( 'ms_view_member_payment_to_html', $html );
	}

};
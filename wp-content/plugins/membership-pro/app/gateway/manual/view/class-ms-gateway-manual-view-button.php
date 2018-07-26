<?php

class MS_Gateway_Manual_View_Button extends MS_View {

	public function to_html() {
		$fields = $this->prepare_fields();
		$subscription = $this->data['ms_relationship'];
		$invoice = $subscription->get_current_invoice();
		$gateway = $this->data['gateway'];

		$action_url = MS_Model_Pages::get_page_url( MS_Model_Pages::MS_PAGE_REGISTER );
		$action_url = apply_filters(
			'ms_gateway_manual_view_button_form_action_url',
			$action_url
		);

		$row_class = 'gateway_' . $gateway->id;
		if ( ! $gateway->is_live_mode() ) {
			$row_class .= ' sandbox-mode';
		}

		ob_start();
		?>
		<form action="<?php echo esc_url( $action_url ); ?>" method="post">
			<?php
			foreach ( $fields as $field ) {
				MS_Helper_Html::html_element( $field );
			}
			?>
		</form>
		<?php
		$payment_form = apply_filters(
			'ms_gateway_form',
			ob_get_clean(),
			$gateway,
			$invoice,
			$this
		);

		ob_start();
		?>
		<tr class="<?php echo esc_attr( $row_class ); ?>">
			<td class="ms-buy-now-column" colspan="2">
				<?php echo $payment_form; ?>
			</td>
		</tr>
		<?php
		$html = ob_get_clean();

		$html = apply_filters(
			'ms_gateway_button-' . $gateway->id,
			$html,
			$this
		);

		$html = apply_filters(
			'ms_gateway_button',
			$html,
			$gateway->id,
			$this
		);

		return $html;
	}

	private function prepare_fields() {
		$gateway = $this->data['gateway'];
		$subscription = $this->data['ms_relationship'];

		$fields = array(
			'_wpnonce' => array(
				'id' => '_wpnonce',
				'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
				'value' => wp_create_nonce( "{$gateway->id}_{$subscription->id}" ),
			),
			'gateway' => array(
				'id' => 'gateway',
				'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
				'value' => $gateway->id,
			),
			'ms_relationship_id' => array(
				'id' => 'ms_relationship_id',
				'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
				'value' => $subscription->id,
			),
			'step' => array(
				'id' => 'step',
				'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
				'value' => $this->data['step'],
			),
		);

		if ( false !== strpos( $gateway->pay_button_url, '://' ) ) {
			$fields['submit'] = array(
				'id' => 'submit',
				'type' => MS_Helper_Html::INPUT_TYPE_IMAGE,
				'value' => $gateway->pay_button_url,
			);
		} else {
			$fields['submit'] = array(
				'id' => 'submit',
				'type' => MS_Helper_Html::INPUT_TYPE_SUBMIT,
				'value' => $gateway->pay_button_url
					? $gateway->pay_button_url
					: __( 'Signup', 'membership2' ),
			);
		}

		return $fields;
	}
}
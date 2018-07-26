<?php

class MS_Gateway_Authorize_View_Card extends MS_View {

	public function to_html() {
		$action_url = '';
		$fields = $this->prepare_fields();

		ob_start();
		?>
			<div class='ms-wrap ms-card-info-wrapper'>
				<h2><?php _e( 'Credit card info', 'membership2' ); ?> </h2>
				<table class="ms-table">
					<tbody>
						<tr>
							<th><?php _e( 'Card Number', 'membership2' ); ?></th>
							<th><?php _e( 'Card Expiration date', 'membership2' ); ?></th>
						</tr>
						<tr>
							<td><?php echo '**** **** **** ' . $this->data['authorize']['card_num']; ?></td>
							<td><?php echo '' . $this->data['authorize']['card_exp']; ?></td>
						</tr>
					</tbody>
				</table>
				<form action="<?php echo esc_url( $action_url ); ?>" method="post">
					<?php
						foreach ( $fields as $field ) {
							MS_Helper_Html::html_element( $field );
						}
					?>
				</form>
				<div class="clear"></div>
			</div>
		<?php
		return ob_get_clean();
	}

	private function prepare_fields() {
		$fields = array(
			'gateway' => array(
				'id' 	=> 'gateway',
				'type' 	=> MS_Helper_Html::INPUT_TYPE_HIDDEN,
				'value' => $this->data['gateway']->id,
			),

			'ms_relationship_id' => array(
				'id' 	=> 'ms_relationship_id',
				'type' 	=> MS_Helper_Html::INPUT_TYPE_HIDDEN,
				'value' => $this->data['ms_relationship_id'],
			),

			'_wpnonce' => array(
				'id' 	=> '_wpnonce',
				'type' 	=> MS_Helper_Html::INPUT_TYPE_HIDDEN,
				'value' => wp_create_nonce( 'update_card' ),
			),

			'action' => array(
				'id' 	=> 'action',
				'type' 	=> MS_Helper_Html::INPUT_TYPE_HIDDEN,
				'value' => 'update_card',
			),

			'submit' => array(
				'id' 	=> 'submit',
				'type' 	=> MS_Helper_Html::INPUT_TYPE_SUBMIT,
				'value' => __( 'Change card number', 'membership2' ),
			),
		);

		return $this;
	}
}
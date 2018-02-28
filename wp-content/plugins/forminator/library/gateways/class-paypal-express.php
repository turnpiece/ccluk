<?php
/**
 * PayPal Express Payment Gateway
 *
 * @since 1.0
 */

/**
 * To do
 * - do form validation before requesting paypal
 */

class Forminator_PayPal_Express extends Forminator_Payment_Gateway {
	/**
	 * Gateway slug
	 *
	 * @var string
	 */
	protected $_slug = 'paypal_express';

	/**
	 * Api mode
	 *
	 * @var string
	 */
	protected $api_mode = '';

	/**
	 * Client ID
	 *
	 * @var string
	 */
	protected $client_id = '';

	/**
	 * Secret
	 *
	 * @var string
	 */
	protected $secret = '';

	/**
	 * Currency
	 *
	 * @var string
	 */
	protected $currency = '';

	/**
	 * Init PayPal settings
	 *
	 * @since 1.0
	 */
	public function init_settings() {
		$this->_total_field = 'payment_gateway_total';
		$this->api_mode     = get_option( "forminator_paypal_api_mode", "sandbox" );
		$this->client_id	= get_option( "forminator_paypal_client_id", false );
		$this->secret    	= get_option( "forminator_paypal_secret", false );
		$this->currency 	= get_option( "forminator_currency", "USD" );
		$this->_enabled 	= forminator_has_paypal_settings();
	}

	/**
	 * Handle purchase
	 *
	 * @since 1.0
	 * @param array $response
	 * @param array $product_fields
	 * @param $field_data_array
	 * @param int $entry_id
	 * @param int $page_id
	 * @param int $shipping
	 *
	 * @return array
	 */
	protected function handle_purchase( $response, $product_fields, $field_data_array, $entry_id, $page_id, $shipping ) {
		return $response;
	}

	/**
	 * Gateway footer scripts
	 *
	 * @since 1.0
	 */
	public function gateway_footer_scripts() {
		?><script src="https://www.paypalobjects.com/api/checkout.js"></script> <?php
	}

	/**
	 * Gateway footer scripts
	 *
	 * @since 1.0
	 */
	public function render_buttons_script( $paypal_form_id ) {
		?>
		<script>
			paypal.Button.render({

				env: '<?php echo ( $this->api_mode === 'live' ) ? "production" : "sandbox"; ?>',
				client: {
					sandbox:    '<?php echo $this->client_id; ?>',
					production: '<?php echo $this->client_id; ?>'
				},

				// Show the buyer a 'Pay Now' button in the checkout flow
				commit: true,

				// payment() is called when the button is clicked
				payment: function(data, actions) {

					// Make a call to the REST api to create the payment
					return actions.payment.create({
						payment: {
							transactions: [
								{
									amount: {
										total: jQuery('.forminator-custom-form-<?php echo $paypal_form_id; ?>').find("input[name='<?php echo $this->_total_field; ?>']").val(),
										currency: '<?php echo $this->currency ?>'
									}
								}
							]
						}
					});
				},

				// onAuthorize() is called when the buyer approves the payment
				onAuthorize: function(data, actions) {
					// Make a call to the REST api to execute the payment
					return actions.payment.execute().then(function() {

						var $form = jQuery('.forminator-custom-form-<?php echo $paypal_form_id; ?>'),
							get_nonce = $form.find('input[name="forminator_nonce"]').val(),
							$target_message = $form.find('.forminator-cform-response-message'),
							get_total = $form.find("input[name='<?php echo $this->_total_field; ?>']").val();

						//send info via ajax to confirm payment on the backend
						var payment_data = { payment_id: data.paymentID, payment_total: get_total, forminator_nonce: get_nonce, action: 'forminator_submit_form_custom-forms'};
						$target_message.html('');
						$target_message.html('<label class="forminator-label--info"><span>' + ForminatorFront.cform.gateway.processing + '</span></label>');
						jQuery.ajax({
							type: 'POST',
							url: ForminatorFront.ajaxUrl,
							data: jQuery.param(payment_data),
							success: function (response) {
								$target_message.html('');
								if (response.data.success === true) {
									$target_message.html('<label class="forminator-label--success"><span>' + ForminatorFront.cform.gateway.paid + '</span></label>');
									jQuery('.forminator-custom-form-<?php echo $paypal_form_id ?>').trigger('submit');
								} else {
									$target_message.html('<label class="forminator-label--error"><span>' + ForminatorFront.cform.gateway.error + '</span></label>');
								}
							},
							error: function () {
								$target_message.html('');
								$target_message.html('<label class="forminator-label--error"><span>' + ForminatorFront.cform.gateway.error + '</span></label>');
							}
						});
					});
				}
			}, '#paypal-button-container-<?php echo $paypal_form_id ?>');

		</script>
		<?php
	}

	/**
	 * Make PayPal call
	 *
	 * @since 1.0
	 * @param $paymentID
	 * @param $payment_total
	 *
	 * @return bool
	 */
	public function paypal_check($paymentID, $payment_total){
		if ( $this->api_mode === 'live' ) {
			$paypal_base_url = "https://api.paypal.com/v1/";
		} else {
			$paypal_base_url = "https://api.sandbox.paypal.com/v1/";
		}

		$ch       = curl_init();
		$clientId = $this->client_id;
		$secret   = $this->secret;
		curl_setopt($ch, CURLOPT_URL, $paypal_base_url .'oauth2/token');
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERPWD, $clientId . ":" . $secret);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
		$result      = curl_exec($ch);
		$accessToken = null;


		if( empty( $result ) ) {
			return false;
		} else {
			$json        = json_decode( $result );
			$accessToken = $json->access_token;
			$curl        = curl_init($paypal_base_url . 'payments/payment/' . $paymentID);
			curl_setopt($curl, CURLOPT_POST, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array(
				'Authorization: Bearer ' . $accessToken,
				'Accept: application/json',
				'Content-Type: application/xml'
			));
			$response = curl_exec($curl);
			$result   = json_decode($response);
			$state    = $result->state;
			$total    = $result->transactions[0]->amount->total;
			$currency = $result->transactions[0]->amount->currency;
			$subtotal = $result->transactions[0]->amount->details->subtotal;
			$recipient_name = $result->transactions[0]->item_list->shipping_address->recipient_name;
			curl_close($ch);
			curl_close($curl);

			$front_price = $payment_total;
			$front_currency = $this->currency;

			if ( $state === 'approved' && $currency === $front_currency && $front_price ==  $subtotal ){
				return true;
			}
			else{
				return false;
			}
		}
	}
}
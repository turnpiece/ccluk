<?php
$api_mode     	= get_option( "forminator_paypal_api_mode", "" );	    	 	 	 	 	 				 	 
$client_id 		= get_option( "forminator_paypal_client_id", "" );
$secret 		= get_option( "forminator_paypal_secret", "" );
?>
<div class="wpmudev-hidden-popup wpmudev-popup-form" style="display: none">
	<div>

	    <label><?php _e( "PayPal Mode", Forminator::DOMAIN ); ?></label>

	    <select class="wpmudev-select" name="api_mode">
	        <option value="test" <?php selected( $api_mode, "test" ); ?> ><?php _e( "Test Mode (Sandbox)", Forminator::DOMAIN ); ?></option>
	        <option value="live" <?php selected( $api_mode, "live" ); ?>><?php _e( "Live Mode", Forminator::DOMAIN ); ?></option>
	    </select>

	</div>

	<div class="wpmudev-box-gray">

	    <div class="wpmudev-row">

	        <div class="wpmudev-col col-12">

	            <label><?php _e( "Client ID", Forminator::DOMAIN ); ?></label>

	            <input class="wpmudev-input" name="client_id" value="<?php echo $client_id; ?>">

	        </div>

	    </div>

	    <div class="wpmudev-row">

	        <div class="wpmudev-col col-12">

	            <label><?php _e( "Secret", Forminator::DOMAIN ); ?></label>

	            <input class="wpmudev-input" name="secret" value="<?php echo $secret; ?>">

	        </div>

	    </div>

		 <div class="wpmudev-row">

	        <div class="wpmudev-col col-12">

	            <button class="wpmudev-button wpmudev-action-done wpmudev-button-blue" data-nonce="<?php echo wp_create_nonce( 'forminator_save_popup_paypal' ) ?>"><?php _e( "Done", Forminator::DOMAIN ); ?> </button>

	        </div>

	    </div>

	</div>
</div>
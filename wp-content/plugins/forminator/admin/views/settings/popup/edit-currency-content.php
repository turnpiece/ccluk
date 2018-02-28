<?php
$currency     	= get_option( "forminator_currency", "USD" );
$currencies 	= forminator_currency_list();
?>
<div class="wpmudev-hidden-popup wpmudev-popup-form" style="display: none">

	<div class="wpmudev-box-gray">

	    <div class="wpmudev-row">

	        <div class="wpmudev-col col-12">

	            <label><?php _e( "Currency", Forminator::DOMAIN ); ?></label>

	            <select class="wpmudev-select" name="currency">
					<?php
					foreach ( $currencies as $key => $value ) {
						?>
						<option value="<?php echo $key; ?>" <?php selected( $currency, $key ); ?>><?php echo $value[0]; ?></option>
						<?php
					}
					?>
				</select>

	        </div>

	    </div>

		 <div class="wpmudev-row">

	        <div class="wpmudev-col col-12">

	            <button class="wpmudev-button wpmudev-action-done wpmudev-button-blue" data-nonce="<?php echo wp_create_nonce( 'forminator_save_popup_currency' ) ?>"><?php _e( "Done", Forminator::DOMAIN ); ?> </button>

	        </div>

	    </div>

	</div>
</div>
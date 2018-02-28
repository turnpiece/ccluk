<?php
$icon_minus   	= forminator_plugin_dir() . "assets/icons/admin-icons/minus.php";
$api_mode     	= get_option( "forminator_paypal_api_mode", "" );
$client_id 		= get_option( "forminator_paypal_client_id", "" );
$secret 		= get_option( "forminator_paypal_secret", "" );
?>

<div class="wpmudev-box wpmudev-can--hide">

    <div class="wpmudev-box-header">

        <div class="wpmudev-header--text">

            <h2 class="wpmudev-subtitle"><?php _e( "Paypal Express", Forminator::DOMAIN ); ?></h2>

        </div>

        <div class="wpmudev-header--action">

			<button class="wpmudev-box--action">

                <span class="wpmudev-icon--plus" aria-hidden="true"></span>

                <span class="wpmudev-sr-only"><?php _e( "Hide box", Forminator::DOMAIN ); ?></span>

            </button>

		</div>

    </div>

    <div class="wpmudev-box-section">

        <?php if( ! forminator_has_paypal_settings() ) { ?>

            <div class="wpmudev-section--text">

                <label class="wpmudev-label--notice"><span><?php _e( "Add Paypal credentials to create checkout forms.", Forminator::DOMAIN ); ?></label>

                <p><?php _e( "Express Checkout is PayPal's premier checkout solution, which streamlines the checkout process for buyers and keeps them on your site after making a purchase.", Forminator::DOMAIN ); ?></p>

                <p><button class="wpmudev-button wpmudev-button-sm wpmudev-button-ghost wpmudev-open-modal" data-modal="paypal" data-nonce="<?php echo wp_create_nonce( 'forminator_popup_paypal' ) ?>"><?php _e( "Add Credentials", Forminator::DOMAIN ); ?></button></p>

            </div>

        <?php } else { ?>

            <div class="wpmudev-section--table">

                <label class="wpmudev-label--info"><span><?php _e( "You may need to do a free upgrade to a business account in order to properly make use of Paypal Express.", Forminator::DOMAIN ); ?></label>

                <table class="wpmudev-table">

                    <thead>

                        <tr><th colspan="2"><?php _e( "Paypal Credentials", Forminator::DOMAIN ); ?></th></tr>

                    </thead>

                    <tbody>

                        <tr>

                            <th>

                                <p class="wpmudev-table--text"><?php _e( "Client ID:", Forminator::DOMAIN ); ?></p>

                            </th>

                            <td>

                                <p class="wpmudev-table--text" style="text-align: left"><?php echo $client_id; ?></p>

                            </td>

                        </tr>

                        <tr>

                            <th>

                                <p class="wpmudev-table--text"><?php _e( "Secret:", Forminator::DOMAIN ); ?></p>

                            </th>

                            <td>

                                <p class="wpmudev-table--text" style="text-align: left"><?php echo $secret; ?></p>

                            </td>

                        </tr>

                    </tbody>

                    <tfoot>

                        <tr>

                            <td colspan="2">

                                <div class="wpmudev-table--text"><button class="wpmudev-button wpmudev-button-sm wpmudev-button-blue wpmudev-open-modal" data-modal="paypal" data-nonce="<?php echo wp_create_nonce( 'forminator_popup_paypal' ) ?>"><?php _e( "Edit Credentials", Forminator::DOMAIN ); ?></button></div>

                            </td>

                        </tr>

                    </tfoot>

                </table>

            </div>

        <?php } ?>

    </div>

</div>
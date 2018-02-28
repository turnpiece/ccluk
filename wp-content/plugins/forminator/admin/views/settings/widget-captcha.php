<?php
$path = forminator_plugin_dir();

$icon_minus = $path . "assets/icons/admin-icons/minus.php";
$captcha_key     = get_option( "forminator_captcha_key", "" );
$captcha_secret  = get_option( "forminator_captcha_secret", "" );

$new = true;
?>

<div class="wpmudev-box wpmudev-can--hide">

    <div class="wpmudev-box-header">

        <div class="wpmudev-header--text">

            <h2 class="wpmudev-subtitle"><?php _e( "Google reCaptcha", Forminator::DOMAIN ); ?></h2>

        </div>

        <div class="wpmudev-header--action">

            <button class="wpmudev-box--action">

                <span class="wpmudev-icon--plus" aria-hidden="true"></span>

                <span class="wpmudev-sr-only"><?php _e( "Hide box", Forminator::DOMAIN ); ?></span>

            </button>

		</div>

    </div>

    <div class="wpmudev-box-section">
		  <?php if( ! forminator_has_captcha_settings() ) { ?>
		  <div class="wpmudev-section--text">

				<label class="wpmudev-label--notice"><span><?php _e( "Add Google Captcha settings to enable Captcha field.", Forminator::DOMAIN ); ?></label>

				<p><?php _e( "reCAPTCHA is a free service that protects your site from spam and abuse", Forminator::DOMAIN ); ?></p>

				<p><button class="wpmudev-button wpmudev-button-sm wpmudev-button-ghost wpmudev-open-modal" data-modal="captcha" data-nonce="<?php echo wp_create_nonce( 'forminator_popup_captcha' ) ?>"><?php _e( "Add Credentials", Forminator::DOMAIN ); ?></button></p>

		  </div>
		  <?php } else { ?>
        <div class="wpmudev-section--table">

            <label class="wpmudev-label--notice"><span><?php _e( "Please note, these settings are required only if you decide to use the reCAPTCHA field.", Forminator::DOMAIN ); ?></label>

            <table class="wpmudev-table">

                <thead>

                    <tr><th colspan="2"><?php _e( "reCAPTCHA Credentials", Forminator::DOMAIN ); ?></th></tr>

                </thead>

                <tbody>

                    <tr>

                        <th>

                            <p class="wpmudev-table--text"><?php _e( "Site Key:", Forminator::DOMAIN ); ?></p>

                        </th>

                        <td>

                            <p class="wpmudev-table--text" style="text-align: left"><?php echo $captcha_key; ?></p>

                        </td>

                    </tr>

                    <tr>

                        <th>

                            <p class="wpmudev-table--text"><?php _e( "Secret Key:", Forminator::DOMAIN ); ?></p>

                        </th>

                        <td>

                            <p class="wpmudev-table--text" style="text-align: left"><?php echo $captcha_secret; ?></p>

                        </td>

                    </tr>

                </tbody>

                <tfoot>

                    <tr>

                        <td colspan="2">

                            <div class="wpmudev-table--text"><button class="wpmudev-button wpmudev-button-sm wpmudev-button-blue wpmudev-open-modal" data-modal="captcha" data-nonce="<?php echo wp_create_nonce( 'forminator_popup_captcha' ) ?>"><?php _e( "Edit Credentials", Forminator::DOMAIN ); ?></button></div>

                        </td>

                    </tr>

                </tfoot>

            </table>

        </div>

	  <?php } ?>

    </div>

</div>
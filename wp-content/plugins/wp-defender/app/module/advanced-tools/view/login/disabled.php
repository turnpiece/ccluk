<h2><?php _e( "Security", wp_defender()->domain ) ?></h2>
<table class="form-table">
    <tbody>
    <tr class="user-sessions-wrap hide-if-no-js">
        <th><?php _e( "Two Factor Authentication", wp_defender()->domain ) ?></th>
        <td aria-live="assertive">
			<?php
			$settings = \WP_Defender\Module\Advanced_Tools\Model\Auth_Settings::instance();
			if ( $settings->forceAuth ):
				?>
                <div class="def-warning">
                    <i class="dashicons dashicons-warning" aria-hidden="true"></i>
					<?php
					echo $settings->forceAuthMess
					?>
                </div>
			<?php endif; ?>
			<?php
			$user  = wp_get_current_user();
			$email = '';
			if ( is_object( $user ) ) {
				$email = $user->user_email;
			}
			?>
            <div id="def2">
                <div class="destroy-sessions">
                    <button type="button" class="button" id="show2AuthActivator">
						<?php _e( "Enable", wp_defender()->domain ) ?>
                    </button>
                </div>
                <p class="description">
					<?php _e( "Use the Google Authenticator app to sign in with a separate passcode.", wp_defender()->domain ) ?>
                </p>
            </div>
            <div id="def2qr">
                <button type="button" id="hide2AuthActivator"
                        class="button"><?php _e( "Cancel", wp_defender()->domain ) ?></button>
                <p><?php _e( "Use the Google Authenticator app to sign in with a separate passcode.", wp_defender()->domain ) ?></p>
                <div class="card">
                    <p>
                        <strong><?php _e( "1. Install the Verification app", wp_defender()->domain ) ?></strong>
                    </p>
                    <p>
						<?php _e( "Download and install the Google Authenticator app on your device using the links below.", wp_defender()->domain ) ?>
                    </p>
                    <a href="https://itunes.apple.com/vn/app/google-authenticator/id388497605?mt=8">
                        <img src="<?php echo wp_defender()->getPluginUrl() . 'assets/img/ios-download.svg' ?>"/>
                    </a>
                    <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2">
                        <img src="<?php echo wp_defender()->getPluginUrl() . 'assets/img/android-download.svg' ?>"/>
                    </a>
                    <div class="line"></div>
                    <p><strong><?php _e( "2. Scan the barcode", wp_defender()->domain ) ?></strong></p>
                    <p><?php _e( "Open the Google Authenticator app you just downloaded, tap the “+” symbol and then use your phone’s camera to scan the barcode below.", wp_defender()->domain ) ?></p>
					<?php echo \WP_Defender\Module\Advanced_Tools\Component\Auth_API::generateQRCode( urlencode( get_bloginfo( 'name' ) ), $email, $secretKey, 149, 149, urlencode( get_bloginfo( 'name' ) ) ) ?>
                    <div class="line"></div>
                    <p><strong><?php _e( "3. Enter passcode", wp_defender()->domain ) ?></strong></p>
                    <p>
						<?php _e( "Enter the 6 digit passcode that is shown on your device into the input field below and hit “Verify”.", wp_defender()->domain ) ?>
                    </p>
                    <div class="well">
                        <p class="error"></p>
                        <input type="text" id="otpCode" class="def-small-text">
                        <button type="button" class="button button-primary" id="verifyOTP">
							<?php _e( "Verify", wp_defender()->domain ) ?>
                        </button>
                        <input type="hidden" id="defNonce" value="<?php echo wp_create_nonce( 'defVerifyOTP' ) ?>"/>
                    </div>
                </div>
            </div>
        </td>
    </tr>
    </tbody>
</table>
<script type="text/javascript"
        src="<?php echo wp_defender()->getPluginUrl() . 'app/module/advanced-tools/js/qrcode.min.js' ?>"></script>
<script type="text/javascript">
    jQuery(function ($) {
        $('#def2qr').hide();
        $('#show2AuthActivator').click(function () {
            $('#def2').hide();
            $('#def2qr').show();
        });
        $('#hide2AuthActivator').click(function () {
            $('#def2qr').hide();
            $('#def2').show();
        })
        $("input#otpCode").keydown(function (event) {
            if (event.keyCode == 13) {
                event.preventDefault();

                var data = {
                    action: 'defVerifyOTP',
                    otp: $('#otpCode').val(),
                    nonce: $('#defNonce').val()
                }
                var that = $(this).next('#verifyOTP');
                var parent = that.closest('.well');
                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: data,
                    beforeSend: function () {
                        that.attr('disabled', 'disabled');
                    },
                    success: function (data) {
                        if (data.success == true) {
                            location.reload();
                        } else {
                            that.removeAttr('disabled');
                            parent.find('.error').text(data.data.message);
                        }
                    }
                })
            }
        });
        $('#verifyOTP').click(function () {
            var data = {
                action: 'defVerifyOTP',
                otp: $('#otpCode').val(),
                nonce: $('#defNonce').val()
            }
            var that = $(this);
            var parent = that.closest('.well');
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: data,
                beforeSend: function () {
                    that.attr('disabled', 'disabled');
                },
                success: function (data) {
                    if (data.success == true) {
                        location.reload();
                    } else {
                        that.removeAttr('disabled');
                        parent.find('.error').text(data.data.message);
                    }
                }
            })
        });
    })
</script>
<?php if ( $settings->forceAuth ): ?>
    <script type="text/javascript">
        jQuery(function ($) {
            $('html, body').animate({scrollTop: $("#show2AuthActivator").offset().top}, 1000);
        });
    </script>
<?php endif; ?>


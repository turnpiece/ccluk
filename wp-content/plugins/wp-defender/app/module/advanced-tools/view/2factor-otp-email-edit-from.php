<?php
$user    = wp_get_current_user();
$subject = ! empty( $settings->email_subject ) ? $settings->email_subject : __( 'Your OTP code', wp_defender()->domain );
$sender  = ! empty( $settings->email_sender ) ? $settings->email_sender : $user->display_name;
$body    = ! empty( $settings->email_body ) ? $settings->email_body : $settings->two_factor_opt_email_default_body();
?>
<dialog id="edit-one-time-password-email">
    <div class="wp-defender">
        <div class="">
	        <?php _e( "This email sends a temporary passcode when the user can't access their phone.", wp_defender()->domain ) ?>
        </div>
        <form method="post">
						<?php wp_nonce_field( 'twoFactorOPTEmail' ) ?>
            <div class="columns">
                <div class="column is-7">
                    <label for="email_subject"><?php _e( 'Subject', wp_defender()->domain ); ?></label>
										<input name="subject" type="text" value="<?php echo $subject; ?>" id="email_subject" />
                </div>
                <div class="column is-5">
										<label for="email_sender"><?php _e( 'Sender', wp_defender()->domain ); ?></label>
										<input name="sender" type="text" value="<?php echo $sender; ?>" id="email_sender" />
                </div>
            </div>
						<div class="columns">
                <div class="column is-12">
                    <label for="email_body"><?php _e( 'Body', wp_defender()->domain ); ?></label>
										<textarea name="body" rows="8" id="email_body"><?php echo $body; ?></textarea>
                </div>
            </div>
						<div class="columns">
							<div class="column is-12">
                    <label><?php _e( 'Available variables', wp_defender()->domain ); ?></label>
										<span class="def-tag tag-generic"><strong>{{passcode}}</strong></span>
										<span class="def-tag tag-generic"><strong>{{display_name}}</strong></span>
							</div>
            </div>
						<div class="columns footer">
                <div class="column is-12">
										<button class="close button button-secondary" aria-label="close" type="button"><?php _e( 'Cancel', wp_defender()->domain ); ?></button>
										<button class="button button-primary float-r save-2f-opt-email" type="button"><?php _e( 'Save Template', wp_defender()->domain ); ?></button>
										<button class="button button-grey float-r 2f-send-test-email" type="button"><?php _e( 'Send Test', wp_defender()->domain ); ?></button>
                </div>
            </div>
        </form>
    </div>
</dialog>
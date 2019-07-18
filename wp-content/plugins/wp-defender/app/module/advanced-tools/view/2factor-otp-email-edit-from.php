<?php
$user    = wp_get_current_user();
$subject = ! empty( $settings->email_subject ) ? $settings->email_subject : __( 'Your OTP code', wp_defender()->domain );
$sender  = ! empty( $settings->email_sender ) ? $settings->email_sender : $user->display_name;
$body    = ! empty( $settings->email_body ) ? $settings->email_body : $settings->two_factor_opt_email_default_body();
?>
<div class="sui-dialog" aria-hidden="true" tabindex="-1" id="edit-one-time-password-email">

    <div class="sui-dialog-overlay" data-a11y-dialog-hide></div>

    <div class="sui-dialog-content" aria-labelledby="dialogTitle" aria-describedby="dialogDescription" role="dialog">

        <div class="sui-box" role="document">

            <div class="sui-box-header">
                <h3 class="sui-box-title" id="dialogTitle"><?php _e( "Edit Email", wp_defender()->domain ) ?></h3>
                <div class="sui-actions-right">
                    <button data-a11y-dialog-hide class="sui-dialog-close"
                            aria-label="Close this dialog window"></button>
                </div>
            </div>
            <form method="post">
				<?php wp_nonce_field( 'twoFactorOPTEmail' ) ?>
                <div class="sui-box-body">
                    <p id="dialogDescription">
						<?php _e( "This email sends a temporary passcode when the user can’t access their phone.", wp_defender()->domain ) ?>
                    </p>
                    <div class="sui-row">
                        <div class="sui-col">
                            <div class="sui-form-field">
                                <label for="dialog-text-5" class="sui-label">
									<?php _e( "Subject", wp_defender()->domain ) ?></label>
                                <input name="subject" class="sui-form-control" type="text"
                                       value="<?php echo $subject; ?>"
                                       id="email_subject"/>
                            </div>
                        </div>
                        <div class="sui-col">
                            <div class="sui-form-field">
                                <label for="dialog-text-6" class="sui-label">
									<?php _e( "Sender", wp_defender()->domain ) ?></label>
                                <input name="sender" class="sui-form-control" type="text" value="<?php echo $sender; ?>"
                                       id="email_sender"/>
                            </div>
                        </div>
                    </div>
                    <div class="sui-row">
                        <div class="sui-col">
                            <label for="dialog-text-6" class="sui-label">
								<?php _e( "Body", wp_defender()->domain ) ?>
                            </label>
                            <textarea class="sui-form-control" name="body" rows="8"
                                      id="email_body"><?php echo stripslashes( $body ); ?></textarea>
                        </div>
                    </div>
                    <div class="sui-row">
                        <div class="sui-col">
                            <label for="dialog-text-6" class="sui-label">
								<?php _e( 'Available variables', wp_defender()->domain ); ?>
                            </label>
                            <span class="sui-tag"><strong>{{passcode}}</strong></span>
                            <span class="sui-tag"><strong>{{display_name}}</strong></span>
                        </div>
                    </div>
                </div>

                <div class="sui-box-footer">
                    <div class="sui-flex-child-right">
                        <button type="button" class="sui-button sui-button-ghost"
                                data-a11y-dialog-hide="my-accessible-dialog">
							<?php _e( 'Cancel', wp_defender()->domain ); ?>
                        </button>
                    </div>
                    <div class="sui-actions-right">
                        <button type="button" class="sui-button save-2f-opt-email">
							<?php _e( 'Save Template', wp_defender()->domain ); ?>
                        </button>
                        <button type="button" class="sui-button sui-button-blue 2f-send-test-email">
							<?php _e( 'Send Test', wp_defender()->domain ); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
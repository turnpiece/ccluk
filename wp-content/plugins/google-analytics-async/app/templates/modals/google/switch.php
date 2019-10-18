<?php

/**
 * Switch profile confirmation modal template for Google.
 *
 * @since 3.2.0
 */

defined( 'WPINC' ) || die();

?>

<div class="sui-dialog sui-dialog-sm" id="beehive-google-switch-confirm" aria-hidden="true">

    <div class="sui-dialog-overlay" data-a11y-dialog-hide="beehive-google-switch-confirm"></div>

    <div class="sui-dialog-content sui-content-fade-out" aria-labelledby="beehive-google-switch-confirm-title" aria-describedby="beehive-google-switch-confirm-description" role="dialog">

        <div class="sui-box" role="document">

            <div class="sui-box-header sui-block-content-center">

                <h3 class="sui-box-title" id="beehive-google-switch-confirm-title"><?php _e( 'Switch profile', 'ga_trans' ); ?></h3>

                <div class="sui-actions-right">
                    <button data-a11y-dialog-hide="beehive-google-switch-confirm" class="sui-dialog-close" aria-label="<?php _e( 'Close this dialog window', 'ga_trans' ); ?>"></button>
                </div>

            </div>

            <div class="sui-box-body sui-box-body-slim sui-block-content-center">

                <p id="beehive-google-switch-confirm-description" class="sui-description">
					<?php _e( 'When you switch your profile, you will be logged out and analytics will be removed from Dashboard.', 'ga_trans' ); ?><br/>
					<?php _e( 'Are you sure you want to switch?', 'ga_trans' ); ?>
                </p>

            </div>

            <div class="sui-box-footer sui-box-footer-right">

                <button class="sui-button sui-button-ghost" data-a11y-dialog-hide="beehive-google-switch-confirm"><?php _e( 'Cancel', 'ga_trans' ); ?></button>

                <button class="sui-button sui-button-ghost beehive-google-account-action-submit" data-action="switch">
                    <span class="sui-loading-text">
                        <i class="sui-icon-update" aria-hidden="true"></i>
                        <?php _e( 'Switch profile', 'ga_trans' ); ?>
                    </span>
                    <i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
                </button>

            </div>

        </div>

    </div>

</div>
<div class="dev-box">
    <div class="box-title">
        <h3 class="def-issues-title">
			<?php _e( "Mask Login Area", wp_defender()->domain ) ?>
        </h3>
    </div>
    <div class="box-content issues-box-content">
        <form method="post" id="ad-mask-settings-frm" class="advanced-settings-frm">
            <p class="line"><?php _e( "Change your default WordPress login URL to hide your login area from hackers and bots.", wp_defender()->domain ) ?></p>
			<?php if ( isset( wp_defender()->global['compatibility'] ) ): ?>
                <div class="well well-error with-cap">
                    <i class="def-icon icon-warning icon-yellow "></i>
					<?php echo implode( '<br/>', array_unique( wp_defender()->global['compatibility'] ) ); ?>
                </div>
			<?php else: ?>
				<?php if ( strlen( trim( $settings->maskUrl ) ) == 0 ): ?>
                    <div class="well well-yellow with-cap">
                        <i class="def-icon icon-warning icon-yellow "></i>
						<?php _e( "Masking is currently inactive. Choose your URL and save your settings to finish setup. ", wp_defender()->domain ) ?>
                    </div>
				<?php else: ?>
                    <div class="well well-green with-cap">
                        <i class="def-icon icon-tick"></i>
						<?php printf( __( "Masking is currently active at <strong>%s</strong>", wp_defender()->domain ), \WP_Defender\Module\Advanced_Tools\Component\Mask_Api::getNewLoginUrl() ) ?>
                    </div>
				<?php endif; ?>
			<?php endif; ?>

            <input type="hidden" name="action" value="saveATMaskLoginSettings"/>
			<?php wp_nonce_field( 'saveATMaskLoginSettings' ) ?>
            <div class="columns">
                <div class="column is-one-third">
                    <label><?php _e( "Masking URLs", wp_defender()->domain ) ?></label>
                    <span class="sub">
                        <?php _e( "Choose the new URL slug where users of your website will now navigate to log in or register.", wp_defender()->domain ) ?>
                    </span>
                </div>
                <div class="column">
                    <span class="form-help"><?php _e( "You can specify any URLs. For security reasons, less obvious URLs are recommended as they are harder for bots to guess.", wp_defender()->domain ) ?></span>
                    <span class="form-help"><strong><?php _e( "New Login URL", wp_defender()->domain ) ?></strong></span>
                    <input type="text" class="tl block" name="maskUrl" value="<?php echo $settings->maskUrl ?>"/>
                    <span class="form-help-s"><?php printf( __( "Users will login at <strong>%s</strong>", wp_defender()->domain ), get_site_url() . '/' . $settings->maskUrl ) ?></span>
                </div>
            </div>
            <div class="columns">
                <div class="column is-one-third">
                    <label><?php _e( "Redirect traffic", wp_defender()->domain ) ?></label>
                    <span class="sub">
                        <?php _e( "With this feature you can send visitors and bots who try to visit the default WordPress login URLs to a separate URL to avoid 404s.", wp_defender()->domain ) ?>
                    </span>
                </div>
                <div class="column">
                    <span class="toggle">
                        <input type="hidden" name="redirectTraffic" value="0"/>
                        <input type="checkbox" <?php checked( 1, $settings->redirectTraffic ) ?> name="redirectTraffic"
                               value="1"
                               class="toggle-checkbox" id="redirectTraffic"/>
                        <label class="toggle-label" for="redirectTraffic"></label>
                    </span>&nbsp;
                    <span><?php _e( "Enable 404 redirection", wp_defender()->domain ) ?></span>
                    <div class="clear mline"></div>
                    <div class="well well-white <?php echo $settings->redirectTraffic == false ? 'is-hidden' : null ?>">
                        <p>
                            <span class="form-help"><strong><?php _e( "Redirection URL", wp_defender()->domain ) ?></strong></span>
                        </p>
                        <input type="text" class="block" name="redirectTrafficUrl"
                               value="<?php echo $settings->redirectTrafficUrl ?>">
						<?php if ( strlen( $settings->redirectTrafficUrl ) ): ?>
                            <p>
                                <span class="form-help-s"><?php printf( __( "Visitors who visit the default login URLs will be redirected to <strong>%s</strong>", wp_defender()->domain ), get_site_url() . '/' . $settings->redirectTrafficUrl ) ?></span>
                            </p>
						<?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="columns mline">
                <div class="column is-one-third">
                    <label><?php _e( "Deactivate", wp_defender()->domain ) ?></label>
                </div>
                <div class="column">
                    <button type="button" class="button button-secondary deactivate-atmasking">
						<?php _e( "Deactivate", wp_defender()->domain ) ?>
                    </button>
                </div>
            </div>
            <div class="clear line"></div>
            <button type="submit" class="button button-primary float-r">
				<?php _e( "Save Settings", wp_defender()->domain ) ?>
            </button>
            <div class="clear"></div>
        </form>
    </div>
</div>
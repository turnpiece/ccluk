<div class="sui-wrap <?php echo \WP_Defender\Behavior\Utils::instance()->maybeHighContrast() ?>">
    <div class="wp-defender" id="wp-defender">
        <div class="settings">
            <div class="sui-header">
                <h1 class="sui-header-title">
					<?php _e( "Settings", wp_defender()->domain ) ?>
                </h1>
				<?php if ( wp_defender()->hideDocLinks === false ): ?>
                    <div class="sui-actions-right">
                        <div class="sui-actions-right">
                            <a href="https://premium.wpmudev.org/docs/wpmu-dev-plugins/defender/" target="_blank"
                               class="sui-button sui-button-ghost">
                                <i class="sui-icon-academy"></i> <?php _e( "View Documentation", wp_defender()->domain ) ?>
                            </a>
                        </div>
                    </div>
				<?php endif; ?>
            </div>
            <div class="sui-row-with-sidenav">
                <div class="sui-sidenav">
                    <ul class="sui-vertical-tabs sui-sidenav-hide-md">
                        <li class="sui-vertical-tab <?php echo \Hammer\Helper\HTTP_Helper::retrieve_get( 'view', false ) == false ? 'current' : null ?>">
                            <a href="<?php echo network_admin_url( 'admin.php?page=wdf-setting' ) ?>">
								<?php _e( "General", wp_defender()->domain ) ?>
                            </a>
                        </li>
                        <li class="sui-vertical-tab <?php echo \Hammer\Helper\HTTP_Helper::retrieve_get( 'view', false ) == 'data' ? 'current' : null ?>">
                            <a href="<?php echo network_admin_url( 'admin.php?page=wdf-setting&view=data' ) ?>">
								<?php _e( "Data & Settings", wp_defender()->domain ) ?>
                            </a>
                        </li>
                        <li class="sui-vertical-tab <?php echo \Hammer\Helper\HTTP_Helper::retrieve_get( 'view', false ) == 'accessibility' ? 'current' : null ?>">
                            <a href="<?php echo network_admin_url( 'admin.php?page=wdf-setting&view=accessibility' ) ?>">
								<?php _e( "Accessibility", wp_defender()->domain ) ?>
                            </a>
                        </li>
                    </ul>
                    <div class="sui-sidenav-hide-lg">
                        <select class="sui-mobile-nav" style="display: none;">
                            <option <?php selected( '', \Hammer\Helper\HTTP_Helper::retrieve_get( 'view' ) ) ?>
                                    value="<?php echo network_admin_url( 'admin.php?page=wdf-setting' ) ?>">
		                        <?php _e( "General", wp_defender()->domain ) ?></option>
                            <option <?php selected( 'data', \Hammer\Helper\HTTP_Helper::retrieve_get( 'view' ) ) ?>
                                    value="<?php echo network_admin_url( 'admin.php?page=wdf-setting&view=data' ) ?>">
		                        <?php _e( "Data & Settings", wp_defender()->domain ) ?></option>
                            <option <?php selected( 'accessibility', \Hammer\Helper\HTTP_Helper::retrieve_get( 'view' ) ) ?>
                                    value="<?php echo network_admin_url( 'admin.php?page=wdf-setting&view=accessibility' ) ?>">
								<?php _e( "Accessibility", wp_defender()->domain ) ?></option>
                        </select>
                    </div>
                </div>
				<?php echo $contents ?>
            </div>
        </div>
        <div class="sui-dialog sui-dialog-sm" aria-hidden="true" tabindex="-1" id="reset-data-confirm">

            <div class="sui-dialog-overlay" data-a11y-dialog-hide></div>

            <div class="sui-dialog-content" aria-labelledby="dialogTitle" aria-describedby="dialogDescription"
                 role="dialog">

                <div class="sui-box" role="document">

                    <div class="sui-box-header">
                        <h3 class="sui-box-title"
                            id="dialogTitle"><?php _e( "Reset Settings", wp_defender()->domain ) ?></h3>
                        <div class="sui-actions-right">
                            <button data-a11y-dialog-hide class="sui-dialog-close"
                                    aria-label="Close this dialog window"></button>
                        </div>
                    </div>

                    <div class="sui-box-body">
                        <p id="dialogDescription">
							<?php _e( "Are you sure you want to reset Defender’s settings back to the factory defaults?", wp_defender()->domain ) ?>
                        </p>

                    </div>
                    <form method="post" class="wd_reset_settings">
						<?php wp_nonce_field( 'wdResetSettings' ) ?>
                        <input type="hidden" name="action" value="wdResetSettings"/>
                        <div class="sui-box-footer sui-space-between">
                            <button type="button" class="sui-button sui-button-ghost"
                                    data-a11y-dialog-hide="reset-data-confirm">
								<?php _e( "Cancel", wp_defender()->domain ) ?></button>
                            <button type="submit" class="sui-button sui-button-ghost">
                                <span class="sui-loading-text">
                                    <i class="sui-icon-undo" aria-hidden="true"></i>
                                    <?php _e( "Reset Settings", wp_defender()->domain ) ?>
                                </span>
                                <i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
                            </button>
                        </div>
                    </form>
                </div>

            </div>

        </div>
		<?php if ( wp_defender()->changeFooter ): ?>
            <div class="sui-footer"><?php echo wp_defender()->footerText ?></div>
		<?php else: ?>
            <div class="sui-footer">Made with <i class="sui-icon-heart"></i> by WPMU DEV</div>
		<?php endif; ?>
		<?php if ( wp_defender()->hideDocLinks == false ): ?>
			<?php if ( wp_defender()->isFree ): ?>
                <ul class="sui-footer-nav">
                    <li><a href="https://profiles.wordpress.org/wpmudev#content-plugins" target="_blank">Free
                            Plugins</a>
                    </li>
                    <li><a href="https://premium.wpmudev.org/features/" target="_blank">Membership</a></li>
                    <li><a href="https://premium.wpmudev.org/roadmap/" target="_blank">Roadmap</a></li>
                    <li><a href="https://wordpress.org/support/plugin/plugin-name" target="_blank">Support</a></li>
                    <li><a href="https://premium.wpmudev.org/docs/" target="_blank">Docs</a></li>
                    <li><a href="https://premium.wpmudev.org/hub/" target="_blank">The Hub</a></li>
                    <li><a href="https://premium.wpmudev.org/terms-of-service/" target="_blank">Terms of Service</a>
                    </li>
                    <li><a href="https://incsub.com/privacy-policy/" target="_blank">Privacy Policy</a></li>
                </ul>
			<?php else: ?>
                <ul class="sui-footer-nav">
                    <li><a href="https://premium.wpmudev.org/hub/" target="_blank">The Hub</a></li>
                    <li><a href="https://premium.wpmudev.org/projects/category/plugins/" target="_blank">Plugins</a>
                    </li>
                    <li><a href="https://premium.wpmudev.org/roadmap/" target="_blank">Roadmap</a></li>
                    <li><a href="https://premium.wpmudev.org/hub/support/" target="_blank">Support</a></li>
                    <li><a href="https://premium.wpmudev.org/docs/" target="_blank">Docs</a></li>
                    <li><a href="https://premium.wpmudev.org/hub/community/" target="_blank">Community</a></li>
                    <li><a href="https://premium.wpmudev.org/terms-of-service/" target="_blank">Terms of Service</a>
                    </li>
                    <li><a href="https://incsub.com/privacy-policy/" target="_blank">Privacy Policy</a></li>
                </ul>
			<?php endif; ?>
            <ul class="sui-footer-social">
                <li><a href="https://www.facebook.com/wpmudev" target="_blank">
                        <i class="sui-icon-social-facebook" aria-hidden="true"></i>
                        <span class="sui-screen-reader-text">Facebook</span>
                    </a></li>
                <li><a href="https://twitter.com/wpmudev" target="_blank">
                        <i class="sui-icon-social-twitter" aria-hidden="true"></i></a>
                    <span class="sui-screen-reader-text">Twitter</span>
                </li>
                <li><a href="https://www.instagram.com/wpmu_dev/" target="_blank">
                        <i class="sui-icon-instagram" aria-hidden="true"></i>
                        <span class="sui-screen-reader-text">Instagram</span>
                    </a></li>
            </ul>
		<?php endif; ?>
    </div>
</div>
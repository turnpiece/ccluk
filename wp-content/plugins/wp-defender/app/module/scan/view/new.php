<div class="sui-wrap <?php echo \WP_Defender\Behavior\Utils::instance()->maybeHighContrast() ?>">
    <div class="wp-defender">
        <div class="wdf-scanning">
            <div class="sui-header">
                <h1 class="sui-header-title">
					<?php _e( "File Scanning", wp_defender()->domain ) ?>
                </h1>
	            <?php if ( wp_defender()->hideDocLinks === false ): ?>
                    <div class="sui-actions-right">
                        <div class="sui-actions-right">
                            <a href="https://premium.wpmudev.org/docs/wpmu-dev-plugins/defender/" target="_blank" class="sui-button sui-button-ghost">
                                <i class="sui-icon-academy"></i> <?php _e( "View Documentation", wp_defender()->domain ) ?>
                            </a>
                        </div>
                    </div>
	            <?php endif; ?>
            </div>
            <div class="sui-box sui-message">
				<?php if ( wp_defender()->hideHeroImage == 0 ): ?>
                    <img src="<?php echo wp_defender()->getPluginUrl() ?>assets/img/scan-man.svg" class="sui-image"
                         aria-hidden="true">
				<?php endif; ?>
                <div class="sui-message-content">
                    <p><?php _e( "Scan your website for file changes, vulnerabilities and injected code and get notified about anything suspicious. Defender will keep an eye on your code without you having to worry.", wp_defender()->domain ) ?></p>
                    <form id="start-a-scan" method="post" class="scan-frm">
						<?php
						wp_nonce_field( 'startAScan' );
						?>
                        <input type="hidden" name="action" value="startAScan"/>
                        <p>
                            <button type="submit" class="sui-button sui-button-blue">
								<?php _e( "Run Scan", wp_defender()->domain ) ?></button>
                        </p>
                    </form>
                </div>
            </div>
        </div>
	    <?php if ( wp_defender()->changeFooter && ! empty( wp_defender()->footerText ) ): ?>
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
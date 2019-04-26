<div class="sui-wrap <?php echo \WP_Defender\Behavior\Utils::instance()->maybeHighContrast() ?>">
    <div class="wp-defender">
        <div class="auditing">
            <div class="sui-header">
                <h1 class="sui-header-title">
			        <?php _e( "Aduit Logging", wp_defender()->domain ) ?>
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
            <div class="sui-box">
                <div class="sui-box-header">
                    <h3 class="sui-box-title">
						<?php _e( "Audit Logging", wp_defender()->domain ) ?>
                    </h3>
                    <div class="sui-actions-left">
                        <span class="sui-tag sui-tag-pro">Pro</span>
                    </div>
                </div>
                <div class="sui-message">
                    <img class="sui-image" src="<?php echo wp_defender()->getPluginUrl() ?>assets/img/audit-free.svg"/>
                    <div class="sui-message-content">
                        <p>
							<?php _e( "Track and log each and every event when changes are made to your website and get detailed reports on what’s going on behind the scenes, including any hacking attempts on your site. This is a pro feature that requires an active WPMU DEV membership. Try it free today!", wp_defender()->domain ) ?>
                        </p>
                        <a href="<?php echo \WP_Defender\Behavior\Utils::instance()->campaignURL( 'defender_auditlogging_upgrade_button' ) ?>"
                           target="_blank"
                           class="sui-button sui-button-purple"><?php esc_html_e( "Upgrade to Pro", wp_defender()->domain ) ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $controller->renderPartial( 'pro-feature' ) ?>
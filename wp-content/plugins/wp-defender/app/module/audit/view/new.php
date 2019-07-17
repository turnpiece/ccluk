<div class="sui-wrap <?php echo \WP_Defender\Behavior\Utils::instance()->maybeHighContrast() ?>">
    <div class="wp-defender">
        <div class="sui-header">
            <h1 class="sui-header-title"><?php _e( "Audit Logging", wp_defender()->domain ) ?></h1>
            <div class="sui-actions-right">
                <div class="sui-actions-right">
                    <a href="#" target="_blank" class="sui-button sui-button-ghost">
                        <i class="sui-icon-academy"></i> <?php _e( "View Documentation", wp_defender()->domain ) ?>
                    </a>
                </div>
            </div>
        </div>
        <div class="sui-box">
            <div class="sui-box-header">
                <h3 class="sui-box-title">
					<?php _e( "Activate", wp_defender()->domain ) ?>
                </h3>
            </div>
            <div class="sui-message">
				<?php if ( wp_defender()->whiteLabel == 0 ): ?>
                    <img src="<?php echo wp_defender()->getPluginUrl() ?>assets/img/def-stand.svg" class="sui-image"
                         aria-hidden="true">
				<?php endif; ?>

                <div class="sui-message-content">

                    <p>    <?php _e( "Track and log each and every event when changes are made to your website and get
			detailed reports on what’s going on behind the scenes, including any hacking attempts on
			your site.", wp_defender()->domain ) ?></p>

                    <form method="post" class="audit-frm active-audit">
                        <input type="hidden" name="action" value="activeAudit"/>
						<?php wp_nonce_field( 'activeAudit' ) ?>
                        <button type="submit" class="sui-button sui-button-blue">
							<?php _e( "Activate", wp_defender()->domain ) ?></button>
                    </form>

                </div>

            </div>
        </div>
    </div>
</div>
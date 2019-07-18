<?php
$checked = $controller->check();
?>
<div id="wp-version" class="sui-accordion-item <?php echo $controller->getCssClass() ?>">
    <div class="sui-accordion-item-header">
        <div class="sui-accordion-item-title">
            <i aria-hidden="true" class="<?php echo $checked ? 'sui-icon-check-tick sui-success'
				: 'sui-icon-warning-alert sui-warning' ?>"></i>
			<?php _e( "WordPress Version", wp_defender()->domain ) ?>
        </div>
        <div class="sui-accordion-col-4">
            <button class="sui-button-icon sui-accordion-open-indicator" aria-label="Open item">
                <i class="sui-icon-chevron-down" aria-hidden="true"></i>
            </button>
        </div>
    </div>
    <div class="sui-accordion-item-body">
        <div class="sui-box">
            <div class="sui-box-body">
                <strong>
					<?php _e( "Overview", wp_defender()->domain ) ?>
                </strong>
                <p>
					<?php _e( "WordPress is an extremely popular platform, and with that popularity comes hackers that increasingly want to exploit WordPress based websites. Leaving your WordPress installation out of date is an almost guaranteed way to get hacked as you’re missing out on the latest security patches. ", wp_defender()->domain ) ?>
                </p>
                <strong>
					<?php _e( "Status", wp_defender()->domain ) ?>
                </strong>
				<?php if ( $checked ): ?>
                    <div class="sui-notice sui-notice-success">
                        <p>
							<?php _e( "You have the latest version of WordPress installed, good stuff!", wp_defender()->domain ) ?>
                        </p>
                    </div>
				<?php else: ?>
                    <div class="sui-border-frame">
                        <div class="sui-row">
                            <div class="sui-col">
                                <strong><?php _e( "Current WordPress version", wp_defender()->domain ) ?></strong>
                                <span class="sui-tag <?php echo $checked ? 'sui-tag-success' : 'sui-tag-warning' ?>"><?php echo \WP_Defender\Behavior\Utils::instance()->getWPVersion() ?></span>
                            </div>
                            <div class="sui-col">
                                <strong><?php _e( "Recommended", wp_defender()->domain ) ?></strong>
                                <span class="sui-tag"><?php echo $controller->getService()->getLatestVersion() ?></span>
                            </div>
                        </div>
                    </div>
                    <p>
						<?php printf( __( "Your current WordPress version is out of date, which means you could be missing out on the latest security patches in v%s", wp_defender()->domain ), $controller->getService()->getLatestVersion() ) ?>
                    </p>
                    <strong>
						<?php _e( "How to fix", wp_defender()->domain ) ?>
                    </strong>
                    <p>
						<?php _e( "We recommend you update your version to the latest stable release, and maintain updating it regularly. Alternately, you can ignore this upgrade if you don’t require the latest version.", wp_defender()->domain ) ?>
                    </p>
				<?php endif; ?>
            </div>
			<?php if ( ! $checked ): ?>
                <div class="sui-box-footer">
                    <div class="sui-actions-left">
						<?php $controller->showIgnoreForm() ?>
                    </div>
                    <div class="sui-actions-right">
                        <a href="<?php echo network_admin_url( 'update-core.php' ) ?>"
                           class="sui-button sui-button-ghost">
							<?php esc_html_e( "Update WordPress", wp_defender()->domain ) ?>
                        </a>
                    </div>
                </div>
			<?php endif; ?>
        </div>
    </div>
</div>
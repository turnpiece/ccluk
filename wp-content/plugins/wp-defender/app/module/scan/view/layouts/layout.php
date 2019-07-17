<?php $countAll = $model->countAll( \WP_Defender\Module\Scan\Model\Result_Item::STATUS_ISSUE );
$core           = $model->getCount( 'core' );
$vuln           = $model->getCount( 'vuln' );
$content        = $model->getCount( 'content' );
$tooltips       = __( "You don't have any outstanding security issues, nice work!", wp_defender()->domain );
if ( $countAll == 1 ) {
	$tooltips = __( "We've detected a potential security risk in your file system. We recommend you take a look and action a fix, or ignore the file if it's harmless.", wp_defender()->domain );
} elseif ( $countAll > 1 ) {
	$tooltips = sprintf( __( "You have %s potential security risks in your file system. We recommend you take a look and action fixes, or ignore the issues if they are harmless." ), $countAll );
}
?>
<div class="sui-wrap <?php echo \WP_Defender\Behavior\Utils::instance()->maybeHighContrast() ?>">
    <div id="wp-defender" class="wp-defender">
        <div class="wdf-scanning">
            <div class="sui-header">
                <h1 class="sui-header-title">
					<?php _e( "File Scanning", wp_defender()->domain ) ?>
                </h1>
                <div class="sui-actions-left">
                    <form id="start-a-scan" method="post" class="scan-frm">
						<?php
						wp_nonce_field( 'startAScan' );
						?>
                        <input type="hidden" name="action" value="startAScan"/>
                        <button type="submit" class="sui-button sui-button-blue">
							<?php _e( "New Scan", wp_defender()->domain ) ?>
                        </button>
                    </form>
                </div>
				<?php if ( wp_defender()->hideDocLinks === false ): ?>
                    <div class="sui-actions-right">
                        <div class="sui-actions-right">
                            <a href="https://premium.wpmudev.org/docs/wpmu-dev-plugins/defender/#security-scans" target="_blank"
                               class="sui-button sui-button-ghost">
                                <i class="sui-icon-academy"></i> <?php _e( "View Documentation", wp_defender()->domain ) ?>
                            </a>
                        </div>
                    </div>
				<?php endif; ?>
            </div>
            <div class="sui-box sui-summary <?php echo \WP_Defender\Behavior\Utils::instance()->getSummaryClass() ?>">
                <div class="sui-summary-image-space" aria-hidden="true"></div>
                <div class="sui-summary-segment">
                    <div class="sui-summary-details">
                        <span class="sui-summary-large issues"><?php echo $countAll ?></span>
						<?php if ( $countAll > 0 ): ?>
                            <span class="sui-tooltip sui-tooltip-top-left sui-tooltip-constrained"
                                  data-tooltip="<?php echo $tooltips ?>">
                            <i aria-hidden="true" class="sui-icon-info sui-warning"></i>
                        </span>
						<?php else: ?>
                            <span class="sui-tooltip sui-tooltip-top-left sui-tooltip-constrained"
                                  data-tooltip="<?php echo $tooltips ?>">
                            <i class="sui-icon-check-tick sui-success" aria-hidden="true"></i>
                        </span>
						<?php endif; ?>
                        <span class="sui-summary-sub"><?php _e( "File scanning issues", wp_defender()->domain ) ?></span>

                        <span class="sui-summary-detail"><?php echo $lastScanDate ?></span>
                        <span class="sui-summary-sub"><?php _e( "Last scan", wp_defender()->domain ) ?></span>
                    </div>
                </div>
                <div class="sui-summary-segment">
                    <ul class="sui-list">
                        <li>
                            <span class="sui-list-label"><?php _e( "Wordpress core", wp_defender()->domain ) ?></span>
                            <span class="sui-list-detail issues_wp">
                                    <?php echo $core > 0 ? '<span class="sui-tag sui-tag-error">' . $core . '</span>' : '<i class="sui-icon-check-tick sui-success" aria-hidden="true"></i>' ?>
                                </span>
                        </li>
                        <li>
                            <span class="sui-list-label"><?php _e( "Plugins & themes", wp_defender()->domain ) ?></span>
                            <span class="sui-list-detail vuln_issues">
                                    <?php echo $vuln > 0 ? '<span class="sui-tag sui-tag-error">' . $vuln . '</span>' : '<i class="sui-icon-check-tick sui-success" aria-hidden="true"></i>' ?>
                                </span>
                        </li>
                        <li>
                            <span class="sui-list-label"><?php _e( "Suspicious code", wp_defender()->domain ) ?></span>
                            <span class="sui-list-detail content_issues">
                                    <?php echo $content > 0 ? '<span class="sui-tag sui-tag-error">' . $content . '</span>' : '<i class="sui-icon-check-tick sui-success" aria-hidden="true"></i>' ?>
                                </span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="sui-row-with-sidenav">
                <div class="sui-sidenav">
                    <ul class="sui-vertical-tabs sui-sidenav-hide-md">
                        <li class="sui-vertical-tab <?php echo $controller->isView( false ) ? 'current' : null ?>">
                            <a href="<?php echo network_admin_url( 'admin.php?page=wdf-scan' ) ?>">
								<?php _e( "Issues", wp_defender()->domain ) ?>
                            </a>
                        </li>
                        <li class="sui-vertical-tab <?php echo $controller->isView( 'ignored' ) ? 'current' : null ?>">
                            <a href="<?php echo network_admin_url( 'admin.php?page=wdf-scan&view=ignored' ) ?>">
								<?php _e( "Ignored", wp_defender()->domain ) ?>
                            </a>
                        </li>
                        <li class="sui-vertical-tab <?php echo $controller->isView( 'settings' ) ? 'current' : null ?>">
                            <a href="<?php echo network_admin_url( 'admin.php?page=wdf-scan&view=settings' ) ?>">
								<?php _e( "Settings", wp_defender()->domain ) ?>
                            </a>
                        </li>
                        <li class="sui-vertical-tab <?php echo $controller->isView( 'notification' ) ? 'current' : null ?>">
                            <a href="<?php echo network_admin_url( 'admin.php?page=wdf-scan&view=notification' ) ?>">
								<?php _e( "Notifications", wp_defender()->domain ) ?>
                            </a>
                        </li>
                        <li class="sui-vertical-tab <?php echo $controller->isView( 'reporting' ) ? 'current' : null ?>">
                            <a href="<?php echo network_admin_url( 'admin.php?page=wdf-scan&view=reporting' ) ?>">
								<?php _e( "Reporting", wp_defender()->domain ) ?>
                            </a>
                        </li>
                    </ul>
                    <div class="sui-sidenav-hide-lg">
                        <select class="sui-mobile-nav" style="display: none;">
                            <option value="#database-optimisation" selected="selected">Improvements</option>
                            <option value="#reporting">Reporting</option>
                            <option value="#lanskc">Content Creator</option>
                        </select>
                    </div>
                </div>
				<?php echo $contents ?>
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
<?php
$countAll = $controller->getCount( 'issues' );
$resolved = $controller->getCount( 'fixed' );
$ignore   = $controller->getCount( 'ignore' );
$tooltip  = '';
$class    = '';
if ( $countAll > 0 ) {
	$tooltip = 'data-tooltip="' . esc_attr( sprintf( __( 'You have %d security tweak(s) needing attention.', wp_defender()->domain ), $countAll ) ) . '"';
	$class   = 'sui-tooltip';
}
$inlineHeroImage = strlen( wp_defender()->heroImage ) > 0 ? 'background-image: url(\'' . wp_defender()->heroImage . '\')' : null;
?>
<div class="sui-wrap <?php echo \WP_Defender\Behavior\Utils::instance()->maybeHighContrast() ?>">
    <div id="wp-defender" class="wp-defender">
        <div class="hardener">
            <div class="sui-header">
                <h1 class="sui-header-title">
					<?php _e( "Security Tweaks", wp_defender()->domain ) ?>
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
            <div class="sui-box sui-summary sui-summary-sm <?php echo \WP_Defender\Behavior\Utils::instance()->getSummaryClass() ?>">
                <div class="sui-summary-image-space"
                     aria-hidden="true"
                     style="<?php echo $inlineHeroImage ?>"></div>

                <div class="sui-summary-segment">

                    <div class="sui-summary-details issues">

                        <span class="sui-summary-large <?php echo $class ?> count-issues" <?php echo $tooltip ?> ><?php echo $countAll ?></span>
						<?php if ( $countAll > 0 ): ?>
                            <i aria-hidden="true" class="sui-icon-info sui-warning"></i>
						<?php else: ?>
                            <i class="sui-icon-check-tick sui-success" aria-hidden="true"></i>
						<?php endif; ?>
                        <span class="sui-summary-sub"><?php _e( "Security issues", wp_defender()->domain ) ?></span>
                    </div>

                </div>

                <div class="sui-summary-segment">
                    <ul class="sui-list">

                        <li>
                            <span class="sui-list-label"><?php _e( "Current PHP version", wp_defender()->domain ) ?></span>
                            <span class="sui-list-detail issues_wp">
                                    <?php echo phpversion() ?>
                                </span>
                        </li>

                        <li>
                            <span class="sui-list-label"><?php _e( "Current WordPress version", wp_defender()->domain ) ?></span>
                            <span class="sui-list-detail vuln_issues">
                                    <?php
                                    global $wp_version;
                                    echo $wp_version
                                    ?>
                                </span>
                        </li>

                    </ul>
                </div>

            </div>
            <div class="sui-row-with-sidenav">
                <div class="sui-sidenav">
                    <ul class="sui-vertical-tabs sui-sidenav-hide-md">
                        <li class="sui-vertical-tab <?php echo \Hammer\Helper\HTTP_Helper::retrieve_get( 'view', false ) == false ? 'current' : null ?>">
                            <a href="<?php echo network_admin_url( 'admin.php?page=wdf-hardener' ) ?>">
								<?php _e( "Issues", wp_defender()->domain ) ?></a>
                            <span class="sui-tag sui-tag-warning <?php echo $countAll ? '' : 'wd-hide' ?> count-issues"><?php echo $countAll ?></span>
                        </li>
                        <li class="sui-vertical-tab <?php echo $controller->isView( 'resolved' ) ? 'current' : null ?>">
                            <a href="<?php echo network_admin_url( 'admin.php?page=wdf-hardener&view=resolved' ) ?>">
								<?php _e( "Resolved", wp_defender()->domain ) ?></a>
                            <span class="sui-tag count-resolved <?php echo $resolved ? '' : 'wd-hide' ?>"><?php echo $resolved ?></span>
                        </li>
                        <li class="sui-vertical-tab <?php echo $controller->isView( 'ignored' ) ? 'current' : null ?>">
                            <a href="<?php echo network_admin_url( 'admin.php?page=wdf-hardener&view=ignored' ) ?>">
								<?php _e( "Ignored", wp_defender()->domain ) ?></a>
                            <span class="sui-tag count-ignored <?php echo $ignore ? '' : 'wd-hide' ?>"><?php echo $ignore ?></span>
                        </li>
<!--                        <li class="sui-vertical-tab --><?php //echo $controller->isView( 'notification' ) ? 'current' : null ?><!--">-->
<!--                            <a href="--><?php //echo network_admin_url( 'admin.php?page=wdf-hardener&view=notification' ) ?><!--">-->
<!--								--><?php //_e( "Notification", wp_defender()->domain ) ?><!--</a>-->
<!--                        </li>-->
                    </ul>
                    <div class="sui-sidenav-hide-lg">
                        <select class="sui-mobile-nav" style="display: none;">
                            <option <?php selected( '', \Hammer\Helper\HTTP_Helper::retrieve_get( 'view' ) ) ?>
                                    value="<?php echo network_admin_url( 'admin.php?page=wdf-hardener' ) ?>"><?php _e( "Issues", wp_defender()->domain ) ?></option>
                            <option <?php selected( 'resolved', \Hammer\Helper\HTTP_Helper::retrieve_get( 'view' ) ) ?>
                                    value="<?php echo network_admin_url( 'admin.php?page=wdf-hardener&view=resolved' ) ?>"><?php _e( "Resolved", wp_defender()->domain ) ?></option>
                            <option <?php selected( 'ignored', \Hammer\Helper\HTTP_Helper::retrieve_get( 'view' ) ) ?>
                                    value="<?php echo network_admin_url( 'admin.php?page=wdf-hardener&view=ignored' ) ?>"><?php _e( "Ignored", wp_defender()->domain ) ?></option>
<!--                            <option --><?php //selected( 'notification', \Hammer\Helper\HTTP_Helper::retrieve_get( 'view' ) ) ?>
<!--                                    value="--><?php //echo network_admin_url( 'admin.php?page=wdf-hardener&view=notification' ) ?><!--">--><?php //_e( "Notification", wp_defender()->domain ) ?><!--</option>-->
                        </select>
                    </div>
                </div>
                <div class="sui-box">
					<?php echo $contents ?>
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
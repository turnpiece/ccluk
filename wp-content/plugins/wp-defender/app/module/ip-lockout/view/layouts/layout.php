<div class="sui-wrap <?php echo \WP_Defender\Behavior\Utils::instance()->maybeHighContrast() ?>">
    <div class="wp-defender" id="wp-defender">
        <div class="iplockout">
            <div class="sui-header">
                <h1 class="sui-header-title"><?php _e( "IP Lockout", wp_defender()->domain ) ?></h1>
				<?php if ( wp_defender()->hideDocLinks === false ): ?>
                    <div class="sui-actions-right">
                        <div class="sui-actions-right">
                            <a href="https://premium.wpmudev.org/docs/wpmu-dev-plugins/defender/#ip-lockouts"
                               target="_blank" class="sui-button sui-button-ghost">
                                <i class="sui-icon-academy"></i> <?php _e( "View Documentation", wp_defender()->domain ) ?>
                            </a>
                        </div>
                    </div>
				<?php endif; ?>
            </div>
            <div class="sui-box sui-summary <?php echo \WP_Defender\Behavior\Utils::instance()->getSummaryClass() ?>"
                 id="lockoutSummary">
                <input type="hidden" id="summaryNonce" value="<?php echo wp_create_nonce( 'lockoutSummaryData' ) ?>"/>
                <div class="sui-summary-image-space" aria-hidden="true"></div>
                <div class="sui-summary-segment">
                    <div class="sui-summary-details">
                        <span class="sui-summary-large lockoutToday">-</span>
                        <span class="sui-summary-sub"><?php _e( "Lockouts in the past 24 hours", wp_defender()->domain ) ?></span>

                        <span class="sui-summary-detail lockoutThisMonth">-</span>
                        <span class="sui-summary-sub"><?php _e( "Total lockouts in the past 30 days", wp_defender()->domain ) ?></span>
                    </div>

                </div>

                <div class="sui-summary-segment">
                    <ul class="sui-list">
                        <li>
                            <span class="sui-list-label"><?php _e( "Last lockout", wp_defender()->domain ) ?></span>
                            <span class="sui-list-detail lastLockout"> .</span>
                        </li>
                        <li>
                            <span class="sui-list-label"><?php _e( "Login lockouts in the past 7 days", wp_defender()->domain ) ?></span>
                            <span class="sui-list-detail loginLockoutThisWeek">.</span>
                        </li>
                        <li>
                            <span class="sui-list-label"><?php _e( "404 lockouts in the past 7 days", wp_defender()->domain ) ?></span>
                            <span class="sui-list-detail lockout404ThisWeek">.</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="sui-row-with-sidenav">
                <div class="sui-sidenav">
                    <ul class="sui-vertical-tabs sui-sidenav-hide-md">
                        <li class="sui-vertical-tab <?php echo \Hammer\Helper\HTTP_Helper::retrieve_get( 'view', false ) == false ? 'current' : null ?>">
                            <a href="<?php echo network_admin_url( 'admin.php?page=wdf-ip-lockout' ) ?>"><?php _e( "Login Protection", wp_defender()->domain ) ?></a>
                        </li>
                        <li class="sui-vertical-tab <?php echo $controller->isView( '404' ) ? 'current' : null ?>">
                            <a href="<?php echo network_admin_url( 'admin.php?page=wdf-ip-lockout&view=404' ) ?>"><?php _e( "404 Detection", wp_defender()->domain ) ?></a>
                        </li>
                        <li class="sui-vertical-tab <?php echo $controller->isView( 'blacklist' ) ? 'current' : null ?>">
                            <a href="<?php echo network_admin_url( 'admin.php?page=wdf-ip-lockout&view=blacklist' ) ?>"><?php _e( "IP Banning", wp_defender()->domain ) ?></a>
                        </li>
                        <li class="sui-vertical-tab <?php echo $controller->isView( 'logs' ) ? 'current' : null ?>">
                            <a href="<?php echo network_admin_url( 'admin.php?page=wdf-ip-lockout&view=logs' ) ?>"><?php _e( "Logs", wp_defender()->domain ) ?></a>
                        </li>
                        <li class="sui-vertical-tab <?php echo $controller->isView( 'notification' ) ? 'current' : null ?>">
                            <a href="<?php echo network_admin_url( 'admin.php?page=wdf-ip-lockout&view=notification' ) ?>"><?php _e( "Notifications", wp_defender()->domain ) ?></a>
                        </li>
                        <li class="sui-vertical-tab <?php echo $controller->isView( 'settings' ) ? 'current' : null ?>">
                            <a href="<?php echo network_admin_url( 'admin.php?page=wdf-ip-lockout&view=settings' ) ?>"><?php _e( "Settings", wp_defender()->domain ) ?></a>
                        </li>
                        <li class="sui-vertical-tab <?php echo $controller->isView( 'reporting' ) ? 'current' : null ?>">
                            <a href="<?php echo network_admin_url( 'admin.php?page=wdf-ip-lockout&view=reporting' ) ?>"><?php _e( "Reporting", wp_defender()->domain ) ?></a>
                        </li>
                    </ul>
                    <div class="sui-sidenav-hide-lg">
                        <select class="sui-mobile-nav" style="display: none;">
                            <option <?php selected( null, \Hammer\Helper\HTTP_Helper::retrieve_get( 'view', null ) ) ?>
                                    value="<?php echo network_admin_url( 'admin.php?page=wdf-ip-lockout' ) ?>"><?php _e( "Login Protection", wp_defender()->domain ) ?></option>
                            <option <?php selected( '404', \Hammer\Helper\HTTP_Helper::retrieve_get( 'view', null ) ) ?>
                                    value="<?php echo network_admin_url( 'admin.php?page=wdf-ip-lockout&view=404' ) ?>"><?php _e( "404 Detection", wp_defender()->domain ) ?></option>
                            <option <?php selected( 'blacklist', \Hammer\Helper\HTTP_Helper::retrieve_get( 'view', null ) ) ?>
                                    value="<?php echo network_admin_url( 'admin.php?page=wdf-ip-lockout&view=blacklist' ) ?>"><?php _e( "IP Blacklist", wp_defender()->domain ) ?></option>
                            <option <?php selected( 'logs', \Hammer\Helper\HTTP_Helper::retrieve_get( 'view', null ) ) ?>
                                    value="<?php echo network_admin_url( 'admin.php?page=wdf-ip-lockout&view=logs' ) ?>"><?php _e( "Logs", wp_defender()->domain ) ?></option>
                            <option <?php selected( 'notification', \Hammer\Helper\HTTP_Helper::retrieve_get( 'view', null ) ) ?>
                                    value="<?php echo network_admin_url( 'admin.php?page=wdf-ip-lockout&view=notification' ) ?>"><?php _e( "Notifications", wp_defender()->domain ) ?></option>
                            <option <?php selected( 'settings', \Hammer\Helper\HTTP_Helper::retrieve_get( 'view', null ) ) ?>
                                    value="<?php echo network_admin_url( 'admin.php?page=wdf-ip-lockout&view=settings' ) ?>"><?php _e( "Settings", wp_defender()->domain ) ?></option>
                            <option <?php selected( 'reporting', \Hammer\Helper\HTTP_Helper::retrieve_get( 'view', null ) ) ?>
                                    value="<?php echo network_admin_url( 'admin.php?page=wdf-ip-lockout&view=reporting' ) ?>"><?php _e( "Reporting", wp_defender()->domain ) ?></option>
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
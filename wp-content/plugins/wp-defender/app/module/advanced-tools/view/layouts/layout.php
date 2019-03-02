<div class="sui-wrap">
    <div class="wp-defender" id="wp-defender">
        <div class="advanced-tools">
            <div class="sui-header">
                <h1 class="sui-header-title">
					<?php _e( "Advanced Tools", wp_defender()->domain ) ?>
                </h1>
                <div class="sui-actions-right">
                    <div class="sui-actions-right">
                        <a href="#" target="_blank" class="sui-button sui-button-ghost">
                            <i class="sui-icon-academy"></i> <?php _e( "View Documentation", wp_defender()->domain ) ?>
                        </a>
                    </div>
                </div>
            </div>
            <div class="sui-row-with-sidenav">
                <div class="sui-sidenav">
                    <ul class="sui-vertical-tabs sui-sidenav-hide-md">
                        <li class="sui-vertical-tab <?php echo \Hammer\Helper\HTTP_Helper::retrieve_get( 'view', false ) == false ? 'current' : null ?>">
                            <a href="<?php echo network_admin_url( 'admin.php?page=wdf-advanced-tools' ) ?>">
								<?php _e( "Two-Factor Auth", wp_defender()->domain ) ?>
                            </a>
                        </li>
                        <li class="sui-vertical-tab <?php echo \Hammer\Helper\HTTP_Helper::retrieve_get( 'view', false ) == 'mask-login' ? 'current' : null ?>">
                            <a href="<?php echo network_admin_url( 'admin.php?page=wdf-advanced-tools&view=mask-login' ) ?>">
								<?php _e( "Mask Login Area", wp_defender()->domain ) ?>
                            </a>
                        </li>
                    </ul>
                    <div class="sui-sidenav-hide-lg">
                        <select class="sui-mobile-nav" style="display: none;">
                            <option <?php selected( '', \Hammer\Helper\HTTP_Helper::retrieve_get( 'view' ) ) ?>
                                    value="<?php echo network_admin_url( 'admin.php?page=wdf-advanced-tools' ) ?>"><?php _e( "Two Factor Authentication", wp_defender()->domain ) ?></option>
                            <option <?php selected( 'mask-login', \Hammer\Helper\HTTP_Helper::retrieve_get( 'view' ) ) ?>
                                    value="<?php echo network_admin_url( 'admin.php?page=wdf-advanced-tools&view=mask-login' ) ?>">
								<?php _e( "Mask Login Area", wp_defender()->domain ) ?>
                            </option>
                        </select>
                    </div>
                </div>
				<?php echo $contents ?>
            </div>
        </div>
        <div class="sui-footer">Made with <i class="sui-icon-heart"></i> by WPMU DEV</div>
		<?php if ( wp_defender()->isFree ): ?>
            <ul class="sui-footer-nav">
                <li><a href="https://profiles.wordpress.org/wpmudev#content-plugins" target="_blank">Free Plugins</a>
                </li>
                <li><a href="https://premium.wpmudev.org/features/" target="_blank">Membership</a></li>
                <li><a href="https://premium.wpmudev.org/roadmap/" target="_blank">Roadmap</a></li>
                <li><a href="https://wordpress.org/support/plugin/plugin-name" target="_blank">Support</a></li>
                <li><a href="https://premium.wpmudev.org/docs/" target="_blank">Docs</a></li>
                <li><a href="https://premium.wpmudev.org/hub/" target="_blank">The Hub</a></li>
                <li><a href="https://premium.wpmudev.org/terms-of-service/" target="_blank">Terms of Service</a></li>
                <li><a href="https://incsub.com/privacy-policy/" target="_blank">Privacy Policy</a></li>
            </ul>
		<?php else: ?>
            <ul class="sui-footer-nav">
                <li><a href="https://premium.wpmudev.org/hub/" target="_blank">The Hub</a></li>
                <li><a href="https://premium.wpmudev.org/projects/category/plugins/" target="_blank">Plugins</a></li>
                <li><a href="https://premium.wpmudev.org/roadmap/" target="_blank">Roadmap</a></li>
                <li><a href="https://premium.wpmudev.org/hub/support/" target="_blank">Support</a></li>
                <li><a href="https://premium.wpmudev.org/docs/" target="_blank">Docs</a></li>
                <li><a href="https://premium.wpmudev.org/hub/community/" target="_blank">Community</a></li>
                <li><a href="https://premium.wpmudev.org/terms-of-service/" target="_blank">Terms of Service</a></li>
                <li><a href="https://incsub.com/privacy-policy/" target="_blank">Privacy Policy</a></li>
            </ul>
		<?php endif; ?>
    </div>
</div>
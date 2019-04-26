<?php
list( $hCount, $sCount ) = $controller->countTotalIssues( true );
$countAll = $hCount + $sCount;
?>
<div class="sui-wrap <?php echo \WP_Defender\Behavior\Utils::instance()->maybeHighContrast() ?>">
    <div id="wp-defender" class="wp-defender">
        <div class="def-dashboard">
            <div class="sui-header">
                <h1 class="sui-header-title">
					<?php _e( "Dashboard", wp_defender()->domain ) ?>
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
            <div class="sui-box sui-summary <?php echo \WP_Defender\Behavior\Utils::instance()->getSummaryClass() ?>">
                <div class="sui-summary-image-space" aria-hidden="true"></div>
                <div class="sui-summary-segment">
                    <div class="sui-summary-details">
                        <span class="sui-summary-large"><?php echo $countAll ?></span>
						<?php if ( $countAll > 0 ): ?>
                            <i aria-hidden="true" class="sui-icon-info sui-error"></i>
						<?php else: ?>
                            <i class="sui-icon-check-tick sui-success" aria-hidden="true"></i>
						<?php endif; ?>
                        <span class="sui-summary-sub"><?php _e( "Security issues", wp_defender()->domain ) ?></span>
                    </div>
                </div>
                <div class="sui-summary-segment">
                    <ul class="sui-list">
                        <li>
                            <span class="sui-list-label"><?php _e( "Security Tweaks Actioned", wp_defender()->domain ) ?></span>
                            <span class="sui-list-detail">
                            <?php
                            $settings = \WP_Defender\Module\Hardener\Model\Settings::instance();
                            echo count( $settings->fixed ) + count( $settings->ignore ) ?>
                                /
								<?php echo count( $settings->getDefinedRules() )
								?>
                            </span>
                        </li>
                        <li>
                            <span class="sui-list-label"><?php _e( "File Scan Issues", wp_defender()->domain ) ?></span>
                            <span class="sui-list-detail">
                                  <?php echo $controller->renderScanStatusText() ?>
                                </span>
                        </li>
                        <li>
                            <span class="sui-list-label"><?php _e( "Last Lockout", wp_defender()->domain ) ?></span>
                            <span class="sui-list-detail lastLockout">
                                    -
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="sui-row">
                <div class="sui-col">
					<?php echo $controller->renderHardenerWidget() ?>
					<?php $controller->renderBlacklistWidget() ?>
					<?php $controller->renderATWidget() ?>
                </div>
                <div class="sui-col">
					<?php $controller->renderScanWidget() ?>
					<?php $controller->renderLockoutWidget() ?>
					<?php $controller->renderAuditWidget() ?>
					<?php $controller->renderReportWidget() ?>
                </div>
            </div>
        </div>
		<?php
		if ( $controller->isShowActivator() ) {
			$view = wp_defender()->isFree ? 'activator-free' : 'activator';
			$controller->renderPartial( $view );
		} ?>
		<?php if ( wp_defender()->isFree ): ?>
            <div id="sui-cross-sell-footer" class="sui-row">

                <div><span class="sui-icon-plugin-2"></span></div>
                <h3><?php _e( "Check out our other free wordpress.org plugins!", wp_defender()->domain ) ?></h3>

            </div>

            <!-- Cross-Sell Modules -->
            <div class="sui-row sui-cross-sell-modules">

                <div class="sui-col-md-4">

                    <!-- Cross-Sell Banner #1 -->
                    <div aria-hidden="true" class="sui-cross-1">
                        <span></span>
                    </div>

                    <div class="sui-box">
                        <div class="sui-box-body">
                            <h3><?php _e( "Smush Image Compression and Optimization", wp_defender()->domain ) ?></h3>
                            <p><?php _e( "Resize, optimize and compress all of your images with the incredibly powerful and
                                award-winning, 100% free WordPress image optimizer.", wp_defender()->domain ) ?></p>
                            <a href="https://wordpress.org/plugins/wp-smushit/"
                               target="_blank"
                               class="sui-button sui-button-ghost">
								<?php _e( "View features", wp_defender()->domain ) ?> <i aria-hidden="true"
                                                                                         class="sui-icon-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                </div>

                <div class="sui-col-md-4">

                    <!-- Cross-Sell Banner #2 -->
                    <div aria-hidden="true" class="sui-cross-2">
                        <span></span>
                    </div>

                    <div class="sui-box">
                        <div class="sui-box-body">
                            <h3><?php _e( "Hummingbird Page Speed Optimization", wp_defender()->domain ) ?></h3>
                            <p><?php _e( "Performance Tests, File Optimization & Compression, Page, Browser & Gravatar Caching,
                                GZIP Compression, CloudFlare Integration & more.", wp_defender()->domain ) ?></p>
                            <a href="https://wordpress.org/plugins/defender-security/"
                               target="_blank"
                               class="sui-button sui-button-ghost">
								<?php _e( "View features", wp_defender()->domain ) ?> <i aria-hidden="true"
                                                                                         class="sui-icon-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                </div>

                <div class="sui-col-md-4">

                    <!-- Cross-Sell Banner #3 -->
                    <div aria-hidden="true" class="sui-cross-3">
                        <span></span>
                    </div>

                    <div class="sui-box">
                        <div class="sui-box-body">
                            <h3><?php _e( "SmartCrawl Search Engine Optimization", wp_defender()->domain ) ?></h3>
                            <p><?php _e( "Customize Titles & Meta Data, OpenGraph, Twitter & Pinterest Support, Auto-Keyword
                                Linking, SEO & Readability Analysis, Sitemaps, URL Crawler & more.", wp_defender()->domain ) ?></p>
                            <span class="sui-tag"><?php _e( "Coming Soon", wp_defender()->domain ) ?></span>
                        </div>
                    </div>

                </div>

            </div>

            <div class="sui-cross-sell-bottom">

                <h3><?php _e( "WPMU DEV - Your WordPress Toolkit", wp_defender()->domain ) ?></h3>
                <p><?php _e( "Pretty much everything you need for developing and managing WordPress based websites, and then
                    some.", wp_defender()->domain ) ?></p>

                <a href="https://premium.wpmudev.org/"
                   target="_blank"
                   role="button"
                   class="sui-button sui-button-green">
					<?php _e( "Learn more", wp_defender()->domain ) ?>
                </a>

                <img class="sui-image" src="<?php echo wp_defender()->getPluginUrl() . '/sui/images/dev-team.png' ?>"
                     aria-hidden="true">

            </div>
		<?php endif; ?>
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

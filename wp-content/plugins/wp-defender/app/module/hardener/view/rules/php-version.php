<?php
$checked  = $controller->check();
$settings = \WP_Defender\Module\Hardener\Model\Settings::instance();
global $wpdb;
?>
<div id="php_version" class="sui-accordion-item <?php echo $controller->getCssClass() ?>">
    <div class="sui-accordion-item-header">
        <div class="sui-accordion-item-title">
            <i aria-hidden="true" class="<?php echo $checked ? 'sui-icon-check-tick sui-success'
				: 'sui-icon-warning-alert sui-warning' ?>"></i>
			<?php _e( "PHP Version", wp_defender()->domain ) ?>
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
					<?php _e( "PHP is the software that powers WordPress. It interprets the WordPress code and generates web pages people view. Naturally, PHP comes in different versions and is regularly updated. As newer versions are released, WordPress drops support for older PHP versions in favour of newer, faster versions with fewer bugs.", wp_defender()->domain ) ?>
                </p>
                <strong>
					<?php _e( "Status", wp_defender()->domain ) ?>
                </strong>
                <div class="sui-border-frame">
                    <div class="sui-row">
                        <div class="sui-col">
                            <strong><?php _e( "Current PHP version", wp_defender()->domain ) ?></strong>
                            <span class="sui-tag <?php echo $checked ? 'sui-tag-success' : 'sui-tag-warning' ?>"><?php echo phpversion() ?></span>
                        </div>
                        <div class="sui-col">
                            <strong><?php _e( "Recommended", wp_defender()->domain ) ?></strong>
                            <span class="sui-tag"><?php printf( __( "%s or above", wp_defender()->domain ), $settings->min_php_version ) ?></span>
                        </div>
                    </div>
                </div>
                <p>
					<?php printf( __( "PHP versions older than %s are no longer supported. For security and stability we strongly recommend you upgrade your PHP version to version %s or newer as soon as possible. ", wp_defender()->domain ), $settings->min_php_version, $settings->min_php_version ) ?>
                </p>
                <p>
					<?php _e( "For more information visit <a target='_blank' href='http://php.net/supported-versions.php'>http://php.net/supported-versions.php</a>", wp_defender()->domain ) ?>
                </p>
                <strong>
					<?php _e( "How to fix", wp_defender()->domain ) ?>
                </strong>
                <p>
					<?php printf( __( "Upgrade your PHP version to %s or above. Currently the latest stable version of PHP is %s.", wp_defender()->domain ), $settings->min_php_version, $settings->stable_php_version ) ?>
                </p>
				<?php if ( $checked ): ?>
                    <div class="sui-notice sui-notice-success">
                        <p><?php _e( "You have the latest version of PHP installed, good stuff!", wp_defender()->domain ) ?></p>
                    </div>
				<?php else: ?>
                    <div class="sui-notice">
                        <p><?php _e( "We can’t update PHP for you, contact your hosting provider or developer to help you upgrade.", wp_defender()->domain ) ?></p>
                    </div>
				<?php endif; ?>
            </div>
			<?php if ( ! $checked ): ?>
                <div class="sui-box-footer">
                    <div class="sui-actions-left">
						<?php $controller->showIgnoreForm() ?>
                    </div>
                </div>
			<?php endif; ?>
        </div>
    </div>
</div>
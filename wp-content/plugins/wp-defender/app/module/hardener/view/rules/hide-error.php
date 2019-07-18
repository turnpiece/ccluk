<?php
$checked = $controller->check();
?>
<div id="disable-file-editor" class="sui-accordion-item <?php echo $controller->getCssClass() ?>">
    <div class="sui-accordion-item-header">
        <div class="sui-accordion-item-title">
            <i aria-hidden="true" class="<?php echo $checked ? 'sui-icon-check-tick sui-success'
				: 'sui-icon-warning-alert sui-warning' ?>"></i>
			<?php _e( "Error Reporting", wp_defender()->domain ) ?>
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
					<?php _e( "Developers often use the built-in PHP and scripts error debugging feature, which displays code errors on the frontend of your website. It’s useful for active development, but on live sites provides hackers yet another way to find loopholes in your site's security.", wp_defender()->domain ) ?>
                </p>
                <strong>
					<?php _e( "Status", wp_defender()->domain ) ?>
                </strong>
				<?php if ( $checked ): ?>
                    <div class="sui-notice sui-notice-success">
                        <p>
							<?php _e( "You've disabled all error reporting, Houston will never report a problem.", wp_defender()->domain ) ?>
                        </p>
                    </div>
				<?php else: ?>
					<?php if ( WP_DEBUG == false || ( WP_DEBUG == true && WP_DEBUG_DISPLAY == false ) ): ?>
                        <div class="sui-notice sui-notice-warning">
                            <p>
								<?php _e( "We attempted to disable the display_errors setting to prevent code errors displaying but it’s being overridden by your server config. Please contact your hosting provider and ask them to set display_errors to false.", wp_defender()->domain ) ?>
                            </p>
                        </div>
					<?php else: ?>
                        <div class="sui-notice sui-notice-warning">
                            <p>
								<?php _e( "Error debugging is currently allowed.", wp_defender()->domain ) ?>
                            </p>
                        </div>
					<?php endif; ?>
                    <p>
						<?php _e( "While it may not be in use, we haven’t found any code stopping debugging information being output. It’s best to remove all doubt and disable error reporting completely.", wp_defender()->domain ) ?>
                    </p>
                    <strong>
						<?php _e( "How to fix", wp_defender()->domain ) ?>
                    </strong>
                    <p>
						<?php _e( "We can automatically disable all error reporting for you below. Alternately, you can ignore this tweak if you don’t require it. Either way, you can easily revert these actions at any time.", wp_defender()->domain ) ?>
                    </p>
				<?php endif; ?>
            </div>
			<?php if ( !$checked ): ?>
				<?php if ( WP_DEBUG == true && ( ! defined( 'WP_DEBUG_DISPLAY' ) || WP_DEBUG_DISPLAY != false ) ): ?>
                    <div class="sui-box-footer">
                        <div class="sui-actions-left">
							<?php $controller->showIgnoreForm() ?>
                        </div>
                        <div class="sui-actions-right">
                            <form method="post" class="hardener-frm rule-process hardener-frm-process-xml-rpc">
								<?php $controller->createNonceField(); ?>
                                <input type="hidden" name="action" value="processHardener"/>
                                <input type="hidden" name="slug" value="<?php echo $controller::$slug ?>"/>
                                <button class="sui-button sui-button-blue" type="submit">
									<?php _e( "Disable error debugging", wp_defender()->domain ) ?></button>
                            </form>
                        </div>
                    </div>
				<?php else: ?>
                    <div class="sui-box-footer">
                        <div class="sui-actions-left">
							<?php $controller->showIgnoreForm() ?>
                        </div>
                    </div>
				<?php endif; ?>
			<?php endif; ?>
        </div>
    </div>
</div>
<?php
$checked = $controller->check();
?>
<div id="disable_xml_rpc" class="sui-accordion-item <?php echo $controller->getCssClass() ?>">
    <div class="sui-accordion-item-header">
        <div class="sui-accordion-item-title">
            <i aria-hidden="true" class="<?php echo $checked ? 'sui-icon-check-tick sui-success'
				: 'sui-icon-warning-alert sui-warning' ?>"></i>
			<?php _e( 'XML-RPC', wp_defender()->domain ) ?>
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
					<?php _e( "XML-RPC is a system that allows you to post on your WordPress blog using popular weblog clients like Windows Live Writer. Technically, it’s a remote procedure call which uses XML to encode its calls and HTTP as a transport mechanism.", wp_defender()->domain ) ?>
                </p>
                <p>
					<?php _e( "If you are using the WordPress mobile app, want to make connections to services like IFTTT, or want to access and publish to your blog remotely, then you need XML-RPC enabled, otherwise it’s just another portal for hackers to target and exploit.", wp_defender()->domain ) ?>
                </p>
				<?php if ( $checked ): ?>
                    <div class="sui-notice sui-notice-success">
                        <p>
							<?php _e( "XML-RPC is disabled.", wp_defender()->domain ) ?>
                        </p>
                    </div>
				<?php else: ?>
                    <strong>
						<?php _e( "Status", wp_defender()->domain ) ?>
                    </strong>
                    <div class="sui-notice sui-notice-warning">
                        <p>
							<?php _e( "XML-RPC is currently enabled.", wp_defender()->domain ) ?>
                        </p>
                    </div>
                    <p>
						<?php _e( "In the past, there were security concerns with XML-RPC so we recommend making sure this feature is fully disabled if you don’t need it active.", wp_defender()->domain ) ?>
                    </p>
                    <strong>
						<?php _e( "How to fix", wp_defender()->domain ) ?>
                    </strong>
                    <p>
						<?php _e( "We can automatically disable XML-RPC for you below. Alternately, you can ignore this tweak if you don’t require it. Either way, you can easily revert these actions at any time.", wp_defender()->domain ) ?>
                    </p>
				<?php endif; ?>
            </div>
            <div class="sui-box-footer">
				<?php if ( $checked ): ?>
                    <form method="post" class="hardener-frm rule-process">
						<?php $controller->createNonceField(); ?>
                        <input type="hidden" name="action" value="processRevert"/>
                        <input type="hidden" name="slug" value="<?php echo $controller::$slug ?>"/>
                        <button class="sui-button" type="submit">
                            <i class="sui-icon-undo" aria-hidden="true"></i>
							<?php _e( "Revert", wp_defender()->domain ) ?></button>
                    </form>
				<?php else: ?>
                    <div class="sui-actions-left">
						<?php $controller->showIgnoreForm() ?>
                    </div>
                    <div class="sui-actions-right">
                        <form method="post" class="hardener-frm rule-process hardener-frm-process-xml-rpc">
							<?php $controller->createNonceField(); ?>
                            <input type="hidden" name="action" value="processHardener"/>
                            <input type="hidden" name="updatePosts" value="no"/>
                            <input type="hidden" name="slug" value="<?php echo $controller::$slug ?>"/>
                            <button class="sui-button sui-button-blue" type="submit">
								<?php _e( "Disable XML-RPC", wp_defender()->domain ) ?></button>
                        </form>
                    </div>
				<?php endif; ?>
            </div>
        </div>
    </div>
</div>
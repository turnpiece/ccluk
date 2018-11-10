<div class="rule closed" id="disable_xml_rpc">
    <div class="rule-title" role="link" tabindex="0">
		<?php if ( $controller->check() == false ): ?>
            <i class="def-icon icon-warning" aria-hidden="true"></i>
		<?php else: ?>
            <i class="def-icon icon-tick" aria-hidden="true"></i>
		<?php endif; ?>
		<?php _e( 'Disable XML-RPC', wp_defender()->domain ) ?>
    </div>
    <div class="rule-content">
        <h3><?php _e( "Overview", wp_defender()->domain ) ?></h3>
        <div class="line end">
			<?php _e( 'XML-RPC is a system that allows you to post on your WordPress blog using popular weblog clients like Windows Live Writer. Technically, it’s a remote procedure call which uses XML to encode its calls and HTTP as a transport mechanism.<br/><br/>
If you are using the WordPress mobile app, want to make connections to services like IFTTT, or want to access and publish to your blog remotely, then you need XML-RPC enabled.<br/><br/>
In the past, there were security concerns with XML-RPC so we recommend making sure this feature is fully disabled if you don’t need it active.', wp_defender()->domain ) ?>
        </div>
        <h3>
			<?php _e( "How to fix", wp_defender()->domain ) ?>
        </h3>
        <div class="line">
			<?php _e( 'Automatically disable this feature below. You can re-enable it at any time if you need to.', wp_defender()->domain ) ?>
        </div>
        <div class="">
			<?php if ( $controller->check() ): ?>
                <p class="mline notification">
                    <i class="def-icon icon-tick" aria-hidden="true"></i>
                    <span><?php _e( 'XML-RPC is disabled.', wp_defender()->domain ) ?></span>
                </p>
                <div class="end"></div>
                <div class="clear mline"></div>
                <form method="post" class="hardener-frm rule-process">
					<?php $controller->createNonceField(); ?>
                    <input type="hidden" name="action" value="processRevert"/>
                    <input type="hidden" name="slug" value="<?php echo $controller::$slug ?>"/>
                    <button class="button button-secondary"
                            type="submit"><?php _e( "Revert", wp_defender()->domain ) ?></button>
                </form>
			<?php else: ?>
                <div class="end"></div>
				<div class="clear mline"></div>
                <form method="post" class="hardener-frm rule-process hardener-frm-process-xml-rpc">
					<?php $controller->createNonceField(); ?>
                    <input type="hidden" name="action" value="processHardener"/>
                    <input type="hidden" name="updatePosts" value="no"/>
                    <input type="hidden" name="slug" value="<?php echo $controller::$slug ?>"/>
                    <button class="button float-r"
                            type="submit"><?php _e( "Disable XML-RPC", wp_defender()->domain ) ?></button>
                </form>
				<?php $controller->showIgnoreForm() ?>
			<?php endif; ?>
        </div>
        <div class="clear"></div>
    </div>
</div>
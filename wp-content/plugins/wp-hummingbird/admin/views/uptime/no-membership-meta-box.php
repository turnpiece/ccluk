<div class="wphb-block-entry">

	<div class="wphb-block-entry-content wphb-block-content-center">

        <img class="wphb-image wphb-image-center wphb-image-icon-content-top"
             src="<?php echo wphb_plugin_url() . 'admin/assets/image/hb-graphic-uptime-disabled@1x.png'; ?>"
             srcset="<?php echo wphb_plugin_url() . 'admin/assets/image/hb-graphic-uptime-disabled@2x.png'; ?> 2x"
             alt="<?php esc_attr_e( 'Monitor your website', 'wphb' ); ?>">

        <div class="content">
            <p><?php _e( 'Uptime monitors your server response time and lets you know when your website is down or too slow for your visitors. Get Uptime monitoring as part of a WPMU DEV membership.', 'wphb' ); ?></p>

            <div class="buttons">
                <a id="wphb-upgrade-membership-modal-link" class="button button-large button-content-cta" href="#wphb-upgrade-membership-modal" rel="dialog"><?php _e( 'Upgrade to Pro', 'wphb') ;?></a>
            </div>
        </div><!-- end content -->

	</div><!-- end wphb-block-entry-content -->

</div><!-- end wphb-block-entry -->

<?php
	wphb_membership_modal();
?>
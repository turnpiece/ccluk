<?php
/**
 * Uptime no membership meta box.
 *
 * @package Hummingbird
 */

?>
<div class="wphb-block-entry">

	<div class="wphb-block-entry-content wphb-block-content-center">

		<img class="wphb-image wphb-image-center wphb-image-icon-content-top"
			 src="<?php echo WPHB_DIR_URL . 'admin/assets/image/hb-graphic-uptime-disabled@1x.png'; ?>"
			 srcset="<?php echo WPHB_DIR_URL . 'admin/assets/image/hb-graphic-uptime-disabled@2x.png'; ?> 2x"
			 alt="<?php esc_attr_e( 'Monitor your website', 'wphb' ); ?>">

		<div class="content">
			<p><?php _e( 'Uptime monitors your server response time and lets you know when your website is down or too slow for your visitors. Get Uptime monitoring as part of a WPMU DEV membership.', 'wphb' ); ?></p>

			<div class="buttons">
				<a id="wphb-upgrade-membership-modal-link" class="button button-large button-content-cta" href="<?php echo WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_uptime_upgrade_button' ); ?>" target="_blank">
					<?php esc_html_e( 'Upgrade to Pro', 'wphb' ); ?>
				</a>
			</div>
		</div><!-- end content -->

	</div><!-- end wphb-block-entry-content -->

</div><!-- end wphb-block-entry -->
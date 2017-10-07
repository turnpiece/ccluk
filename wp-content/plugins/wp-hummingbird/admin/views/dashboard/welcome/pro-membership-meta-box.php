<?php
/**
 * No membership meta box on dashboard page.
 *
 * @package Hummingbird
 */

?>
<div class="wphb-block-entry">

	<div class="wphb-block-content-center">

		<div class="content">
			<span class="callout-title"><?php esc_html_e( 'Try pro features for free!', 'wphb' ); ?></span>
			<p><?php esc_html_e( 'Upgrade to Hummingbird Pro and get access to up to 2x minify compression, automated performance tests, host your files on our blazing fast WPMU DEV CDN and monitor your website’s downtime with Uptime monitor.', 'wphb' ); ?></p>
			<p><?php esc_html_e( 'Get all this as part of a WPMU DEV Membership, and the best part is you can try everything absolutely free.', 'wphb' ); ?></p>

			<a class="button button-large button-content-cta" href="#wphb-upgrade-membership-modal" id="dash-uptime-update-membership" rel="dialog">
				<?php esc_html_e( 'Find out more', 'wphb' ); ?>
			</a>
		</div><!-- end content -->

		<div class="wphb-block-entry-image wphb-block-entry-image-bottom wphb-block-entry-image-center">
			<img class="wphb-image"
				 src="<?php echo wphb_plugin_url() . 'admin/assets/image/dev-team.png'; ?>"
				 srcset="<?php echo wphb_plugin_url() . 'admin/assets/image/dev-team@2x.png'; ?> 2x"
				 alt="<?php esc_attr_e( 'Try pro features for free!', 'wphb' ); ?>">
		</div>

	</div><!-- end wphb-block-entry-content -->

</div><!-- end wphb-block-entry -->
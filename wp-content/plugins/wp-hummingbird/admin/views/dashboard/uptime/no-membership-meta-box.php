<?php
/**
 * Uptime no membership meta box on dashboard page.
 *
 * @package Hummingbird
 */

?>
<div class="wphb-block-entry">

	<div class="wphb-block-entry-content">

		<div class="content">
			<p><?php esc_html_e( 'Monitor your website and get notified if/when it’s inaccessible. We’ll also watch your server response time.', 'wphb' ); ?></p>
		</div><!-- end content -->

		<div class="content-box content-box-two-cols-image-left">
			<div class="wphb-block-entry-content wphb-upsell-free-message">
				<p>
					<?php printf(
						__( 'Performance improvements hardly matter if your website isn’t accessible. Monitor your uptime and downtime with WPMU DEV’s Uptime Monitoring website management tool. <a href="%s" target="_blank">Try Pro for FREE today!</a>', 'wphb' ),
						WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_dash_uptime_upsell_link' )
					); ?>
				</p>
			</div>
		</div>

	</div><!-- end wphb-block-entry-content -->

</div><!-- end wphb-block-entry -->
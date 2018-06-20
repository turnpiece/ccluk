<?php
/**
 * Uptime no membership meta box on dashboard page.
 *
 * @package Hummingbird
 */

?>
<div class="sui-box-settings-row sui-no-padding-bottom">
	<p><?php esc_html_e( 'Monitor your website and get notified if/when it’s inaccessible. We’ll also watch your server response time.', 'wphb' ); ?></p>
</div>

<div class="sui-box-settings-row sui-upsell-row">
	<img class="sui-image sui-upsell-image"
		 src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hb-graphic-upsell-uptime.png' ); ?>"
		 srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hb-graphic-upsell-uptime@2x.png' ); ?> 2x"
		 alt="<?php esc_attr_e( 'Try Pro for FREE', 'wphb' ); ?>">

	<div class="sui-upsell-notice">
		<p>
			<?php printf(
				__( 'Performance improvements hardly matter if your website isn’t accessible. Monitor your uptime and downtime with WPMU DEV’s Uptime Monitoring website management tool. <a href="%s" target="_blank">Try Pro for FREE today!</a>', 'wphb' ),
				WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_dash_uptime_upsell_link' )
			); ?>
		</p>
	</div>
</div>
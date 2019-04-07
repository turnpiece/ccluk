<?php
/**
 * Performance tests reporting meta box.
 *
 * @package Hummingbird
 */

?>
<div class="sui-box-body">
	<p><?php esc_html_e( 'Configure Hummingbird to automatically and regularly test your website and email you reports.', 'wphb' ); ?></p>
</div>
<div class="sui-box-settings-row sui-disabled">
	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label">
			<?php esc_html_e( 'Configure', 'wphb' ); ?>
		</span>
		<span class="sui-description">
			<?php esc_html_e( 'Choose from daily, weekly or monthly email reports.', 'wphb' ); ?>
		</span>
	</div>
	<div class="sui-box-settings-col-2">
		<label class="sui-toggle">
			<input type="hidden" name="email-notifications" value="0" />
			<input type="checkbox" name="email-notifications" value="1" id="chk1" />
			<span class="sui-toggle-slider"></span>
		</label>
		<label for="chk1">
			<?php esc_html_e( 'Run regular scans & reports', 'wphb' ); ?>
		</label>
	</div>
</div>
<div class="sui-box-settings-row sui-upsell-row">
	<img class="sui-image sui-upsell-image"
		 src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hummingbird-upsell-minify.png' ); ?>"
		 srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hummingbird-upsell-minify@2x.png' ); ?> 2x"
		 alt="<?php esc_attr_e( 'Scheduled automated performance tests', 'wphb' ); ?>">

	<div class="sui-upsell-notice">
		<p>
			<?php
			printf(
				__( 'Schedule automated performance tests and receive email reports direct to your inbox. You\'ll get Hummingbird Pro plus 100+ WPMU DEV plugins & 24/7 WP support. <a href="%s" target="_blank">Try Pro for FREE today!</a>', 'wphb' ),
				WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_test_upsell_link' )
			);
			?>
		</p>
	</div>
</div>
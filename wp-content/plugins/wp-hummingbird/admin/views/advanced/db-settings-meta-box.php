<?php
/**
 * Advanced tools database cleanup settings meta box.
 *
 * @package Hummingbird
 * @since 1.8
 */

?>

<div class="sui-box-settings-row sui-disabled">
	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'Schedule Cleanups', 'wphb' ) ?></span>
		<span class="sui-description">
			<?php esc_html_e( 'Schedule Hummingbird to automatically clean your database daily, weekly or monthly.', 'wphb' ); ?>
		</span>
	</div><!-- end col-third -->
	<div class="sui-box-settings-col-2">
		<label class="sui-toggle sui-tooltip sui-tooltip-top-right" data-tooltip="<?php esc_attr_e( 'Enabled scheduled cleanups', 'wphb' ); ?>">
			<input type="checkbox" name="scheduled_cleanup" id="scheduled_cleanup">
			<span class="sui-toggle-slider"></span>
		</label>
		<label for="scheduled_cleanup"><?php esc_html_e( 'Enabled scheduled cleanups', 'wphb' ); ?></label>
	</div>
</div>

<div class="sui-box-settings-row sui-upsell-row">
	<img class="sui-image sui-upsell-image"
		 src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hb-graphic-db-upsell.png' ); ?>"
		 srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hb-graphic-db-upsell@2x.png' ); ?> 2x"
		 alt="<?php esc_attr_e( 'Scheduled automated database cleanup', 'wphb' ); ?>">

	<div class="sui-upsell-notice">
		<?php printf(
			__( '<p>Regular cleanups of your database ensures you’re regularly removing extra bloat which can slow down your host server. Upgrade to Hummingbird Pro as part of a WPMU DEV membership to unlock this feature today! <a href="%s" target="_blank">Learn More</a>.</p>', 'wphb' ),
			WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_dbcleanup_schedule_upsell_link' )
		); ?>
	</div>
</div>
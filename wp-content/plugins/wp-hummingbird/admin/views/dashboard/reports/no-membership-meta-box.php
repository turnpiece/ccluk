<?php
/**
 * Reports no membership meta box on dashboard page.
 *
 * @package Hummingbird
 *
 * @var string $title  Reports module title.
 */

?>

<div class="sui-box-settings-row sui-no-padding-bottom">
	<p><?php esc_html_e( 'Get tailored performance reports delivered to your inbox so you don’t have to worry about checking in.', 'wphb' ); ?></p>

	<div class="sui-row">
		<div class="sui-col-lg-6">
			<a href="<?php echo WP_Hummingbird_Utils::get_admin_menu_url( 'performance' ) . '&view=reports'; ?>">
				<div class="report-status with-corner">
					<i class="sui-icon-hummingbird" aria-hidden="true"></i>
					<strong><?php esc_html_e( 'Performance Test', 'wphb' ); ?></strong>
					<div class="corner">
						<?php esc_html_e( 'Pro', 'wphb' ); ?>
					</div>
				</div>
			</a>
		</div>
		<div class="sui-col-lg-6">
			<a href="<?php echo WP_Hummingbird_Utils::get_admin_menu_url( 'advanced' ) . '&view=db#wphb-box-advanced-db-settings'; ?>">
				<div class="report-status with-corner">
					<i class="sui-icon-user-reputation-points" aria-hidden="true"></i>
					<strong><?php esc_html_e( 'Database Cleanup', 'wphb' ); ?></strong>
					<span class="sui-tag sui-tag-inactive"><?php esc_html_e( 'Coming soon', 'wphb' ); ?></span>
					<div class="corner">
						<?php esc_html_e( 'Pro', 'wphb' ); ?>
					</div>
				</div>
			</a>
		</div>
	</div>
</div>

<div class="sui-box-settings-row sui-upsell-row">
	<img class="sui-image sui-upsell-image"
		 src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hb-graphic-upsell-reports.png' ); ?>"
		 srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hb-graphic-upsell-reports@2x.png' ); ?> 2x"
		 alt="<?php esc_attr_e( 'Scheduled automated reports', 'wphb' ); ?>">

	<div class="sui-upsell-notice">
		<p>
			<?php printf(
				__( 'Schedule automated performance tests and receive email reports direct to your inbox. Get reporting as part of a full WPMU DEV membership. <a href="%s" target="_blank">Try Pro for FREE today!</a>', 'wphb' ),
				WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_dash_reports_upsell_link' )
			); ?>
		</p>
	</div>
</div>
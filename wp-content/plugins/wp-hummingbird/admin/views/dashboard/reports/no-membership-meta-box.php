<?php
/**
 * Reports no membership meta box on dashboard page.
 *
 * @package Hummingbird
 *
 * @var string $title  Reports module title.
 */

?>
<div class="wphb-block-entry">

	<div class="wphb-block-entry-content">

		<div class="content">
			<p><?php esc_html_e( 'Get tailored performance reports delivered to your inbox so you don’t have to worry about checking in.', 'wphb' ); ?></p>
		</div><!-- end content -->

		<div class="row">
			<div class="col-half">
				<a href="<?php echo WP_Hummingbird_Utils::get_admin_menu_url( 'performance' ) . '&view=reports'; ?>">
					<div class="report-status with-corner">
						<i class="hb-icon-performancetest"></i>
						<strong><?php esc_html_e( 'Performance Test', 'wphb' ); ?></strong>
						<div class="corner">
							<?php esc_html_e( 'Pro', 'wphb' ); ?>
						</div>
					</div>
				</a>
			</div>
			<!--
			<div class="col-half">
				<a href="<?php echo WP_Hummingbird_Utils::get_admin_menu_url( 'uptime' ); ?>">
					<div class="report-status with-corner">
						<i class="hb-icon-smush"></i>
						<strong><?php esc_html_e( 'Uptime Report', 'wphb' ); ?></strong>
						<div class="corner">
							<?php esc_html_e( 'Pro', 'wphb' ); ?>
						</div>
					</div>
				</a>
			</div>
			-->
		</div>

		<div class="content-box content-box-two-cols-image-left">
			<div class="wphb-block-entry-content wphb-upsell-free-message">
				<p>
					<?php printf(
						__( 'Schedule automated performance tests and receive email reports direct to your inbox. Get reporting as part of a full WPMU DEV membership. <a href="%s" target="_blank">Try Pro for FREE today!</a>', 'wphb' ),
						WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_dash_reports_upsell_link' )
					); ?>
				</p>
			</div>
		</div>

	</div><!-- end wphb-block-entry-content -->

</div><!-- end wphb-block-entry -->
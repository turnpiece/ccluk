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
	<p><?php esc_html_e( 'Automate your workflow with daily, weekly or monthly reports sent directly to your inbox.', 'wphb' ); ?></p>

	<table class="sui-table sui-flushed">
		<thead>
		<tr>
			<th width="50%"><?php esc_html_e( 'Report', 'wphb' ); ?></th>
			<th width="50%">&nbsp;</th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td>
				<i class="sui-icon-hummingbird" aria-hidden="true"></i>
				<strong><?php esc_html_e( 'Performance Test', 'wphb' ); ?></strong>
			</td>
			<td>
				<a href="<?php echo WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_dash_reports_upgrade_button' ); ?>" class="sui-button sui-button-green">
					<?php esc_html_e( 'Upgrade', 'wphb' ); ?>
				</a>
			</td>
		</tr>
		<tr>
			<td>
				<i class="sui-icon-user-reputation-points" aria-hidden="true"></i>
				<strong><?php esc_html_e( 'Database Cleanup', 'wphb' ); ?></strong>
			</td>
			<td>
				<a href="<?php echo WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_dash_reports_upgrade_button' ); ?>" class="sui-button sui-button-green">
					<?php esc_html_e( 'Upgrade', 'wphb' ); ?>
				</a>
			</td>
		</tr>
		<tr>
			<td>
				<i class="sui-icon-uptime" aria-hidden="true"></i>
				<strong><?php esc_html_e( 'Uptime', 'wphb' ); ?></strong>
			</td>
			<td>
				<a href="<?php echo WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_dash_reports_upgrade_button' ); ?>" class="sui-button sui-button-green">
					<?php esc_html_e( 'Upgrade', 'wphb' ); ?>
				</a>
			</td>
		</tr>
		</tbody>
	</table>
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
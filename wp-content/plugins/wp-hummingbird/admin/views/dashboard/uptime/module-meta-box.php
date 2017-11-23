<?php
/**
 * Uptime meta box on dashboard page.
 *
 * @package Hummingbird
 *
 * @var object $uptime_stats  Uptime stats.
 */

?>
<div class="content">
	<div class="wphb-notice wphb-notice-success">
		<p><?php esc_html_e( 'Your website is currently up and humming.', 'wphb' ); ?></p>
	</div>
</div>

<div class="wphb-dash-table two-columns">
	<div class="wphb-dash-table-row">
		<div><?php esc_html_e( 'Availability', 'wphb' ); ?></div>
		<div><?php echo esc_html( $uptime_stats->availability ); ?></div>
	</div>

	<div class="wphb-dash-table-row">
		<div><?php esc_html_e( 'Downtime', 'wphb' ); ?></div>
		<div><?php echo esc_html( $uptime_stats->period_downtime ); ?></div>
	</div>

	<div class="wphb-dash-table-row">
		<div><?php esc_html_e( 'Average Response Time', 'wphb' ); ?></div>
		<div>
			<?php echo $uptime_stats->response_time ? esc_html( $uptime_stats->response_time ) : esc_html__( 'Calculating...', 'wphb' ); ?>
		</div>
	</div>

	<div class="wphb-dash-table-row">
		<?php
		$gmt_date = date( 'Y-m-d H:i:s', $uptime_stats->up_since );
		$site_date = get_date_from_gmt( $gmt_date, get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
		?>
		<div><?php esc_html_e( 'Last Down', 'wphb' ); ?></div>
		<div><?php echo esc_html( $site_date ); ?></div>
	</div>
</div><!-- end wphb-dash-table -->
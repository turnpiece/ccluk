<?php
/**
 * Uptime meta box on dashboard page.
 *
 * @package Hummingbird
 *
 * @var object $uptime_stats  Uptime stats.
 */

?>
<p><?php esc_html_e( 'Monitor your website and get notified if/when it’s inaccessible. We’ll also watch your server response time.', 'wphb' ); ?></p>

<div class="sui-notice sui-notice-success">
	<p><?php esc_html_e( 'Your website is currently up and humming.', 'wphb' ); ?></p>
</div>

<ul class="sui-list sui-list-top-border sui-margin-top sui-no-margin-bottom">
	<li>
		<span class="sui-list-label"><?php esc_html_e( 'Availability', 'wphb' ); ?></span>
		<span class="sui-list-detail"><?php echo esc_html( $uptime_stats->availability ); ?></span>
	</li>
	<li>
		<span class="sui-list-label"><?php esc_html_e( 'Downtime', 'wphb' ); ?></span>
		<span class="sui-list-detail"><?php echo esc_html( $uptime_stats->period_downtime ); ?></span>
	</li>
	<li>
		<span class="sui-list-label"><?php esc_html_e( 'Average Response Time', 'wphb' ); ?></span>
		<span class="sui-list-detail"><?php echo $uptime_stats->response_time ? esc_html( $uptime_stats->response_time ) : esc_html__( 'Calculating...', 'wphb' ); ?></span>
	</li>
	<li>
		<?php
		$gmt_date = date( 'Y-m-d H:i:s', $uptime_stats->up_since );
		$site_date = get_date_from_gmt( $gmt_date, get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
		?>
		<span class="sui-list-label"><?php esc_html_e( 'Last Down', 'wphb' ); ?></span>
		<span class="sui-list-detail"><?php echo esc_html( $site_date ); ?></span>
	</li>
</ul>
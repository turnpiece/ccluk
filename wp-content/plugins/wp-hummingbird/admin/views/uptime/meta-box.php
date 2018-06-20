<?php
/**
 * Uptime meta box.
 *
 * @package Hummingbird
 *
 * @var object    $uptime_stats    Last stats report.
 * @var string    $error           Error message.
 * @var string    $error_type      Error type.
 * @var string    $retry_url       Run uptime URL.
 * @var string    $support_url     Support URL.
 */

?>
<?php if ( $error && ( ! strpos( $error, 'down for maintenance' ) ) ) : ?>
	<div class="sui-notice sui-notice-<?php echo esc_attr( $error_type ); ?> wphb-notice-box">
		<p><?php echo esc_html( $error ); ?></p>
		<a href="<?php echo esc_url( $retry_url ); ?>" class="sui-button sui-button-primary button-notice"><?php esc_html_e( 'Try again', 'wphb' ); ?></a>
		<a target="_blank" href="<?php echo esc_url( $support_url ); ?>" class="sui-button sui-button-primary button-notice"><?php esc_html_e( 'Support', 'wphb' ); ?></a>
		<span class="sui-notice-dismiss">
			<a href="#">Dismiss</a>
		</span>
	</div>
<?php elseif ( strpos( $error, 'down for maintenance' ) ) : ?>
	<div class="sui-block-content-center">
		<img class="wphb-image-icon-content wphb-image-icon-content-top wphb-image-icon-content-center wphb-uptime-icon"
			 src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hb-graphic-uptime-disabled@1x.png' ); ?>"
			 srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hb-graphic-uptime-disabled@2x.png' ); ?> 2x">

		<p>
			<?php _e( 'Uptime monitors your server response time and lets you know when your website is down<br>
			or too slow for your visitors. This service is currently under maintenance as we build a<br>
			brand new monitoring service. Check back soon!', 'wphb' ); ?>
		</p>
	</div>
<?php else : ?>
	<p>
		<?php esc_html_e( 'Server response time is the amount of time it takes for a web server to
		respond to a request from a browser. The longer it takes, the longer your visitors wait for the page
		to start loading.', 'wphb' ); ?>
	</p>
	<?php if ( null === $uptime_stats->response_time && ! is_wp_error( $uptime_stats ) ) : ?>
		<div class="sui-notice sui-notice-blue">
			<p>
				<?php esc_html_e( 'We don’t have any data feeding in yet. It can take an hour or two
				for this graph to populate with data so feel free to check back soon!', 'wphb' ); ?>
			</p>
		</div>
	<?php endif; ?>

	<input type="hidden" id="uptime-chart-json" value="<?php echo esc_attr( $uptime_stats->chart_json ); ?>">
	<div class="uptime-chart wphb-uptime-graph" id="uptime-chart">
		<span class="loader i-wpmu-dev-loader"></span>
	</div>

	<input type="hidden" id="downtime-chart-json" value="<?php echo esc_attr( $downtime_chart_json ); ?>">
	<div class="downtime-chart" id="downtime-chart">
		<span class="loader i-wpmu-dev-loader"></span>
	</div>

	<div class="downtime-chart-key">
		<span class="response-time-key"><?php esc_html_e( 'Response Time', 'wphb' ); ?></span>
		<span class="uptime-key"><?php esc_html_e( 'Uptime', 'wphb' ); ?></span>
		<span class="downtime-key"><?php esc_html_e( 'Downtime', 'wphb' ); ?></span>
		<span class="unknown-key"><?php esc_html_e( 'Unknown', 'wphb' ); ?></span>
	</div>

	<script>
		jQuery(document).ready( function() {
			window.WPHB_Admin.getModule( 'uptime' );
		});
	</script>
<?php endif; ?>
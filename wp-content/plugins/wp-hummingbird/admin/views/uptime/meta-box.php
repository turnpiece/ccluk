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
	<div class="row">
		<div class="wphb-notice wphb-notice-<?php echo esc_attr( $error_type ); ?> wphb-notice-box can-close">
			<span class="close"></span>
			<p><?php echo esc_html( $error ); ?></p>
			<a href="<?php echo esc_url( $retry_url ); ?>" class="button button-notice"><?php esc_html_e( 'Try again', 'wphb' ); ?></a>
			<a target="_blank" href="<?php echo esc_url( $support_url ); ?>" class="button button-notice"><?php esc_html_e( 'Support', 'wphb' ); ?></a>
		</div>
	</div>
<?php elseif ( strpos( $error, 'down for maintenance' ) ) : ?>
	<div class="wphb-block-entry">

		<div class="wphb-block-entry-content wphb-block-content-center">

            <div class="content">
                <img class="wphb-image-icon-content wphb-image-icon-content-top wphb-image-icon-content-center wphb-uptime-icon"
                     src="<?php echo WPHB_DIR_URL . 'admin/assets/image/hb-graphic-uptime-disabled@1x.png'; ?>"
                     srcset="<?php echo WPHB_DIR_URL . 'admin/assets/image/hb-graphic-uptime-disabled@2x.png'; ?> 2x">

				<p><?php esc_html_e( 'Uptime monitors your server response time and lets you know when your website is down or<br> too slow for your visitors. This service is currently under maintenance as we build a brand<br> new monitoring service. Check back soon!', 'wphb' ); ?></p>
			</div><!-- end content -->

		</div><!-- end wphb-block-entry-content -->

	</div><!-- end wphb-block-entry -->
<?php else : ?>
	<div class="row row-space-large">
		<div class="wphb-block wphb-block-uptime-average-responsive-time">
			<div class="wphb-block-header">
				<p class="wphb-block-description"><?php esc_html_e( 'Server response time is the amount of time it takes for a web server to respond to a request from a browser. The longer it takes, the longer your visitors wait for the page to start loading.', 'wphb' ); ?></p>
				<?php if ( $uptime_stats->response_time == null && ! is_wp_error( $uptime_stats ) ) : ?>
					<div class="wphb-notice wphb-notice-blue">
						<p><?php esc_html_e( 'We don’t have any data feeding in yet. It can take an hour or two for this graph to populate with data so feel free to check back soon!', 'wphb' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
			<div class="wphb-block-content">
				<input type="hidden" id="uptime-chart-json" value="<?php echo esc_attr( $uptime_stats->chart_json ); ?>">
				<div class="uptime-chart wphb-uptime-graph" id="uptime-chart" style="height:400px">
					<span class="loader i-wpmu-dev-loader"></span>
				</div>
				<div class="wphb-block-content wphb-downtime-basic">
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
				</div>
			</div>
		</div><!-- end wphb-block-uptime-average-responsive-time -->
	</div>

	<script>
		jQuery(document).ready( function() {
			window.WPHB_Admin.getModule( 'uptime' );
		});
	</script>
<?php endif; ?>
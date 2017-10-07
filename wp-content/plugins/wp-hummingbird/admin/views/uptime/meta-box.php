<?php if ( $error && ( ! strpos( $error, 'down for maintenance' ) ) ): ?>
	<div class="row">
		<div class="wphb-notice wphb-notice-<?php echo $error_type; ?> wphb-notice-box can-close">
			<span class="close"></span>
			<p><?php echo $error; ?></p>
			<a href="<?php echo esc_url( $retry_url ); ?>" class="button button-notice"><?php _e( 'Try again', 'wphb' ); ?></a>
			<a target="_blank" href="<?php echo esc_url( $support_url ); ?>" class="button button-notice"><?php _e( 'Support', 'wphb' ); ?></a>
		</div>
	</div>
<?php elseif ( strpos( $error, 'down for maintenance' ) ) : ?>
    <div class="wphb-block-entry">

        <div class="wphb-block-entry-content wphb-block-content-center">

            <div class="content">
                <img class="wphb-image-icon-content wphb-image-icon-content-top wphb-image-icon-content-center wphb-uptime-icon"
                     src="<?php echo wphb_plugin_url() . 'admin/assets/image/hb-graphic-uptime-disabled@1x.png'; ?>"
                     srcset="<?php echo wphb_plugin_url() . 'admin/assets/image/hb-graphic-uptime-disabled@2x.png'; ?> 2x">

                <p><?php _e( 'Uptime monitors your server response time and lets you know when your website is down or<br> too slow for your visitors. This service is currently under maintenance as we build a brand<br> new monitoring service. Check back soon!', 'wphb' ); ?></p>
            </div><!-- end content -->

        </div><!-- end wphb-block-entry-content -->

    </div><!-- end wphb-block-entry -->
<?php else: ?>

	<div class="row row-space-large">
		<div class="wphb-block wphb-block-uptime-average-responsive-time">
			<div class="wphb-block-header">
				<p class="wphb-block-description"><?php _e( 'Server response time is the amount of time it takes for a web server to respond to a request from a browser. The longer it takes, the longer your visitors wait for the page to start loading.', 'wphb' ); ?></p>
			</div>
			<div class="wphb-block-content">
				<input type="hidden" id="uptime-chart-json" value="<?php echo $uptime_stats->chart_json; ?>">
				<div class="uptime-chart" id="uptime-chart" style="height:400px">
					<span class="loader i-wpmu-dev-loader"></span>
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

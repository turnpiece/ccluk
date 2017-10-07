<div class="wphb-block-entry">

	<div class="wphb-block-entry-image wphb-block-entry-image-bottom">
		<img class="wphb-image"
		     src="<?php echo wphb_plugin_url() . 'admin/assets/image/hb-graphic-uptime-up.png'; ?>"
		     srcset="<?php echo wphb_plugin_url() . 'admin/assets/image/hb-graphic-uptime-up@2x.png'; ?> 2x"
		     alt="<?php _e('Hummingbird', 'wphb'); ?>">
	</div>

	<div class="wphb-block-entry-third">
        <span class="not-present">
	        <?php if ( $uptime_stats && ! is_wp_error( $uptime_stats ) ):
		        echo round( $uptime_stats->availability, 1 ) . '%';
	        endif; ?>
        </span>
		<p class="current-performance-score"><?php _e( 'Website availability during the reporting period', 'wphb' ); ?></p>
		<span>
			<span class="list-detail-stats-heading-extra-info">
            <?php if ( $uptime_stats && ! is_wp_error( $uptime_stats ) ):
                echo $uptime_stats->response_time ? $uptime_stats->response_time : "Calculating...";
            endif; ?>
			</span>
        </span>
		<p><?php _e( 'Average server response time during the reporting period', 'wphb' ); ?></p>
	</div>

	<div class="wphb-block-entry-third">
		<ul class="dev-list">
			<li>
				<span class="list-label"><?php _e( 'Outages', 'wphb' ); ?></span>
				<span class="list-detail">
					<?php if ( intval( $uptime_stats->outages ) > 0 ) : ?>
						<div class="wphb-dash-numbers"><?php echo intval( $uptime_stats->outages ); ?></div>
					<?php else : ?>
						<p><?php _e( 'None', 'wphb' ); ?></p>
					<?php endif; ?>
				</span>
			</li>
			<li>
				<span class="list-label"><?php _e( 'Downtime', 'wphb' ); ?></span>
				<span class="list-detail">
					<?php if ( isset( $uptime_stats->period_downtime ) ) : ?>
						<div class="wphb-dash-numbers"><?php echo $uptime_stats->period_downtime; ?></div>
					<?php else : ?>
						<p><?php _e( 'None', 'wphb' ); ?></p>
					<?php endif; ?>
				</span>
			</li>
			<li>
				<span class="list-label"><?php _e( 'Up Since', 'wphb' ); ?></span>
				<span class="list-detail">
					<?php $site_date = '';
					if ( $uptime_stats->up_since ) {
						$gmt_date = date( 'Y-m-d H:i:s', $uptime_stats->up_since );
						$site_date = get_date_from_gmt( $gmt_date, get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
					} ?>
					<div class="wphb-dash-numbers">
						<?php
						if ( empty( $site_date ) ) {
							esc_html_e( 'Website is reported down', 'wphb' );
						} else {
							echo $site_date;
						} ?>
					</div>
				</span>
			</li>
		</ul>
	</div>

</div><!-- end wphb-block-entry -->
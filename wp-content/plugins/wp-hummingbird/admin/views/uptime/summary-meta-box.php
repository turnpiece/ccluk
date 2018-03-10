<?php
/**
 * Uptime meta box.
 *
 * @package Hummingbird
 *
 * @var object $uptime_stats       Last stats report.
 * @var string $data_range_text    Human readable data range text.
 */

?>
<div class="wphb-block-entry">

	<div class="wphb-block-entry-image wphb-block-entry-image-bottom">
		<img class="wphb-image"
		     src="<?php echo WPHB_DIR_URL . 'admin/assets/image/hb-graphic-uptime-up.png'; ?>"
		     srcset="<?php echo WPHB_DIR_URL . 'admin/assets/image/hb-graphic-uptime-up@2x.png'; ?> 2x"
		     alt="<?php _e('Hummingbird', 'wphb'); ?>">
	</div>

	<div class="wphb-block-entry-third">
		<span class="not-present">
			<?php
			if ( $uptime_stats && ! is_wp_error( $uptime_stats ) ) :
				if ( 0 === round( $uptime_stats->availability, 1 ) || null === $uptime_stats->response_time ) :
					echo esc_html( '100%' );
				else :
					echo esc_html( round( $uptime_stats->availability, 1 ) ) . '%';
				endif;
			endif;
			?>
		</span>
		<p class="current-performance-score"><?php echo esc_html__( 'Website availability in the last ', 'wphb' ) . esc_html( $data_range_text ); ?></p>
		<span>
			<span class="list-detail-stats-heading-extra-info">
			<?php
			if ( $uptime_stats && ! is_wp_error( $uptime_stats ) ) :
				echo $uptime_stats->response_time ? esc_html( $uptime_stats->response_time ) : esc_html( 'Waiting on data...' );
			endif;
			?>
			</span>
		</span>
		<p><?php esc_html_e( 'Average server response time during the reporting period', 'wphb' ); ?></p>
	</div>

	<div class="wphb-block-entry-third">
		<ul class="dev-list">
			<li>
				<span class="list-label"><?php esc_html_e( 'Outages', 'wphb' ); ?></span>
				<span class="list-detail">
					<?php if ( intval( $uptime_stats->outages ) > 0 ) : ?>
						<div class="wphb-dash-numbers"><?php echo intval( $uptime_stats->outages ); ?></div>
					<?php else : ?>
						<p><?php esc_html_e( 'None', 'wphb' ); ?></p>
					<?php endif; ?>
				</span>
			</li>
			<li>
				<span class="list-label"><?php esc_html_e( 'Downtime', 'wphb' ); ?></span>
				<span class="list-detail">
					<?php if ( isset( $uptime_stats->period_downtime ) ) : ?>
						<div class="wphb-dash-numbers"><?php echo esc_html( $uptime_stats->period_downtime ); ?></div>
					<?php else : ?>
						<p><?php esc_html_e( 'None', 'wphb' ); ?></p>
					<?php endif; ?>
				</span>
			</li>
			<li>
				<span class="list-label"><?php esc_html_e( 'Up Since', 'wphb' ); ?></span>
				<span class="list-detail">
					<?php
					$site_date = '';
					if ( $uptime_stats->up_since ) {
						$gmt_date = date( 'Y-m-d H:i:s', $uptime_stats->up_since );
						$site_date = get_date_from_gmt( $gmt_date, get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
					}
					?>
					<div class="wphb-dash-numbers">
						<?php
						if ( empty( $site_date ) ) {
							esc_html_e( 'Website is reported down', 'wphb' );
						} else {
							echo esc_html( $site_date );
						}
						?>
					</div>
				</span>
			</li>
		</ul>
	</div>

</div><!-- end wphb-block-entry -->
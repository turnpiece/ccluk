<?php
/**
 * Uptime downtime meta box.
 *
 * @package Hummingbird
 *
 * @var object $uptime_stats           Last stats report.
 */

?>
<div class="row">
	<div class="wphb-block wphb-block-uptime-downtime">
		<p class="wphb-block-description"><?php esc_html_e( 'Here’s a snapshot of when your site went down, which means visitors couldn’t view your website.', 'wphb' ); ?></p>
		<div class="wphb-block-content">
			<input type="hidden" id="downtime-chart-json" value="<?php echo esc_attr( $downtime_chart_json ); ?>">
			<div class="downtime-chart" id="downtime-chart">
				<span class="loader i-wpmu-dev-loader"></span>
			</div>
			<div class="downtime-chart-key">
				<span class="uptime-key"><?php esc_html_e( 'Uptime', 'wphb' ); ?></span>
				<span class="downtime-key"><?php esc_html_e( 'Downtime', 'wphb' ); ?></span>
				<span class="unknown-key"><?php esc_html_e( 'Unknown', 'wphb' ); ?></span>
			</div>
		</div>
	</div><!-- end wphb-block-uptime-downtime -->
	<div class="row">
		<?php
		$this->admin_notices->show(
			'uptime-info',
			'Uptime monitor will report your site as down when it takes 30+ seconds to load your homepage. Your host may report your site as online, but as far as user experience goes, slow page speeds are bad practice. Consider upgrading your hosting if your site is regularly down.',
			'grey',
			false,
			true
		);
		?>
		<div class="wphb-block wphb-block-uptime-downtime">
			<div class="wphb-block-header">
				<strong><?php esc_html_e( 'Logs', 'wphb' ); ?></strong>
			</div>
			<div class="wphb-block-content">

				<ul class="dev-list-stats dev-list-stats-standalone">
					<?php if ( ! count( $uptime_stats->events ) ) : ?>
						<div class="wphb-caching-success wphb-notice wphb-notice-success">
							<p><?php esc_html_e( 'No downtime has been reported during the reporting period.', 'wphb' ); ?></p>
						</div>
					<?php else : ?>
						<?php foreach ( $uptime_stats->events as $event ) : ?>
							<li class="dev-list-stats-item">
								<div>
									<span class="list-label list-label-stats">
									<?php if ( ! empty( $event->down ) && ! empty( $event->up ) ) : ?>
										<?php $down = strtotime( get_date_from_gmt( date( 'Y-m-d H:i:s', $event->down ) ) ); ?>
										<?php $up = strtotime( get_date_from_gmt( date( 'Y-m-d H:i:s', $event->up ) ) ); ?>
										<div class="wphb-pills-group">
											<span class="wphb-pills red" tooltip="<?php echo esc_attr( $event->details ); ?>"><i class="dev-icon dev-icon-caret_down"></i> <?php echo esc_html( date_i18n( 'M j @ g:ia', $down ) ); ?></span>
											<?php
											if ( $event->downtime ) :
												echo '<span class="list-detail-stats">' . esc_html( $event->downtime ) . '</span>';
											endif;
											?>
											<img class="wphb-image-pills-divider"
													   src="<?php echo WPHB_DIR_URL . 'admin/assets/image/downtime-splice.svg'; ?>"
													   alt="<?php esc_attr_e( 'Spacer image', 'wphb' ); ?>">
											<span class="wphb-pills green"><i class="dev-icon dev-icon-caret_up"></i> <?php echo esc_html( date_i18n( 'M j @ g:ia', $up ) ); ?></span>
										</div>
									<?php endif; ?>
									</span>
								</div>
							</li><!-- end dev-list-stats-item -->
						<?php endforeach; ?>
					<?php endif; ?>
				</ul>
			</div>
		</div>
	</div>
</div>
<script>
	jQuery(document).ready( function() {
		window.WPHB_Admin.getModule( 'uptime' );
	});
</script>
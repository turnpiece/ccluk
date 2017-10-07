<div class="row row-space-large">

	<div class="wphb-block wphb-block-uptime-downtime">

		<div class="wphb-block-header">
			<p class="wphb-block-description"><?php _e( 'Here\'s a log of when your website was inaccessible for visitors.', 'wphb' ); ?></p>
		</div>
		<div class="wphb-block-content">
			<ul class="dev-list dev-list-stats dev-list-stats-standalone dev-list-stats-border">
				<?php if ( ! count( $uptime_stats->events ) ): ?>
                    <div class="wphb-caching-success wphb-notice wphb-notice-blue">
					    <p><strong><?php _e( 'No events in the chosen date range', 'wphb' ); ?></strong></p>
                    </div>
				<?php else: ?>
					<?php foreach ( $uptime_stats->events as $event ): ?>
						<li class="dev-list-stats-item">
							<div>
								<span class="list-label list-label-stats">
								<?php if ( ! empty( $event->down ) && ! empty( $event->up ) ): ?>
									<?php $down = strtotime( get_date_from_gmt( date( 'Y-m-d H:i:s', $event->down ) ) ); ?>
									<?php $up = strtotime( get_date_from_gmt( date( 'Y-m-d H:i:s', $event->up ) ) ); ?>
									<div class="wphb-pills-group">
										<span class="wphb-pills red" tooltip="<?php echo esc_attr( $event->details ); ?>"><i class="dev-icon dev-icon-caret_down"></i> <?php echo date_i18n( 'M j, Y g:ia', $down ); ?></span>
										<span class="wphb-pills green"><i class="dev-icon dev-icon-caret_up"></i> <?php echo date_i18n( 'M j, Y g:ia', $up ); ?></span>
									</div>
								<?php else : ?>
									<?php if ( ! empty( $event->down ) ): ?>
										<?php $down = strtotime( get_date_from_gmt( date( 'Y-m-d H:i:s', $event->down ) ) ); ?>
										<span class="wphb-pills red" tooltip="<?php echo esc_attr( $event->details ); ?>"><i class="dev-icon dev-icon-caret_down"></i> <?php echo date_i18n( 'M j, Y g:ia', $down ); ?></span>
									<?php endif; ?>
									<?php if ( ! empty( $event->up ) ): ?>
										<?php $up = strtotime( get_date_from_gmt( date( 'Y-m-d H:i:s', $event->up ) ) ); ?>
										<span class="wphb-pills green"><i class="dev-icon dev-icon-caret_up"></i> <?php echo date_i18n( 'M j, Y g:ia', $up ); ?></span>
									<?php endif; ?>
								<?php endif; ?>
								</span>
								<?php if ( $event->downtime ): ?>
									<span class="list-detail list-detail-stats list-detail-stats-heading tooltip-right" tooltip="<?php echo esc_attr( $event->details ); ?>"><?php echo $event->downtime; ?></span>
								<?php elseif ( ! $event->up && $uptime_stats->downtime ): ?>
									<span class="list-detail list-detail-stats list-detail-stats-heading tooltip-right" tooltip="<?php echo esc_attr( $event->details ); ?>"><?php echo $uptime_stats->downtime; ?></span>
								<?php endif; ?>
							</div>
						</li><!-- end dev-list-stats-item -->
					<?php endforeach; ?>
				<?php endif; ?>
			</ul>
		</div>

	</div><!-- end wphb-block-uptime-downtime -->

</div>
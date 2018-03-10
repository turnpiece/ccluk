<div class="wphb-block-entry">

	<div class="wphb-block-entry-image wphb-block-entry-image-bottom">
		<img class="wphb-image"
			 src="<?php echo WPHB_DIR_URL . 'admin/assets/image/graphic-hb-minify-summary.png'; ?>"
			 srcset="<?php echo WPHB_DIR_URL . 'admin/assets/image/graphic-hb-minify-summary@2x.png'; ?> 2x"
			 alt="<?php _e( 'Hummingbird', 'wphb' ); ?>">
	</div>

	<div class="wphb-block-entry-third">
		<span class="not-present">
			<?php
			if ( $last_report && ! is_wp_error( $last_report ) && ! $report_dismissed ) {
				$error_class = ( 'aplus' === $last_report->score_class || 'a' === $last_report->score_class || 'b' === $last_report->score_class ) ? 'tick' : 'warning';
				echo $last_report->score . "<i class='hb-wpmudev-icon-{$error_class}'></i><span class='score-span'>/100</span>";
			} elseif ( $report_dismissed ) {
				echo $last_score . '<span class="tooltip" tooltip="' . esc_attr( __( 'You have ignored your current performance test score', 'wphb' ) ) . '"><i class="hb-wpmudev-icon-info"></i><span class="score-span">/100</span></span>';
			} else {
				echo '-';
			} ?>
		</span>
		<p class="current-performance-score"><?php _e( 'Current performance score', 'wphb' ); ?></p>
		<span>
			<?php
			if ( $last_report && ! is_wp_error( $last_report ) ) {
				$data_time = strtotime( get_date_from_gmt( date( 'Y-m-d H:i:s', $last_report->time ) ) );
				echo date_i18n( get_option( 'date_format' ), $data_time ); ?> <span class="list-detail-stats-heading-extra-info"><?php printf( _x( 'at %s', 'Time of the last performance report', 'wphb' ), date_i18n( get_option( 'time_format' ), $data_time ) );
			} else {
				_e( 'Never', 'wphb' );
			} ?>
		</span>
		<p><?php _e( 'Last test date', 'wphb' ); ?></p>
	</div>

	<div class="wphb-block-entry-third">
		<ul class="dev-list">
			<li>
				<span class="list-label"><?php _e( 'Previous score', 'wphb' ); ?></span>
				<span class="list-detail">
					<?php if ( intval( $last_score ) > 0 ) : ?>
						<div class="wphb-dash-numbers"><?php echo intval( $last_score ); ?></div>
					<?php else : ?>
						<p><?php _e( 'Available after next test', 'wphb' ); ?></p>
					<?php endif; ?>
				</span>
			</li>
			<li>
				<span class="list-label"><?php _e( 'Improvement', 'wphb' ); ?></span>
				<span class="list-detail">
					<?php if ( intval( $improvement ) === 0 && intval( $last_score ) === 0 ) : ?>
						<p><?php _e( 'Available after next test', 'wphb' ); ?></p>
					<?php elseif ( intval( $improvement ) > 0 ) : ?>
						<div class="wphb-pills green">+<?php echo intval( $improvement ); ?></div>
					<?php elseif ( intval( $improvement ) === 0 ) : ?>
						<div class="wphb-pills grey">0</div>
					<?php else : ?>
						<div class="wphb-pills"><?php echo intval( $improvement ); ?></div>
					<?php endif; ?>
				</span>
			</li>
			<li>
				<span class="list-label"><?php _e( 'Recommendations', 'wphb' ); ?></span>
				<span class="list-detail">
					<div class="wphb-dash-numbers"><?php echo intval( $recommendations ); ?></div>
				</span>
			</li>
		</ul>
	</div>

</div><!-- end wphb-block-entry -->
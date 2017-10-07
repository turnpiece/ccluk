<h3><?php echo esc_html( $title ); ?></h3>
<?php if ( $last_report && ! is_wp_error( $last_report ) ): ?>
	<div class="test-results wphb-performance-report-overall-score hide-on-mobile">
		<span class="test-results-label"><?php _e( 'Overall Score', 'wphb' ); ?></span>
		<div class="wphb-score wphb-score-have-label">
			<div class="tooltip-box">
				<div class="wphb-score-result wphb-score-result-grade-<?php echo $last_report->score_class ?>" tooltip="<?php echo $last_report->score; ?>/100">
					<div class="wphb-score-type wphb-score-type-circle">
						<svg class="wphb-score-graph wphb-score-graph-svg" xmlns="http://www.w3.org/2000/svg" width="30" height="30">
							<circle class="wphb-score-graph-circle" r="12.5" cx="15" cy="15" fill="transparent" stroke-dasharray="0" stroke-dashoffset="0"></circle>
							<circle class="wphb-score-graph-circle wphb-score-graph-result" r="12.5" cx="15" cy="15" fill="transparent" stroke-dasharray="80" stroke-dashoffset="0"></circle>
						</svg>
					</div><!-- end wphb-score-type -->
					<div class="wphb-score-result-label"><?php echo $last_report->score; ?></div>
				</div><!-- end wphb-score-result -->
			</div><!-- end tooltip-box -->
		</div><!-- end wphb-score -->
	</div><!-- end test-results -->
<?php endif; ?>
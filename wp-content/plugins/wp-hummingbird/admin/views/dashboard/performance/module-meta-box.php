<?php
/**
 * Performance meta box on dashboard page.
 *
 * @package Hummingbird
 *
 * @var bool   $notifications    Performance cron reports status.
 * @var object $report           Last report.
 * @var string $viewreport_link  Link to performance page.
 */

?>
<div class="content">
	<p><?php esc_html_e( 'Here are your latest performance test results. A score above 85 is considered a good benchmark.', 'wphb' ); ?></p>
</div>

<div class="wphb-dash-table three-columns">
	<div class="wphb-dash-table-header">
		<span><?php esc_html_e( 'Recommendation', 'wphb' ); ?></span>
		<span><?php esc_html_e( 'Score /100', 'wphb' ); ?></span>
		<span>&nbsp;</span>
	</div>

	<?php foreach ( $report->rule_result as $rule => $rule_result ) : ?>
		<div class="wphb-dash-table-row">
			<div>
				<?php echo esc_html( $rule_result->label ); ?>
			</div>

			<div class="wphb-performance-report-item-score tooltip-s wphb-score-result-grade-<?php echo esc_attr( $rule_result->impact_score_class ) ?>"
				 tooltip="<?php echo esc_html( $rule_result->impact_score ); ?>/100">
				<div class="wphb-score-type-circle">
					<svg class="wphb-score-graph wphb-score-graph-svg" xmlns="http://www.w3.org/2000/svg" width="30" height="30">
						<circle class="wphb-score-graph-circle" r="12.5" cx="15" cy="15" fill="transparent" stroke-dasharray="0" stroke-dashoffset="0"></circle>
						<circle class="wphb-score-graph-circle wphb-score-graph-result" r="12.5" cx="15" cy="15" fill="transparent" stroke-dasharray="80" stroke-dashoffset="0"></circle>
					</svg>
				</div>
				<div class="wphb-score-result-label"><?php echo esc_html( $rule_result->impact_score ); ?></div>
			</div>

			<div>
				<?php if ( $rule_result->impact_score < 100 ) : ?>
					<a href="<?php echo esc_url( $viewreport_link ); ?>#rule-<?php echo esc_attr( $rule ); ?>" class="button button-ghost" name="submit">
						<?php esc_html_e( 'Improve', 'wphb' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>

<div class="buttons">
	<a href="<?php echo esc_url( $viewreport_link ); ?>" class="button button-ghost alignleft">
		<?php esc_html_e( 'View Full Report', 'wphb' ); ?>
	</a>
	<span class="status-text alignright">
		<?php $notifications ? esc_html_e( 'Automated performance tests are enabled', 'wphb' ) : esc_html_e( 'Automated performance tests are disabled', 'wphb' ); ?>
	</span>
</div>
<div class="clear"></div>
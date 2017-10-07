<?php
/**
 * Performance meta box header on dashboard page.
 *
 * @package Hummingbird
 *
 * @var object $last_report  Performance report object.
 * @var string $title        Performance module title.
 */

?>
<h3><?php echo esc_html( $title ); ?></h3>
<?php if ( $last_report && ! is_wp_error( $last_report ) ) : ?>
	<div class="test-results wphb-performance-report-overall-score hide-on-mobile">
		<div class="wphb-performance-report-item-score tooltip-s wphb-score-result-grade-<?php echo esc_attr( $last_report->score_class ); ?>" tooltip="<?php echo esc_attr( $last_report->score ); ?>/100">
			<div class="wphb-score-type-circle">
				<svg class="wphb-score-graph wphb-score-graph-svg" xmlns="http://www.w3.org/2000/svg" width="30" height="30">
					<circle class="wphb-score-graph-circle" r="12.5" cx="15" cy="15" fill="transparent" stroke-dasharray="0" stroke-dashoffset="0"></circle>
					<circle class="wphb-score-graph-circle wphb-score-graph-result" r="12.5" cx="15" cy="15" fill="transparent" stroke-dasharray="80" stroke-dashoffset="0"></circle>
				</svg>
			</div><!-- end wphb-score-type-circle -->
			<div class="wphb-score-result-label"><?php echo esc_html( $last_report->score ); ?></div>
		</div><!-- end wphb-performance-report-item-score -->
	</div><!-- end test-results -->
<?php endif; ?>
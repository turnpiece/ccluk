<?php
/**
 * Performance meta box on dashboard page.
 *
 * @package Hummingbird
 *
 * @var object $report           Last report.
 * @var string $viewreport_link  Url to performance module.
 */

?>
<div class="sui-box-body">
	<p><?php esc_html_e( 'Here are your latest performance test results. A score above 85 is considered a good benchmark.', 'wphb' ); ?></p>
</div>

<table class="sui-table sui-accordion">
	<thead>
		<tr>
			<th><?php esc_html_e( 'Recommendation', 'wphb' ); ?></th>
			<th><?php esc_html_e( 'Score /100', 'wphb' ); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ( $report->rule_result as $rule => $rule_result ) :
			switch ( $rule_result->impact_score_class ) {
				case 'aplus':
				case 'a':
				case 'b':
					$impact_score_class = 'success';
					$impact_icon_class = 'check-tick';
					break;
				case 'c':
				case 'd':
					$impact_score_class = 'warning';
					$impact_icon_class = 'warning-alert';
					break;
				case 'e':
				case 'f':
					$impact_score_class = 'error';
					$impact_icon_class = 'warning-alert';
					break;
				default:
					$impact_score_class = 'warning';
					$impact_icon_class = 'warning-alert';

			}
			?>
			<tr class="sui-accordion-item sui-<?php echo esc_attr( $impact_score_class ); ?>">
				<td class="sui-accordion-item-title">
					<i class="sui-icon-<?php echo esc_attr( $impact_icon_class ); ?> sui-<?php echo esc_attr( $impact_score_class ); ?>"></i> <?php echo esc_html( $rule_result->label ); ?>
				</td>
				<td>
					<div class="sui-circle-score sui-grade-<?php echo esc_attr( $rule_result->impact_score_class ); ?> sui-tooltip"
					data-tooltip="<?php echo esc_html( $rule_result->impact_score ); ?>/100" data-score="<?php echo esc_attr( $rule_result->impact_score ); ?>"></div>
				</td>
				<td>
					<?php if ( $rule_result->impact_score < 100 ) : ?>
						<a href="<?php echo esc_url( $viewreport_link ); ?>#rule-<?php echo esc_attr( $rule ); ?>" class="sui-button sui-button-ghost" name="submit">
							<?php esc_html_e( 'Improve', 'wphb' ); ?>
						</a>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php
/**
 * Performance summary meta box.
 *
 * @package Hummingbird
 *
 * @var bool     $report_dismissed  If performance report is dismissed.
 * @var bool     $can_run_test      If there is no cool down period and user can run a new test.
 * @var string   $retry_url         URL to trigger a new performance scan.
 * @var stdClass $last_test         Last test details.
 * @var string   $type              Report type: desktop or mobile.
 */

?>

<div class="sui-box-body">
	<?php if ( $report_dismissed ) : ?>
		<div class="sui-notice">
			<p><?php esc_html_e( 'You have chosen to ignore this performance test. Run a new test to see new recommendations.', 'wphb' ); ?></p>
			<div class="sui-notice-buttons">
				<?php if ( true === $can_run_test ) : ?>
					<a href="<?php echo esc_url( $retry_url ); ?>"  class="sui-button sui-button-blue">
						<?php esc_html_e( 'Run Test', 'wphb' ); ?>
					</a>
				<?php else : ?>
					<?php
					$tooltip = sprintf(
						/* translators: %d: number of minutes. */
						_n(
							'Hummingbird is just catching her breath - you can run another test in %d minute',
							'Hummingbird is just catching her breath - you can run another test in %d minutes',
							$can_run_test,
							'wphb'
						),
						number_format_i18n( $can_run_test )
					);
					?>
					<span class="sui-tooltip sui-tooltip-constrained sui-tooltip-bottom-right" disabled="disabled" data-tooltip="<?php echo esc_attr( $tooltip ); ?>" aria-hidden="true">
						<a href="<?php echo esc_url( $retry_url ); ?>" disabled class="sui-button sui-button-blue">
							<?php esc_html_e( 'Run Test', 'wphb' ); ?>
						</a>
					</span>
				<?php endif; ?>
			</div>
		</div>
		<?php
		$impact_score_class = 'dismissed';
		$impact_icon_class  = 'warning-alert';
		?>
	<?php else : ?>
		<p><?php esc_html_e( 'Your performance score is calculated based on how your site performs on each of the following metrics. You can expand the metrics for recommendations on improving them.', 'wphb' ); ?></p>
	<?php endif; ?>
</div>

<div class="sui-accordion sui-accordion-flushed">
	<?php foreach ( $last_test->metrics as $rule => $rule_result ) : ?>
		<?php
		$score = isset( $rule_result->score ) ? $rule_result->score : 0;

		if ( ! $report_dismissed ) {
			$impact_score_class = WP_Hummingbird_Module_Performance::get_impact_class( absint( $score * 100 ) );
			$impact_icon_class  = WP_Hummingbird_Module_Performance::get_impact_class( absint(  $score * 100 ), 'icon' );
		}
		?>
		<div class="sui-accordion-item sui-<?php echo esc_attr( $impact_score_class ); ?>" id="<?php echo esc_attr( $rule ); ?>">
			<div class="sui-accordion-item-header">
				<div class="sui-accordion-item-title">
					<i aria-hidden="true" class="sui-icon-<?php echo esc_attr( $impact_icon_class ); ?> sui-<?php echo esc_attr( $impact_score_class ); ?>"></i> <?php echo esc_html( $rule_result->title ); ?>
				</div>
				<div>
					<?php $gray_class = isset( $score ) && 0 === $score ? 'wphb-gray-color' : ''; ?>
					<div class="sui-circle-score sui-grade-<?php echo esc_attr( $impact_score_class ) . ' ' . esc_attr( $gray_class ); ?>" data-score="<?php echo absint( $score * 100 ); ?>"></div>
				</div>
				<div>
					<?php if ( 'disabled' !== $impact_score_class && $this->view_exists( "performance/metrics/{$rule}" ) ) : ?>
						<?php if ( ! empty( $rule_result->description ) || ! empty( $rule_result->tip ) ) : ?>
							<?php echo isset( $rule_result->displayValue ) ? esc_html( $rule_result->displayValue ) : esc_html__( 'N/A', 'wphb' ); ?>
							<button class="sui-button-icon sui-accordion-open-indicator" aria-label="<?php esc_attr_e( 'Open item', 'wphb' ); ?>">
								<i class="sui-icon-chevron-down" aria-hidden="true"></i>
							</button>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( $this->view_exists( "performance/metrics/{$rule}" ) ) : ?>
				<div class="sui-accordion-item-body">
					<div class="sui-box">
						<div class="sui-box-body">
							<?php
							$this->view(
								"performance/metrics/{$rule}",
								array(
									'audit' => $rule_result,
									'url'   => WP_Hummingbird_Utils::get_admin_menu_url( 'performance' ) . '&view=audits&type=' . $type,
								)
							);
							?>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>

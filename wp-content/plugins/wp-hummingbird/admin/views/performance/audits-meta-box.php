<?php
/**
 * Performance summary meta box.
 *
 * @package Hummingbird
 *
 * @var stdClass $last_test         Last test details.
 * @var bool     $report_dismissed  If performance report is dismissed.
 * @var string   $type              Report type.
 */

?>

<div class="sui-box-body">
	<?php
	if ( $report_dismissed ) {
		$impact_score_class = 'dismissed';
		$impact_icon_class  = 'warning-alert';
	}

	if ( 'opportunities' === $type ) {
		$description = __(
			'Each suggestion in this section is an opportunity to improve your page load speed and estimates
			how much faster the page will load if the improvement is implemented.',
			'wphb'
		);
	} elseif ( 'diagnostics' === $type ) {
		$description = __(
			'This section provides additional information about how your page adheres to best practices of web
			development. These improvements may not directly impact the load speed, however, help to improve the
			overall performance score of your page.',
			'wphb'
		);
	} else {
		$description = __(
			'This section lists the audits with a score of 90 or more. There are still opportunities to improve
			the overall performance score by aiming for a score of 100 for all the passed audits.',
			'wphb'
		);
	}
	?>
	<p><?php echo esc_html( $description ); ?></p>

	<?php if ( empty( $last_test ) ) : ?>
		<div class="sui-notice sui-notice-success">
			<p><?php esc_html_e( 'Nice! All tests passed.', 'wphb' ); ?></p>
		</div>
	<?php endif; ?>
</div>

<?php if ( ! empty( $last_test ) ) : ?>
	<div class="sui-accordion sui-accordion-flushed">
		<?php foreach ( $last_test as $rule => $rule_result ) : ?>
			<?php
			if ( ! $report_dismissed ) {
				$impact_score_class = 'error';
				$impact_icon_class  = 'warning-alert';

				if ( 'passed' === $type || ( isset( $rule_result->score ) && 90 <= $rule_result->score * 100 ) ) {
					$impact_score_class = 'success';
					$impact_icon_class  = 'check-tick';
				} elseif ( isset( $rule_result->score ) && 50 <= $rule_result->score * 100 ) {
					$impact_score_class = 'warning';
					$impact_icon_class  = 'warning-alert';
				}
			}
			?>
			<div class="sui-accordion-item sui-<?php echo esc_attr( $impact_score_class ); ?>" id="<?php echo esc_attr( $rule ); ?>">
				<div class="sui-accordion-item-header">
					<div class="sui-accordion-item-title">
						<i aria-hidden="true" class="sui-icon-<?php echo esc_attr( $impact_icon_class ); ?> sui-<?php echo esc_attr( $impact_score_class ); ?>"></i>
						<?php echo esc_html( $rule_result->title ); ?>
					</div>
					<div>
						<?php $gray_class = isset( $rule_result->score ) && 0 === $rule_result->score ? 'wphb-gray-color' : ''; ?>
						<div class="sui-circle-score sui-grade-<?php echo esc_attr( $impact_score_class ) . ' ' . esc_attr( $gray_class ); ?>" data-score="<?php echo isset( $rule_result->score ) ? absint( $rule_result->score * 100 ) : 0; ?>"></div>
					</div>
					<div>
						<?php if ( 'disabled' !== $impact_score_class && $this->view_exists( "performance/audits/{$rule}" ) ) : ?>
							<?php
							if ( 'time-to-first-byte' === $rule && 1 !== $rule_result->score && isset( $rule_result->details->overallSavingsMs ) ) {
								printf(
									/* translators: %s - number of ms */
									esc_html__( 'Potential savings of %s ms', 'wphb' ),
									esc_html( $rule_result->details->overallSavingsMs )
								);
							} elseif ( ! empty( $rule_result->description ) || ! empty( $rule_result->tip ) ) {
								echo isset( $rule_result->displayValue ) ? esc_html( $rule_result->displayValue ) : '';
							}
							?>
							<button class="sui-button-icon sui-accordion-open-indicator" aria-label="<?php esc_attr_e( 'Open item', 'wphb' ); ?>">
								<i class="sui-icon-chevron-down" aria-hidden="true"></i>
							</button>
						<?php endif; ?>
					</div>
				</div>

				<?php if ( $this->view_exists( "performance/audits/{$rule}" ) ) : ?>
					<div class="sui-accordion-item-body">
						<div class="sui-box">
							<div class="sui-box-body">
								<?php
								$this->view(
									"performance/audits/{$rule}",
									array(
										'audit' => $rule_result,
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
<?php endif; ?>

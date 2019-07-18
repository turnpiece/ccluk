<?php
/**
 * Performance meta box on dashboard page.
 *
 * @package Hummingbird
 *
 * @var object $report           Last report.
 * @var string $type             Report type: desktop or mobile.
 * @var string $viewreport_link  Url to performance module.
 * @var array  $widgets          Widgets settings.
 */

?>

<?php if ( ! $widgets['show_metrics'] && ! $widgets['show_audits'] && ! $widgets['show_historic'] ) : ?>
	<div class="sui-box-body">
		<div class="sui-notice">
			<p>
				<?php esc_html_e( 'You have not enabled any widgets. Please use the Customize widget link below to do so.', 'wphb' ); ?>
			</p>
		</div>
	</div>
<?php endif; ?>


<?php if ( $widgets['show_metrics'] ) : ?>
	<div class="sui-box-body wphb-metrics-widget">
		<strong><?php esc_html_e( 'Score Metrics', 'wphb' ); ?></strong>
		<span class="status-text">
			<a href="<?php echo esc_url( $viewreport_link . '&type=' . $type ); ?>">
				<?php esc_html_e( 'More details', 'wphb' ); ?>
			</a>
		</span>
		<span class="sui-description">
			<?php esc_html_e( 'Your performance score is calculated based on how your site performs on each of the following metrics.', 'wphb' ); ?>
		</span>

		<table class="sui-table">
			<?php $perf_link = WP_Hummingbird_Utils::get_admin_menu_url( 'performance' ) . '&view=metrics&type=' . $type; ?>
			<?php foreach ( $report->metrics as $rule => $rule_result ) : ?>
				<?php $score = isset( $rule_result->score ) ? $rule_result->score : 0; ?>
				<tr class="wphb-performance-report-item" data-performance-url="<?php echo esc_attr( $perf_link . '#' . $rule ); ?>">
					<td>
						<strong><?php echo esc_html( $rule_result->title ); ?></strong>
					</td>
					<td>
						<div class="sui-circle-score sui-grade-<?php echo esc_attr( WP_Hummingbird_Module_Performance::get_impact_class( absint( $score * 100 ) ) ); ?> sui-tooltip" data-tooltip="<?php echo absint( $score * 100 ); ?>/100" data-score="<?php echo absint( $score * 100 ); ?>"></div>
					</td>
					<td>
						<span><?php echo isset( $rule_result->displayValue ) ? esc_html( $rule_result->displayValue ) : esc_html__( 'N/A', 'wphb' ); ?></span>
						<i aria-hidden="true" class="sui-icon-<?php echo esc_attr( WP_Hummingbird_Module_Performance::get_impact_class( absint( $score * 100 ), 'icon' ) ); ?> sui-<?php echo esc_attr( WP_Hummingbird_Module_Performance::get_impact_class( absint( $score * 100 ) ) ); ?> sui-md"></i>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
	</div>
<?php endif; ?>

<?php if ( $widgets['show_audits'] ) : ?>
	<div class="sui-box-body wphb-audits-widget">
		<strong><?php esc_html_e( 'Audits', 'wphb' ); ?></strong>
		<span class="status-text">
			<a href="<?php echo esc_url( $viewreport_link . '&view=audits&type=' . $type ); ?>">
				<?php esc_html_e( 'More details', 'wphb' ); ?>
			</a>
		</span>
		<span class="sui-description">
			<?php esc_html_e( 'Audit results are divided into following three categories. Opportunities and Diagnostics provide recommendations to improve the performance score.', 'wphb' ); ?>
		</span>

		<ul class="sui-list">
			<?php $perf_link = WP_Hummingbird_Utils::get_admin_menu_url( 'performance' ) . '&view=audits&type=' . $type; ?>
			<li class="wphb-performance-report-item" data-performance-url="<?php echo esc_attr( $perf_link . '#wphb-box-performance-audits-opportunities' ); ?>">
				<span class="sui-list-label">
					<strong><?php esc_html_e( 'Opportunities', 'wphb' ); ?></strong>
				</span>
				<span class="sui-list-detail">
					<span class="sui-tag sui-tag-<?php echo esc_attr( WP_Hummingbird_Module_Performance::get_audits_class( $report->audits->opportunities ) ); ?>">
						<?php echo isset( $report->audits->opportunities ) ? count( get_object_vars( $report->audits->opportunities ) ) : '-'; ?>
					</span>
				</span>
			</li>
			<li class="wphb-performance-report-item" data-performance-url="<?php echo esc_attr( $perf_link . '#wphb-box-performance-audits-diagnostics' ); ?>">
				<span class="sui-list-label">
					<strong><?php esc_html_e( 'Diagnostics', 'wphb' ); ?></strong>
				</span>
				<span class="sui-list-detail">
					<span class="sui-tag sui-tag-<?php echo esc_attr( WP_Hummingbird_Module_Performance::get_audits_class( $report->audits->diagnostics ) ); ?>">
						<?php echo isset( $report->audits->diagnostics ) ? count( get_object_vars( $report->audits->diagnostics ) ) : '-'; ?>
					</span>
				</span>
			</li>
			<li class="wphb-performance-report-item" data-performance-url="<?php echo esc_attr( $perf_link . '#wphb-box-performance-audits-passed' ); ?>">
				<span class="sui-list-label">
					<strong><?php esc_html_e( 'Passed Audits', 'wphb' ); ?></strong>
				</span>
				<span class="sui-list-detail">
					<span class="sui-tag sui-tag-success">
						<?php echo count( get_object_vars( $report->audits->passed ) ); ?>
					</span>
				</span>
			</li>
		</ul>
	</div>
<?php endif; ?>

<?php if ( $widgets['show_historic'] ) : ?>
	<div class="sui-box-body wphb-historic-widget">
		<strong><?php esc_html_e( 'Historic Field Data', 'wphb' ); ?></strong>
		<span class="status-text">
			<a href="<?php echo esc_url( $viewreport_link . '&view=historic&type=' . $type ); ?>">
				<?php esc_html_e( 'More details', 'wphb' ); ?>
			</a>
		</span>
		<span class="sui-description">
			<?php
			printf(
				/* translators: %1$s - starting a tag, %2$s - ending a tag */
				esc_html__( 'We use %1$sChrome User Experience Report%2$s to generate insights about the real users’ experience with your webpage over the last 30 days.', 'wphb' ),
				'<a href="https://developers.google.com/web/tools/chrome-user-experience-report/" target="_blank">',
				'</a>'
			);
			?>
		</span>

		<?php if ( ! $report->field_data ) : ?>
			<div class="sui-notice">
				<p>
					<?php esc_html_e( 'The Chrome User Experience Report does not have sufficient real-world speed data for this page.', 'wphb' ); ?>
				</p>
			</div>

			<span class="sui-description sui-margin-bottom">
				<?php esc_html_e( 'Note: This report can take months to populate and is aimed at well established websites.', 'wphb' ); ?>
			</span>
		<?php else : ?>
			<table class="sui-table">
				<tr>
					<td>
						<strong><?php esc_html_e( 'First Contentful Paint (FCP)', 'wphb' ); ?></strong>
					</td>
					<td>
						<?php echo esc_html( ucfirst( strtolower( $report->field_data->FIRST_CONTENTFUL_PAINT_MS->category ) ) ); ?>
					</td>
					<td>
						<?php
						/* translators: %s - number of seconds */
						printf( '%s s', esc_html( round( $report->field_data->FIRST_CONTENTFUL_PAINT_MS->percentile / 1000, 1 ) ) );

						switch ( $report->field_data->FIRST_CONTENTFUL_PAINT_MS->category ) {
							case 'FAST':
								echo '<i class="sui-icon-check-tick sui-success sui-md" aria-hidden="true"></i>';
								break;
							case 'AVERAGE':
								echo '<i class="sui-icon-warning-alert sui-warning sui-md" aria-hidden="true"></i>';
								break;
							case 'SLOW':
							default:
								echo '<i class="sui-icon-warning-alert sui-error sui-md" aria-hidden="true"></i>';
								break;
						}
						?>
					</td>
				</tr>
				<tr>
					<td>
						<strong><?php esc_html_e( 'First Input Delay (FID)', 'wphb' ); ?></strong>
					</td>
					<td>
						<?php echo esc_html( ucfirst( strtolower( $report->field_data->FIRST_INPUT_DELAY_MS->category ) ) ); ?>
					</td>
					<td>
						<?php
						/* translators: %s - number of milliseconds */
						printf( '%s ms', esc_html( $report->field_data->FIRST_INPUT_DELAY_MS->percentile ) );

						switch ( $report->field_data->FIRST_INPUT_DELAY_MS->category ) {
							case 'FAST':
								echo '<i class="sui-icon-check-tick sui-success sui-md" aria-hidden="true"></i>';
								break;
							case 'AVERAGE':
								echo '<i class="sui-icon-warning-alert sui-warning sui-md" aria-hidden="true"></i>';
								break;
							case 'SLOW':
							default:
								echo '<i class="sui-icon-warning-alert sui-error sui-md" aria-hidden="true"></i>';
								break;
						}
						?>
					</td>
				</tr>
			</table>
		<?php endif; ?>
	</div>
<?php endif; ?>

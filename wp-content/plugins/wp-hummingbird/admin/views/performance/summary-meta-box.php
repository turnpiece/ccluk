<?php
/**
 * Performance test summary meta box.
 *
 * @package Hummingbird
 *
 * @var string            $type              Report type: desktop/mobile.
 * @var stdClass|WP_Error $last_report       Last performance report.
 * @var bool              $report_dismissed  Is report dismissed.
 * @var bool              $is_doing_report   Is running a scan.
 * @var int               $opportunities     Number of failed opportunities.
 * @var int               $diagnostics       Number of failed diagnostics.
 * @var int               $passed_audits     Number of passed audits (passed opportunities + passed diagnostics).
 */

?>

<div class="sui-summary-image-space"></div>
<div class="sui-summary-segment">
	<div class="sui-summary-details">
		<?php if ( $last_report && ! is_wp_error( $last_report ) && ! $report_dismissed ) : ?>
			<span class="sui-summary-large"><?php echo esc_html( $last_report->{$type}->score ); ?></span>
			<i class="sui-icon-<?php echo esc_attr( WP_Hummingbird_Module_Performance::get_impact_class( $last_report->{$type}->score, 'icon' ) ); ?> sui-md sui-<?php echo esc_attr( WP_Hummingbird_Module_Performance::get_impact_class( $last_report->{$type}->score ) ); ?>"></i>
			<span class='sui-summary-percent'>/100</span>
		<?php elseif ( $report_dismissed ) : ?>
			<?php if ( isset( $last_report->{$type}->score ) ) : ?>
				<span class="sui-summary-large"><?php echo esc_html( $last_report->{$type}->score ); ?></span>
				<i class="sui-icon-info sui-md"></i>
				<span class='sui-summary-percent'>/100</span>
			<?php else : ?>
				<span class="sui-summary-large">-</span>
			<?php endif; ?>
		<?php else : ?>
			-
		<?php endif; ?>
		<span class="sui-summary-sub"><?php esc_html_e( 'Performance score', 'wphb' ); ?></span>

		<span class="sui-summary-detail">
			<?php
			if ( $last_report && ! is_wp_error( $last_report ) ) {
				$data_time    = strtotime( get_date_from_gmt( date( 'Y-m-d H:i:s', $last_report->time ) ) );
				$time_string  = esc_html( date_i18n( get_option( 'date_format' ), $data_time ) );
				$time_string .= sprintf(
					/* translators: %s - time in proper format */
					esc_html_x( ' at %s', 'Time of the last performance report', 'wphb' ),
					esc_html( date_i18n( get_option( 'time_format' ), $data_time ) )
				);
				echo esc_html( $time_string );
			} elseif ( $is_doing_report ) {
				$time_string = esc_html__( 'Running scan...', 'wphb' );
			} else {
				$time_string = esc_html__( 'Never', 'wphb' );
			}
			?>
		</span>
		<span class="sui-summary-sub">
			<?php esc_html_e( 'Last test date', 'wphb' ); ?>
		</span>
	</div>
</div>
<div class="sui-summary-segment">
	<ul class="sui-list">
		<li>
			<span class="sui-list-label"><?php esc_html_e( 'Opportunities', 'wphb' ); ?></span>
			<span class="sui-list-detail">
				<?php if ( is_wp_error( $last_report ) ) : ?>
					-
				<?php else : ?>
					<span class="sui-tag sui-tag-<?php echo esc_attr( WP_Hummingbird_Module_Performance::get_audits_class( $last_report->{$type}->audits->opportunities ) ); ?>">
						<?php echo esc_html( $opportunities ); ?>
					</span>
				<?php endif; ?>
			</span>
		</li>
		<li>
			<span class="sui-list-label"><?php esc_html_e( 'Diagnostics', 'wphb' ); ?></span>
			<span class="sui-list-detail">
				<?php if ( is_wp_error( $last_report ) ) : ?>
					-
				<?php else : ?>
					<span class="sui-tag sui-tag-<?php echo esc_attr( WP_Hummingbird_Module_Performance::get_audits_class( $last_report->{$type}->audits->opportunities ) ); ?>">
						<?php echo esc_html( $diagnostics ); ?>
					</span>
				<?php endif; ?>
			</span>
		</li>
		<li>
			<span class="sui-list-label"><?php esc_html_e( 'Passed audits', 'wphb' ); ?></span>
			<span class="sui-list-detail">
				<?php if ( is_wp_error( $last_report ) ) : ?>
					-
				<?php else : ?>
					<span class="sui-tag sui-tag-success"><?php echo esc_html( $passed_audits ); ?></span>
				<?php endif; ?>
			</span>
		</li>
	</ul>
</div>

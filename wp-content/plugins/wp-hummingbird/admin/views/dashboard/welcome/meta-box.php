<?php
/**
 * Welcome meta box on dashboard page.
 *
 * @package Hummingbird
 *
 * @var int    $caching_issues     Number of issues.
 * @var bool   $cf_active          CloudFlare status.
 * @var int    $cf_current         CloudFlare expiry settings.
 * @var int    $gzip_issues        Number of gzip issues.
 * @var bool   $is_doing_report    If is doing performance report.
 * @var object $last_report        Last report object.
 * @var bool   $uptime_active      Uptime status.
 * @var object $uptime_report      Uptime report object.
 * @var bool   $report_dismissed   Last report dismissed warning.
 */

?>
<div class="sui-summary-image-space">
</div>
<div class="sui-summary-segment">
	<div class="sui-summary-details">
		<?php if ( $last_report && ! is_wp_error( $last_report ) && ! $report_dismissed ) :
			if ( 85 <= $last_report->data->score ) {
				$error_class = 'success';
				$icon_class  = 'check-tick';
			} elseif ( 65 <= $last_report->data->score ) {
				$error_class = 'warning';
				$icon_class  = 'warning-alert';
			} else {
				$error_class = 'error';
				$icon_class  = 'warning-alert';
			} ?>
			<span class="sui-summary-large"><?php echo esc_html( $last_report->data->score ); ?></span>
			<i class="sui-icon-<?php echo esc_attr( $icon_class ); ?> sui-lg sui-<?php echo esc_attr( $error_class ); ?>"></i>
			<span class='sui-summary-percent'>/100</span>
		<?php elseif ( $is_doing_report ) : ?>
			<div class="sui-progress-text sui-icon-loader sui-loading">
		<?php elseif ( $report_dismissed ) :
			?>
			<span class="sui-summary-large"><?php echo esc_html( $last_report->data->score ); ?></span>
			<i class="sui-icon-info sui-lg"></i>
			<span class='sui-summary-percent'>/100</span>
		<?php else : ?>
			&mdash;
		<?php endif; ?>
		<span class="sui-summary-sub"><?php esc_html_e( 'Current performance score', 'wphb' ); ?></span>
		<?php
		if ( $last_report && ! is_wp_error( $last_report ) ) {
			$data_time = strtotime( get_date_from_gmt( date( 'Y-m-d H:i:s', $last_report->data->time ) ) );
			echo date_i18n( get_option( 'date_format' ), $data_time ); ?>
			<span class="sui-summary-detail">
					<?php printf( _x( 'at %s', 'Time of the last performance report', 'wphb' ), date_i18n( get_option( 'time_format' ), $data_time ) ); ?>
				</span>
			<?php
		} elseif ( $is_doing_report ) {
			esc_html_e( 'Running scan...', 'wphb' );
		} else {
			esc_html_e( 'Never', 'wphb' );
		} ?>
		<span class="sui-summary-sub"><?php esc_html_e( 'Last test', 'wphb' ); ?></span>
	</div>
</div>
<div class="sui-summary-segment">
	<ul class="sui-list">
		<li>
			<span class="sui-list-label"><?php esc_html_e( 'Browser Caching', 'wphb' ); ?></span>
			<span class="sui-list-detail">
					<?php if ( $cf_active ) : ?>
						<?php if ( 691200 <= $cf_current ) : ?>
							<i class="sui-icon-check-tick sui-lg sui-success" aria-hidden="true"></i>
						<?php else : ?>
							<span class="sui-tag">5</span>
						<?php endif; ?>
					<?php else : ?>
						<?php if ( $caching_issues ) : ?>
							<span class="sui-tag"><?php echo intval( $caching_issues ); ?></span>
						<?php else : ?>
							<i class="sui-icon-check-tick sui-lg sui-success" aria-hidden="true"></i>
						<?php endif; ?>
					<?php endif; ?>
				</span>
		</li>
		<li>
			<span class="sui-list-label"><?php esc_html_e( 'GZIP Compression', 'wphb' ); ?></span>
			<span class="sui-list-detail">
					<?php if ( $gzip_issues ) : ?>
						<span class="sui-tag"><?php echo intval( $gzip_issues ); ?></span>
					<?php else : ?>
						<i class="sui-icon-check-tick sui-lg sui-success" aria-hidden="true"></i>
					<?php endif; ?>
				</span>
		</li>
		<li>
			<span class="sui-list-label"><?php esc_html_e( 'Last Down', 'wphb' ); ?></span>
			<span class="sui-list-detail">
					<?php if ( ! WP_Hummingbird_Utils::is_member() ) : ?>
						<a class="sui-button sui-button-ghost sui-button-green"  href="<?php echo WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_dash_summary_pro_tag' ); ?>" target="_blank">
							<?php esc_html_e( 'Pro Feature', 'wphb' ); ?>
						</a>
					<?php elseif ( is_wp_error( $uptime_report ) || ( ! $uptime_active ) ) : ?>
						<span class="sui-tag sui-tag-disabled">
							<?php esc_html_e( 'Uptime Inactive', 'wphb' ); ?>
						</span>
					<?php elseif ( empty( $site_date ) ) :
						esc_html_e( 'Website is reported down', 'wphb' ); ?>
					<?php else :
						echo esc_html( $site_date );
					endif; ?>
				</span>
		</li>
	</ul>
</div>
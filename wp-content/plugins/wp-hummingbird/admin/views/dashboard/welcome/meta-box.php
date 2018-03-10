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
<div class="wphb-block-entry">

	<div class="wphb-block-entry-image wphb-block-entry-image-bottom">
		<img class="wphb-image"
			 src="<?php echo WPHB_DIR_URL . 'admin/assets/image/hb-graphic-dash-top.png'; ?>"
			 srcset="<?php echo WPHB_DIR_URL . 'admin/assets/image/hb-graphic-dash-top@2x.png'; ?> 2x"
			 alt="<?php esc_attr_e( 'Hummingbird', 'wphb' ); ?>">
	</div>

	<div class="wphb-block-entry-third">
		<span class="not-present">
			<?php
			if ( $last_report && ! is_wp_error( $last_report ) && ! $report_dismissed ) :
				$error_class = ( 'aplus' === $last_report->data->score_class || 'a' === $last_report->data->score_class || 'b' === $last_report->data->score_class ) ? 'tick' : 'warning';
				echo $last_report->data->score . "<i class='hb-wpmudev-icon-{$error_class}'></i><span class='score-span'>/100</span>";
			elseif ( $is_doing_report ) :
				?>
				<span class="wphb-scan-progress-text"></span>
			<?php
			elseif ( $report_dismissed ) :
				echo $last_report->data->last_score['score'] . '<span class="tooltip" tooltip="' . esc_attr( __( 'You have ignored your current performance test score', 'wphb' ) ) . '"><i class="hb-wpmudev-icon-info"></i></span><span class="score-span">/100</span>';
			else :
				?>
				&mdash;
			<?php endif; ?>
		</span>
		<p class="current-performance-score"><?php esc_html_e( 'Current performance score', 'wphb' ); ?></p>
		<span>
			<?php
			if ( $last_report && ! is_wp_error( $last_report ) ) {
				$data_time = strtotime( get_date_from_gmt( date( 'Y-m-d H:i:s', $last_report->data->time ) ) );
				echo date_i18n( get_option( 'date_format' ), $data_time ); ?>
				<span class="list-detail-stats-heading-extra-info">
					<?php printf( _x( 'at %s', 'Time of the last performance report', 'wphb' ), date_i18n( get_option( 'time_format' ), $data_time ) ); ?>
				</span>
			<?php
			} elseif ( $is_doing_report ) {
				esc_html_e( 'Running scan...', 'wphb' );
			} else {
				esc_html_e( 'Never', 'wphb' );
			} ?>
		</span>
		<p><?php esc_html_e( 'Last test', 'wphb' ); ?></p>
	</div>

	<div class="wphb-block-entry-third">
		<ul class="dev-list">
			<li>
				<span class="list-label"><?php esc_html_e( 'Browser Caching', 'wphb' ); ?></span>
				<span class="list-detail">
					<?php if ( $cf_active ) : ?>
						<?php if ( 691200 <= $cf_current ) : ?>
							<span class="status-ok">&nbsp;</span>
						<?php else : ?>
							<div class="wphb-pills">5</div>
						<?php endif; ?>
					<?php else : ?>
						<?php if ( $caching_issues ) : ?>
							<div class="wphb-pills"><?php echo intval( $caching_issues ); ?></div>
						<?php else : ?>
							<span class="status-ok">&nbsp;</span>
						<?php endif; ?>
					<?php endif; ?>
				</span>
			</li>
			<li>
				<span class="list-label"><?php esc_html_e( 'GZIP Compression', 'wphb' ); ?></span>
				<span class="list-detail">
					<?php if ( $gzip_issues ) : ?>
						<div class="wphb-pills"><?php echo intval( $gzip_issues ); ?></div>
					<?php else : ?>
						<span class="status-ok">&nbsp;</span>
					<?php endif; ?>
				</span>
			</li>
			<li>
				<span class="list-label"><?php esc_html_e( 'Last Down', 'wphb' ); ?></span>
				<span class="list-detail">
					<?php if ( ! WP_Hummingbird_Utils::is_member() ) : ?>
						<a class="button button-content-cta button-ghost" href="<?php echo WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_dash_summary_pro_tag' ); ?>" target="_blank">
							<?php esc_html_e( 'Pro Feature', 'wphb' ); ?>
						</a>
					<?php elseif ( is_wp_error( $uptime_report ) || ( ! $uptime_active ) ) : ?>
						<a target="_blank" class="button button-disabled" id="dash-uptime-inactive">
							<?php esc_html_e( 'Uptime Inactive', 'wphb' ); ?>
						</a>
					<?php elseif ( empty( $site_date ) ) :
						esc_html_e( 'Website is reported down', 'wphb' ); ?>
					<?php else :
						echo esc_html( $site_date );
					endif; ?>
				</span>
			</li>
		</ul>
	</div>

</div><!-- end wphb-block-entry -->
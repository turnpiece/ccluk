<div class="sui-summary-image-space">
</div>
<div class="sui-summary-segment">
	<div class="sui-summary-details">
		<?php
		if ( $last_report && ! is_wp_error( $last_report ) && ! $report_dismissed ) :
			if ( 85 <= $last_report->score ) :
				$error_class = 'success';
				$icon_class = 'check-tick';
			elseif ( 65 <= $last_report->score ) :
				$error_class = 'warning';
				$icon_class = 'warning-alert';
			else :
				$error_class = 'error';
				$icon_class = 'warning-alert';
			endif;
			?>
			<span class="sui-summary-large"><?php echo esc_html( $last_report->score ); ?></span>
			<i class="sui-icon-<?php echo esc_attr( $icon_class ); ?> sui-lg sui-<?php echo esc_attr( $error_class ); ?>"></i>
			<span class='sui-summary-percent'>/100</span>
		<?php elseif ( $report_dismissed ) : ?>
			<?php if ( isset( $last_report->score ) ) : ?>
				<span class="sui-summary-large"><?php echo esc_html( $last_report->score ); ?></span>
				<i class="sui-icon-info sui-lg"></i>
				<span class='sui-summary-percent'>/100</span>
			<?php else : ?>
				<span class="sui-summary-large">-</span>
			<?php endif; ?>
			<?php
		else :
			echo '-';
		endif;
		?>
		<span class="sui-summary-sub"><?php esc_html_e( 'Current performance score', 'wphb' ); ?></span>
		<?php
		if ( $last_report && ! is_wp_error( $last_report ) ) {
			$data_time = strtotime( get_date_from_gmt( date( 'Y-m-d H:i:s', $last_report->time ) ) );
			echo date_i18n( get_option( 'date_format' ), $data_time );
			?>
			<span class="sui-summary-detail">
				<?php printf( _x( 'at %s', 'Time of the last performance report', 'wphb' ), date_i18n( get_option( 'time_format' ), $data_time ) ); ?>
			</span>
			<?php
		} elseif ( $is_doing_report ) {
			esc_html_e( 'Running scan...', 'wphb' );
		} else {
			esc_html_e( 'Never', 'wphb' );
		}
		?>
		<span class="sui-summary-sub"><?php esc_html_e( 'Last test', 'wphb' ); ?></span>
	</div>
</div>
<div class="sui-summary-segment">
	<ul class="sui-list">
		<li>
			<span class="sui-list-label"><?php esc_html_e( 'Previous score', 'wphb' ); ?></span>
			<span class="sui-list-detail">
				<?php if ( intval( $last_score ) > 0 ) : ?>
					<?php echo intval( $last_score ); ?>
				<?php else : ?>
					<?php esc_html_e( 'Available after next test', 'wphb' ); ?>
				<?php endif; ?>
			</span>
		</li>
		<li>
			<span class="sui-list-label"><?php esc_html_e( 'Improvement', 'wphb' ); ?></span>
			<span class="sui-list-detail">
				<?php if ( intval( $improvement ) === 0 && intval( $last_score ) === 0 ) : ?>
					<?php esc_html_e( 'Available after next test', 'wphb' ); ?>
				<?php elseif ( intval( $improvement ) > 0 ) : ?>
					<span class="sui-tag sui-tag-success">+<?php echo intval( $improvement ); ?></span>
				<?php elseif ( intval( $improvement ) === 0 ) : ?>
					<div class="sui-tag sui-tag-disabled">0</div>
				<?php else : ?>
					<div class="sui-tag"><?php echo intval( $improvement ); ?></div>
				<?php endif; ?>
			</span>
		</li>
		<li>
			<span class="sui-list-label"><?php esc_html_e( 'Recommendations', 'wphb' ); ?></span>
			<span class="sui-list-detail">
				<div class="wphb-dash-numbers"><?php echo intval( $recommendations ); ?></div>
			</span>
		</li>
	</ul>
</div>

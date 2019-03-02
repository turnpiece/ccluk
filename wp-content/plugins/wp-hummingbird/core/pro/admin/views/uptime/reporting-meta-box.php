<?php
/**
 * Uptime reporting meta box.
 *
 * @since 1.9.3
 * @package Hummingbird
 *
 * @var string $notice_class     Class for the notice.
 * @var string $notice_message   Message for the notice.
 * @var string $send_time        Send time for the report.
 * @var array  $reports_settings Settings for Uptime Reports.
 */

?>
<p><?php esc_html_e( 'Enable scheduled email reports direct to recipient inboxes of your choice. The report will include response time data and any downtime logs in the selected period.', 'wphb' ); ?></p>
<div class="sui-notice sui-notice-<?php echo esc_attr( $notice_class ); ?>">
	<p><?php echo esc_html( $notice_message ); ?></p>
</div>

<form method="post" id="wphb-uptime-reporting" class="wphb-report-settings">
	<div class="sui-box-settings-row">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label"><?php esc_html_e( 'Configure', 'wphb' ); ?></span>
			<span class="sui-description">
				<?php esc_html_e( 'Choose from daily, weekly or monthly email reports.', 'wphb' ); ?>
			</span>
		</div><!-- end col-third -->
		<div class="sui-box-settings-col-2">
			<label class="sui-toggle">
				<input type="hidden" name="scheduled-reports" value="0">
				<input type="checkbox" name="scheduled-reports" value="1" <?php checked( $reports_settings['enabled'] ); ?> id="chk1">
				<span class="sui-toggle-slider"></span>
			</label>
			<label for="chk1"><?php esc_html_e( 'Send scheduled uptime reports', 'wphb' ); ?></label>
			<div class="sui-border-frame sui-toggle-content schedule-box wphb-uptime-reporting-recipients <?php echo $reports_settings['enabled'] ? '' : 'sui-hidden'; ?>">
				<div class="sui-recipients">
					<label class="sui-label"><?php esc_html_e( 'Recipients', 'wphb' ); ?></label>
					<?php if ( count( $reports_settings['recipients'] ) ) : ?>
						<?php foreach ( $reports_settings['recipients'] as $key => $id ) : ?>
							<?php
							$input_value        = new stdClass();
							$input_value->name  = $id['name'];
							$input_value->email = $id['email'];
							$input_value        = wp_json_encode( $input_value );
							?>
							<div class="sui-recipient">
								<input data-id="<?php echo esc_attr( $key ); ?>" type="hidden" id="report-recipient" name="report-recipients[]" value="<?php echo esc_attr( $input_value ); ?>">
								<span class="sui-recipient-name"><?php echo esc_html( $id['name'] ); ?></span>
								<span class="sui-recipient-email"><?php echo esc_html( $id['email'] ); ?></span>
								<button type="button" class="sui-button-icon wphb-remove-recipient"><i class="sui-icon-trash" aria-hidden="true"></i></button>
							</div>
						<?php endforeach; ?>
					<?php else : ?>
						<div class="sui-notice sui-notice-warning sui-no-margin-top wphb-no-recipients">
							<p><?php esc_html_e( 'You haven\'t added any recipients yet.', 'wphb' ); ?></p>
						</div>
					<?php endif; ?>
				</div>
				<a class="sui-add-recipient sui-button sui-button-ghost" data-a11y-dialog-show="wphb-add-recipient-modal"><i class="sui-icon-plus" aria-hidden="true"></i>Add Recipient</a>
				<div class="sui-form-field">
					<label for="report-frequency" class="sui-label"><?php esc_html_e( 'Schedule', 'wphb' ); ?></label>
					<select name="report-frequency" id="report-frequency">
						<option <?php selected( 1, $reports_settings['frequency'] ); ?> value="1">
							<?php esc_html_e( 'Daily', 'wphb' ); ?>
						</option>
						<option <?php selected( 7, $reports_settings['frequency'] ); ?> value="7">
							<?php esc_html_e( 'Weekly', 'wphb' ); ?>
						</option>
						<option <?php selected( 30, $reports_settings['frequency'] ); ?> value="30">
							<?php esc_html_e( 'Monthly', 'wphb' ); ?>
						</option>
					</select>
				</div>
				<div class="sui-form-field days-container" data-type="week">
					<select name="report-day" id="report-day">
						<?php foreach ( WP_Hummingbird_Utils::get_days_of_week() as $day ) : ?>
							<option <?php selected( $day, $reports_settings['day'] ); ?> value="<?php echo esc_attr( $day ); ?>">
								<?php echo esc_html( ucfirst( $day ) ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="sui-form-field days-container sui-hidden" data-type="month">
					<select name="report-day-month" id="report-day">
						<?php
						$days = WP_Hummingbird_Utils::get_days_of_month();
						if ( ! in_array( $reports_settings['day'], $days ) ) {
							$reports_settings['day'] = rand( 1, 28 );
						}
						?>

						<?php foreach ( $days as $day ) : ?>
							<option <?php selected( $day, $reports_settings['day'] ); ?> value="<?php echo esc_attr( $day ); ?>">
								<?php echo esc_html( ucfirst( $day ) ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="sui-form-field">
					<select name="report-time" id="report-time">
						<?php foreach ( WP_Hummingbird_Utils::get_times() as $time ) : ?>
							<option <?php selected( $time, $send_time ); ?> value="<?php echo esc_attr( $time ); ?>">
								<?php echo esc_html( strftime( '%I:%M %p', strtotime( $time ) ) ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>
	</div>
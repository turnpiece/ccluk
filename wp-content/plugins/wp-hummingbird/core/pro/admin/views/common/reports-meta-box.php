<?php
/**
 * Common reports meta box for: performance reports, uptime reports, etc...
 *
 * @since 1.9.4
 *
 * @package Hummingbird
 *
 * @var bool   $enabled         Status of performance reports.
 * @var int    $frequency       Report frequency.
 * @var string $module          Report module.
 * @var string $notice_class    Class for the notice.
 * @var string $notice_message  Message for the notice.
 * @var array  $recipients      Recipients list.
 * @var string $send_day        Report send day.
 * @var string $send_time       Report send time.
 */

if ( 'performance' === $module ) {
	$p_text = __( 'Configure Hummingbird to automatically and regularly test your website and email you reports.', 'wphb' );
} elseif ( 'uptime' === $module ) {
	$p_text = __( 'Enable scheduled email reports direct to recipient inboxes of your choice. The report will include response time data and any downtime logs in the selected period.', 'wphb' );
}
?>

<p><?php echo esc_html( $p_text ); ?></p>

<div class="sui-notice sui-notice-<?php echo esc_attr( $notice_class ); ?>">
	<p><?php echo esc_html( $notice_message ); ?></p>
</div>

<form method="post" class="wphb-report-settings" id="wphb-<?php echo esc_attr( $module ); ?>-reporting" data-module="<?php echo esc_attr( $module ); ?>">
	<div class="sui-box-settings-row">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label">
				<?php esc_html_e( 'Configure', 'wphb' ); ?>
			</span>
			<span class="sui-description">
				<?php esc_html_e( 'Choose from daily, weekly or monthly email reports.', 'wphb' ); ?>
			</span>
		</div>
		<div class="sui-box-settings-col-2">
			<label class="sui-toggle">
				<input type="hidden" name="scheduled-reports" value="0"/>
				<input type="checkbox" name="scheduled-reports" value="1" id="chk1" <?php checked( 1, $enabled ); ?>/>
				<span class="sui-toggle-slider"></span>
			</label>
			<label for="chk1">
				<?php
				printf( /* translators: %s: module name */
					esc_html__( 'Send scheduled %s reports', 'wphb' ),
					esc_html( $module )
				);

				?>
			</label>
			<div class="sui-border-frame sui-toggle-content schedule-box <?php echo $enabled ? '' : 'sui-hidden'; ?>">
				<div class="sui-recipients">
					<label class="sui-label"><?php esc_html_e( 'Recipients', 'wphb' ); ?></label>
					<?php if ( count( $recipients ) ) : ?>
						<div class="sui-notice sui-notice-warning sui-no-margin-top wphb-no-recipients sui-hidden">
							<p><?php esc_html_e( "You've removed all recipients. If you save without a recipient, we'll automatically turn off reports.", 'wphb' ); ?></p>
						</div>
						<?php foreach ( $recipients as $key => $id ) : ?>
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
								<button data-id="<?php echo esc_attr( $key ); ?>" type="button" class="sui-button-icon wphb-remove-recipient"><i class="sui-icon-trash" aria-hidden="true"></i></button>
							</div>
						<?php endforeach; ?>
					<?php else : ?>
						<div class="sui-notice sui-notice-warning sui-no-margin-top wphb-no-recipients">
							<p><?php esc_html_e( "You've removed all recipients. If you save without a recipient, we'll automatically turn off reports.", 'wphb' ); ?></p>
						</div>
					<?php endif; ?>
				</div>
				<a class="sui-add-recipient sui-button sui-button-ghost" data-a11y-dialog-show="wphb-add-recipient-modal">
					<i class="sui-icon-plus" aria-hidden="true"></i>
					<?php esc_html_e( 'Add Recipient', 'wphb' ); ?>
				</a>

				<div class="sui-form-field">
					<label for="report-frequency" class="sui-label"><?php esc_html_e( 'Schedule', 'wphb' ); ?></label>
					<select name="report-frequency" id="report-frequency">
						<option <?php selected( 1, $frequency ); ?> value="1">
							<?php esc_html_e( 'Daily', 'wphb' ); ?>
						</option>
						<option <?php selected( 7, $frequency ); ?> value="7">
							<?php esc_html_e( 'Weekly', 'wphb' ); ?>
						</option>
						<option <?php selected( 30, $frequency ); ?> value="30">
							<?php esc_html_e( 'Monthly', 'wphb' ); ?>
						</option>
					</select>
				</div>

				<div class="sui-form-field days-container" data-type="week">
					<label class="sui-label" for="report-day">
						<?php esc_html_e( 'Day of the week', 'wphb' ); ?>
					</label>
					<select name="report-day" id="report-day">
						<?php foreach ( WP_Hummingbird_Utils::get_days_of_week() as $day ) : ?>
							<option <?php selected( $day, $send_day ); ?> value="<?php echo esc_attr( $day ); ?>">
								<?php echo esc_html( ucfirst( $day ) ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="sui-form-field days-container sui-hidden" data-type="month">
					<label class="sui-label" for="report-day">
						<?php esc_html_e( 'Day of the month', 'wphb' ); ?>
					</label>
					<select name="report-day-month" id="report-day">
						<?php
						$days = WP_Hummingbird_Utils::get_days_of_month();
						if ( ! in_array( $send_day, $days ) ) {
							$send_day = rand( 1, 28 );
						}
						?>

						<?php foreach ( $days as $day ) : ?>
							<option <?php selected( $day, $send_day ); ?> value="<?php echo esc_attr( $day ); ?>">
								<?php echo esc_html( ucfirst( $day ) ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="sui-form-field">
					<label class="sui-label" for="report-time">
						<?php esc_html_e( 'Time of day', 'wphb' ); ?>
					</label>
					<select name="report-time" id="report-time">
						<?php foreach ( WP_Hummingbird_Utils::get_times() as $time ) : ?>
							<option <?php selected( $time, $send_time ); ?> value="<?php echo esc_attr( $time ); ?>">
								<?php echo esc_html( strftime( '%I:%M %p', strtotime( $time ) ) ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div><!-- end sui-border-frame -->
		</div>
	</div>
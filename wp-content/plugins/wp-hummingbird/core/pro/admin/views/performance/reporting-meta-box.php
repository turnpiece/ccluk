<?php
/**
 * Performance tests reporting meta box.
 *
 * @package Hummingbird
 *
 * @var int    $frequency     Report frequency.
 * @var bool   $notification  Status of performance reports.
 * @var array  $recipients    Recipients list.
 * @var string $send_day      Report send day.
 * @var string $send_time     Report send time.
 */

?>

<p><?php esc_html_e( 'Configure Hummingbird to automatically and regularly test your website and email you reports.', 'wphb' ); ?></p>
<form method="post" class="scan-frm scan-settings">
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
				<input type="checkbox" name="scheduled-reports" value="1" id="chk1" <?php checked( 1, $notification ); ?>/>
				<span class="sui-toggle-slider"></span>
			</label>
			<label for="chk1"><?php esc_html_e( 'Run regular scans & reports', 'wphb' ); ?></label>
			<div class="sui-border-frame sui-toggle-content schedule-box <?php echo $notification ? '' : 'sui-hidden'; ?>">
				<div class="sui-recipients">
					<label class="sui-label"><?php esc_html_e( 'Recipients', 'wphb' ); ?></label>
					<?php foreach ( $recipients as $key => $id ) : ?>
						<?php
						$input_value        = new stdClass();
						$input_value->name  = $id['name'];
						$input_value->email = $id['email'];
						$input_value        = wp_json_encode( $input_value );
						?>
						<div class="sui-recipient">
							<input data-id="<?php echo esc_attr( $key ); ?>" type="hidden" id="report-recipient" name="report-recipients[]" value="<?php echo esc_attr( $input_value ); ?>">
							<span class="sui-recipient-name">
								<?php echo esc_html( $id['name'] ); ?>
							</span>
							<span class="sui-recipient-email"><?php echo esc_html( $id['email'] ); ?></span>
							<button data-id="<?php echo esc_attr( $key ); ?>" type="button" class="sui-button-icon wphb-remove-recipient"><i class="sui-icon-trash" aria-hidden="true"></i></button>
						</div>
					<?php endforeach; ?>
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
				<div class="sui-form-field days-container">
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

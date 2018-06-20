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

<form method="post" class="scan-frm scan-settings">
	<div class="sui-box-settings-row">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label">
				<?php esc_html_e( 'Schedule Scans', 'wphb' ); ?>
			</span>
			<span class="sui-description">
				<?php esc_html_e( 'Configure Hummingbird to automatically and regularly test your website and email you reports.', 'wphb' ); ?>
			</span>
		</div>
		<div class="sui-box-settings-col-2">
			<label class="sui-toggle">
				<input type="hidden" name="email-notifications" value="0"/>
				<input type="checkbox" name="email-notifications" value="1" id="chk1" <?php checked( 1, $notification ); ?>/>
				<span class="sui-toggle-slider"></span>
			</label>
			<label for="chk1"><?php esc_html_e( 'Run regular scans & reports', 'wphb' ); ?></label>
			<div class="sui-border-frame schedule-box">
				<div class="sui-form-field">
					<label for="email-frequency" class="sui-label"><?php esc_html_e( 'Frequency', 'wphb' ); ?></label>
					<select name="email-frequency" id="email-frequency">
						<option <?php selected( 1, $frequency ); ?> value="1">
							<?php esc_html_e( 'Daily', 'wphb' ); ?>
						</option>
						<option <?php selected( 7, $frequency ); ?> value="7">
							<?php esc_html_e( 'Weekly', 'wphb' ); ?>
						</option>
						<option <?php selected( 30, $frequency ) ?> value="30">
							<?php esc_html_e( 'Monthly', 'wphb' ); ?>
						</option>
					</select>
				</div>
				<div class="sui-form-field days-container">
					<label class="sui-label" for="email-day">
						<?php esc_html_e( 'Day of the week', 'wphb' ); ?>
					</label>
					<select name="email-day" id="email-day">
						<?php foreach ( WP_Hummingbird_Utils::get_days_of_week() as $day ) : ?>
							<option <?php selected( $day, $send_day ); ?> value="<?php echo esc_attr( $day ); ?>">
								<?php echo esc_html( ucfirst( $day ) ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="sui-form-field">
					<label class="sui-label" for="email-time">
						<?php esc_html_e( 'Time of day', 'wphb' ); ?>
					</label>
					<select name="email-time" id="email-time">
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

	<div class="sui-box-settings-row">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label">
				<?php esc_html_e( 'Email Recipients', 'wphb' ); ?>
			</span>
			<span class="sui-description">
				<?php esc_html_e( 'Choose which of your website’s users will receive the test results in their inbox.', 'wphb' ); ?>
			</span>
		</div>
		<div class="sui-box-settings-col-2">
			<div class="receipt">
				<div class="recipients">
					<?php foreach ( $recipients as $key => $id ) : ?>
						<?php
						if ( ! $uid = get_user_by( 'email', $id['email'] ) ) {
							$uid = $id['email'];
						}

						$input_value = new stdClass();
						$input_value->name = $id['name'];
						$input_value->email = $id['email'];
						$input_value = wp_json_encode( $input_value );
						?>
						<div class="recipient">
							<input type="hidden" id="scan_recipient" name="email-recipients[]" value="<?php echo esc_attr( $input_value ); ?>">
							<span class="name">
								<?php echo get_avatar( $uid, 30 ); ?>
								<span><?php echo esc_html( $id['name'] ); ?></span>
							</span>
							<span class="email"><?php echo esc_html( $id['email'] ); ?></span>
							<a data-id="<?php echo esc_attr( $key ); ?>" class="remove wphb-remove-recipient float-r" href="#">
								<i class="sui-icon-close"></i>
							</a>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="add-recipient sui-form-field">
					<input
						class=" sui-form-control"
						placeholder="<?php esc_attr_e( 'First Name', 'wphb' ); ?>"
						name="first-name"
						id="wphb-first-name"
						type="text"
					/>
					<input
						class=" sui-form-control"
						data-empty-msg="<?php esc_attr_e( 'Empty email', 'wphb' ); ?>"
						placeholder="<?php esc_attr_e( 'Email Address', 'wphb' ); ?>"
						name="term"
						id="wphb-username-search"
						type="email"
					/>
					<button type="submit" <?php disabled( ! WP_Hummingbird_Utils::is_member() ); ?> id="add-receipt" class="sui-button sui-button-primary sui-button-lg <?php echo ( ! WP_Hummingbird_Utils::is_member() ) ? 'disabled' : ''; ?>">
						<i class="sui-icon-plus"></i>
					</button>
				</div>
			</div><!-- end receipt -->
		</div><!-- end sui-box-settings-col-2 -->
	</div>
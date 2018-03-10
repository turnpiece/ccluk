<form method="post" class="scan-frm scan-settings">
	<div class="box-content settings-form <?php echo ( ! WP_Hummingbird_Utils::is_member() ) ? 'disabled' : ''; ?>">
		<?php if ( ! WP_Hummingbird_Utils::is_member() ) : ?>
			<div class="wphb-disabled-overlay"></div>
		<?php endif; ?>

		<div class="row with-bottom-border">
			<div class="col-third">
				<strong><?php esc_html_e( 'Schedule Scans', 'wphb' ) ?></strong>
				<span class="sub">
					<?php esc_html_e( 'Configure Hummingbird to automatically and regularly test your website and email you reports.', 'wphb' ); ?>
				</span>
			</div><!-- end col-third -->
			<div class="col-two-third">
				<span class="toggle">
					<input type="hidden" name="email-notifications" value="0"/>
					<input type="checkbox" class="toggle-checkbox" name="email-notifications" value="1"
							id="chk1" <?php checked( 1, $notification ); ?> <?php disabled( ! WP_Hummingbird_Utils::is_member() ); ?>/>
					<label class="toggle-label small" for="chk1"></label>
				</span>
				<label><?php esc_html_e( 'Run regular scans & reports', 'wphb' ) ?></label>
				<div class="clear mline"></div>
				<div class="wphb-border-frame with-padding schedule-box">
					<strong><?php esc_html_e( 'Schedule', 'wphb' ) ?></strong>
					<label for="email-frequency"><?php esc_html_e( 'Frequency', 'wphb' ) ?></label>
					<select name="email-frequency" id="email-frequency" <?php disabled( ! WP_Hummingbird_Utils::is_member() ); ?>>
						<option <?php selected( 1, $frequency ) ?> value="1">
							<?php esc_html_e( 'Daily', 'wphb' ) ?>
						</option>
						<option <?php selected( 7, $frequency ) ?> value="7">
							<?php esc_html_e( 'Weekly', 'wphb' ) ?>
						</option>
						<option <?php selected( 30, $frequency ) ?> value="30">
							<?php esc_html_e( 'Monthly', 'wphb' ) ?>
						</option>
					</select>
					<div class="days-container">
						<label for="email-day"><?php esc_html_e( 'Day of the week', 'wphb' ) ?></label>
						<select name="email-day" id="email-day" <?php disabled( ! WP_Hummingbird_Utils::is_member() ); ?>>
							<?php foreach ( WP_Hummingbird_Utils::get_days_of_week() as $day ) : ?>
								<option <?php selected( $day, $send_day ) ?>
									value="<?php echo $day ?>"><?php echo ucfirst( $day ) ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<label for="email-time"><?php esc_html_e( 'Time of day', 'wphb' ) ?></label>
					<select name="email-time" id="email-time" <?php disabled( ! WP_Hummingbird_Utils::is_member() ); ?>>
						<?php foreach ( WP_Hummingbird_Utils::get_times() as $time ) : ?>
							<option <?php selected( $time, $send_time ) ?> value="<?php echo $time ?>">
								<?php echo strftime( '%I:%M %p', strtotime( $time ) ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div><!-- end well -->
			</div><!-- end col-two-third -->
		</div><!-- end row -->

		<div class="row">
			<div class="col-third">
				<strong><?php esc_html_e( 'Email Recipients', 'wphb' ) ?></strong>
				<span class="sub">
					<?php esc_html_e( 'Choose which of your website’s users will receive the test results in their inbox.', 'wphb' ) ?>
				</span>
			</div><!-- end col-third -->
			<div class="col-two-third">
				<div class="receipt">
					<div class="recipients">
						<?php foreach ( $recipients as $key => $id ) : ?>
							<?php
							$user = get_user_by( 'email', $id['email'] );
							if ( ! $user ) {
								$avatar = get_avatar( 0, 30 );
							} else {
								$avatar = get_avatar( $user->ID, 30 );
							}
							$email = $id['email'];
							$name = $id['name'];

							$input_value = new stdClass();
							$input_value->name = $name;
							$input_value->email = $email;
							$input_value = wp_json_encode( $input_value );
							?>
							<div class="recipient">
								<input type="hidden" id="scan_recipient" name="email-recipients[]" value="<?php echo esc_attr( $input_value ); ?>">
								<span class="name">
									<?php echo $avatar; ?>
									<span><?php echo $name; ?></span>
								</span>
								<span class="email"><?php echo $email; ?></span>
								<a data-id="<?php echo esc_attr( $key ) ?>"
								   class="remove wphb-remove-recipient float-r <?php echo ( ! WP_Hummingbird_Utils::is_member() ) ? 'disabled' : ''; ?>"
								   href="#"><i class="dev-icon dev-icon-cross"></i></a>
							</div>
						<?php endforeach; ?>
					</div>
					<div class="add-recipient">
						<input
							placeholder="<?php esc_attr_e( 'First Name', 'wphb' ); ?>"
							name="first-name"
							id="wphb-first-name"
							type="text"
							<?php disabled( ! WP_Hummingbird_Utils::is_member() ); ?>
						/>
						<input
							data-empty-msg="<?php esc_attr_e( 'Empty email', 'wphb' ); ?>"
							placeholder="<?php esc_attr_e( 'Email Address', 'wphb' ); ?>"
							name="term"
							id="wphb-username-search"
							type="email"
							<?php disabled( ! WP_Hummingbird_Utils::is_member() ); ?>
						/>
						<button type="submit" <?php disabled( ! WP_Hummingbird_Utils::is_member() ); ?> id="add-receipt" class="button button-notice button-large <?php echo ( ! WP_Hummingbird_Utils::is_member() ) ? 'disabled' : ''; ?>">
							<i class="wdv-icon wdv-icon-plus"></i>
						</button>
					</div>
				</div><!-- end receipt -->
			</div><!-- end col-two-third -->
		</div><!-- end row -->
	</div>
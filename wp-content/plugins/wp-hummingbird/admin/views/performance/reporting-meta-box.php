<?php
/**
 * Performance tests reporting meta box.
 *
 * @package Hummingbird
 */

?>

<div class="sui-box-settings-row sui-disabled">
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
			<input type="hidden" name="email-notifications" value="0" />
			<input type="checkbox" name="email-notifications" value="1" id="chk1" />
			<span class="sui-toggle-slider"></span>
		</label>
		<label for="chk1">
			<?php esc_html_e( 'Run regular scans & reports', 'wphb' ); ?>
		</label>
		<div class="sui-border-frame schedule-box">
			<span class="sui-settings-label">
				<?php esc_html_e( 'Schedule', 'wphb' ); ?>
			</span>
			<div class="sui-form-field">
				<label for="email-frequency" class="sui-label">
					<?php esc_html_e( 'Frequency', 'wphb' ); ?>
				</label>
				<select name="email-frequency" id="email-frequency">
					<option value="7"><?php esc_html_e( 'Weekly', 'wphb' ); ?></option>
				</select>
			</div>
			<div class="sui-form-field days-container">
				<label class="sui-label" for="email-day">
					<?php esc_html_e( 'Day of the week', 'wphb' ); ?>
				</label>
				<select name="email-day" id="email-day">
					<?php foreach ( WP_Hummingbird_Utils::get_days_of_week() as $day ) : ?>
						<option value="<?php echo esc_attr( $day ); ?>">
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
						<option value="<?php echo esc_attr( $time ); ?>">
							<?php echo strftime( '%I:%M %p', strtotime( $time ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div><!-- end schedule-box -->
	</div>
</div>

<div class="sui-box-settings-row sui-disabled">
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
				<button type="submit" id="add-receipt" class="sui-button sui-button-primary sui-button-lg">
					<i class="sui-icon-plus"></i>
				</button>
			</div>
		</div><!-- end receipt -->
	</div><!-- end sui-box-settings-col-2 -->
</div>

<div class="sui-box-settings-row sui-upsell-row">
	<img class="sui-image sui-upsell-image"
		 src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hummingbird-upsell-minify.png' ); ?>"
		 srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hummingbird-upsell-minify@2x.png' ); ?> 2x"
		 alt="<?php esc_attr_e( 'Scheduled automated performance tests', 'wphb' ); ?>">

	<div class="sui-upsell-notice">
		<p>
			<?php printf(
				__( 'Schedule automated performance tests and receive email reports direct to your inbox. You\'ll get Hummingbird Pro plus 100+ WPMU DEV plugins & 24/7 WP support. <a href="%s" target="_blank">Try Pro for FREE today!</a>', 'wphb' ),
				WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_test_upsell_link' )
			); ?>
		</p>
	</div>
</div>
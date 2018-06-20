<?php
/**
 * Settings meta box.
 *
 * @since 1.8.1
 * @package Hummingbird
 *
 * @var bool   $control    Cache control.
 * @var string $detection  File change detection. Accepts: 'manual', 'auto' and 'none'.
 */
?>

<div class="sui-box-settings-row">
	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'Admin Cache Control', 'wphb' ); ?></span>
		<span class="sui-description">
			<?php esc_html_e( 'This feature adds a Clear Cache button to the WordPress Admin Top
			bar area for admin users.', 'wphb' ); ?>
		</span>
	</div>
	<div class="sui-box-settings-col-2">
		<label class="sui-toggle">
			<input type="checkbox" class="toggle-checkbox" name="cc_button" id="cc_button" <?php checked( $control ); ?>>
			<span class="sui-toggle-slider"></span>
		</label>
		<label for="cc_button"><?php esc_html_e( 'Show Clear Cache button in Admin area', 'wphb' ); ?></label>
	</div>
</div>

<div class="sui-box-settings-row">
	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'File Change Detection', 'wphb' ); ?></span>
		<span class="sui-description">
			<?php esc_html_e( 'Choose how you want Hummingbird to react when we detect changes
			to your file structure.', 'wphb' ); ?>
		</span>
	</div>
	<div class="sui-box-settings-col-2">
		<label class="sui-radio">
			<input type="radio" name="detection" id="manual" value="manual" <?php checked( $detection, 'manual' ); ?>>
			<span aria-hidden="true"></span>
			<span class="sui-description"><?php esc_html_e( 'Manual Notice', 'wphb' ); ?></span>
		</label>
		<span class="sui-description sui-radio-description">
			<?php esc_html_e( 'Get a global notice inside your WordPress Admin area anytime your
			cache needs clearing.', 'wphb' ); ?>
		</span>
		<label class="sui-radio">
			<input type="radio" name="detection" id="automatic" value="auto" <?php checked( $detection, 'auto' ); ?>>
			<span aria-hidden="true"></span>
			<span class="sui-description"><?php esc_html_e( 'Automatic', 'wphb' ); ?></span>
		</label>
		<span class="sui-description sui-radio-description">
			<?php esc_html_e( 'Set Hummingbird to automatically clear your cache instead of
			prompting you to do it manually.', 'wphb' ); ?>
		</span>

		<label class="sui-radio">
			<input type="radio" name="detection" id="none" value="none" <?php checked( $detection, 'none' ); ?>>
			<span aria-hidden="true"></span>
			<span class="sui-description"><?php esc_html_e( 'None', 'wphb' ); ?></span>
		</label>
		<span class="sui-description sui-radio-description">
				<?php esc_html_e( 'Disable warnings in your WP Admin area.', 'wphb' ); ?>
		</span>
	</div>
</div>
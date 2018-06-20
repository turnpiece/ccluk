<?php
/**
 * Advanced tools database cleanup settings meta box.
 *
 * @package Hummingbird
 * @since 1.8
 *
 * @var array $fields     Array of tables used to build checkboxes.
 * @var int   $frequency  Cleanup frequency.
 * @var bool  $schedule   If schedule is enabled or disabled.
 */

?>

<div class="sui-box-settings-row">
	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'Schedule Cleanups', 'wphb' ) ?></span>
		<span class="sui-description">
			<?php esc_html_e( 'Schedule Hummingbird to automatically clean your database daily, weekly or monthly.', 'wphb' ); ?>
		</span>
	</div><!-- end col-third -->
	<div class="sui-box-settings-col-2">
		<label class="sui-toggle sui-tooltip sui-tooltip-top-right" data-tooltip="<?php esc_attr_e( 'Enabled scheduled cleanups', 'wphb' ); ?>">
			<input type="checkbox" name="scheduled_cleanup" id="scheduled_cleanup" <?php checked( $schedule ); ?>>
			<span class="sui-toggle-slider"></span>
		</label>
		<label for="scheduled_cleanup"><?php esc_html_e( 'Enabled scheduled cleanups', 'wphb' ); ?></label>

		<div class="sui-border-frame with-padding schedule-box <?php echo $schedule ? '' : 'hidden' ?>">
			<div class="sui-form-field">
				<label class="sui-label" for="cleanup_frequency"><?php esc_html_e( 'Frequency', 'wphb' ); ?></label>
				<select name="cleanup_frequency" id="cleanup_frequency">
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
			</div>
			<div class="sui-form-field">
				<label class="sui-label" for="included-tables"><?php esc_html_e( 'Included Tables', 'wphb' ); ?></label>
				<div id="included-tables" class="included-tables">
					<?php foreach ( $fields as $type => $field ) : ?>
						<label for="<?php echo esc_attr( $type ); ?>" class="sui-checkbox">
							<input type="checkbox" name="<?php echo esc_attr( $type ); ?>" id="<?php echo esc_attr( $type ); ?>" <?php checked( $field['checked'] ); ?>>
							<span aria-hidden="true"></span>
							<span class="sui-description sui-description-sm"><?php echo esc_html( $field['title'] ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
</div>